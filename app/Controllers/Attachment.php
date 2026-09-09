<?php

namespace App\Controllers;

use App\Enums\FolderStatus;
use App\Models\DocumentAttachmentModel;
use App\Models\DocumentModel;
use App\Models\EvaluationRoutingModel;
use CodeIgniter\RESTful\ResourceController;

class Attachment extends BaseController
{
    /**
     * POST /attachments/upload
     * Upload an evidence file (MOV) attached to an accomplishment row.
     */
    public function upload()
    {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $docId = $this->request->getPost('document_id');
        $rowId = $this->request->getPost('row_id');

        if (!$docId || !$rowId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing document or row identifier.']);
        }

        $documentModel = new DocumentModel();
        $doc = $documentModel->getDocumentWithFolderInfo($docId);

        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Document not found.']);
        }

        // Only document owner or Admin can upload MOVs
        if ($doc['owner_id'] != $userId && $sysRole !== 'Admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Only the document owner can attach evidence.']);
        }

        // Check if cycle is archived/frozen
        if (!empty($doc['folder_deleted_at'])) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is archived and frozen.']);
        }

        // Check if folder is in the Evaluation Phase
        $status = $doc['folder_status'] ?? '';
        $evalPhaseStatuses = [
            FolderStatus::SUBMITTED->value,
            FolderStatus::TO_EVALUATE->value,
            FolderStatus::REEVALUATE->value,
            FolderStatus::EVALUATED->value,
            FolderStatus::APPROVED->value,
            FolderStatus::TWG_APPROVED->value,
            FolderStatus::TWG_DISAPPROVED->value,
            FolderStatus::UNEVALUATED->value,
        ];

        $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
        $now = date('Y-m-d H:i:s');
        $targetEndCol = $ownerDocType . '_target_end';
        $tEnd = $doc[$targetEndCol] ?? null;
        $isPastTargetDate = (!empty($tEnd) && $now > $tEnd);

        $isEvaluationPhase = in_array($status, $evalPhaseStatuses) || $isPastTargetDate;

        if (!$isEvaluationPhase && $sysRole !== 'Admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Means of Verification (MOV) evidence can only be attached during the Evaluation Phase.'
            ]);
        }

        if ($sysRole !== 'Admin' && !in_array($status, [FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value]) && !$isPastTargetDate) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Evidence attachments can only be uploaded while evaluating accomplishments.'
            ]);
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => $file ? $file->getErrorString() : 'No file uploaded.'
            ]);
        }

        // Validate MIME / extension
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'docx', 'doc'];
        $ext = strtolower($file->getClientExtension());

        if (!in_array($ext, $allowedExtensions)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Invalid file format. Allowed formats: PDF, PNG, JPG, WEBP, DOC, DOCX.'
            ]);
        }

        // Max 15MB
        if ($file->getSizeByUnit('mb') > 15) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'File size exceeds the 15MB limit.'
            ]);
        }

        $uploadDir = WRITEPATH . 'uploads/movs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $clientName = $file->getClientName();
        $newName = 'mov_' . $docId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($uploadDir, $newName);

        $attachmentModel = new DocumentAttachmentModel();
        $data = [
            'document_id' => $docId,
            'row_id'      => $rowId,
            'file_name'   => $clientName,
            'file_path'   => 'uploads/movs/' . $newName,
            'file_type'   => $file->getClientMimeType() ?: 'application/octet-stream',
            'file_size'   => $file->getSize(),
            'uploaded_by' => $userId,
        ];

        $insertedId = $attachmentModel->insert($data);
        $saved = $attachmentModel->find($insertedId);
        $saved['formatted_size'] = $attachmentModel->formatBytes($saved['file_size']);
        $saved['uploader_name'] = trim((session()->get('first_name') ?? '') . ' ' . (session()->get('last_name') ?? ''));

        audit_log('MOV_UPLOADED', 'EVIDENCE', 'document_attachment', (int) $insertedId, "Evidence attached: {$clientName} to document #{$docId} (row: {$rowId})");

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Evidence attached successfully.',
            'attachment' => $saved
        ]);
    }

    /**
     * DELETE /attachments/{id}
     * Remove an evidence attachment.
     */
    public function delete($id = null)
    {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$userId || !$id) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $attachmentModel = new DocumentAttachmentModel();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Attachment not found.']);
        }

        $documentModel = new DocumentModel();
        $doc = $documentModel->getDocumentWithFolderInfo($attachment['document_id']);

        // Check if cycle is archived
        if (!empty($doc['folder_deleted_at'])) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Cannot remove attachments from an archived cycle.']);
        }

        // Check if in Evaluation Phase
        $status = $doc['folder_status'] ?? '';
        $evalPhaseStatuses = [
            FolderStatus::SUBMITTED->value,
            FolderStatus::TO_EVALUATE->value,
            FolderStatus::REEVALUATE->value,
            FolderStatus::EVALUATED->value,
            FolderStatus::APPROVED->value,
            FolderStatus::TWG_APPROVED->value,
            FolderStatus::TWG_DISAPPROVED->value,
            FolderStatus::UNEVALUATED->value,
        ];
        $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
        $now = date('Y-m-d H:i:s');
        $targetEndCol = $ownerDocType . '_target_end';
        $tEnd = $doc[$targetEndCol] ?? null;
        $isPastTargetDate = (!empty($tEnd) && $now > $tEnd);

        $isEvaluationPhase = in_array($status, $evalPhaseStatuses) || $isPastTargetDate;

        if (!$isEvaluationPhase && $sysRole !== 'Admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Attachments cannot be modified outside the Evaluation Phase.'
            ]);
        }

        // Permission: Uploader, Document Owner, or Admin
        $isUploader = ($attachment['uploaded_by'] == $userId);
        $isOwner    = ($doc && $doc['owner_id'] == $userId);
        $isAdmin    = ($sysRole === 'Admin');

        if (!$isUploader && !$isOwner && !$isAdmin) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'You do not have permission to delete this attachment.']);
        }

        $attachmentModel->delete($id);

        audit_log('MOV_DELETED', 'EVIDENCE', 'document_attachment', (int) $id, "Evidence deleted: {$attachment['file_name']} from document #{$attachment['document_id']}");

        // Optionally delete physical file if not needed
        $filePath = WRITEPATH . $attachment['file_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Attachment removed.'
        ]);
    }

    /**
     * GET /attachments/view/{id}
     * Stream file inline for in-app browser preview (PDF, Images).
     */
    public function view($id = null)
    {
        return $this->serveFile($id, 'inline');
    }

    /**
     * GET /attachments/download/{id}
     * Download file as attachment.
     */
    public function download($id = null)
    {
        return $this->serveFile($id, 'attachment');
    }

    /**
     * GET /attachments/row/{docId}/{rowId}
     * Get list of attachments for a specific row.
     */
    public function listByRow($docId = null, $rowId = null)
    {
        $userId = session()->get('user_id');
        if (!$userId || !$docId || !$rowId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid parameters.']);
        }

        $attachmentModel = new DocumentAttachmentModel();
        $all = $attachmentModel->getAttachmentsGroupedByRow((int)$docId);
        $rowAttachments = $all[$rowId] ?? [];

        return $this->response->setJSON([
            'status'      => 'success',
            'attachments' => $rowAttachments
        ]);
    }

    /**
     * Internal helper to verify authorization and stream the file.
     */
    protected function serveFile($id, string $disposition = 'inline')
    {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$userId || !$id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $attachmentModel = new DocumentAttachmentModel();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $documentModel = new DocumentModel();
        $doc = $documentModel->getDocumentWithFolderInfo($attachment['document_id']);

        if (!$doc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Authorization check: Owner, Admin, TWG, or assigned Evaluator
        $docOwnerId = $doc['owner_id'];
        $hasAccess  = ($docOwnerId == $userId) || in_array($sysRole, ['Admin', 'TWG']);

        if (!$hasAccess) {
            $routingModel = new EvaluationRoutingModel();
            $hasAccess = $routingModel->where('folder_id', $doc['document_folder_id'])
                                      ->where('evaluator_id', $userId)
                                      ->countAllResults() > 0;
        }

        if (!$hasAccess) {
            return $this->response->setStatusCode(403)->setBody('Access denied to this document attachment.');
        }

        $filePath = WRITEPATH . $attachment['file_path'];
        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Attachment file not found on disk.');
        }

        $mimeType = $attachment['file_type'] ?: 'application/octet-stream';
        $fileName = $attachment['file_name'] ?: 'evidence';

        // Clean headers and stream
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $disposition . '; filename="' . addslashes($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=86400');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }
}

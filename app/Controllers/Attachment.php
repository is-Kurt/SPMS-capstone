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

        $isRubricUpload = ($rowId === 'rubric');

        if ($isRubricUpload) {
            // Rubric attachments can be uploaded during the Target-Setting Phase (Draft / Pending Approval)
            // or whenever target editing is permissible
            $targetUploadStatuses = [
                FolderStatus::DRAFT->value,
                FolderStatus::PENDING_TARGET_APPROVAL->value,
                FolderStatus::TARGET_APPROVED->value,
            ];
            $canUploadRubric = in_array($status, $targetUploadStatuses) || ($sysRole === 'Admin');
            if (!$canUploadRubric && $sysRole !== 'Admin') {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Rubric documents can only be attached while drafting or finalizing targets.'
                ]);
            }
        } else {
            // Check if folder is in the Evaluation Phase for MOV evidence
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

            if ($sysRole !== 'Admin' && !in_array($status, [FolderStatus::DRAFT->value, FolderStatus::TARGET_APPROVED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value]) && !$isPastTargetDate) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Evidence attachments can only be uploaded while evaluating accomplishments.'
                ]);
            }
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => $file ? $file->getErrorString() : 'No file uploaded.'
            ]);
        }

        // Validate MIME / extension
        $allowedExtensions = $isRubricUpload
            ? ['pdf', 'xlsx', 'xls', 'docx', 'doc', 'csv', 'png', 'jpg', 'jpeg']
            : ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'docx', 'doc'];
        $ext = strtolower($file->getClientExtension());

        if (!in_array($ext, $allowedExtensions)) {
            $allowedMsg = $isRubricUpload
                ? 'Allowed formats: PDF, XLSX, XLS, DOC, DOCX, CSV, PNG, JPG.'
                : 'Allowed formats: PDF, PNG, JPG, WEBP, DOC, DOCX.';
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Invalid file format. ' . $allowedMsg
            ]);
        }

        // Max 20MB
        if ($file->getSizeByUnit('mb') > 20) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'File size exceeds the 20MB limit.'
            ]);
        }

        $subDir = $isRubricUpload ? 'uploads/rubrics/' : 'uploads/movs/';
        $uploadDir = WRITEPATH . $subDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $clientName = $file->getClientName();
        $prefix = $isRubricUpload ? 'rubric_' : 'mov_';
        $newName = $prefix . $docId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        // Read raw file content and encrypt with AES-256
        $rawContents = file_get_contents($file->getTempName());
        try {
            $encrypter = \Config\Services::encrypter();
            $dataToSave = $encrypter->encrypt($rawContents);
        } catch (\Throwable $e) {
            log_message('error', 'Attachment encryption error: ' . $e->getMessage());
            $dataToSave = $rawContents;
        }

        file_put_contents($uploadDir . $newName, $dataToSave);

        $attachmentModel = new DocumentAttachmentModel();

        // If uploading a rubric, soft delete any older rubric attachments for this document
        // so the single current active rubric is clean
        if ($isRubricUpload) {
            $existingRubrics = $attachmentModel->where('document_id', $docId)
                                               ->where('row_id', 'rubric')
                                               ->where('deleted_at IS NULL')
                                               ->findAll();
            foreach ($existingRubrics as $ex) {
                $attachmentModel->delete($ex['id']);
            }
        }
        $data = [
            'document_id' => $docId,
            'row_id'      => $rowId,
            'file_name'   => $clientName,
            'file_path'   => $subDir . $newName,
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
     * POST /attachments/add-link
     * Attach a Google Drive or external cloud web link as evidence (MOV).
     */
    public function addLink()
    {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $docId    = $this->request->getPost('document_id');
        $rowId    = $this->request->getPost('row_id');
        $linkUrl  = trim((string)$this->request->getPost('link_url'));
        $linkName = trim((string)$this->request->getPost('link_name'));

        if (!$docId || !$rowId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing document or row identifier.']);
        }

        if (empty($linkUrl)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Please provide a valid link URL.']);
        }

        // Validate URL format
        if (!filter_var($linkUrl, FILTER_VALIDATE_URL) || (!str_starts_with($linkUrl, 'http://') && !str_starts_with($linkUrl, 'https://'))) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'The link must be a valid HTTP or HTTPS web address (e.g., https://drive.google.com/...).']);
        }

        $documentModel = new DocumentModel();
        $doc = $documentModel->getDocumentWithFolderInfo($docId);

        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Document not found.']);
        }

        // Only document owner or Admin can attach links
        if ($doc['owner_id'] != $userId && $sysRole !== 'Admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Only the document owner can attach evidence.']);
        }

        // Check if cycle is archived/frozen
        if (!empty($doc['folder_deleted_at'])) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is archived and frozen.']);
        }

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

        $isRubricUpload = ($rowId === 'rubric');

        if ($isRubricUpload) {
            $targetUploadStatuses = [
                FolderStatus::DRAFT->value,
                FolderStatus::PENDING_TARGET_APPROVAL->value,
                FolderStatus::TARGET_APPROVED->value,
            ];
            $canUploadRubric = in_array($status, $targetUploadStatuses) || ($sysRole === 'Admin');
            if (!$canUploadRubric && $sysRole !== 'Admin') {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Rubric links can only be attached while drafting or finalizing targets.'
                ]);
            }
        } else {
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

            if ($sysRole !== 'Admin' && !in_array($status, [FolderStatus::DRAFT->value, FolderStatus::TARGET_APPROVED->value, FolderStatus::TO_EVALUATE->value, FolderStatus::REEVALUATE->value]) && !$isPastTargetDate) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Evidence attachments can only be added while evaluating accomplishments.'
                ]);
            }
        }

        // Determine link provider and default title
        $lowerUrl = strtolower($linkUrl);
        if (str_contains($lowerUrl, 'drive.google.com') || str_contains($lowerUrl, 'docs.google.com')) {
            $provider = 'google_drive';
            $fileType = 'link/google_drive';
            $defaultTitle = 'Google Drive Evidence';
        } elseif (str_contains($lowerUrl, 'onedrive.live.com') || str_contains($lowerUrl, 'sharepoint.com')) {
            $provider = 'onedrive';
            $fileType = 'link/onedrive';
            $defaultTitle = 'OneDrive Evidence';
        } elseif (str_contains($lowerUrl, 'dropbox.com')) {
            $provider = 'dropbox';
            $fileType = 'link/dropbox';
            $defaultTitle = 'Dropbox Evidence';
        } else {
            $provider = 'web_link';
            $fileType = 'link/url';
            $defaultTitle = 'Cloud Evidence Link';
        }

        $finalTitle = !empty($linkName) ? $linkName : $defaultTitle;

        $attachmentModel = new DocumentAttachmentModel();

        if ($isRubricUpload) {
            $existingRubrics = $attachmentModel->where('document_id', $docId)
                                               ->where('row_id', 'rubric')
                                               ->where('deleted_at IS NULL')
                                               ->findAll();
            foreach ($existingRubrics as $ex) {
                $attachmentModel->delete($ex['id']);
            }
        }

        $data = [
            'document_id' => $docId,
            'row_id'      => $rowId,
            'file_name'   => $finalTitle,
            'file_path'   => $linkUrl,
            'file_type'   => $fileType,
            'file_size'   => 0,
            'is_link'     => 1,
            'link_url'    => $linkUrl,
            'uploaded_by' => $userId,
        ];

        $insertedId = $attachmentModel->insert($data);
        $saved = $attachmentModel->find($insertedId);
        $saved['is_link']       = true;
        $saved['link_url']      = $linkUrl;
        $saved['link_provider'] = $provider;
        $saved['formatted_size'] = ($provider === 'google_drive') ? 'Google Drive' : 'Cloud Link';
        $saved['uploader_name'] = trim((session()->get('first_name') ?? '') . ' ' . (session()->get('last_name') ?? ''));

        audit_log('MOV_LINK_ATTACHED', 'EVIDENCE', 'document_attachment', (int) $insertedId, "Cloud evidence link attached: {$finalTitle} to document #{$docId} (row: {$rowId})");

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Evidence link attached successfully.',
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

        // Optionally delete physical file if not a link
        if (empty($attachment['is_link']) && !str_starts_with($attachment['file_type'] ?? '', 'link/')) {
            $filePath = WRITEPATH . $attachment['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Attachment removed.'
        ]);
    }

    /**
     * GET /attachments/view/{id}
     * Stream file inline for in-app browser preview (PDF, Images) or redirect to external link.
     */
    public function view($id = null)
    {
        return $this->serveFile($id, 'inline');
    }

    /**
     * GET /attachments/download/{id}
     * Download file as attachment or redirect to external link.
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
        $all = $attachmentModel->getAttachmentsGroupedByRow((string)$docId);
        $rowAttachments = $all[$rowId] ?? [];

        return $this->response->setJSON([
            'status'      => 'success',
            'attachments' => $rowAttachments
        ]);
    }

    /**
     * Internal helper to verify authorization and stream the file or redirect.
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

        // Authorization check: Owner, Admin, TWG (assigned), or assigned Evaluator
        $docOwnerId = $doc['owner_id'];
        $hasAccess  = ($docOwnerId == $userId) || ($sysRole === 'Admin') ||
            ($sysRole === 'TWG' && (new \App\Models\TwgUnitAssignmentModel())->isTwgAssignedToFolder($userId, $doc['document_folder_id']));

        if (!$hasAccess) {
            $routingModel = new EvaluationRoutingModel();
            $hasAccess = $routingModel->where('folder_id', $doc['document_folder_id'])
                                      ->where('evaluator_id', $userId)
                                      ->countAllResults() > 0;
        }

        if (!$hasAccess) {
            return $this->response->setStatusCode(403)->setBody('Access denied to this document attachment.');
        }

        // If it's a web/cloud link, redirect directly to the URL
        if (!empty($attachment['is_link']) || str_starts_with($attachment['file_type'] ?? '', 'link/')) {
            $targetUrl = $attachment['link_url'] ?: $attachment['file_path'];
            return redirect()->to($targetUrl);
        }

        $filePath = WRITEPATH . $attachment['file_path'];
        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Attachment file not found on disk.');
        }

        $rawContents = file_get_contents($filePath);
        $payload = $rawContents;

        // Decrypt if encrypted, or return raw contents
        try {
            $encrypter = \Config\Services::encrypter();
            $decrypted = $encrypter->decrypt($rawContents);
            if ($decrypted !== false && $decrypted !== null) {
                $payload = $decrypted;
            }
        } catch (\Throwable $e) {
            $payload = $rawContents;
        }

        $mimeType = $attachment['file_type'] ?: 'application/octet-stream';
        $fileName = $attachment['file_name'] ?: 'evidence';

        // Clean headers and stream
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $disposition . '; filename="' . addslashes($fileName) . '"');
        header('Content-Length: ' . strlen($payload));
        header('Cache-Control: private, max-age=86400');
        header('Pragma: public');

        echo $payload;
        exit;
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentModel;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\TemplateModel;

/**
 * Handles individual documents (the actual IPCR/DPCR/OPCR pages inside a folder):
 * viewing/editing content, creating new documents from a template, and marking
 * which document in a folder counts as the "target" used for the final rating.
 */
class Document extends BaseController
{
    /** GET /document/{docId} - Opens the TinyMCE editor view for a single document. */
    public function index($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        
        if (!$docId) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $documentModel = new DocumentModel();
        $userModel     = new \App\Models\UserModel();
        
        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);

        if (!$docInfo) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $docOwnerId = $docInfo['owner_id'];
        $isGuide = false;
        $routingStatus = null;

        if ($docOwnerId !== $userId) {
            $routingModel = new EvaluationRoutingModel();
            $routing = $routingModel->where('folder_id', $docInfo['document_folder_id'])
                                    ->where('evaluator_id', $userId)
                                    ->first();
            
            if ($routing) {
                $routingStatus = is_object($routing) ? $routing->status : $routing['status'];
            } elseif ($sysRole === 'Admin' || $sysRole === 'TWG') {
                $routingStatus = null;
            } else {
                $isGuide = true;
            }
        }

        $parentFolder = null;
        $isParentTargetApproved = true;
        $basisDoc = null;
        $superiorUser = null;

        if (!empty($docInfo['parent_folder_id'])) {
            $folderModel = new \App\Models\DocumentFolderModel();
            $parentFolder = $folderModel->find($docInfo['parent_folder_id']);
            if ($parentFolder) {
                $isMyDocOpcr = (strtoupper($docInfo['title'] ?? '') === 'OPCR');

                if ($isMyDocOpcr) {
                    // OPCR is Stage 1 (Apex Root Commitment). There are no superior targets above it.
                    $isParentTargetApproved = true;
                    $parentFolder = null;
                    $basisDoc = null;
                } else {
                    $candidateBasis = $documentModel->where('document_folder_id', $parentFolder['id'])->where('is_target', 1)->first()
                                   ?? $documentModel->where('document_folder_id', $parentFolder['id'])->first();

                    if ($candidateBasis) {
                        $isParentTargetApproved = ($parentFolder['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value);
                        $basisDoc = $candidateBasis;
                        $superiorUser = $userModel->find($parentFolder['user_id']);
                        if ($superiorUser) {
                            $plantilla = $userModel->getActivePlantillaDetails($superiorUser['id']);
                            $superiorUser['position'] = $plantilla['position'] ?? 'Supervisor';
                            $superiorUser['department'] = $plantilla['department'] ?? '';
                        }
                    } else {
                        // Parent folder has no documents (pure cycle container)
                        $isParentTargetApproved = true;
                        $parentFolder = null;
                        $basisDoc = null;
                    }
                }
            }
        }

        $basisFormData = null;
        $basisDocContent = '';
        if ($basisDoc) {
            $basisDocContent = $basisDoc['content'] ?? '';
            if (!empty($basisDoc['tabs'])) {
                $bTabs = is_string($basisDoc['tabs']) ? json_decode($basisDoc['tabs'], true) : $basisDoc['tabs'];
                if (!empty($bTabs) && is_array($bTabs)) {
                    $basisFormData = $bTabs[0]['formData'] ?? null;
                    if (empty($basisDocContent) && !empty($bTabs[0]['content'])) {
                        $basisDocContent = $bTabs[0]['content'];
                    }
                }
            }
        }

        $currentUser = $userModel->find($userId);
        $currentPlantilla = $userModel->getActivePlantillaDetails($userId);
        $data['currentReviewerRole']    = $currentPlantilla['position'] ?? (session()->get('role') ?: 'Reviewer');
        $data['currentReviewerName']    = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));

        // Document Owner (Ratee) details for automatic commitment header prefilling
        $ownerUser = $userModel->find($docOwnerId);
        $ownerPlantilla = $userModel->getActivePlantillaDetails($docOwnerId);
        $ownerRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $docOwnerId)->first();
        $ownerRoleName = $ownerRolePivot ? ((new \App\Models\RoleModel())->find($ownerRolePivot['role_id'])['name'] ?? '') : '';

        $ownerName = trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? ''));
        $ownerPosition = $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Faculty');
        $ownerDept = $ownerPlantilla['department'] ?? '';
        $docPeriod = $docInfo['folder_title'] ?? '';

        $data['ownerInfo'] = [
            'name'     => $ownerName,
            'position' => $ownerPosition,
            'dept'     => $ownerDept,
            'period'   => $docPeriod,
        ];

        $isCycleArchived = !empty($docInfo['folder_deleted_at']) || (!empty($parentFolder) && !empty($parentFolder['deleted_at']));

        // Resolve root evaluation cycle folder ID
        $folderModel = new \App\Models\DocumentFolderModel();
        $rootFolderId = $docInfo['parent_folder_id'] ?? $docInfo['document_folder_id'];
        if (!empty($docInfo['parent_folder_id'])) {
            $pObj = $folderModel->find($docInfo['parent_folder_id']);
            while ($pObj && !empty($pObj['parent_folder_id'])) {
                $rootFolderId = $pObj['parent_folder_id'];
                $pObj = $folderModel->find($rootFolderId);
            }
        }
        $data['rootFolderId'] = $rootFolderId;

        // Seamless Ratee Navigation for Evaluators/Supervisors/TWG
        $rateeNav = null;
        if ($docOwnerId !== $userId && !$isGuide) {
            $siblingFolders = $folderModel->getRatingDashboardFolders($userId, $sysRole, $rootFolderId);
            $currentIndex = -1;
            foreach ($siblingFolders as $idx => $sFolder) {
                if ($sFolder['folder_id'] == $docInfo['document_folder_id']) {
                    $currentIndex = $idx;
                    break;
                }
            }

            if ($currentIndex !== -1 && count($siblingFolders) > 1) {
                $totalRatees = count($siblingFolders);
                $prevFolder = ($currentIndex > 0) ? $siblingFolders[$currentIndex - 1] : null;
                $nextFolder = ($currentIndex < $totalRatees - 1) ? $siblingFolders[$currentIndex + 1] : null;
                $rateeNav = [
                    'currentIndex' => $currentIndex + 1,
                    'totalRatees'  => $totalRatees,
                    'prev'         => $prevFolder ? [
                        'folder_id' => $prevFolder['folder_id'],
                        'name'      => $prevFolder['username'],
                        'position'  => $prevFolder['position'] ?? ''
                    ] : null,
                    'next'         => $nextFolder ? [
                        'folder_id' => $nextFolder['folder_id'],
                        'name'      => $nextFolder['username'],
                        'position'  => $nextFolder['position'] ?? ''
                    ] : null,
                ];
            }
        }

        $data['isCycleArchived']        = $isCycleArchived;
        $data['isOwner']                = ($docOwnerId == $userId);
        $data['rateeNav']               = $rateeNav;
        $data['routingStatus']          = $routingStatus;
        $data['doc']                    = $docInfo;
        $data['isGuide']                = $isGuide;
        $data['parentFolder']           = $parentFolder;
        $data['isParentTargetApproved'] = $isParentTargetApproved;
        $data['basisDoc']               = $basisDoc;
        $data['superiorUser']           = $superiorUser;
        $data['basisFormData']          = $basisFormData;
        $data['basisDocContent']        = $basisDocContent;
        $data['isEmbed']                = (bool) $this->request->getGet('embed');

        $attachmentModel                = new \App\Models\DocumentAttachmentModel();
        $data['attachmentsByRow']       = $attachmentModel->getAttachmentsGroupedByRow((int)$docId);
        
        return view('document/show', $data);
    }
    
    /** POST /document - Creates a new (optionally template-seeded) document inside a folder. */
    public function store() {
        return $this->tryOrFail(function() {
            $documentModel = new DocumentModel();
            $userId   = session()->get('user_id');
            $folderId = $this->request->getPost('folder_id');
            $title  = trim($this->request->getPost('title')) ?: 'Untitled Document';
            $templateId = $this->request->getPost('template');
            $initialTabs = [];

            if (!empty($templateId)) {
                $templateModel = new TemplateModel();
                $template = $templateModel->find($templateId);
                if ($template && !empty($template['tabs'])) {
                    $initialTabs = is_string($template['tabs']) ? json_decode($template['tabs'], true) : $template['tabs'];
                }
            }

            if (empty($initialTabs)) {
                $initialTabs = [
                    [
                        'id' => 'tab-' . uniqid(),
                        'title' => 'Main Document',
                        'content' => ''
                    ]
                ];
            }

            $hasExistingTarget = $documentModel->where('document_folder_id', $folderId)->where('is_target', 1)->first();
            $isTarget = (!$hasExistingTarget || !empty($templateId)) ? 1 : 0;
            if ($isTarget && $hasExistingTarget && !empty($templateId)) {
                $documentModel->where('document_folder_id', $folderId)->set(['is_target' => 0])->update();
            }

            $docs = $documentModel->getUserDocuments($userId);
            $payload = [
                'title'              => resolve_unique_title($title, $docs),
                'user_id'            => $userId,
                'document_folder_id' => $folderId,
                'tabs'               => $initialTabs,
                'is_target'          => $isTarget,
                'status'             => 'draft'
            ];
            $newId = create_unique_row($documentModel, $payload);

            if (!$newId) {
                return $this->respondError("Could not generate a unique ID.", 400);
            }

            return $this->respond(['status' => 'success', 'id' => $folderId]);
        });
    }

    /** POST /document/update - Autosave endpoint: persists title/content for an owner or assigned evaluator. */
    public function update() {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');
        $docId  = $this->request->getPost('id');

        $documentModel = new DocumentModel();

        $docOwnerInfo = $documentModel->db->table('documents d')
            ->select('df.user_id as owner_id, df.id as folder_id, df.deleted_at as folder_deleted_at, df.parent_folder_id')
            ->join('document_folders df', 'df.id = d.document_folder_id')
            ->where('d.id', $docId)->get()->getRowArray();

        if (!$docOwnerInfo) return $this->response->setJSON(['status' => 'error', 'message' => 'Document not found']);

        // Check if cycle or folder is archived / frozen
        if (!empty($docOwnerInfo['folder_deleted_at'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is closed and archived. Alterations are frozen.']);
        }
        if (!empty($docOwnerInfo['parent_folder_id'])) {
            $folderModel = new DocumentFolderModel();
            $pFolder = $folderModel->find($docOwnerInfo['parent_folder_id']);
            if ($pFolder && !empty($pFolder['deleted_at'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This evaluation cycle is closed and archived. Alterations are frozen.']);
            }
        }

        $isAuthorized = false;

        if ($docOwnerInfo['owner_id'] === $userId || $sysRole === 'Admin') {
            $isAuthorized = true; 
        } else {
            $routingModel = new EvaluationRoutingModel();
            $isEvaluator = $routingModel->where('folder_id', $docOwnerInfo['folder_id'])
                                        ->where('evaluator_id', $userId)
                                        ->countAllResults() > 0;
            if ($isEvaluator) $isAuthorized = true;
        }

        if (!$isAuthorized) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $payload = [
            'id'    => $docId,
            'title' => $this->request->getPost('title')
        ];

        $tabsJson = $this->request->getPost('tabs');
        if ($tabsJson) {
            $decoded = json_decode($tabsJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload['tabs'] = $decoded;
            }
        }

        $documentModel->save($payload);

        return $this->response->setJSON(['status' => 'success']);
    }
    
    /**
     * POST /document/set-target - Marks one document (the basis target) in a folder as the
     * basis for the final rating. Clears the flag on every other document in the
     * same folder first, since only one document can be the target at a time.
     */
    public function setTarget() {
        return $this->tryOrFail(function() {
            $docId = $this->request->getPost('doc_id');
            $folderId = $this->request->getPost('folder_id');
            $isTarget = $this->request->getPost('is_target') !== null ? (int)$this->request->getPost('is_target') : 1;
            $userId = session()->get('user_id');
            $role   = session()->get('role');

            $folderModel = new \App\Models\DocumentFolderModel();
            $folder = $folderModel->find($folderId);

            if (!$folder) {
                return $this->respondError('Folder not found.', 404);
            }

            // Must be the folder owner, or an Admin/routed evaluator
            $isOwner = ($folder['user_id'] == $userId);
            $isAdmin = ($role === 'Admin');
            $isEvaluator = (new \App\Models\EvaluationRoutingModel())
                ->where('folder_id', $folderId)
                ->where('evaluator_id', $userId)
                ->countAllResults() > 0;

            if (!$isOwner && !$isAdmin && !$isEvaluator) {
                return $this->respondError('Unauthorized to modify targets for this folder.', 403);
            }

            $documentModel = new DocumentModel();

            // Clear any existing target in the folder
            $documentModel->where('document_folder_id', $folderId)->set(['is_target' => 0])->update();
            
            if ($isTarget === 1) {
                $documentModel->where('id', $docId)->where('document_folder_id', $folderId)->set(['is_target' => 1])->update();
            }

            return $this->respond(['status' => 'success', 'message' => 'Target document updated.']);
        });
    }

    /** POST /document/delete - Deletes a document after confirming the requester owns its folder. */
    public function destroy() {
        $docId = $this->request->getPost('doc_id');
        $userId = session()->get('user_id');
        $documentModel = new DocumentModel();

        // Verify ownership via folder join since documents no longer have user_id
        if (!$documentModel->getUserDocuments($userId, $docId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $documentModel->delete($docId);

        return $this->response->setJSON(['status' => 'success']);
    }

    /** GET /document/{id}/export-excel - Exports document to official CSC Excel spreadsheet */
    public function exportExcel($docId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        if (!$docId || !$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $documentModel = new DocumentModel();
        $userModel     = new \App\Models\UserModel();

        $docInfo = $documentModel->getDocumentWithFolderInfo($docId);
        if (!$docInfo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $docOwnerId = $docInfo['owner_id'];

        // Authorization check: Only Document Owner and Admin can export
        if ($docOwnerId !== $userId && $sysRole !== 'Admin') {
            return redirect()->back()->with('error', 'Only the document owner and Admin can export this document.');
        }

        // Parse form data from tabs
        $formData = null;
        if (!empty($docInfo['tabs'])) {
            $tabs = is_string($docInfo['tabs']) ? json_decode($docInfo['tabs'], true) : $docInfo['tabs'];
            if (!empty($tabs) && is_array($tabs)) {
                $formData = $tabs[0]['formData'] ?? null;
            }
        }

        // Owner Account Details
        $ownerUser = $userModel->find($docOwnerId);
        $ownerPlantilla = $userModel->getActivePlantillaDetails($docOwnerId);
        $ownerRolePivot = (new \App\Models\UserRoleModel())->where('user_id', $docOwnerId)->first();
        $ownerRoleName = $ownerRolePivot ? ((new \App\Models\RoleModel())->find($ownerRolePivot['role_id'])['name'] ?? '') : '';

        $ownerInfo = [
            'name'     => trim(($ownerUser['first_name'] ?? '') . ' ' . ($ownerUser['last_name'] ?? '')),
            'position' => $ownerPlantilla['position'] ?? ($ownerRoleName ?: 'Faculty'),
            'dept'     => $ownerPlantilla['department'] ?? '',
            'period'   => $docInfo['folder_title'] ?? '',
        ];

        // Superior / Supervisor Account Details
        $superiorInfo = null;
        if (!empty($docInfo['parent_folder_id'])) {
            $folderModel = new \App\Models\DocumentFolderModel();
            $parentFolder = $folderModel->find($docInfo['parent_folder_id']);
            if ($parentFolder && !empty($parentFolder['user_id'])) {
                $superiorUser = $userModel->find($parentFolder['user_id']);
                if ($superiorUser) {
                    $plantilla = $userModel->getActivePlantillaDetails($superiorUser['id']);
                    $superiorUser['position'] = $plantilla['position'] ?? 'Supervisor';
                    $superiorUser['department'] = $plantilla['department'] ?? '';
                    $superiorInfo = $superiorUser;
                }
            }
        }

        \App\Libraries\CscExcelExporter::export($docInfo, $formData, $ownerInfo, $superiorInfo);
    }
}
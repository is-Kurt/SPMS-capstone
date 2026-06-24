<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Enums\FolderStatus;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\RoutingPresetMemberModel;
use App\Models\DocumentModel;
use App\Models\TemplateModel;
use App\Model\UserModel;
use App\Models\RoutingPresetModel;

class Folder extends BaseController
{
    public function index($folderId = null) {
        $userId   = session()->get('user_id');
        $sysRole  = session()->get('role');
        $userPos  = session()->get('position');
        $dept     = session()->get('department');

        $folderModel   = new \App\Models\DocumentFolderModel();
        $documentModel = new \App\Models\DocumentModel();
        $presetModel   = new \App\Models\RoutingPresetModel();
        $db            = \Config\Database::connect();

        // 1. Get Folder Sidebar
        $folders = $folderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        // 2. Clean URL Redirect Logic
        if (!$folderId) {
            $lastId = session()->get('active_folder_id');

            if ($lastId && array_search($lastId, array_column($folders, 'id')) !== false) {
                return redirect()->to('folders/' . $lastId);
            } elseif (!empty($folders)) {
                return redirect()->to('folders/' . $folders[0]['id']);
            }
        } else {
            session()->set('active_folder_id', $folderId);
        }

        $activeFolder = null;
        $myDocs = [];
        $groupedGuides = []; 
        $isReadOnly = true; 

        // 3. Fetch presets for the dropdown
        $presets = $presetModel->where('owner_id', $userId)->orderBy('name', 'ASC')->findAll();

        if ($folderId) {
            $activeFolder = $folderModel->find($folderId);
            
            // Security check
            if (!$activeFolder || $activeFolder['user_id'] != $userId) {
                session()->remove('active_folder_id');

                return redirect()->to('folders'); 
            }

            // 4. Hierarchical Security Check
            $folderOwnerId = $activeFolder['user_id'];
            $isAuthorized = false;

            if ($folderOwnerId == $userId) {
                $isAuthorized = true;
                $isReadOnly = false;
            } elseif ($sysRole === 'Admin') {
                $isAuthorized = true;
            } else {
                $ownerPlantilla = $db->table('plantilla p')
                    ->select('pos.title as position, un.name as department')
                    ->join('positions pos', 'pos.id = p.position_id')
                    ->join('units un', 'un.id = p.unit_id')
                    ->where('p.user_id', $folderOwnerId)
                    ->where('p.ended_at IS NULL')->get()->getRowArray();

                if ($ownerPlantilla) {
                    $ownerPos = $ownerPlantilla['position'];
                    $ownerDept = $ownerPlantilla['department'];
                    $opcrPos = ['Vice President', 'Campus Administrator'];
                    $dpcrPos = ['Dean', 'Director', 'Head of Office'];

                    if (in_array($userPos, $opcrPos) && in_array($ownerPos, $dpcrPos)) {
                        $isAuthorized = true;
                    } elseif (in_array($userPos, $dpcrPos) && !in_array($ownerPos, array_merge($opcrPos, $dpcrPos)) && $ownerDept === $dept) {
                        $isAuthorized = true;
                    }
                }
            }

            if (!$isAuthorized) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unauthorized.');

            // 5. Load Docs and Guides
            $myDocs = $documentModel->where('document_folder_id', $folderId)->findAll();

            $routingModel = new \App\Models\EvaluationRoutingModel();

            $cascadedRoutes = $routingModel->select('evaluation_routings.*, u.first_name, u.last_name, pos.title as evaluator_position')
                                            ->join('users u', 'u.id = evaluation_routings.evaluator_id')
                                            ->join('plantilla p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
                                            ->join('positions pos', 'pos.id = p.position_id', 'left')
                                            ->where('folder_id', $folderId)
                                            ->findAll();

            foreach ($cascadedRoutes as $route) {
                
                $guideFolder = $folderModel->find($route['evaluator_folder_id']);
                
                if ($guideFolder) {
                    $docs = $documentModel->where('document_folder_id', $guideFolder['id'])->findAll();
                    
                    if (!empty($docs)) {
                        $groupedGuides[] = [
                            'superior' => [
                                'id'   => $route['evaluator_id'],
                                'name' => $route['first_name'] . ' ' . $route['last_name'],
                                'role' => $route['evaluator_position'] ?? 'Evaluator' 
                            ], 
                            'docs' => $docs
                        ];
                    }
                }
            }

            $mergedGuides = [];

            foreach ($groupedGuides as $guide) {
                $key = $guide['superior']['name']; 

                if (!isset($mergedGuides[$key])) {
                    $mergedGuides[$key] = $guide;
                } else {
                    $existingRoles = $mergedGuides[$key]['superior']['role'];
                    $newRole       = $guide['superior']['role'];

                    if (strpos($existingRoles, $newRole) === false) {
                        $mergedGuides[$key]['superior']['role'] .= ', ' . $newRole;
                    }
                }
            }

            $groupedGuides = array_values($mergedGuides);
            }

        $templateModel = new \App\Models\TemplateModel();
        
        return view('app_shell', [
            'sidebarFolders'   => $this->getSidebarFolders(), 
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'mainView'         => 'document/_doc_rows',
            'templates'        => $templateModel->findAll(),
            'mainData'         => [
                'activeFolder'  => $activeFolder,
                'myDocs'        => $myDocs,
                'groupedGuides' => $groupedGuides,
                'isReadOnly'    => $isReadOnly,
                'presets'       => $presets
            ]
        ]);
    }

    public function cascadeTeam() {
        return $this->tryOrFail(function() {

            $folderId = $this->request->getPost('folder_id'); 
            $teamId   = $this->request->getPost('team_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            // 1. Initialize all Models (No raw DB connections)
            $folderModel       = new \App\Models\DocumentFolderModel();
            $routingModel      = new \App\Models\EvaluationRoutingModel();
            $presetMemberModel = new \App\Models\RoutingPresetMemberModel();
            $userModel         = new \App\Models\UserModel(); 

            $activeFolder = $folderModel->find($folderId);
            if (!$activeFolder) throw new \Exception("Folder not found.");

            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            if (empty($members)) throw new \Exception("The selected team has no members.");

            $folderModel->db->transStart();

            // Update the master folder to permanently remember this team
            $folderModel->update($folderId, ['routing_preset_id' => $teamId]);
            $emailsQueued = 0; // Track how many emails we generate

            if ($role === 'Admin') {
                foreach ($members as $member) {
                    $exists = $folderModel->where('user_id', $member['user_id'])
                                          ->where('parent_folder_id', $activeFolder['id'])->first();
                    
                    if (!$exists) {
                        // FIX: Capture the generated ID so we can use it in the email link
                        $newFolderId = create_unique_row($folderModel, [
                            'title'            => $activeFolder['title'],
                            'user_id'          => $member['user_id'],
                            'parent_folder_id' => $activeFolder['id'],
                            'eval_date_start'  => $activeFolder['eval_date_start'],
                            'eval_date_end'    => $activeFolder['eval_date_end'],
                            'status'           => \App\Enums\FolderStatus::DRAFT->value
                        ]);
                    } else {
                        // If it already existed, grab the ID so the link still works
                        $newFolderId = $exists['id']; 
                    }

                    // Only send an email if a NEW folder was actually created 
                    // (Prevents spamming users if the Admin clicks Cascade twice)
                    if (!$exists) {
                        $memberInfo = $userModel->find($member['user_id']); // Use UserModel
                        
                        if ($memberInfo) {
                            $link = site_url("folders/" . $newFolderId);

                            queue_email(
                                $memberInfo['email'], 
                                'New Evaluation Folder: Drafting Period Open', 
                                "Hello {$memberInfo['first_name']},<br><br>A new performance evaluation folder has been assigned to you. You may now begin drafting your entries and submitting your self-rating.<br><br><a href='{$link}'>Click here to open your folder</a>"
                            );
                            $emailsQueued++;
                        }
                    }
                }
                $message = "Batch evaluation distributed to team members.";
            } else {
                $batchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];

                foreach ($members as $member) {
                    $subFolder = $folderModel->where('user_id', $member['user_id'])
                                             ->where('parent_folder_id', $batchId)->first();
                    
                    if ($subFolder) {
                        $exists = $routingModel->where('folder_id', $subFolder['id'])
                                               ->where('evaluator_id', $userId)->first();

                        if (!$exists) {
                            $routingModel->insert([
                                'folder_id'           => $subFolder['id'],
                                'evaluator_id'        => $userId,
                                'evaluator_folder_id' => $folderId,
                                'status'              => \App\Enums\FolderStatus::DRAFT->value
                            ]);
                        }
                    }
                }
                $message = "Goals successfully cascaded to your team.";
            }

            $folderModel->db->transComplete();

            return $this->respond(['status' => 'success', 'message' => $message]);
        });
    }

    public function uncascadeTeam() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id'); 
            $teamId   = $this->request->getPost('team_id');
            $userId   = session()->get('user_id');
            $role     = session()->get('role');

            $folderModel = new DocumentFolderModel();
            $routingModel = new EvaluationRoutingModel();
            $presetMemberModel = new RoutingPresetMemberModel();
            
            $activeFolder = $folderModel->find($folderId);
            $members = $presetMemberModel->where('preset_id', $teamId)->findAll();
            $memberIds = array_column($members, 'user_id');

            $folderModel->db->transStart();

            // 1. Clear the memory
            $folderModel->update($folderId, ['routing_preset_id' => null]);

            if (!empty($members)) {
                if ($role === 'Admin') {
                    $folderModel->where('parent_folder_id', $activeFolder['id'])
                                ->whereIn('user_id', $memberIds)->delete();
                } else {
                    $batchId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
                    $subFolders = $folderModel->whereIn('user_id', $memberIds)
                                            ->where('parent_folder_id', $batchId)->findAll();
                    $subFolderIds = array_column($subFolders, 'id');

                    if (!empty($subFolderIds)) {
                        // Using Model to delete
                        $routingModel->where('evaluator_id', $userId)
                                    ->where('evaluator_folder_id', $folderId)
                                    ->whereIn('folder_id', $subFolderIds)->delete();
                    }
                }
            }

            $folderModel->db->transComplete();
            return $this->respond(['status' => 'success', 'message' => 'Cascade revoked successfully.']);
        });
    }

    private function updateFolderConsensus($folderId) {
        $db = \Config\Database::connect();
        $folderModel = new DocumentFolderModel();
        
        $routings = $db->table('evaluation_routings')
                    ->where('folder_id', $folderId)
                    ->get()->getResultArray();
                    
        if (empty($routings)) return;

        $hasRevision = false;
        $allApproved = true;

        foreach ($routings as $r) {
            if ($r['status'] === FolderStatus::REEVALUATE->value) $hasRevision = true;
            if ($r['status'] !== FolderStatus::APPROVED->value) $allApproved = false;
        }

        if ($hasRevision) {
            $folderModel->update($folderId, ['status' => FolderStatus::REEVALUATE->value]);
        } elseif ($allApproved) {
            $folderModel->update($folderId, ['status' => FolderStatus::APPROVED->value, 'rated_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function store() {
        return $this->tryOrFail(function() {
            $documentFolderModel = new DocumentFolderModel();
            $userId = session()->get('user_id');
            $title = trim($this->request->getPost('title')) ?: 'Untitled Evaluation';

            // NEW: Capture the dates sent by your Javascript modal
            $startDate = $this->request->getPost('eval_date_start');
            $endDate   = $this->request->getPost('eval_date_end');

            $payload = [
                'title'           => resolve_unique_title($title, ['user_id' => $userId], 'title', $documentFolderModel),
                'user_id'         => $userId,
                'eval_date_start' => $startDate ?: null,
                'eval_date_end'   => $endDate ?: null
            ];
            
            $newId = create_unique_row($documentFolderModel, $payload);

            if (!$newId) {
                throw new \Exception("Could not generate a unique ID.");
            }

            return $this->respond(['status' => 'success', 'id' => $newId]);
        });
    }

    public function destroy() {
        $folderId = $this->request->getPost('doc_id'); 
        $folderModel = new DocumentFolderModel();
        
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        $folder = $folderModel->find($folderId);

        if (!$folder || $folder['user_id'] != $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($role === 'Admin') {
            $folderModel->delete($folderId);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function update() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $folderModel = new DocumentFolderModel();

            // Admin only check
            if (session()->get('role') !== 'Admin') {
                throw new \Exception("Unauthorized to edit folders.");
            }

            $title = $this->request->getPost('title');
            $dateStart = $this->request->getPost('eval_date_start');
            $dateEnd = $this->request->getPost('eval_date_end');

            // 1. Update the Admin's Master Folder
            $folderModel->update($folderId, [
                'title'           => $title,
                'eval_date_start' => $dateStart,
                'eval_date_end'   => $dateEnd,
            ]);

            // 2. NEW: Cascade the update to all distributed Child Folders!
            $db = \Config\Database::connect();
            $db->table('document_folders')
               ->where('parent_folder_id', $folderId)
               ->update([
                   'title'           => $title,
                   'eval_date_start' => $dateStart,
                   'eval_date_end'   => $dateEnd,
               ]);

            return $this->respond(['status' => 'success', 'message' => 'Folder updated and synced to all users.']);
        });
    }

    public function submit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);
            if (!$folder || $folder['user_id'] != $userId) {
                throw new \Exception("Unauthorized to submit this folder.");
            }

            // Using Model Update
            $folderModel->update($folderId, [
                'status'       => FolderStatus::SUBMITTED->value,
                'submitted_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Folder submitted for evaluation.']);
        });
    }

    public function unsubmit() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $userId = session()->get('user_id');
            $folderModel = new DocumentFolderModel();

            $folder = $folderModel->find($folderId);
            if (!$folder || $folder['user_id'] != $userId) throw new \Exception("Unauthorized.");

            // Check deadline
            if (!empty($folder['eval_date_end']) && date('Y-m-d H:i:s') > $folder['eval_date_end']) {
                throw new \Exception("Cannot unsubmit: Evaluation window has closed.");
            }

            $folderModel->update($folderId, ['status' => FolderStatus::DRAFT->value, 'submitted_at' => null]);
            return $this->respond(['status' => 'success', 'message' => 'Submission revoked.']);
        });
    }

    public function evaluate() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $finalRating = $this->request->getPost('final_rating');
            $folderModel = new \App\Models\DocumentFolderModel();

            $folderModel->update($folderId, [
                'status'       => \App\Enums\FolderStatus::EVALUATED->value,
                'final_rating' => (float) $finalRating,
                'rated_at'     => date('Y-m-d H:i:s')
            ]);

            $userModel    = new \App\Models\UserModel();
            $routingModel = new \App\Models\EvaluationRoutingModel();
            
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $routings = $routingModel->where('folder_id', $folderId)->findAll();
            
            foreach ($routings as $route) {
                $evaluator = $userModel->find($route['evaluator_id']);
                
                if ($evaluator) {
                    $link = site_url("ratings/show/" . $folderId);
                    
                    queue_email(
                        $evaluator['email'],
                        'Pending Review: ' . $subordinate['first_name'] . ' has evaluated their folder',
                        "Hello {$evaluator['first_name']},<br><br>{$subordinate['first_name']} {$subordinate['last_name']} has completed the self-evaluation for their folder (<b>{$folder['title']}</b>). It is now in your queue and ready for your official review.<br><br><a href='{$link}'>Click here to review their evaluation</a>"
                    );
                }
            }

            return $this->respond(['status' => 'success', 'message' => 'Folder successfully evaluated and locked.']);
        });
    }

    public function approve() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $db = \Config\Database::connect();

            $db->table('evaluation_routings')
                ->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->update(['status' => FolderStatus::APPROVED->value, 'updated_at' => date('Y-m-d H:i:s')]);

            $this->updateFolderConsensus($folderId);

            $userModel = new UserModel();
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $supervisor = $userModel->find(session()->get('user_id'));
            $link = site_url("folders/" . $folderId);
            
            queue_email(
                $subordinate['email'],
                'Folder Approved: ' . $folder['title'],
                "Hello {$subordinate['first_name']},<br><br>Your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}, has officially approved your evaluation folder (<b>{$folder['title']}</b>).<br><br><a href='{$link}'>Click here to view your finalized rating</a>"
            );

            return $this->respond(['status' => 'success', 'message' => 'Approved!']);
        });
    }

    public function returnRevision() {
        return $this->tryOrFail(function() {
            $folderId = $this->request->getPost('folder_id');
            $db = \Config\Database::connect();

            $db->table('evaluation_routings')
                ->where('folder_id', $folderId)
                ->where('evaluator_id', session()->get('user_id'))
                ->update(['status' => FolderStatus::REEVALUATE->value, 'updated_at' => date('Y-m-d H:i:s')]);

            $this->updateFolderConsensus($folderId);

            $userModel = new \App\Models\UserModel();
            $folderModel = new DocumentFolderModel();
            $folder = $folderModel->find($folderId);
            $subordinate = $userModel->find($folder['user_id']);
            $supervisor = $userModel->find(session()->get('user_id'));
            $link = site_url("folders/" . $folderId);
            
            queue_email(
                $subordinate['email'],
                'Action Required: Folder Returned for Revision',
                "Hello {$subordinate['first_name']},<br><br>Your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}, has returned your evaluation folder (<b>{$folder['title']}</b>) for re-evaluation or corrections.<br><br><a href='{$link}'>Click here to open your folder and make adjustments</a>"
            );

            return $this->respond(['status' => 'success', 'message' => 'Returned for revision.']);
        });
    }
}
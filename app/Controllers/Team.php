<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\UnitModel;
use App\Models\PositionModel;
use App\Models\RoutingPresetModel;
use App\Models\RoutingPresetMemberModel;
use App\Models\DocumentFolderModel;

/**
 * "My Teams": lets Admins/Supervisors build reusable distribution lists
 * (routing presets) of people, which then get "cascaded" onto a folder via
 * Folder::cascadeTeam() to assign evaluators or spin up subordinate folders.
 */
class Team extends BaseController
{
    /** GET /teams - Lists this user's saved teams and shows the selected one's member roster. */
    public function index(?string $paramTeamId = null) {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $userId = session()->get('user_id');
        $teamId = $paramTeamId ?? $this->request->getGet('team_id');

        $userModel     = new UserModel();
        $unitModel     = new UnitModel();
        $positionModel = new PositionModel();
        $presetModel   = new RoutingPresetModel();
        $memberModel   = new RoutingPresetMemberModel();
        $folderModel   = new DocumentFolderModel();

        // Scope calculation: Admin has full university access, Supervisor is scoped to their unit branch
        $allowedUnitIds = null;
        $hasLowerLevels = true;
        $supervisorUnit = null;

        if ($role !== 'Admin') {
            $db = \Config\Database::connect();
            $userPlantillas = $db->table('plantillas')
                ->select('unit_id')
                ->where('user_id', $userId)
                ->where('ended_at IS NULL')
                ->where('unit_id IS NOT NULL')
                ->get()->getResultArray();
            $myUnitIds = array_unique(array_filter(array_map('intval', array_column($userPlantillas, 'unit_id'))));
            $allowedUnitIds = !empty($myUnitIds) ? $unitModel->getDescendantIds($myUnitIds) : [];
            
            // Check if supervisor's assigned unit has any child/subordinate units
            $childUnits = array_diff($allowedUnitIds, $myUnitIds);
            $hasLowerLevels = !empty($childUnits);

            if (!empty($myUnitIds)) {
                $supervisorUnit = $unitModel->find(reset($myUnitIds));
            }
        }

        $users = $userModel->getEligibleTeamMembers($userId, $allowedUnitIds);
        if ($role === 'Admin') {
            $units = $unitModel->orderBy('name', 'ASC')->findAll();
        } else {
            $units = !empty($allowedUnitIds) ? $unitModel->whereIn('id', $allowedUnitIds)->orderBy('name', 'ASC')->findAll() : [];
        }

        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
        }
        unset($u);

        // Positions aren't unit-linked in the schema (the same title can span multiple
        // top-level branches - e.g. "Vice President" holds under both OVPAA and OVPAF),
        // so instead of listing every position system-wide, scope the "All Positions"
        // filter to only positions actually held by someone in $users (already
        // unit-scoped above for Supervisors). A position nobody in scope holds would
        // never match a user anyway, so excluding it costs nothing.
        $activePositionIds = [];
        foreach ($users as $u) {
            if (!empty($u['position_id'])) {
                $activePositionIds = array_merge($activePositionIds, array_map('intval', explode(',', $u['position_id'])));
            }
        }
        $activePositionIds = array_unique($activePositionIds);

        $positions = $positionModel->orderBy('title', 'ASC')->findAll();
        $positions = array_values(array_filter($positions, fn ($p) => in_array((int) $p['id'], $activePositionIds, true)));

        $presets = $presetModel->getPresetsWithDetails($userId);

        foreach ($presets as &$p) {
            $p['in_use'] = $folderModel->where('routing_preset_id', $p['id'])
                                       ->where('deleted_at IS NULL')
                                       ->countAllResults() > 0;
        }
        unset($p);

        if (!$teamId && !empty($presets)) {
            return redirect()->to('teams?team_id=' . $presets[0]['id']);
        }

        $activeTeam = null;
        $activeMemberIds = [];

        if ($teamId) {
            $activeTeam = $presetModel->where('id', $teamId)->where('owner_id', $userId)->first();
            if ($activeTeam) {
                $members = $memberModel->where('preset_id', $teamId)->findAll();
                $activeMemberIds = array_column($members, 'user_id');

                $cascadedFolders = $folderModel->where('routing_preset_id', $teamId)
                                               ->where('user_id', $userId)
                                               ->where('deleted_at IS NULL')
                                               ->findAll();
                $activeTeam['in_use']          = !empty($cascadedFolders);
                $activeTeam['cycle_titles']    = array_values(array_unique(array_filter(array_column($cascadedFolders, 'title'))));
                $targetCycleTitles             = [];
                $lockedCycleTitles             = [];
                foreach ($cascadedFolders as $cf) {
                    $cfTitle = trim((string)($cf['title'] ?? ''));
                    if ($this->isFolderInActiveTargetWindow($cf, (string)$role)) {
                        if ($cfTitle !== '') {
                            $targetCycleTitles[] = $cfTitle;
                        }
                    } else {
                        if ($cfTitle !== '') {
                            $lockedCycleTitles[] = $cfTitle;
                        }
                    }
                }
                $activeTeam['target_cycle_titles'] = array_values(array_unique($targetCycleTitles));
                $activeTeam['locked_cycle_titles'] = array_values(array_unique($lockedCycleTitles));
                $activeTeam['in_target_phase']     = !empty($targetCycleTitles);
            } else {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); 
            }
        }

        return view('components/app_shell', [
            'context'        => 'teams',
            'sidebarTitle'   => 'My Staff',
            'sidebarView'    => 'teams/_sidebar',
            'sidebarData'    => [
                'presets'        => $presets,
                'selectedTeamId' => $activeTeam['id'] ?? null
            ],
            'mainView'       => 'teams/index',
            'mainData'       => [
                'users'           => $users,
                'units'           => $units,
                'positions'       => $positions,
                'presets'         => $presets,
                'activeTeam'      => $activeTeam,
                'activeMemberIds' => $activeMemberIds,
                'hasLowerLevels'  => $hasLowerLevels,
                'supervisorUnit'  => $supervisorUnit,
                'myUnitIds'       => $myUnitIds ?? [],
            ]
        ]);
    }

    /** POST /teams/create - Creates an empty, unnamed staff shell so the UI has an id to attach members to. */
    public function createShell() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return $this->respondError('Unauthorized.', 403);

        $presetModel = new RoutingPresetModel();

        $name = trim($this->request->getPost('name'));
        if (empty($name)) $name = 'Staff';

        $name = resolve_unique_title($name, ['owner_id' => session()->get('user_id')], 'name', $presetModel);

        $newId = create_unique_row($presetModel, [
            'owner_id'    => session()->get('user_id'),
            'name'        => $name,
            'description' => trim($this->request->getPost('description')) ?: null,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        if (!$newId) {
            return $this->respondError('Could not generate a unique ID.', 400);
        }

        return $this->respond(['status' => 'success', 'id' => $newId]);
    }

    /** POST /teams - Saves a staff list's name/description and replaces its full member list. */
    public function store() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $teamId  = $this->request->getPost('team_id');
        $name    = trim($this->request->getPost('name'));
        $userIds = $this->request->getPost('user_ids') ?? [];
        $userId  = session()->get('user_id');

        $presetModel = new RoutingPresetModel();
        $memberModel = new RoutingPresetMemberModel();

        // Scope check: Supervisor can only add members belonging to their unit branch
        $allowedUnitIds = null;
        if ($role !== 'Admin') {
            $db = \Config\Database::connect();
            $userPlantillas = $db->table('plantillas')
                ->select('unit_id')
                ->where('user_id', $userId)
                ->where('ended_at IS NULL')
                ->where('unit_id IS NOT NULL')
                ->get()->getResultArray();
            $myUnitIds = array_unique(array_filter(array_map('intval', array_column($userPlantillas, 'unit_id'))));
            $unitModel = new UnitModel();
            $allowedUnitIds = !empty($myUnitIds) ? $unitModel->getDescendantIds($myUnitIds) : [];
        }

        $userModel = new UserModel();
        $eligible = $userModel->getEligibleTeamMembers($userId, $allowedUnitIds);
        $allowedUserIds = array_map('strval', array_column($eligible, 'user_id'));
        $userIds = array_values(array_intersect($userIds, $allowedUserIds));

        if (empty($name)) $name = 'Staff';

        $name = resolve_unique_title($name, function($model) use ($teamId, $userId) {
            $model->where('owner_id', $userId)->where('id !=', $teamId);
        }, 'name', $presetModel);

        $isNew = empty($teamId);
        if ($isNew) {
            $teamId = create_unique_row($presetModel, [
                'owner_id'    => $userId,
                'name'        => $name,
                'description' => trim($this->request->getPost('description')) ?: null,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            if (!$teamId) {
                if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' || str_contains($this->request->getHeaderLine('Accept'), 'application/json')) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Could not generate unique staff ID.'])->setStatusCode(400);
                }
                return redirect()->to(site_url('teams'))->with('error', 'Could not generate unique staff ID.');
            }
        }

        $previousUserIds = [];
        if (!$isNew) {
            $prevRows = $memberModel->where('preset_id', $teamId)->findAll();
            $previousUserIds = array_map('intval', array_column($prevRows, 'user_id'));
        }

        $presetModel->db->transStart();

        if (!$isNew) {
            $presetModel->where('id', $teamId)->where('owner_id', $userId)->set([
                'name'        => $name,
                'description' => trim($this->request->getPost('description')) ?: null,
                'updated_at'  => date('Y-m-d H:i:s')
            ])->update();
            
            $memberModel->where('preset_id', $teamId)->delete();
        }

        $membersData = [];
        foreach ($userIds as $uid) {
            $membersData[] = [
                'preset_id'  => $teamId,
                'user_id'    => $uid,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($membersData)) {
            $memberModel->insertBatch($membersData);
        }

        $syncSummary = ['added' => 0, 'removed' => 0];
        if (!$isNew) {
            $syncSummary = $this->syncActiveCascadedFolders(
                (string)$teamId,
                (int)$userId,
                (string)$role,
                $previousUserIds,
                array_map('intval', $userIds)
            );
        }

        $presetModel->db->transComplete();

        $isJsonRequest = $this->request->isAJAX() 
                      || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' 
                      || str_contains($this->request->getHeaderLine('Accept'), 'application/json');

        if ($presetModel->db->transStatus() === false) {
            if ($isJsonRequest) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save staff list.'])->setStatusCode(500);
            }
            return redirect()->to(site_url('teams' . ($teamId ? ('?team_id=' . $teamId) : '')))->with('error', 'Failed to update staff list.');
        }

        $saveMsg = 'Staff list saved successfully!';
        if ($syncSummary['added'] > 0 && $syncSummary['removed'] > 0) {
            $saveMsg = "Staff list saved! Added {$syncSummary['added']} and removed {$syncSummary['removed']} member(s) in the active cycle.";
        } elseif ($syncSummary['added'] > 0) {
            $saveMsg = "Staff list saved! Automatically cascaded {$syncSummary['added']} new member(s) into the active cycle.";
        } elseif ($syncSummary['removed'] > 0) {
            $saveMsg = "Staff list saved! Removed {$syncSummary['removed']} member(s) from the active cycle.";
        }

        if ($isJsonRequest) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $saveMsg,
                'team'    => [
                    'id'           => $teamId,
                    'name'         => $name,
                    'member_count' => count($userIds),
                    'description'  => trim($this->request->getPost('description')) ?: ''
                ]
            ]);
        }

        return redirect()->to(site_url('teams?team_id=' . $teamId))->with('success', $saveMsg);
    }

    /**
     * Option A Gating: Checks whether a cascaded folder is still in an active Target Setting phase
     * and its target submission window has not yet expired.
     */
    private function isFolderInActiveTargetWindow(array $folder, string $role, ?string $targetDocType = null): bool
    {
        $isAdminRoot = ($role === 'Admin' && empty($folder['parent_folder_id']));
        if (!$isAdminRoot) {
            $cStatus = \App\Enums\FolderStatus::canonical($folder['status'] ?? '');
            if (!in_array($cStatus, [
                \App\Enums\FolderStatus::DRAFT_TARGET,
                \App\Enums\FolderStatus::TARGET_SUBMITTED,
                \App\Enums\FolderStatus::TARGET_APPROVED,
            ], true)) {
                return false;
            }
        }

        $db = \Config\Database::connect();
        $cycleFolderId = $folder['cycle_folder_id'] ?? $folder['id'];
        $cycleId       = $folder['cycle_id'] ?? null;

        $builder = $db->table('cycle_deadlines');
        if (!empty($cycleId)) {
            $builder->where('cycle_id', $cycleId);
        } else {
            $builder->where('cycle_folder_id', $cycleFolderId);
        }

        if ($targetDocType !== null) {
            $builder->where('LOWER(doc_type)', strtolower($targetDocType));
        }

        $deadlines = $builder->get()->getResultArray();
        if (empty($deadlines)) {
            return true;
        }

        $nowTs = time();
        foreach ($deadlines as $dl) {
            if (empty($dl['target_end'])) {
                return true;
            }
            $endTs = strtotime((string)$dl['target_end']);
            if ($endTs === false || $endTs >= $nowTs) {
                return true;
            }
        }

        return false;
    }

    /**
     * Automatically synchronizes newly added and removed staff members into any active
     * Target-Phase evaluation cycles where this staff preset is currently cascaded.
     */
    private function syncActiveCascadedFolders(string $teamId, int $userId, string $role, array $previousUserIds, array $newUserIds): array
    {
        helper('functions');
        $folderModel  = new DocumentFolderModel();
        $routingModel = new \App\Models\EvaluationRoutingModel();
        $userModel    = new UserModel();

        $cascadedFolders = $folderModel->where('routing_preset_id', $teamId)
                                       ->where('user_id', $userId)
                                       ->where('deleted_at IS NULL')
                                       ->findAll();

        $addedTotal   = 0;
        $removedTotal = 0;

        if (empty($cascadedFolders)) {
            return ['added' => 0, 'removed' => 0];
        }

        $removedUserIds = array_values(array_diff($previousUserIds, $newUserIds));

        foreach ($cascadedFolders as $parentFolder) {
            // Option A: Block syncing into cycles past the Target Setting phase
            if (!$this->isFolderInActiveTargetWindow($parentFolder, $role)) {
                continue;
            }

            $parentFolderId = $parentFolder['id'];
            $cycleFolderId  = $parentFolder['cycle_folder_id'] ?? $folderModel->getRootCycleFolderId($parentFolderId);

            // 1. Sync Additions (and any member in $newUserIds missing from this cycle)
            foreach ($newUserIds as $targetUserId) {
                $targetUser = $userModel->find($targetUserId);
                if (!$targetUser) {
                    continue;
                }

                $docType = strtolower($targetUser['doc_type'] ?? ($role === 'Admin' ? 'opcr' : 'ipcr'));
                if (!$this->isFolderInActiveTargetWindow($parentFolder, $role, $docType)) {
                    continue;
                }

                // Check if already cascaded/routed under this parent folder
                $exists = $folderModel->where('user_id', $targetUserId)
                                      ->where('parent_folder_id', $parentFolderId)
                                      ->where('deleted_at IS NULL')
                                      ->first();
                if (!$exists) {
                    $routedFolderIds = $routingModel->where('evaluator_folder_id', $parentFolderId)
                        ->findColumn('folder_id') ?: [];
                    if (!empty($routedFolderIds)) {
                        $exists = $folderModel->where('user_id', $targetUserId)
                            ->whereIn('id', $routedFolderIds)
                            ->where('deleted_at IS NULL')
                            ->first();
                    }
                }
                if ($exists) {
                    continue;
                }

                // Check if subordinate already has a folder in this cycle from another co-supervisor
                $existingCycleBuilder = $folderModel->where('user_id', $targetUserId)
                                                    ->where('deleted_at IS NULL');
                if (!empty($parentFolder['cycle_id'])) {
                    $existingCycleBuilder->where('cycle_id', $parentFolder['cycle_id']);
                } elseif (!empty($cycleFolderId)) {
                    $existingCycleBuilder->where('cycle_folder_id', $cycleFolderId);
                } else {
                    $existingCycleBuilder->where('title', $parentFolder['title']);
                }
                $existingCycleFolder = $existingCycleBuilder->first();

                if ($existingCycleFolder) {
                    $routingExists = $routingModel->where('folder_id', $existingCycleFolder['id'])
                                                  ->where('evaluator_id', $userId)
                                                  ->first();
                    if (!$routingExists) {
                        $routingModel->insert([
                            'folder_id'           => $existingCycleFolder['id'],
                            'evaluator_id'        => $userId,
                            'evaluator_folder_id' => $parentFolderId,
                            'status'              => \App\Enums\EvaluationRoutingStatus::DRAFT->value
                        ]);

                        // If previously approved by another co-supervisor, revert to target_submitted so the new supervisor can also review
                        if ($existingCycleFolder['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value) {
                            $folderModel->update($existingCycleFolder['id'], [
                                'status'             => \App\Enums\FolderStatus::TARGET_SUBMITTED->value,
                                'target_approved_at' => null,
                            ]);
                        }

                        $addedTotal++;
                    }
                    continue;
                }

                // Provision a new child folder & default form template
                $tabs = get_default_form_template(strtoupper($docType));
                $newFolderId = create_unique_row($folderModel, [
                    'title'                        => $parentFolder['title'],
                    'user_id'                      => $targetUserId,
                    'cycle_id'                     => $parentFolder['cycle_id'] ?? null,
                    'parent_folder_id'             => $parentFolderId,
                    'cycle_folder_id'              => $cycleFolderId,
                    'rating_period'                => $parentFolder['rating_period'] ?? null,
                    'approving_authority_name'     => $parentFolder['approving_authority_name'] ?? null,
                    'approving_authority_position' => $parentFolder['approving_authority_position'] ?? null,
                    'tabs'                         => json_encode($tabs),
                    'status'                       => \App\Enums\FolderStatus::DRAFT_TARGET->value
                ]);

                if ($newFolderId) {
                    $routingModel->insert([
                        'folder_id'           => $newFolderId,
                        'evaluator_id'        => $userId,
                        'evaluator_folder_id' => $parentFolderId,
                        'status'              => \App\Enums\EvaluationRoutingStatus::DRAFT->value
                    ]);

                    $paperType = strtoupper($docType);
                    notify_user($targetUserId, [
                        'sender_id' => $userId,
                        'type'      => 'target_assigned',
                        'title'     => 'Evaluation Target Assigned',
                        'message'   => "You have been assigned to prepare your {$paperType} for \"{$parentFolder['title']}\".",
                        'link'      => 'folders/' . $newFolderId,
                        'icon'      => 'file'
                    ]);

                    $addedTotal++;
                }
            }

            // 2. Sync Removals
            foreach ($removedUserIds as $removedUserId) {
                $childFolder = $folderModel->where('user_id', $removedUserId)
                                           ->where('parent_folder_id', $parentFolderId)
                                           ->where('deleted_at IS NULL')
                                           ->first();

                if (!$childFolder) {
                    $routedFolderIds = $routingModel->where('evaluator_folder_id', $parentFolderId)
                        ->findColumn('folder_id') ?: [];
                    if (!empty($routedFolderIds)) {
                        $childFolder = $folderModel->where('user_id', $removedUserId)
                            ->whereIn('id', $routedFolderIds)
                            ->where('deleted_at IS NULL')
                            ->first();
                    }
                }

                if (!$childFolder) {
                    continue;
                }

                $cChildStatus = \App\Enums\FolderStatus::canonical($childFolder['status'] ?? '');
                if (!$cChildStatus->isTargetPhase()) {
                    continue;
                }

                // Multi-Supervisor Check: Does this subordinate still have another supervisor in this cycle?
                $otherRoutings = $routingModel->where('folder_id', $childFolder['id'])
                                              ->where('evaluator_id !=', $userId)
                                              ->findAll();

                if (!empty($otherRoutings)) {
                    // Detach ONLY this supervisor's routing row; keep folder & progress intact for remaining supervisor(s)
                    $routingModel->where('folder_id', $childFolder['id'])
                                 ->where('evaluator_id', $userId)
                                 ->delete();

                    $updateData = [];
                    if ($childFolder['parent_folder_id'] == $parentFolderId && !empty($otherRoutings[0]['evaluator_folder_id'])) {
                        $updateData['parent_folder_id'] = $otherRoutings[0]['evaluator_folder_id'];
                    }

                    // If folder was waiting on this supervisor and all remaining supervisors have already approved, mark target_approved
                    if ($cChildStatus === \App\Enums\FolderStatus::TARGET_SUBMITTED) {
                        $allRemainingApproved = true;
                        foreach ($otherRoutings as $or) {
                            if (($or['status'] ?? '') !== \App\Enums\EvaluationRoutingStatus::APPROVED->value) {
                                $allRemainingApproved = false;
                                break;
                            }
                        }
                        if ($allRemainingApproved) {
                            $updateData['status']             = \App\Enums\FolderStatus::TARGET_APPROVED->value;
                            $updateData['target_approved_at'] = date('Y-m-d H:i:s');
                        }
                    }

                    if (!empty($updateData)) {
                        $folderModel->update($childFolder['id'], $updateData);
                    }

                    $removedTotal++;
                    continue;
                }

                // Single Supervisor: Delete subordinate's cycle folder and any descendants
                $allFolderIds = [$childFolder['id']];
                $currentLevel = [$childFolder['id']];
                $levels       = [[$childFolder['id']]];
                while (!empty($currentLevel)) {
                    $nextLevel = $folderModel->whereIn('parent_folder_id', $currentLevel)->findColumn('id');
                    if (!empty($nextLevel)) {
                        $levels[]     = $nextLevel;
                        $allFolderIds = array_merge($allFolderIds, $nextLevel);
                        $currentLevel = $nextLevel;
                    } else {
                        break;
                    }
                }
                $allFolderIds = array_unique($allFolderIds);

                if ($folderModel->db->tableExists('document_attachments')) {
                    $folderModel->db->table('document_attachments')
                        ->groupStart()
                            ->whereIn('document_id', $allFolderIds)
                            ->orWhereIn('folder_id', $allFolderIds)
                        ->groupEnd()
                        ->delete();
                }
                if ($folderModel->db->tableExists('document_comments')) {
                    $folderModel->db->table('document_comments')->whereIn('document_id', $allFolderIds)->delete();
                }
                if ($folderModel->db->tableExists('evaluation_routings')) {
                    $folderModel->db->table('evaluation_routings')
                        ->groupStart()
                            ->whereIn('folder_id', $allFolderIds)
                            ->orWhereIn('evaluator_folder_id', $allFolderIds)
                        ->groupEnd()
                        ->delete();
                }
                foreach ($allFolderIds as $delId) {
                    $folderModel->db->query("DELETE FROM notifications WHERE link LIKE '%folders/{$delId}%' OR link LIKE '%ratings/show/{$delId}%'");
                }

                $levelsReversed = array_reverse($levels);
                foreach ($levelsReversed as $levelIds) {
                    $folderModel->whereIn('id', $levelIds)->delete(null, true);
                }

                notify_user((int)$removedUserId, [
                    'sender_id' => $userId,
                    'type'      => 'target_revoked',
                    'title'     => 'Evaluation Assignment Removed',
                    'message'   => ($role === 'Admin')
                        ? "Your evaluation assignment for \"{$parentFolder['title']}\" has been revoked by the Administrator."
                        : "Your evaluation target assignment for \"{$parentFolder['title']}\" has been removed by your supervisor.",
                    'link'      => 'folders',
                    'icon'      => 'user-minus'
                ]);

                $removedTotal++;
            }

            // If no cascaded children or routings remain under parentFolder, clear routing_preset_id
            $remainingDirect = $folderModel->where('parent_folder_id', $parentFolderId)
                                           ->where('deleted_at IS NULL')
                                           ->countAllResults();
            $remainingRouted = $routingModel->where('evaluator_folder_id', $parentFolderId)
                                            ->countAllResults();
            if ($remainingDirect === 0 && $remainingRouted === 0) {
                $folderModel->update($parentFolderId, ['routing_preset_id' => null]);
            }
        }

        return ['added' => $addedTotal, 'removed' => $removedTotal];
    }

    /** POST /teams/delete - Archives (soft-deletes) a staff list if cascaded, or hard-deletes if unused. */
    public function delete() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return $this->respondError('Unauthorized.', 403);

        $presetId = $this->request->getVar('preset_id') ?? $this->request->getPost('preset_id');
        $presetModel = new RoutingPresetModel();

        $preset = $presetModel->where('id', $presetId)->where('owner_id', session()->get('user_id'))->first();

        if ($preset) {
            $folderModel = new \App\Models\DocumentFolderModel();
            $isCascaded = $folderModel->where('routing_preset_id', $presetId)->countAllResults() > 0;
            
            if ($isCascaded) {
                // Soft delete: folders already cascaded from this staff list keep their
                // routing_preset_id pointing at it, so archiving is safe.
                $presetModel->delete($presetId);
            } else {
                // Hard delete: if it was never used, we can wipe it entirely
                $presetModel->delete($presetId, true);
            }
            return $this->respond(['status' => 'success', 'message' => 'Staff list archived/deleted successfully.']);
        }

        return $this->respondError('Staff list not found or unauthorized.');
    }
}
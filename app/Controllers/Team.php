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
    public function index() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $userId = session()->get('user_id');
        $teamId = $this->request->getGet('team_id');

        $userModel     = new UserModel();
        $unitModel     = new UnitModel();
        $positionModel = new PositionModel();
        $presetModel   = new RoutingPresetModel();
        $memberModel   = new RoutingPresetMemberModel();
        $folderModel   = new DocumentFolderModel();

        $users = $userModel->getEligibleTeamMembers($userId);
        $units = $unitModel->orderBy('name', 'ASC')->findAll();

        // Supervisors are scoped to their own unit and everything nested under it
        // (e.g. a VP only sees/builds teams from their colleges/offices); Admins see everyone.
        if ($role === 'Supervisor') {
            $ownPlantilla  = $userModel->getActivePlantillaDetails($userId);
            $scopedUnitIds = $ownPlantilla ? $unitModel->getDescendantIds([$ownPlantilla['unit_id']]) : [];

            $units = array_values(array_filter($units, fn ($u) => in_array($u['id'], $scopedUnitIds)));
            $users = array_values(array_filter($users, function ($u) use ($scopedUnitIds) {
                if (empty($u['unit_id'])) return false;
                return count(array_intersect(explode(',', $u['unit_id']), $scopedUnitIds)) > 0;
            }));
        }

        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
        }
        unset($u);

        $positions = $positionModel->orderBy('title', 'ASC')->findAll();

        $presets = $presetModel->getPresetsWithDetails($userId);

        foreach ($presets as &$p) {
            $p['in_use'] = $folderModel->where('routing_preset_id', $p['id'])->countAllResults() > 0;
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
            } else {
                return redirect()->to('teams')->with('error', 'Team not found.'); 
            }
        }

        return view('app_shell', [
            'context'        => 'teams',
            'sidebarTitle'   => 'Teams',
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
                'activeMemberIds' => $activeMemberIds
            ]
        ]);
    }

    /** POST /teams/create - Creates an empty, unnamed team shell so the UI has an id to attach members to. */
    public function createShell() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $presetModel = new RoutingPresetModel();
        
        $name = trim($this->request->getPost('name'));
        if (empty($name)) $name = 'Team';
        
        $name = resolve_unique_title($name, ['owner_id' => session()->get('user_id')], 'name', $presetModel);
        
        $newId = $presetModel->insert([
            'owner_id'    => session()->get('user_id'),
            'name'        => $name,
            'description' => trim($this->request->getPost('description')) ?: null,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
        
        return redirect()->to("teams?team_id={$newId}");
    }

    /** POST /teams - Saves a team's name/description and replaces its full member list. */
    public function store() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $teamId  = $this->request->getPost('team_id');
        $name    = trim($this->request->getPost('name'));
        $userIds = $this->request->getPost('user_ids') ?? [];
        $userId  = session()->get('user_id');

        $presetModel = new RoutingPresetModel();
        $memberModel = new RoutingPresetMemberModel();

        // Server-side mirror of the index() scoping: a Supervisor can't add members
        // outside their own unit branch by crafting the request directly.
        if ($role === 'Supervisor') {
            $userModel     = new UserModel();
            $unitModel     = new UnitModel();
            $ownPlantilla  = $userModel->getActivePlantillaDetails($userId);
            $scopedUnitIds = $ownPlantilla ? $unitModel->getDescendantIds([$ownPlantilla['unit_id']]) : [];

            $eligible = array_filter($userModel->getEligibleTeamMembers($userId), function ($u) use ($scopedUnitIds) {
                if (empty($u['unit_id'])) return false;
                return count(array_intersect(explode(',', $u['unit_id']), $scopedUnitIds)) > 0;
            });
            $allowedUserIds = array_map('strval', array_column($eligible, 'user_id'));

            $userIds = array_values(array_intersect($userIds, $allowedUserIds));
        }

        if (empty($name)) $name = 'Team';

        $name = resolve_unique_title($name, function($db) use ($teamId, $userId) {
            return $db->table('routing_presets')->where('owner_id', $userId)->where('id !=', $teamId);
        }, 'name', $presetModel);

        $presetModel->db->transStart();

        $presetModel->where('id', $teamId)->where('owner_id', $userId)->set([
            'name'        => $name,
            'description' => trim($this->request->getPost('description')) ?: null,
            'updated_at'  => date('Y-m-d H:i:s')
        ])->update();
        
        $memberModel->where('preset_id', $teamId)->delete();

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

        $presetModel->db->transComplete();

        if ($presetModel->db->transStatus() === false) return redirect()->back()->with('error', 'Failed to update team.');
        return redirect()->back()->with('success', 'Distribution list saved successfully!');
    }

    /** POST /teams/delete - Deletes a team, refusing if it's currently cascaded onto a live folder. */
    public function delete() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $presetId = $this->request->getPost('preset_id');
        $presetModel = new RoutingPresetModel();
        
        $preset = $presetModel->where('id', $presetId)->where('owner_id', session()->get('user_id'))->first();
        
        if ($preset) {
            $folderModel = new DocumentFolderModel();
            if ($folderModel->where('routing_preset_id', $presetId)->countAllResults() > 0) {
                return redirect()->back()->with('error', 'Backend blocked: Team is actively cascaded.');
            }

            $presetModel->delete($presetId);
            return redirect()->to('teams')->with('success', 'Team deleted successfully.');
        }

        return redirect()->back()->with('error', 'Team not found or unauthorized.');
    }
}
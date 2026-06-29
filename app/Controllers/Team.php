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

class Team extends BaseController
{
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

        // 1. Fetch Master Data
        $users = $userModel->db->table('users u')
            ->select("u.id as user_id, u.first_name, u.last_name, u.email, 
                      GROUP_CONCAT(DISTINCT pos.id) as position_id, 
                      REPLACE(GROUP_CONCAT(DISTINCT pos.title), ',', ', ') as position, 
                      MAX(pos.is_teaching) as is_teaching, 
                      GROUP_CONCAT(DISTINCT un.id) as unit_id, 
                      REPLACE(GROUP_CONCAT(DISTINCT un.name), ',', ', ') as department")
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->where('u.is_active', 1)
            ->where('u.id !=', $userId) 
            ->groupBy('u.id') 
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();
        
        foreach ($users as &$u) {
            if ($u['position'])   $u['position']   = str_replace(',', ', ', $u['position']);
            if ($u['department']) $u['department'] = str_replace(',', ', ', $u['department']);
        }

        $units     = $unitModel->orderBy('name', 'ASC')->findAll();
        $positions = $positionModel->orderBy('title', 'ASC')->findAll();

        $presets = $presetModel->select('routing_presets.*, COUNT(rpm.id) as member_count')
            ->join('routing_preset_members rpm', 'rpm.preset_id = routing_presets.id', 'left')
            ->where('routing_presets.owner_id', $userId)
            ->groupBy('routing_presets.id')
            ->orderBy('routing_presets.created_at', 'DESC')
            ->findAll();

        foreach ($presets as &$p) {
            $p['in_use'] = $folderModel->where('routing_preset_id', $p['id'])->countAllResults() > 0;
        }
        unset($p);

        // 2. Handle Auto-Routing & Active Team selection
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

        // 3. Return using the App Shell paradigm
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

    public function createShell() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $presetModel = new RoutingPresetModel();
        
        $newId = $presetModel->insert([
            'owner_id'    => session()->get('user_id'),
            'name'        => trim($this->request->getPost('name')),
            'description' => trim($this->request->getPost('description')) ?: null,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
        
        return redirect()->to("teams?team_id={$newId}");
    }

    public function store() {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $teamId  = $this->request->getPost('team_id');
        $name    = trim($this->request->getPost('name'));
        $userIds = $this->request->getPost('user_ids') ?? [];

        if (empty($name)) return redirect()->back()->with('error', 'Team name is required.');

        $presetModel = new RoutingPresetModel();
        $memberModel = new RoutingPresetMemberModel();

        $presetModel->db->transStart();

        $presetModel->where('id', $teamId)->where('owner_id', session()->get('user_id'))->set([
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
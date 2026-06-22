<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Team extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $teamId = $this->request->getGet('team_id');

        // 1. Fetch all active users with their Plantilla assignments
        $users = $db->table('users u')
            ->select('u.id as user_id, u.first_name, u.last_name, u.email, pos.id as position_id, pos.title as position, pos.is_teaching, un.id as unit_id, un.name as department')
            ->join('plantilla p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left')
            ->where('u.is_active', 1)
            ->where('u.id !=', $userId) 
            ->orderBy('u.last_name', 'ASC')
            ->get()->getResultArray();

        $units = $db->table('units')->orderBy('name', 'ASC')->get()->getResultArray();
        $positions = $db->table('positions')->orderBy('title', 'ASC')->get()->getResultArray();

        // 2. Fetch existing Presets for the current user
        $presets = $db->table('routing_presets rp')
            ->select('rp.*, COUNT(rpm.id) as member_count')
            ->join('routing_preset_members rpm', 'rpm.preset_id = rp.id', 'left')
            ->where('rp.owner_id', $userId)
            ->groupBy('rp.id')
            ->orderBy('rp.created_at', 'DESC')
            ->get()->getResultArray();

        // 3. State Management: Fetch Active Team if selected
        $activeTeam = null;
        $activeMemberIds = [];

        if ($teamId) {
            $activeTeam = $db->table('routing_presets')->where('id', $teamId)->where('owner_id', $userId)->get()->getRowArray();
            if ($activeTeam) {
                // Fetch just the IDs of the members to pass to JavaScript for pre-loading
                $members = $db->table('routing_preset_members')->where('preset_id', $teamId)->get()->getResultArray();
                $activeMemberIds = array_column($members, 'user_id');
            } else {
                return redirect()->to('teams')->with('error', 'Team not found.'); // Prevent URL tampering
            }
        }

        return view('teams/index', [
            'users'           => $users,
            'units'           => $units,
            'positions'       => $positions,
            'presets'         => $presets,
            'activeTeam'      => $activeTeam,
            'activeMemberIds' => $activeMemberIds
        ]);
    }

    // NEW: Creates the empty shell from the Modal and redirects to the workspace
    public function createShell()
    {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $db = \Config\Database::connect();
        
        $db->table('routing_presets')->insert([
            'owner_id'    => session()->get('user_id'),
            'name'        => trim($this->request->getPost('name')),
            'description' => trim($this->request->getPost('description')) ?: null,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
        
        $newId = $db->insertID();
        return redirect()->to("teams?team_id={$newId}");
    }

    // UPDATED: Syncs the selected team members
    public function store()
    {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $db = \Config\Database::connect();
        $teamId  = $this->request->getPost('team_id');
        $name    = trim($this->request->getPost('name'));
        $userIds = $this->request->getPost('user_ids') ?? []; // Array of selected IDs (can be empty)

        if (empty($name)) return redirect()->back()->with('error', 'Team name is required.');

        $db->transStart();

        // 1. Update the Team's Name and Description
        $db->table('routing_presets')->where('id', $teamId)->where('owner_id', session()->get('user_id'))->update([
            'name'        => $name,
            'description' => trim($this->request->getPost('description')) ?: null,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);
        
        // 2. Wipe the old members
        $db->table('routing_preset_members')->where('preset_id', $teamId)->delete();

        // 3. Insert the new members
        $membersData = [];
        foreach ($userIds as $uid) {
            $membersData[] = [
                'preset_id'  => $teamId,
                'user_id'    => $uid,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($membersData)) {
            $db->table('routing_preset_members')->insertBatch($membersData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) return redirect()->back()->with('error', 'Failed to update team.');
        
        return redirect()->back()->with('success', 'Distribution list saved successfully!');
    }

    public function delete()
    {
        $role = session()->get('role');
        if (!in_array($role, ['Admin', 'Supervisor'])) return redirect()->to('/');

        $presetId = $this->request->getPost('preset_id');
        $db = \Config\Database::connect();
        
        $preset = $db->table('routing_presets')->where('id', $presetId)->where('owner_id', session()->get('user_id'))->get()->getRowArray();
        
        if ($preset) {
            $db->table('routing_presets')->where('id', $presetId)->delete();
            return redirect()->to('teams')->with('success', 'Team deleted successfully.');
        }

        return redirect()->back()->with('error', 'Team not found or unauthorized.');
    }
}

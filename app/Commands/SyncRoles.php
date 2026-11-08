<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\OrgStructure;

/**
 * Role follows position (2026-11-07): sets every Staff account to Supervisor when they hold a head position and to
 * Employee otherwise. The app keeps this in step after each change; run this once to fix older mismatches
 * (e.g. an Employee still holding Department Chair). --dry only lists what would change.
 */
class SyncRoles extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:sync-roles';
    protected $description = 'Sets each Staff account to Supervisor (holds a head position) or Employee (does not).';
    protected $usage       = 'spms:sync-roles [--dry]';
    protected $options     = ['--dry' => 'List the changes without saving them.'];

    public function run(array $params)
    {
        helper(['functions', 'audit']);
        $dry = CLI::getOption('dry') !== null || in_array('--dry', $params, true);
        $org = OrgStructure::instance();
        $db = \Config\Database::connect();
        $rows = $db->table('users u')->select('u.id, u.email, r.name AS role')->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')->whereIn('r.name', ['Employee', 'Supervisor'])->orderBy('u.email')->get()->getResultArray();
        $changes = 0;
        foreach ($rows as $r) {
            $should = $org->isHead((int) $r['id']) ? 'Supervisor' : 'Employee';
            if ($should === $r['role']) continue;
            $changes++;
            CLI::write(sprintf('  %-40s %s -> %s', $r['email'], $r['role'], $should));
            if (!$dry) sync_rated_role((int) $r['id']);
        }
        CLI::write($changes ? ($dry ? "{$changes} change(s) would be made (dry run)." : "{$changes} change(s) made.") : 'Every Staff account already matches their position.', $changes ? 'yellow' : 'green');
    }
}

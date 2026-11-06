<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\OrgStructure;

/**
 * Prints the organisation structure as the system works it out: every unit with its head position(s),
 * who holds them, how many positions its members can pick, and anything that needs the admin's attention
 * (no head position, several heads, people whose doc type the rules no longer allow).
 */
class OrgReport extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:org-report';
    protected $description = 'Shows each unit\'s head position(s) and holders, and flags what needs attention.';

    public function run(array $params)
    {
        $org = OrgStructure::instance();
        $units = $org->units();
        $children = [];
        foreach ($units as $u) $children[$u['parent_id'] ?? 0][] = $u;
        foreach ($children as &$list) usort($list, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        unset($list);

        $title = fn(int $id) => $org->position($id)['title'] ?? "#{$id}";
        $walk = function (int $parent, int $depth) use (&$walk, $children, $org, $title) {
            foreach ($children[$parent] ?? [] as $u) {
                $heads = $org->headPositionIds($u['id']);
                $holders = array_map(fn($h) => trim($h['first_name'] . ' ' . $h['last_name']), $org->headsOf($u['id']));
                $link = $u['head_position_id'] ? ' [linked]' : '';
                $line = str_repeat('  ', $depth) . $u['name'] . $link . ' | head: '
                      . ($heads ? implode(' / ', array_map($title, $heads)) : '(none)')
                      . ' | held by: ' . ($holders ? implode(', ', $holders) : '-')
                      . ' | member positions: ' . count($org->memberPositionIds($u['id']));
                $color = !$heads ? 'red' : (count($holders) > 1 ? 'yellow' : null);
                CLI::write($line, $color);
                $walk($u['id'], $depth + 1);
            }
        };
        CLI::write('Units (red: no head position; yellow: several people hold the head position):', 'yellow');
        $walk(0, 0);

        CLI::newLine();
        CLI::write('People whose doc type the rules do not allow:', 'yellow');
        $db = \Config\Database::connect();
        $n = 0;
        foreach ($db->table('users')->where('is_active', 1)->get()->getResultArray() as $u) {
            $role = $org->roleOf((int) $u['id']);
            if (!OrgStructure::isRatedRole($role)) continue;
            $allowed = $org->allowedDocTypesForUser((int) $u['id']);
            if (!in_array(strtoupper((string) $u['doc_type']), $allowed, true)) {
                $n++;
                CLI::write("  {$u['email']} ({$role}, {$u['employment_status']}): " . ($u['doc_type'] ?: 'none')
                    . ' -> allowed: ' . ($allowed ? implode(', ', $allowed) : 'nothing'), 'red');
            }
        }
        if (!$n) CLI::write('  none');
    }
}

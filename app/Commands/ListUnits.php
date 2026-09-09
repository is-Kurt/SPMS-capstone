<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ListUnits extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:list-units';
    protected $description = 'Lists all colleges and departments currently in the database.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $units = $db->table('units')->select('id, name, parent_id')->orderBy('id', 'ASC')->get()->getResultArray();

        $tree = [];
        foreach ($units as $u) {
            $pid = $u['parent_id'] ? (int)$u['parent_id'] : 0;
            $tree[$pid][] = $u;
        }

        $printNode = function($parentId, $level) use (&$printNode, &$tree) {
            if (!isset($tree[$parentId])) return;
            foreach ($tree[$parentId] as $u) {
                $indent = str_repeat('    ', $level);
                CLI::write($indent . "- [ID: " . $u['id'] . "] " . $u['name'], $level === 0 ? 'yellow' : ($level === 1 ? 'cyan' : 'white'));
                $printNode($u['id'], $level + 1);
            }
        };

        CLI::newLine();
        CLI::write("=========================================================", 'yellow');
        CLI::write(" BENGUET STATE UNIVERSITY - UNITS & DEPARTMENTS HIERARCHY", 'yellow');
        CLI::write("=========================================================", 'yellow');
        CLI::newLine();
        $printNode(0, 0);
        CLI::newLine();
    }
}

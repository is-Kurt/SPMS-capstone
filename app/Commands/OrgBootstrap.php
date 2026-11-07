<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * One-time setup of the organisation structure for an existing database (2026-11-07). Run once after
 * `spark migrate`; safe to run again (it only fills in what is still empty and never undoes admin changes).
 *
 *  1. Removes the President from the structure: the system starts at the Vice Presidents (their OPCRs are
 *     approved by the Admin). The "Office of the President" unit goes (its sub-units become top-level) and
 *     the "University President" position goes, when nobody holds them.
 *  2. Builds the position tree from the positions this database was seeded with, and the "General" root.
 *  3. Links the top units to their head positions (OVPAA -> VPAA, OVPAF -> VPAF, Planning / QA -> Unit Head).
 *  4. Sets the starting doc-type rules (roles and employment statuses) where none are set.
 *
 * This is the only place job titles and unit names are matched, once, to set up the data. The application
 * itself only reads the structure (App\Libraries\OrgStructure). Everything here can be changed afterwards
 * in Accounts > System Data. Use --dry to see what would change.
 */
class OrgBootstrap extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:org-bootstrap';
    protected $description = 'One-time setup of the position tree, unit links and doc-type rules for an existing database.';
    protected $options     = ['--dry' => 'Show what would change without changing anything.'];

    private bool $dry = false;
    private $db;

    private const POSITION_TREE = [
        // child title => parent title
        'Dean'                       => 'Vice President for Academic Affairs',
        'Department Chair'           => 'Dean',
        'Professor'                  => 'Department Chair',
        'Associate Professor'        => 'Department Chair',
        'Assistant Professor'        => 'Department Chair',
        'Instructor II'              => 'Department Chair',
        'Instructor I'               => 'Department Chair',
        'Unit Head'                  => 'Vice President for Administration and Finance',
        'Chief Accountant'           => 'Unit Head',
        'Bookkeeper'                 => 'Unit Head',
        'HR Management Officer'      => 'Unit Head',
        'Administrative Officer'     => 'Unit Head',
        'Planning Officer'           => 'Unit Head',
        'Quality Assurance Officer'  => 'Unit Head',
    ];
    private const GENERAL_POSITIONS = ['Administrative Assistant', 'Administrative Aide', 'Security Officer'];
    private const UNIT_LINKS = [
        'Office of the Vice President for Academic Affairs'          => 'Vice President for Academic Affairs',
        'Office of the Vice President for Administration and Finance' => 'Vice President for Administration and Finance',
        'Planning and Development Office'                            => 'Unit Head',
        'Quality Assurance Office'                                   => 'Unit Head',
    ];
    // Only the rated roles fill in forms: Admin, TWG and PMT never get a paper (Folder skips them on purpose).
    private const ROLE_RULES = ['Employee' => 'IPCR,IPERF', 'Supervisor' => 'DPCR,OPCR'];
    private const STATUS_RULES = ['Permanent' => 'IPCR,DPCR,OPCR', 'Contractual' => 'IPERF'];

    public function run(array $params)
    {
        $this->dry = CLI::getOption('dry') !== null || in_array('--dry', $params, true);
        $this->db = \Config\Database::connect();
        CLI::write('=== SPMS organisation structure setup' . ($this->dry ? ' (dry run)' : '') . ' ===', 'yellow');

        $this->db->transStart();
        $this->removePresident();
        $this->buildPositionTree();
        $this->linkUnits();
        $this->setRules();
        $this->db->transComplete();

        \App\Libraries\OrgStructure::reset();
        CLI::write($this->dry ? 'Dry run: nothing was changed.' : 'Done.', 'green');
    }

    private function say(string $msg): void
    {
        CLI::write(($this->dry ? '[would] ' : '') . $msg);
    }

    private function positionId(string $title): ?int
    {
        $row = $this->db->table('positions')->where('title', $title)->get()->getRowArray();
        return $row ? (int) $row['id'] : null;
    }

    private function removePresident(): void
    {
        $office = $this->db->table('units')->where('name', 'Office of the President')->get()->getRowArray();
        if ($office) {
            $inUse = $this->db->table('plantillas')->where('unit_id', $office['id'])->countAllResults();
            if ($inUse) {
                CLI::write("Office of the President kept: {$inUse} assignment(s) still use it. Move them, then run this again.", 'red');
            } else {
                $children = $this->db->table('units')->where('parent_id', $office['id'])->get()->getResultArray();
                foreach ($children as $c) {
                    $this->say("unit '{$c['name']}' becomes top-level");
                    if (!$this->dry) $this->db->table('units')->where('id', $c['id'])->update(['parent_id' => null]);
                }
                $this->say("delete unit 'Office of the President'");
                if (!$this->dry) {
                    $this->db->table('twg_unit_assignments')->where('unit_id', $office['id'])->delete();
                    $this->db->table('units')->where('id', $office['id'])->delete();
                }
            }
        }
        $pres = $this->positionId('University President');
        if ($pres !== null) {
            $inUse = $this->db->table('plantillas')->where('position_id', $pres)->countAllResults();
            if ($inUse) {
                CLI::write("Position 'University President' kept: {$inUse} assignment(s) use it.", 'red');
            } else {
                $this->say("delete position 'University President'");
                if (!$this->dry) $this->db->table('positions')->where('id', $pres)->delete();
            }
        }
    }

    private function buildPositionTree(): void
    {
        foreach (self::POSITION_TREE as $child => $parent) {
            $c = $this->positionId($child);
            $p = $this->positionId($parent);
            if ($c === null || $p === null) continue;
            $row = $this->db->table('positions')->where('id', $c)->get()->getRowArray();
            if (!empty($row['parent_id'])) continue;                   // already placed (by an admin or an earlier run)
            $this->say("position '{$child}' under '{$parent}'");
            if (!$this->dry) $this->db->table('positions')->where('id', $c)->update(['parent_id' => $p]);
        }

        $general = $this->db->table('positions')->where('is_general_root', 1)->get()->getRowArray();
        $generalId = $general ? (int) $general['id'] : null;
        if ($generalId === null) {
            $this->say("create the 'General' root");
            if (!$this->dry) {
                $this->db->table('positions')->insert(['title' => 'General', 'is_teaching' => 0, 'is_general_root' => 1,
                                                       'created_at' => date('Y-m-d H:i:s')]);
                $generalId = (int) $this->db->insertID();
            }
        }
        foreach (self::GENERAL_POSITIONS as $title) {
            $id = $this->positionId($title);
            if ($id === null) continue;
            $row = $this->db->table('positions')->where('id', $id)->get()->getRowArray();
            if (!empty($row['parent_id'])) continue;
            $this->say("position '{$title}' under 'General'");
            if (!$this->dry && $generalId) $this->db->table('positions')->where('id', $id)->update(['parent_id' => $generalId]);
        }
    }

    private function linkUnits(): void
    {
        foreach (self::UNIT_LINKS as $unitName => $posTitle) {
            $unit = $this->db->table('units')->where('name', $unitName)->get()->getRowArray();
            $pos = $this->positionId($posTitle);
            if (!$unit || $pos === null || !empty($unit['head_position_id'])) continue;
            $this->say("link unit '{$unitName}' to '{$posTitle}'");
            if (!$this->dry) $this->db->table('units')->where('id', $unit['id'])->update(['head_position_id' => $pos]);
        }
    }

    private function setRules(): void
    {
        foreach ([['roles', 'role_doc_types', 'role_id', self::ROLE_RULES], ['employment_statuses', 'employment_status_doc_types', 'employment_status_id', self::STATUS_RULES]] as [$parent, $table, $key, $rules]) {
            foreach ($rules as $name => $csv) {
                $row = $this->db->table($parent)->where('name', $name)->get()->getRowArray();
                if (!$row || $this->db->table($table)->where($key, $row['id'])->countAllResults() > 0) continue;   // already set
                $this->say("{$name} allows {$csv}");
                if ($this->dry) continue;
                foreach (explode(',', $csv) as $t) $this->db->table($table)->insert([$key => $row['id'], 'doc_type' => $t]);
            }
        }
    }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\ReviewHandover;

/**
 * Lists (or with --apply, revokes) the supervisors whose review on an unfinished paper is out of scope: they no
 * longer head a unit above the paper's owner. The app does this after each change; use --dry to look first.
 */
class ReviewScope extends BaseCommand
{
    protected $group       = 'SPMS';
    protected $name        = 'spms:review-scope';
    protected $description = 'Shows (or revokes with --apply) out-of-scope supervisors on unfinished papers.';
    protected $usage       = 'spms:review-scope [--apply]';

    public function run(array $params)
    {
        helper(['functions', 'audit', 'notification']);
        $apply = CLI::getOption('apply') !== null || in_array('--apply', $params, true);
        if (!ReviewHandover::ready()) { CLI::write('Run the migrations first (waiting_supervisor column missing).', 'red'); return; }
        $res = ReviewHandover::reconcile(!$apply);
        foreach ($res as $folder => $r) {
            CLI::write(sprintf('  %-14s %-34s %-18s revoke: %s%s', $folder, $r['owner'], $r['status'], implode(', ', $r['revoked']), $r['others_left'] ? " ({$r['others_left']} supervisor(s) stay)" : ' (none left: waits)'));
        }
        CLI::write($res ? count($res) . ' paper(s) ' . ($apply ? 'revoked.' : 'would be affected (dry run; add --apply).') : 'Every unfinished paper is reviewed by someone in scope.', $res ? 'yellow' : 'green');
    }
}

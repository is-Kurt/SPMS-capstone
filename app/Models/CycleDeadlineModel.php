<?php

namespace App\Models;

use CodeIgniter\Model;

class CycleDeadlineModel extends Model
{
    protected $table            = 'cycle_deadlines';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cycle_id',
        'cycle_folder_id',
        'doc_type',
        'target_start',
        'target_end',
        'eval_start',
        'eval_end',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Supported official SPMS document forms in the institutional cascade.
     */
    public const SUPPORTED_DOC_TYPES = ['opcr', 'dpcr', 'ipcr', 'iperf'];

    /**
     * Fetches all deadline records for a given root cycle folder, keyed by doc_type.
     */
    public function getDeadlinesForCycle($cycleId): array
    {
        $rows = $this->groupStart()
                     ->where('cycle_id', (int)$cycleId)
                     ->orWhere('cycle_folder_id', (string)$cycleId)
                     ->groupEnd()
                     ->findAll();
        $keyed = [];
        foreach ($rows as $row) {
            $keyed[$row['doc_type']] = $row;
        }
        return $keyed;
    }

    /**
     * Gets deadline for a specific doc_type within a cycle folder or rating cycle.
     */
    public function getDeadline($cycleId, string $docType): ?array
    {
        $dt = strtolower($docType);
        if ($dt === 'cdpcr') {
            $dt = 'dpcr';
        }
        return $this->groupStart()
                    ->where('cycle_id', (int)$cycleId)
                    ->orWhere('cycle_folder_id', (string)$cycleId)
                    ->groupEnd()
                    ->where('doc_type', $dt)
                    ->first();
    }

    /**
     * Returns deadlines as a flattened key-value array matching the legacy column names
     * (e.g. ['opcr_target_start' => ..., 'ipcr_eval_end' => ...]).
     * This provides seamless 100% backward compatibility for modals and stat tiles.
     */
    public function getCycleDatesFlat(string $cycleFolderId): array
    {
        $deadlines = $this->getDeadlinesForCycle($cycleFolderId);
        $flat = [];

        foreach (self::SUPPORTED_DOC_TYPES as $type) {
            $d = $deadlines[$type] ?? null;
            $flat["{$type}_target_start"] = $d['target_start'] ?? null;
            $flat["{$type}_target_end"]   = $d['target_end'] ?? null;
            $flat["{$type}_eval_start"]   = $d['eval_start'] ?? null;
            $flat["{$type}_eval_end"]     = $d['eval_end'] ?? null;
        }

        // Backward compatibility aliases for cdpcr
        $flat['cdpcr_target_start'] = $flat['dpcr_target_start'] ?? null;
        $flat['cdpcr_target_end']   = $flat['dpcr_target_end'] ?? null;
        $flat['cdpcr_eval_start']   = $flat['dpcr_eval_start'] ?? null;
        $flat['cdpcr_eval_end']     = $flat['dpcr_eval_end'] ?? null;

        return $flat;
    }

    /**
     * Extracts dates from an HTTP request (supporting both 'general' and 'staggered' schedule modes)
     * and persists them to cycle_deadlines for the given cycle folder.
     */
    public function saveDeadlinesFromRequest(string|int $cycleFolderId, $request, ?int $cycleId = null): void
    {
        if ($cycleId === null) {
            $docFolder = (new \App\Models\DocumentFolderModel())->find($cycleFolderId);
            if ($docFolder && !empty($docFolder['cycle_id'])) {
                $cycleId = (int)$docFolder['cycle_id'];
            } elseif (is_numeric($cycleFolderId)) {
                $cycleId = (int)$cycleFolderId;
            }
        }

        $scheduleMode   = $request->getPost('schedule_mode');
        $genTargetStart = $request->getPost('general_target_start');
        $genTargetEnd   = $request->getPost('general_target_end');
        $genEvalStart   = $request->getPost('general_eval_start');
        $genEvalEnd     = $request->getPost('general_eval_end');

        $isGeneralMode = ($scheduleMode === 'general') || (!empty($genTargetStart) && empty($request->getPost('opcr_target_start')));

        $cleanGenTStart = !empty($genTargetStart) ? str_replace('T', ' ', (string) $genTargetStart) : null;
        $cleanGenTEnd   = !empty($genTargetEnd)   ? str_replace('T', ' ', (string) $genTargetEnd)   : null;
        $cleanGenEStart = !empty($genEvalStart)   ? str_replace('T', ' ', (string) $genEvalStart)   : null;
        $cleanGenEEnd   = !empty($genEvalEnd)     ? str_replace('T', ' ', (string) $genEvalEnd)     : null;

        foreach (self::SUPPORTED_DOC_TYPES as $type) {
            if ($isGeneralMode && (!empty($genTargetStart) || !empty($genTargetEnd) || !empty($genEvalStart) || !empty($genEvalEnd))) {
                $tStart = $cleanGenTStart;
                $tEnd   = $cleanGenTEnd;
                $eStart = $cleanGenEStart;
                $eEnd   = $cleanGenEEnd;
            } else {
                $rawTStart = $request->getPost("{$type}_target_start") ?: $genTargetStart;
                $rawTEnd   = $request->getPost("{$type}_target_end")   ?: $genTargetEnd;
                $rawEStart = $request->getPost("{$type}_eval_start")   ?: $genEvalStart;
                $rawEEnd   = $request->getPost("{$type}_eval_end")     ?: $genEvalEnd;

                $tStart = !empty($rawTStart) ? str_replace('T', ' ', (string) $rawTStart) : null;
                $tEnd   = !empty($rawTEnd)   ? str_replace('T', ' ', (string) $rawTEnd)   : null;
                $eStart = !empty($rawEStart) ? str_replace('T', ' ', (string) $rawEStart) : null;
                $eEnd   = !empty($rawEEnd)   ? str_replace('T', ' ', (string) $rawEEnd)   : null;
            }

            $existing = $this->where('cycle_folder_id', (string)$cycleFolderId)
                             ->where('doc_type', $type)
                             ->first();
            if (!$existing && $cycleId) {
                $existing = $this->where('cycle_id', $cycleId)
                                 ->where('doc_type', $type)
                                 ->first();
            }

            $data = [
                'cycle_id'        => $cycleId,
                'cycle_folder_id' => (string)$cycleFolderId,
                'doc_type'        => $type,
                'target_start'    => $tStart,
                'target_end'      => $tEnd,
                'eval_start'      => $eStart,
                'eval_end'        => $eEnd,
            ];

            if ($existing) {
                $this->update($existing['id'], $data);
            } else {
                $this->insert($data);
            }
        }
    }
}

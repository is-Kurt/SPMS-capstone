<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Enums\RatingCycleStatus;

class RatingCycleModel extends Model
{
    protected $table            = 'rating_cycles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'rating_period',
        'approving_authority_name',
        'approving_authority_position',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get the currently active rating cycle.
     */
    public function getActiveCycle(): ?array
    {
        return $this->where('status', RatingCycleStatus::ACTIVE->value)
                    ->where('deleted_at IS NULL')
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }

    /**
     * Get a cycle enriched with its staggered and unified deadlines.
     */
    public function getCycleWithDeadlines(int $cycleId): ?array
    {
        $cycle = $this->find($cycleId);
        if (!$cycle) return null;

        $deadlineModel = new CycleDeadlineModel();
        $deadlines = $deadlineModel->getDeadlinesForCycle((string)$cycleId);
        $flatDates = $deadlineModel->getCycleDatesFlat((string)$cycleId);

        return array_merge($cycle, $flatDates, ['deadlines' => $deadlines]);
    }
}

<?php
$unitModel = new \App\Models\UnitModel();
$hierarchyTree = $unitModel->getHierarchyTree();
$enrichedUnits = $unitModel->getEnrichedHierarchyUnits();
$unitCoverageMeta = $twgUnitCoverageMeta ?? (new \App\Models\TwgUnitAssignmentModel())->getUnitCoverageMetadata();

// Derive unique tiers present in the dataset
$availableTiers = [];
foreach ($enrichedUnits as $eu) {
    $t = (int) $eu['tier'];
    if (!in_array($t, $availableTiers, true)) {
        $availableTiers[] = $t;
    }
}
sort($availableTiers);

// Compute initial institutional coverage summary
$totalUnitsCount      = count($unitCoverageMeta);
$coveredUnitsCount    = 0;
$sharedUnitsCount     = 0;
$allPositionKeys      = [];
$coveredPositionKeys  = [];
$coveredStaffUserIds  = [];

foreach ($unitCoverageMeta as $uMeta) {
    $revCount = count($uMeta['reviewers'] ?? []);
    if ($revCount > 0) {
        $coveredUnitsCount++;
    }
    if ($revCount > 1) {
        $sharedUnitsCount++;
    }
    foreach (($uMeta['personnel'] ?? []) as $person) {
        $pKey = strtolower(trim((string) ($person['position'] ?? '')));
        if ($pKey !== '') {
            $allPositionKeys[$pKey] = true;
            if ($revCount > 0) {
                $coveredPositionKeys[$pKey] = true;
            }
        }
        if ($revCount > 0 && !empty($person['user_id'])) {
            $coveredStaffUserIds[(int) $person['user_id']] = true;
        }
    }
}
$unassignedUnitsCount = max(0, $totalUnitsCount - $coveredUnitsCount);

// Helper to compute per-TWG designated positions & staff summary on initial load
$computeReviewerSummary = static function (int $twgUserId, array $assignedUnitIds) use ($unitCoverageMeta): array {
    $positionsMap = [];
    $staffMap     = [];
    $unitNames    = [];

    foreach ($assignedUnitIds as $uid) {
        $uid = (int) $uid;
        if (!isset($unitCoverageMeta[$uid])) {
            continue;
        }
        $uMeta = $unitCoverageMeta[$uid];
        $unitNames[] = strtolower((string) $uMeta['unit_name']);
        foreach (($uMeta['personnel'] ?? []) as $p) {
            if ((int) ($p['user_id'] ?? 0) === $twgUserId) {
                continue;
            }
            $staffMap[(int) $p['user_id']] = true;
            $posTitle = trim((string) ($p['position'] ?? ''));
            if ($posTitle !== '') {
                $positionsMap[strtolower($posTitle)] = $posTitle;
            }
        }
    }

    return [
        'office_count'   => count($assignedUnitIds),
        'position_count' => count($positionsMap),
        'staff_count'    => count($staffMap),
        'search_terms'   => implode(' ', array_merge($unitNames, array_keys($positionsMap))),
    ];
};

$twgUsersJs = [];
foreach (($twgUsers ?? []) as $tu) {
    $twgUsersJs[(int) $tu['id']] = [
        'id'        => (int) $tu['id'],
        'name'      => trim(($tu['first_name'] ?? '') . ' ' . ($tu['last_name'] ?? '')),
        'email'     => $tu['email'] ?? '',
        'is_active' => (int) ($tu['is_active'] ?? 0),
        'initials'  => strtoupper(substr($tu['first_name'] ?? 'T', 0, 1) . substr($tu['last_name'] ?? 'W', 0, 1)),
    ];
}
?>

<script id="twg-initial-state" type="application/json"><?= json_encode([
    'unitCoverageMeta'   => $unitCoverageMeta,
    'assignmentsGrouped' => $twgAssignmentsGrouped ?? [],
    'twgUsers'           => $twgUsersJs,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>

<div id="tab-content-twg" class="tab-content <?= $activeTab === 'twg' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface p-4 lg:p-6 overflow-y-auto custom-scrollbar">

    <!-- Header & Search Toolbar -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-surface-border shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-accent/15 border border-accent/25 text-amber-700 dark:text-accent flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-black tracking-tight text-text">Technical Working Group (TWG) Assignments</h2>
                <p class="text-xs font-medium text-text-muted mt-0.5">
                    Assign departments &amp; units to TWG reviewers. Multiple reviewers can share coverage over the same department or unit.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="twg-search-input" oninput="filterTwgReviewers()" placeholder="Search reviewer or department / unit..."
                       class="w-full bg-input border border-surface-border rounded-xl pl-9 pr-3 py-2 text-xs font-semibold focus:border-accent outline-none text-text transition-all placeholder:text-text-muted/60">
            </div>
        </div>
    </div>

    <!-- Institutional Coverage Summary Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-5 shrink-0" id="twg-coverage-summary-bar">
        <div class="px-3.5 py-2.5 rounded-xl bg-input/60 border border-surface-border flex items-center justify-between gap-2">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Covered Departments &amp; Units</div>
                <div class="text-sm font-black text-text mt-0.5">
                    <span id="twg-stat-covered-units"><?= $coveredUnitsCount ?></span>
                    <span class="text-xs font-semibold text-text-muted">/ <?= $totalUnitsCount ?></span>
                </div>
            </div>
            <span class="w-7 h-7 rounded-lg bg-success-500/10 border border-success-500/20 text-success-600 dark:text-success-400 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </span>
        </div>

        <div id="twg-stat-unassigned-card" class="px-3.5 py-2.5 rounded-xl border flex items-center justify-between gap-2 transition-colors <?= $unassignedUnitsCount > 0 ? 'bg-amber-500/5 border-amber-500/30' : 'bg-input/60 border-surface-border' ?>">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider <?= $unassignedUnitsCount > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted' ?>" id="twg-stat-unassigned-label">Unassigned Departments &amp; Units</div>
                <div class="text-sm font-black text-text mt-0.5">
                    <span id="twg-stat-unassigned-units"><?= $unassignedUnitsCount ?></span>
                    <span class="text-[11px] font-semibold text-text-muted ml-0.5" id="twg-stat-unassigned-sub"><?= $unassignedUnitsCount === 0 ? 'All covered' : 'Need TWG' ?></span>
                </div>
            </div>
            <span class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>

        <div class="px-3.5 py-2.5 rounded-xl bg-input/60 border border-surface-border flex items-center justify-between gap-2">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Shared Departments &amp; Units</div>
                <div class="text-sm font-black text-text mt-0.5">
                    <span id="twg-stat-shared-units"><?= $sharedUnitsCount ?></span>
                    <span class="text-[11px] font-semibold text-text-muted ml-0.5">Multi-TWG</span>
                </div>
            </div>
            <span class="w-7 h-7 rounded-lg bg-info-500/10 border border-info-500/20 text-info-600 dark:text-info-400 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- Reviewer Cards Container -->
    <?php if (empty($twgUsers)): ?>
        <div class="flex-1 flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-surface-border rounded-2xl bg-input/30">
            <div class="w-12 h-12 rounded-2xl bg-accent/15 border border-accent/25 text-amber-700 dark:text-accent flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-text">No TWG Reviewers Registered</h3>
            <p class="text-xs text-text-muted max-w-sm mt-1">
                Assign the "TWG" role to users in the <button type="button" onclick="switchUserTab('directory')" class="text-amber-700 dark:text-accent underline font-bold cursor-pointer">User Directory</button> or invite new members using <button type="button" onclick="switchUserTab('create')" class="text-amber-700 dark:text-accent underline font-bold cursor-pointer">Send Invitations</button>.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-3.5" id="twg-reviewers-grid">
            <?php foreach ($twgUsers as $twg): ?>
                <?php
                    $twgId           = (int) $twg['id'];
                    $assignedUnits   = $twgAssignmentsGrouped[$twgId] ?? [];
                    $assignedCount   = count($assignedUnits);
                    $assignedUnitIds = array_map('intval', array_column($assignedUnits, 'unit_id'));
                    $initials        = strtoupper(substr($twg['first_name'] ?? 'T', 0, 1) . substr($twg['last_name'] ?? 'W', 0, 1));
                    $revStats        = $computeReviewerSummary($twgId, $assignedUnitIds);
                ?>
                <div class="twg-reviewer-card p-4 lg:p-5 rounded-2xl bg-surface border border-surface-border shadow-xs hover:border-accent/40 transition-all flex flex-col xl:flex-row xl:items-center justify-between gap-4"
                     data-user-id="<?= $twgId ?>"
                     data-name="<?= strtolower(esc($twg['first_name'] . ' ' . $twg['last_name'])) ?>"
                     data-email="<?= strtolower(esc($twg['email'])) ?>"
                     data-coverage="<?= esc($revStats['search_terms']) ?>"
                     id="twg-card-<?= $twgId ?>">

                    <!-- Left: Profile Info -->
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0 xl:w-72 shrink-0">
                        <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/25 text-amber-700 dark:text-accent font-black text-xs flex items-center justify-center shrink-0">
                            <?= esc($initials) ?>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-text truncate">
                                    <?= esc($twg['first_name'] . ' ' . $twg['last_name']) ?>
                                </span>
                                <?php if ((int) $twg['is_active'] === 1): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-success-500/10 text-success-600 dark:text-success-400 border border-success-500/20">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-danger-500/10 text-danger-500 border border-danger-500/20">Disabled</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-semibold text-text-muted truncate"><?= esc($twg['email']) ?></span>
                            <span class="text-[11px] font-bold text-text-muted/80 mt-0.5" id="twg-submeta-<?= $twgId ?>">
                                <?= $revStats['staff_count'] ?> staff covered
                            </span>
                        </div>
                    </div>

                    <!-- Middle: Assigned Departments & Units List -->
                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Assigned Departments &amp; Units</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $assignedCount > 0 ? 'bg-accent/15 border border-accent/30 text-amber-800 dark:text-accent' : 'bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400' ?> js-assigned-count-label" id="twg-count-label-<?= $twgId ?>">
                                <?= $assignedCount > 0 ? ($assignedCount === 1 ? '1 Department / Unit' : "{$assignedCount} Departments & Units") : '0 Assigned' ?>
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto custom-scrollbar js-assigned-chips-container" id="twg-chips-<?= $twgId ?>">
                            <?php if (!empty($assignedUnits)): ?>
                                <?php foreach ($assignedUnits as $au): ?>
                                    <?php
                                        $auId       = (int) $au['unit_id'];
                                        $uMeta      = $unitCoverageMeta[$auId] ?? null;
                                        $revs       = $uMeta['reviewers'] ?? [];
                                        $coRevs     = array_values(array_filter($revs, static fn($r) => (int) $r['id'] !== $twgId));
                                        $isShared   = !empty($coRevs);
                                        $sharedTip  = $isShared ? 'Shared with: ' . implode(', ', array_column($coRevs, 'name')) : '';
                                        $uStaffCnt  = 0;
                                        foreach (($uMeta['personnel'] ?? []) as $per) {
                                            if ((int) ($per['user_id'] ?? 0) !== $twgId) {
                                                $uStaffCnt++;
                                            }
                                        }
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-lg text-xs font-bold bg-input border border-surface-border text-text hover:border-accent/40 transition-colors"
                                          title="<?= esc($uMeta['path_str'] ?? $au['unit_name']) ?><?= $sharedTip ? ' • ' . esc($sharedTip) : '' ?>">
                                        <span class="truncate max-w-[210px]"><?= esc($au['unit_name']) ?></span>
                                        <?php if ($uStaffCnt > 0): ?>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-surface border border-surface-border text-text-muted"><?= $uStaffCnt ?></span>
                                        <?php endif; ?>
                                        <?php if ($isShared): ?>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-info-500/10 text-info-600 dark:text-info-400 border border-info-500/25" title="<?= esc($sharedTip) ?>">
                                                Shared
                                            </span>
                                        <?php endif; ?>
                                        <button type="button"
                                                onclick="quickRemoveTwgUnit(event, <?= $twgId ?>, <?= $auId ?>)"
                                                class="w-4 h-4 rounded flex items-center justify-center text-text-muted hover:text-danger-500 hover:bg-danger-500/15 transition-colors cursor-pointer shrink-0"
                                                title="Remove <?= esc($au['unit_name']) ?>">
                                            &times;
                                        </button>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>No departments &amp; units assigned (Review queue is empty)</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div class="shrink-0 flex flex-wrap items-center justify-end gap-2">
                        <button type="button"
                                onclick="openTwgAssignmentModal(<?= $twgId ?>)"
                                class="px-4 py-2.5 rounded-xl text-xs font-black bg-accent hover:bg-accent-hover text-black border border-accent transition-all cursor-pointer flex items-center gap-2 shadow-xs active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Assign Departments &amp; Units</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div id="twg-reviewers-empty-search" class="hidden flex-col items-center justify-center p-10 text-center border border-dashed border-surface-border rounded-2xl bg-input/20">
                <span class="text-xs font-bold text-text">No TWG reviewers match your search</span>
                <p class="text-[11px] text-text-muted mt-0.5">Try searching by reviewer name, email, or assigned department / unit.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: ASSIGN DEPARTMENTS & UNITS                      -->
<!-- ======================================================== -->
<div id="twg-assignment-modal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-zinc-950/70 p-3 sm:p-4 backdrop-blur-sm transition-all duration-200">
    <div class="relative w-full max-w-4xl bg-surface border border-surface-border rounded-2xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95 duration-150">

        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-surface-border flex items-center justify-between shrink-0 bg-surface">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent/15 border border-accent/25 text-amber-700 dark:text-accent flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-black text-text tracking-tight">Assign Departments &amp; Units</h3>
            </div>
            <button type="button" onclick="closeTwgAssignmentModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-muted hover:text-text hover:bg-input border border-transparent hover:border-surface-border transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Filter & Quick Select Toolbar -->
        <div class="px-6 py-3 border-b border-surface-border bg-input/40 flex flex-col gap-2.5 shrink-0">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="modal-unit-filter" oninput="filterModalUnits()"
                           placeholder="Filter by department or unit name..."
                           class="w-full bg-surface border border-surface-border rounded-xl pl-9 pr-3 py-2 text-xs font-semibold text-text placeholder:text-text-muted/60 focus:border-accent outline-none transition-all">
                </div>

                <!-- Tier & Coverage Filters -->
                <div class="flex items-center gap-2 shrink-0">
                    <div class="relative">
                        <select id="modal-coverage-filter" onchange="filterModalUnits()"
                                class="appearance-none bg-surface border border-surface-border rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-text focus:border-accent outline-none transition-all cursor-pointer">
                            <option value="all">All Coverage</option>
                            <option value="unassigned">Unassigned Only</option>
                            <option value="selected">Selected Only</option>
                            <option value="shared">Assigned to Others</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="relative">
                        <select id="modal-tier-filter" onchange="filterModalUnits()"
                                class="appearance-none bg-surface border border-surface-border rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-text focus:border-accent outline-none transition-all cursor-pointer">
                            <option value="all">All Tiers</option>
                            <?php foreach ($availableTiers as $tNum): ?>
                                <option value="<?= $tNum ?>">Tier <?= $tNum ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Select Action Pills -->
            <div class="flex flex-wrap items-center gap-1.5 pt-0.5 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-text-muted mr-0.5">Quick Select:</span>
                <button type="button" onclick="selectModalUnits('visible')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-accent/15 hover:bg-accent/25 text-amber-800 dark:text-accent border border-accent/30 transition-all cursor-pointer">
                    Select Visible
                </button>
                <button type="button" onclick="selectModalUnits('clear-visible')" class="px-2 py-1 rounded-lg text-[11px] font-bold text-danger-500 hover:bg-danger-500/10 transition-all cursor-pointer">
                    Clear Visible
                </button>
                <button type="button" onclick="selectModalUnits('none')" class="px-2 py-1 rounded-lg text-[11px] font-bold text-text-muted hover:text-text hover:bg-input transition-all cursor-pointer">
                    Clear All
                </button>
            </div>
        </div>

        <!-- Modal Body: Streamlined Organizational Tree -->
        <form id="twg-assignment-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitTwgAssignments(event)">
            <input type="hidden" name="user_id" id="modal-user-id" value="">

            <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4" id="modal-units-list">

                <?php foreach ($hierarchyTree as $root): ?>
                    <?php
                        $rootId        = (int) $root['id'];
                        $rootMeta      = $unitCoverageMeta[$rootId] ?? [];
                        $rootPositions = array_column($rootMeta['positions'] ?? [], 'title');
                        $rootStaffCnt  = (int) ($rootMeta['staff_count'] ?? 0);
                        $rootPosTip    = !empty($rootPositions)
                            ? 'Positions: ' . implode(', ', array_map(static fn($p) => "{$p['title']} ({$p['count']})", $rootMeta['positions']))
                            : 'No active ratee staff currently in this unit';
                    ?>
                    <div class="tree-branch-container rounded-2xl border border-surface-border bg-input/20 p-3.5 space-y-3" data-sector="<?= strtolower(esc($root['root_name'])) ?>">

                        <!-- Tier 1 Root Unit Card -->
                        <div class="modal-unit-item relative flex items-center justify-between gap-2 p-3 rounded-xl border border-surface-border/80 bg-surface hover:border-accent/50 shadow-2xs transition-all cursor-pointer select-none group"
                             data-id="<?= $rootId ?>"
                             data-name="<?= strtolower(esc($root['name'])) ?>"
                             data-positions="<?= strtolower(esc(implode(' ', $rootPositions))) ?>"
                             data-tier="<?= esc($root['tier']) ?>"
                             data-sector="<?= strtolower(esc($root['root_name'])) ?>"
                             data-type="<?= esc($root['type']) ?>"
                             data-path="<?= strtolower(esc($root['path_str'])) ?>"
                             onclick="toggleUnitCard(this)">

                            <div class="flex items-center gap-3 min-w-0">
                                <div class="unit-check-indicator w-5 h-5 rounded-md border-2 border-surface-border bg-input flex items-center justify-center shrink-0 transition-all group-hover:border-accent/60">
                                    <svg class="check-icon w-3.5 h-3.5 text-black stroke-[3] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="unit-name-text text-sm font-black text-text transition-colors leading-tight truncate">
                                    <?= esc($root['name']) ?>
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-input border border-surface-border text-text-muted shrink-0">
                                    Tier 1
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="js-unit-coverage-badge"></span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-input border border-surface-border text-text-muted" title="<?= esc($rootPosTip) ?>">
                                    <?= $rootStaffCnt ?> staff
                                </span>
                            </div>

                            <input type="checkbox" name="unit_ids[]" value="<?= $rootId ?>" class="js-unit-checkbox sr-only" tabindex="-1">
                        </div>

                        <!-- Tier 2 Children (items-start eliminates uneven empty grid stretching!) -->
                        <?php if (!empty($root['children'])): ?>
                            <div class="tree-children-container pl-3 sm:pl-4 border-l-2 border-surface-border/70 ml-2.5">
                                <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-2.5">
                                    <?php foreach ($root['children'] as $child): ?>
                                        <?php
                                            $childId        = (int) $child['id'];
                                            $childMeta      = $unitCoverageMeta[$childId] ?? [];
                                            $childPositions = array_column($childMeta['positions'] ?? [], 'title');
                                            $childStaffCnt  = (int) ($childMeta['staff_count'] ?? 0);
                                            $childPosTip    = !empty($childPositions)
                                                ? 'Positions: ' . implode(', ', array_map(static fn($p) => "{$p['title']} ({$p['count']})", $childMeta['positions']))
                                                : 'No active ratee staff currently in this unit';
                                            $hasTier3       = !empty($child['children']);
                                            $tier3Count     = count($child['children'] ?? []);
                                        ?>
                                        <div class="tier2-wrapper rounded-xl border border-surface-border/80 bg-surface p-2.5 space-y-2 transition-all">

                                            <!-- Tier 2 Unit Row -->
                                            <div class="modal-unit-item relative flex items-center justify-between gap-2 p-2 rounded-lg border border-transparent hover:border-accent/40 hover:bg-input/50 transition-all cursor-pointer select-none group"
                                                 data-id="<?= $childId ?>"
                                                 data-name="<?= strtolower(esc($child['name'])) ?>"
                                                 data-positions="<?= strtolower(esc(implode(' ', $childPositions))) ?>"
                                                 data-tier="<?= esc($child['tier']) ?>"
                                                 data-sector="<?= strtolower(esc($child['root_name'])) ?>"
                                                 data-type="<?= esc($child['type']) ?>"
                                                 data-path="<?= strtolower(esc($child['path_str'])) ?>"
                                                 onclick="toggleUnitCard(this)">

                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="unit-check-indicator w-4.5 h-4.5 rounded border-2 border-surface-border bg-input flex items-center justify-center shrink-0 transition-all group-hover:border-accent/60">
                                                        <svg class="check-icon w-3 h-3 text-black stroke-[3] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <span class="unit-name-text text-xs font-bold text-text transition-colors leading-tight">
                                                        <?= esc($child['name']) ?>
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <span class="js-unit-coverage-badge"></span>
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-input border border-surface-border text-text-muted" title="<?= esc($childPosTip) ?>">
                                                        <?= $childStaffCnt ?>
                                                    </span>
                                                </div>

                                                <input type="checkbox" name="unit_ids[]" value="<?= $childId ?>" class="js-unit-checkbox sr-only" tabindex="-1">
                                            </div>

                                            <!-- Collapsible Tier 3 Sub-units + 1-Click Branch Helper -->
                                            <?php if ($hasTier3): ?>
                                                <div class="tier3-section pt-1 border-t border-surface-border/60">
                                                    <div class="flex items-center justify-between px-1.5 pb-1">
                                                        <button type="button"
                                                                onclick="toggleTier3Collapse(this, event)"
                                                                class="js-tier3-collapse-btn inline-flex items-center gap-1.5 text-[11px] font-bold text-text-muted hover:text-text transition-colors cursor-pointer">
                                                            <svg class="js-tier3-chevron w-3.5 h-3.5 transition-transform duration-150 -rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                            <span><?= $tier3Count ?> Sub-unit<?= $tier3Count > 1 ? 's' : '' ?></span>
                                                            <span class="js-tier3-selected-count hidden px-1.5 py-0.2 rounded-full text-[9px] font-black bg-accent/20 text-amber-800 dark:text-accent">0/<?= $tier3Count ?></span>
                                                        </button>

                                                        <button type="button"
                                                                onclick="toggleBranchUnits(this, event)"
                                                                class="js-branch-toggle-btn text-[10px] font-extrabold text-amber-700 dark:text-accent hover:underline cursor-pointer px-1.5 py-0.5 rounded hover:bg-accent/10 transition-colors">
                                                            + Sub-units
                                                        </button>
                                                    </div>

                                                    <div class="tier3-container hidden pl-2.5 border-l-2 border-surface-border/60 ml-2 space-y-1 pt-1">
                                                        <?php foreach ($child['children'] as $prog): ?>
                                                            <?php
                                                                $progId        = (int) $prog['id'];
                                                                $progMeta      = $unitCoverageMeta[$progId] ?? [];
                                                                $progPositions = array_column($progMeta['positions'] ?? [], 'title');
                                                                $progStaffCnt  = (int) ($progMeta['staff_count'] ?? 0);
                                                                $progPosTip    = !empty($progPositions)
                                                                    ? 'Positions: ' . implode(', ', array_map(static fn($p) => "{$p['title']} ({$p['count']})", $progMeta['positions']))
                                                                    : 'No active ratee staff currently in this unit';
                                                            ?>
                                                            <div class="modal-unit-item relative flex items-center justify-between gap-2 p-1.5 rounded-lg border border-transparent hover:border-accent/40 hover:bg-input/50 transition-all cursor-pointer select-none group"
                                                                 data-id="<?= $progId ?>"
                                                                 data-name="<?= strtolower(esc($prog['name'])) ?>"
                                                                 data-positions="<?= strtolower(esc(implode(' ', $progPositions))) ?>"
                                                                 data-tier="<?= esc($prog['tier']) ?>"
                                                                 data-sector="<?= strtolower(esc($prog['root_name'])) ?>"
                                                                 data-type="<?= esc($prog['type']) ?>"
                                                                 data-path="<?= strtolower(esc($prog['path_str'])) ?>"
                                                                 onclick="toggleUnitCard(this)">

                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <div class="unit-check-indicator w-4 h-4 rounded border border-surface-border bg-input flex items-center justify-center shrink-0 transition-all group-hover:border-accent/60">
                                                                        <svg class="check-icon w-2.5 h-2.5 text-black stroke-[3] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                    </div>
                                                                    <span class="unit-name-text text-[11px] font-semibold text-text-muted group-hover:text-text transition-colors leading-tight">
                                                                        <?= esc($prog['name']) ?>
                                                                    </span>
                                                                </div>

                                                                <div class="flex items-center gap-1 shrink-0">
                                                                    <span class="js-unit-coverage-badge"></span>
                                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-input border border-surface-border text-text-muted" title="<?= esc($progPosTip) ?>">
                                                                        <?= $progStaffCnt ?>
                                                                    </span>
                                                                </div>

                                                                <input type="checkbox" name="unit_ids[]" value="<?= $progId ?>" class="js-unit-checkbox sr-only" tabindex="-1">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>

                <!-- Empty State Message when Filter Matches Nothing -->
                <div id="modal-units-empty-msg" class="hidden flex-col items-center justify-center p-12 text-center border-2 border-dashed border-surface-border rounded-2xl bg-input/20">
                    <div class="w-10 h-10 rounded-xl bg-accent/15 border border-accent/25 text-amber-700 dark:text-accent flex items-center justify-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-text">No departments &amp; units match your filter criteria</span>
                    <p class="text-[11px] text-text-muted mt-0.5">Try clearing the search input or switching the coverage / tier filters.</p>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 border-t border-surface-border bg-surface flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex flex-wrap items-center gap-2 px-3 py-1.5 rounded-xl bg-input border border-surface-border text-xs font-semibold text-text-muted">
                    <span class="w-2 h-2 rounded-full bg-accent"></span>
                    <span><strong id="modal-selected-badge" class="font-black text-text">0</strong> departments &amp; units</span>
                    <span>&middot;</span>
                    <span><strong id="modal-selected-staff-badge" class="font-black text-text">0</strong> staff</span>
                </div>

                <div class="flex items-center gap-2.5 ml-auto">
                    <button type="button" onclick="closeTwgAssignmentModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-text-muted hover:text-text hover:bg-input border border-surface-border transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-twg-assignments" class="px-5 py-2.5 rounded-xl text-xs font-black bg-accent hover:bg-accent-hover text-black shadow-sm transition-all cursor-pointer flex items-center gap-2 active:scale-95">
                        <span>Save Assignments</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

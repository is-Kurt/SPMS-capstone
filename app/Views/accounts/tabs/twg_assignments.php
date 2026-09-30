<?php
$colleges = [];
$adminOffices = [];
foreach ($units as $u) {
    if (empty($u['parent_id'])) {
        if (stripos($u['name'], 'College of') !== false || stripos($u['name'], 'Graduate School') !== false) {
            $colleges[] = $u;
        } else {
            $adminOffices[] = $u;
        }
    }
}

// Pre-calculate sub-unit count for each parent unit
$subUnitCounts = [];
foreach ($units as $u) {
    if (!empty($u['parent_id'])) {
        $subUnitCounts[$u['parent_id']] = ($subUnitCounts[$u['parent_id']] ?? 0) + 1;
    }
}
?>

<div id="tab-content-twg" class="tab-content <?= $activeTab === 'twg' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface p-4 lg:p-6 overflow-y-auto custom-scrollbar">

    <!-- Header & Info Banner -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-surface-border shrink-0">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent/15 border border-accent/25 text-accent flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black tracking-tight text-text">Technical Working Group (TWG) Office Assignments</h2>
                    <p class="text-xs font-medium text-text-muted mt-0.5">
                        Assign academic colleges and delivery units to TWG calibration reviewers to balance evaluation workload.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="twg-search-input" oninput="filterTwgReviewers()" placeholder="Search TWG reviewers..." 
                       class="w-full bg-input border border-surface-border rounded-xl pl-9 pr-3 py-2 text-xs font-semibold focus:border-accent outline-none text-text transition-all placeholder:text-text-muted/60">
            </div>
        </div>
    </div>

    <!-- Reviewer Cards Container -->
    <?php if (empty($twgUsers)): ?>
        <div class="flex-1 flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-surface-border rounded-2xl bg-input/30">
            <div class="w-12 h-12 rounded-2xl bg-accent/15 border border-accent/25 text-accent flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-text">No TWG Reviewers Registered</h3>
            <p class="text-xs text-text-muted max-w-sm mt-1">
                Assign the "TWG" role to users in the <button type="button" onclick="switchUserTab('directory')" class="text-accent underline font-bold cursor-pointer">User Directory</button> or invite new members using <button type="button" onclick="switchUserTab('create')" class="text-accent underline font-bold cursor-pointer">Send Invitations</button>.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-3.5" id="twg-reviewers-grid">
            <?php foreach ($twgUsers as $twg): ?>
                <?php 
                    $assignedUnits = $twgAssignmentsGrouped[$twg['id']] ?? [];
                    $assignedCount = count($assignedUnits);
                    $assignedUnitIds = array_column($assignedUnits, 'unit_id');
                    $initials = strtoupper(substr($twg['first_name'] ?? 'T', 0, 1) . substr($twg['last_name'] ?? 'W', 0, 1));
                ?>
                <div class="twg-reviewer-card p-4 lg:p-5 rounded-2xl bg-surface border border-surface-border shadow-xs hover:border-accent/40 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                     data-name="<?= strtolower(esc($twg['first_name'] . ' ' . $twg['last_name'])) ?>"
                     data-email="<?= strtolower(esc($twg['email'])) ?>"
                     id="twg-card-<?= $twg['id'] ?>">
                    
                    <!-- Left: Profile Info -->
                    <div class="flex items-start md:items-center gap-3.5 min-w-0 md:w-72 shrink-0">
                        <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/25 text-accent font-black text-xs flex items-center justify-center shrink-0">
                            <?= esc($initials) ?>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-text truncate">
                                    <?= esc($twg['first_name'] . ' ' . $twg['last_name']) ?>
                                </span>
                                <?php if ($twg['is_active'] == 1): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-success-500/10 text-success-600 dark:text-success-400 border border-success-500/20">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-danger-500/10 text-danger-500 border border-danger-500/20">Disabled</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-semibold text-text-muted truncate"><?= esc($twg['email']) ?></span>
                        </div>
                    </div>

                    <!-- Middle: Assigned Offices List -->
                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Assigned Review Coverage</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $assignedCount > 0 ? 'bg-accent/10 border border-accent/20 text-accent' : 'bg-amber-500/10 border border-amber-500/20 text-amber-500' ?> js-assigned-count-label" id="twg-count-label-<?= $twg['id'] ?>">
                                <?= $assignedCount > 0 ? "{$assignedCount} Office" . ($assignedCount > 1 ? 's' : '') : '0 Assigned' ?>
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto custom-scrollbar js-assigned-chips-container" id="twg-chips-<?= $twg['id'] ?>">
                            <?php if (!empty($assignedUnits)): ?>
                                <?php foreach ($assignedUnits as $au): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-input border border-surface-border text-text hover:border-accent/40 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="truncate max-w-[220px]"><?= esc($au['unit_name']) ?></span>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>No offices assigned (Review queue is empty)</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div class="shrink-0 flex items-center justify-end">
                        <button type="button" 
                                onclick='openTwgAssignmentModal(<?= $twg['id'] ?>, "<?= esc(addslashes($twg['first_name'] . ' ' . $twg['last_name'])) ?>", "<?= esc(addslashes($twg['email'])) ?>", <?= json_encode(array_values($assignedUnitIds)) ?>)'
                                class="px-4 py-2.5 rounded-xl text-xs font-black bg-accent/10 hover:bg-accent text-accent hover:text-black border border-accent/25 hover:border-accent transition-all cursor-pointer flex items-center gap-2 group shadow-xs active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-accent group-hover:text-black transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Assign Offices</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ======================================================== -->
<!-- MODAL: CONFIGURE TWG OFFICE ASSIGNMENTS                  -->
<!-- ======================================================== -->
<div id="twg-assignment-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-zinc-950/70 p-4 backdrop-blur-sm transition-all duration-200">
    <div class="relative w-full max-w-3xl bg-surface border border-surface-border rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-surface-border flex items-center justify-between shrink-0 bg-surface">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent/15 border border-accent/25 text-accent flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-text tracking-tight">Assign Review Offices</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Assigning to <span id="modal-reviewer-name" class="font-bold text-accent">TWG Reviewer</span>
                        <span id="modal-reviewer-email" class="text-text-muted ml-1"></span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeTwgAssignmentModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-muted hover:text-text hover:bg-input border border-transparent hover:border-surface-border transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Filter & Quick Select Bar (Theme Matched) -->
        <div class="px-6 py-3 border-b border-surface-border bg-input/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="modal-unit-filter" oninput="filterModalUnits()" placeholder="Filter colleges & offices..." 
                       class="w-full bg-surface border border-surface-border rounded-xl pl-9 pr-3 py-1.5 text-xs font-semibold text-text placeholder:text-text-muted/60 focus:border-accent outline-none transition-all">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="selectModalUnits('colleges')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-accent/10 hover:bg-accent/20 text-accent border border-accent/20 transition-all cursor-pointer">
                    All Colleges
                </button>
                <button type="button" onclick="selectModalUnits('all')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-surface hover:bg-input text-text border border-surface-border transition-all cursor-pointer">
                    Select All
                </button>
                <button type="button" onclick="selectModalUnits('none')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-danger-500 hover:bg-danger-500/10 border border-transparent transition-all cursor-pointer">
                    Clear
                </button>
            </div>
        </div>

        <!-- Modal Body: Interactive Card Grid -->
        <form id="twg-assignment-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitTwgAssignments(event)">
            <input type="hidden" name="user_id" id="modal-user-id" value="">
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6" id="modal-units-list">
                
                <!-- Colleges Section -->
                <div>
                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-[11px] font-black uppercase tracking-wider text-text-muted flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
                            Academic Colleges (15)
                        </span>
                        <span class="text-[10px] font-semibold text-text-muted">Auto-includes degree programs</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        <?php foreach ($colleges as $c): ?>
                            <?php $subCount = $subUnitCounts[$c['id']] ?? 0; ?>
                            <div class="modal-unit-item relative flex items-center justify-between p-3.5 rounded-xl border border-surface-border/70 bg-surface/50 hover:bg-surface hover:border-accent/40 transition-all cursor-pointer select-none group"
                                 data-name="<?= strtolower(esc($c['name'])) ?>"
                                 data-type="college"
                                 onclick="toggleUnitCard(this)">
                                
                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                    <!-- Custom Checkbox Indicator -->
                                    <div class="unit-check-indicator w-5 h-5 rounded-md border-2 border-surface-border bg-input/80 flex items-center justify-center shrink-0 transition-all group-hover:border-accent/60">
                                        <svg class="check-icon w-3.5 h-3.5 text-black stroke-[3] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="unit-name-text text-xs font-bold text-text group-hover:text-accent transition-colors leading-tight">
                                        <?= esc($c['name']) ?>
                                    </span>
                                </div>

                                <?php if ($subCount > 0): ?>
                                    <span class="sub-prog-badge shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-input border border-surface-border text-text-muted transition-colors">
                                        <?= $subCount ?> progs
                                    </span>
                                <?php endif; ?>

                                <!-- Hidden actual checkbox input -->
                                <input type="checkbox" name="unit_ids[]" value="<?= esc($c['id']) ?>" 
                                       class="js-unit-checkbox sr-only" tabindex="-1">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Administrative Offices Section -->
                <?php if (!empty($adminOffices)): ?>
                <div>
                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-[11px] font-black uppercase tracking-wider text-text-muted flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-text-muted"></span>
                            Administrative &amp; Support Offices
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        <?php foreach ($adminOffices as $ao): ?>
                            <div class="modal-unit-item relative flex items-center justify-between p-3.5 rounded-xl border border-surface-border/70 bg-surface/50 hover:bg-surface hover:border-accent/40 transition-all cursor-pointer select-none group"
                                 data-name="<?= strtolower(esc($ao['name'])) ?>"
                                 data-type="admin"
                                 onclick="toggleUnitCard(this)">
                                
                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                    <!-- Custom Checkbox Indicator -->
                                    <div class="unit-check-indicator w-5 h-5 rounded-md border-2 border-surface-border bg-input/80 flex items-center justify-center shrink-0 transition-all group-hover:border-accent/60">
                                        <svg class="check-icon w-3.5 h-3.5 text-black stroke-[3] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="unit-name-text text-xs font-bold text-text group-hover:text-accent transition-colors leading-tight">
                                        <?= esc($ao['name']) ?>
                                    </span>
                                </div>

                                <!-- Hidden actual checkbox input -->
                                <input type="checkbox" name="unit_ids[]" value="<?= esc($ao['id']) ?>" 
                                       class="js-unit-checkbox sr-only" tabindex="-1">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-surface-border bg-surface flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-input border border-surface-border">
                    <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                    <span class="text-xs font-bold text-text"><span id="modal-selected-badge" class="font-black text-accent text-sm">0</span> offices selected</span>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="closeTwgAssignmentModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-text-muted hover:text-text hover:bg-input border border-surface-border transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-twg-assignments" class="px-6 py-2.5 rounded-xl text-xs font-black bg-accent hover:bg-accent-hover text-black shadow-lg shadow-accent/25 transition-all cursor-pointer flex items-center gap-2 active:scale-95">
                        <span>Save Assignments</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

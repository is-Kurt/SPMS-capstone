<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-hidden">
    
    <!-- FOLDER / DASHBOARD HEADER -->
    <div class="px-6 lg:px-8 py-5 border-b border-surface-border shrink-0">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30">
                <?= ($sysRole === 'Supervisor') ? esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Departmental Oversight' : 'Collegiate Oversight')) : 'University-Wide Oversight' ?>
            </span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="min-w-0">
                <button onclick="toggleAppSidebar()" class="text-left group cursor-pointer lg:cursor-default w-full">
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <?= esc($activeCycle['title'] ?? 'Evaluation Period') ?>
                    </h1>
                </button>
                <?php if ($sysRole === 'Supervisor'): ?>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                        Tracking faculty & staff submissions for <span class="font-bold text-slate-700 dark:text-slate-200"><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Your Department' : 'Your College')) ?></span>
                    </p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <?php if ($sysRole === 'Admin'): ?>
                <!-- VIEW SWITCHER (Analytics vs Masterlist) -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-[#061e14] rounded-xl border border-slate-200 dark:border-[#0c4a33] shadow-2xs">
                    <button type="button" id="btn-view-analytics" onclick="switchDashboardView('analytics')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-emerald-600 text-slate-900 dark:text-white shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Overview Analytics</span>
                    </button>
                    <button type="button" id="btn-view-masterlist" onclick="switchDashboardView('masterlist')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Ratings Master List</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                            <?= count($cycleFolders) ?>
                        </span>
                    </button>
                </div>

                <!-- College / Department Filter -->
                <div class="flex items-center gap-2">
                    <label for="college-filter" class="text-xs font-bold text-slate-500 dark:text-slate-400 hidden sm:inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Filter:</span>
                    </label>
                    <select id="college-filter" onchange="applyCollegeFilter(this.value)"
                            class="text-xs font-semibold px-3 py-2 rounded-xl bg-white dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs [color-scheme:light] dark:[color-scheme:dark]">
                        <option value="">All Colleges &amp; Divisions</option>
                        <?php if (!empty($allUnits)): ?>
                            <?php
                            $colleges = [];
                            $adminOffices = [];
                            $subDepts = [];
                            foreach ($allUnits as $u) {
                                if (empty($u['parent_id'])) {
                                    if (stripos($u['name'], 'College of') !== false || stripos($u['name'], 'Graduate School') !== false) {
                                        $colleges[] = $u;
                                    } else {
                                        $adminOffices[] = $u;
                                    }
                                } else {
                                    $subDepts[] = $u;
                                }
                            }
                            ?>
                            <optgroup label="Colleges">
                                <?php foreach ($colleges as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$c['id']) ? 'selected' : '' ?>>
                                        <?= esc($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php if (!empty($adminOffices)): ?>
                            <optgroup label="Administrative Offices / Divisions">
                                <?php foreach ($adminOffices as $ao): ?>
                                    <option value="<?= $ao['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$ao['id']) ? 'selected' : '' ?>>
                                        <?= esc($ao['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($subDepts)): ?>
                            <optgroup label="Degree Programs / Sub-Departments">
                                <?php foreach ($subDepts as $sd): ?>
                                    <option value="<?= $sd['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$sd['id']) ? 'selected' : '' ?>>
                                        <?= esc($sd['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if (!empty($selectedUnitId)): ?>
                    <button onclick="applyCollegeFilter('')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 transition-colors shadow-2xs"
                            title="Reset college filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span><?= esc($selectedUnitName ?? 'Filtered') ?> &times;</span>
                    </button>
                <?php endif; ?>
                <?php endif; ?>

                <a href="<?= site_url('ratings') ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 dark:border-emerald-500/30 transition-colors shadow-2xs">
                    <span>Evaluator Queue</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

<?php if ($sysRole === 'Supervisor'): ?>
    <!-- SUPERVISOR (DEAN) VIEW: COLLEGE SUBMISSION COMPLIANCE MONITOR -->
    <div class="p-6 lg:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-6">

        <!-- 1. TOP SUMMARY CARDS: WHO SUBMITTED VS WHO HAS NOT -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total College Headcount -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= !empty($isChairScope) ? 'Department Headcount' : 'College Headcount' ?></span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-info-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Personnel</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22] text-xs text-slate-500 dark:text-slate-400 truncate">
                    <span><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Department roster' : 'College roster')) ?></span>
                </div>
            </div>

            <!-- Card 2: Targets Submitted & Approved -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Target Commitments</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">/ <?= $totalPersonnel ?> Approved (<?= $targetComplianceRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Missing Target Submissions (Draft) -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border <?= ($pipeline['target']['draft'] > 0) ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20 dark:bg-[#0c1510]' : 'border-slate-200 dark:border-[#1a2b22]' ?> shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Missing Targets</span>
                        <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-danger-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-rose-600 dark:text-rose-400"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-xs font-bold text-rose-500 dark:text-rose-400">Still in Draft</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22] text-xs font-semibold truncate">
                    <?php if ($pipeline['target']['draft'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                            <svg class="w-3.5 h-3.5 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Faculty still need to submit targets</span>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-3.5 h-3.5 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>All targets submitted</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 4: Accomplishments Finalized -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Final Evaluations</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">/ <?= $totalPersonnel ?> Finalized (<?= $evalCompletionRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. PHASE SUBMISSION BREAKDOWN (TARGETS VS ACCOMPLISHMENTS) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Phase 1 Card -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">1</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Target Commitment Phase</h3>
                            <span class="text-xs text-slate-400">Submission & Approval status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300">
                        <?= $totalPersonnel ?> Total
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Draft (Missing)</span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:border-amber-800/80 dark:text-amber-400 border border-amber-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['returned'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Revision</span>
                    </div>
                </div>
            </div>

            <!-- Phase 2 Card -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black">2</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Accomplishment Report Phase</h3>
                            <span class="text-xs text-slate-400">Evaluation & Grading status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300">
                        <?= $totalPersonnel ?> Total
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-indigo-50 text-indigo-800 dark:bg-highlight-500/20 dark:border-highlight-500/20 dark:text-highlight-400 border border-indigo-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['action'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Evaluating</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Submitted</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Draft (Missing)</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. "WHO HAS SUBMITTED & WHO HAS NOT" COMPLIANCE ROSTER TABLE -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white"><?= !empty($isChairScope) ? 'Department Personnel Submission Roster' : 'College Personnel Submission Roster' ?></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Track who has submitted their targets/accomplishments and who is still missing or in draft.
                    </p>
                </div>

                <!-- Search & Department Filter Controls -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '')) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-[#0c4a33] dark:hover:bg-emerald-700 border border-emerald-600/30 transition-all shadow-xs cursor-pointer"
                       title="Download CSC SLIR Excel Sheet for this unit">
                        <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Master List (.xlsx)</span>
                    </a>

                    <?php if (!empty($collegeDepartments) && count($collegeDepartments) > 1 && empty($isChairScope)): ?>
                        <select id="roster-dept-filter" onchange="filterRoster()"
                                class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All Sub-Departments</option>
                            <?php foreach ($collegeDepartments as $cd): ?>
                                <option value="<?= esc($cd) ?>"><?= esc($cd) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div class="relative w-64 max-w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="roster-search" onkeyup="filterRoster()"
                               placeholder="Search faculty name or position..."
                               class="w-full text-xs font-medium py-2 pl-9 pr-3 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition-all shadow-2xs" />
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pill Tabs -->
            <div class="flex items-center gap-2 pb-4 overflow-x-auto custom-scrollbar border-b border-slate-100 dark:border-[#1a2b22]">
                <button type="button" onclick="setRosterFilter('all', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-500 text-white shadow-2xs">
                    All Personnel (<?= count($cycleFolders) ?>)
                </button>
                <button type="button" onclick="setRosterFilter('missing', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Missing Submissions (Draft) (<?= $pipeline['target']['draft'] + ($pipeline['evaluation']['draft'] ?? $pipeline['evaluation']['pending']) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('review', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Submitted (In Review) (<?= $pipeline['target']['pending'] + $pipeline['evaluation']['action'] + $pipeline['evaluation']['submitted'] ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('revision', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Needs Revision (<?= $pipeline['target']['returned'] + ($pipeline['evaluation']['returned'] ?? 0) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('completed', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Completed / Approved (<?= $pipeline['evaluation']['completed'] ?>)</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto mt-4 rounded-xl border border-slate-200 dark:border-[#1a2b22]">
                <table class="w-full text-left border-collapse" id="roster-table">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-[#032316] border-b border-slate-200 dark:border-[#1a2b22] text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-3 px-4">Faculty / Personnel</th>
                            <th class="py-3 px-4">Department / Unit</th>
                            <th class="py-3 px-4 text-center">Phase 1: Targets</th>
                            <th class="py-3 px-4 text-center">Phase 2: Accomplishments</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($cycleFolders)): ?>
                            <tr>
                                <td colspan="5" class="py-10 px-4 text-center text-slate-400 dark:text-slate-500 italic">
                                    No personnel records found for this evaluation cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cycleFolders as $rf): ?>
                                <tr class="roster-row hover:bg-slate-50/80 dark:hover:bg-white/5 transition-colors"
                                    data-filter="<?= esc($rf['submission_filter'] ?? 'all') ?>"
                                    data-target-state="<?= esc($rf['target_state'] ?? 'draft') ?>"
                                    data-eval-state="<?= esc($rf['eval_state'] ?? 'draft') ?>"
                                    data-name="<?= strtolower(esc($rf['full_name'] . ' ' . $rf['email'] . ' ' . $rf['position'])) ?>"
                                    data-dept="<?= esc($rf['department']) ?>">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <span><?= esc($rf['full_name']) ?></span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider <?= ($rf['is_teaching'] == 1) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400' ?>">
                                                <?= ($rf['is_teaching'] == 1) ? 'Teaching' : 'Non-Teaching' ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 font-normal"><?= esc($rf['position']) ?> &bull; <?= esc($rf['email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                        <?= esc($rf['department']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $rf['target_badge'] ?> shadow-2xs">
                                            <?= esc($rf['target_label']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $rf['eval_badge'] ?> shadow-2xs">
                                            <?= esc($rf['eval_label']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <?php if (!empty($rf['folder_id']) && !in_array($rf['folder_status'] ?? '', [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value, 'unstarted'])): ?>
                                            <a href="<?= site_url('ratings/show/' . $rf['folder_id']) ?>"
                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-50 dark:hover:bg-white/5 transition-colors shadow-2xs">
                                                Inspect
                                            </a>
                                        <?php elseif (in_array($rf['folder_status'] ?? '', [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value])): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-slate-400 dark:text-zinc-500 bg-slate-50 dark:bg-zinc-800/40 border border-slate-200 dark:border-zinc-700/40">
                                                Drafting
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 italic">No Folder</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="roster-empty-message" class="hidden">
                            <td colspan="5" class="py-10 px-4 text-center text-slate-400 dark:text-slate-500 italic">
                                No employees found matching the selected filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php else: ?>
    <!-- MAIN SCROLLABLE CONTENT (ADMIN EXECUTIVE ANALYTICS & MASTERLIST) -->
    <div class="p-6 lg:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-5">

        <!-- 1. EXECUTIVE ANALYTICS VIEW -->
        <div id="dashboard-view-analytics" class="space-y-5">

        <!-- 1. TOP KPI SUMMARY METRIC CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Ratees -->
            <div class="p-5 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Ratees</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-info-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Personnel</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200/70 dark:border-[#1a2b22] flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Roster</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Active cycle ratees</span>
                </div>
            </div>

            <!-- Card 2: Overall Average Rating -->
            <div class="p-5 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Average Rating</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-success-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3 flex-wrap">
                        <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white"><?= $overallAverage > 0 ? number_format($overallAverage, 2) : '--' ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">/ 5.00</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-black uppercase border <?= $adjectivalBadgeClass ?>">
                            <?= esc($adjectivalLabel) ?>
                        </span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200/70 dark:border-[#1a2b22] flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Evaluated</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($totalRated) ?> rated</span>
                </div>
            </div>

            <!-- Card 3: Target Compliance -->
            <div class="p-5 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Target Compliance</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-highlight-500/20 text-indigo-600 dark:text-highlight-400 flex items-center justify-center shadow-2xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white"><?= $targetComplianceRate ?>%</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Approved</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200/70 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-200 dark:bg-zinc-800 rounded-full h-2 overflow-hidden mb-1.5">
                        <div class="bg-indigo-600 dark:bg-highlight-500 h-2 rounded-full transition-all" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Status</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($pipeline['target']['approved']) ?> of <?= number_format($totalPersonnel) ?> approved</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Cycle Completion -->
            <div class="p-5 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cycle Completion</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white"><?= $evalCompletionRate ?>%</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Finalized</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-200/70 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-200 dark:bg-zinc-800 rounded-full h-2 overflow-hidden mb-1.5">
                        <div class="bg-amber-600 dark:bg-amber-400 h-2 rounded-full transition-all" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Status</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($pipeline['evaluation']['completed']) ?> of <?= number_format($totalPersonnel) ?> finalized</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. SPMS 4-STAGE LIFECYCLE PIPELINE (CSC MC No. 6, s. 2012) -->
        <div class="rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs overflow-hidden">
            <!-- Header Banner with Stepper Trail -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-[#16281f] bg-slate-50/70 dark:bg-[#08130e] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            CSC MC No. 6, s. 2012
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Standard University Cycle</span>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">SPMS 4-Stage Performance Lifecycle</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">End-to-end performance cycle: target commitments, evidence collection, review calibrations, and merit incentives</p>
                </div>
                
                <!-- Interconnected Stepper Indicator Flow -->
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-[#091712] border border-slate-200/80 dark:border-[#16281f] text-xs">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold text-slate-700 dark:text-slate-200">
                        <span class="w-4 h-4 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] font-black">1</span>
                        <span class="text-xs">Planning</span>
                    </div>
                    <span class="text-slate-400 dark:text-zinc-600 font-bold px-0.5">&rsaquo;</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold text-slate-700 dark:text-slate-200">
                        <span class="w-4 h-4 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px] font-black">2</span>
                        <span class="text-xs">Coaching</span>
                    </div>
                    <span class="text-slate-400 dark:text-zinc-600 font-bold px-0.5">&rsaquo;</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold text-slate-700 dark:text-slate-200">
                        <span class="w-4 h-4 rounded-full bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center text-[10px] font-black">3</span>
                        <span class="text-xs">Review</span>
                    </div>
                    <span class="text-slate-400 dark:text-zinc-600 font-bold px-0.5">&rsaquo;</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold text-slate-700 dark:text-slate-200">
                        <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px] font-black">4</span>
                        <span class="text-xs">Rewarding</span>
                    </div>
                </div>
            </div>

            <!-- 4 Stage Columns Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-[#16281f]">
                
                <!-- STAGE 1: Performance Planning & Commitment -->
                <div class="p-5 flex flex-col justify-between hover:bg-slate-50/40 dark:hover:bg-white/[0.015] transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center border border-indigo-200/50 dark:border-indigo-500/20">
                                    1
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">Planning &amp; Commitment</h3>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 font-medium">Target Setting Phase</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-2xs font-extrabold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20">
                                Stage 1
                            </span>
                        </div>

                        <!-- Hero Number & Progress -->
                        <div class="mt-4 mb-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white"><?= $pipeline['stage1']['approved'] ?></span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-bold">/ <?= $totalPersonnel ?> Approved</span>
                                </div>
                                <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-200/40 dark:border-indigo-500/20">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-[#07130e] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Row Breakdown -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#16281f] space-y-1.5 mt-2">
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50 shrink-0"></span>
                                Approved Targets
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage1']['approved'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-xs shadow-blue-500/50 shrink-0"></span>
                                In Review
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage1']['pending'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shadow-xs shadow-amber-500/50 shrink-0"></span>
                                Needs Revision
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage1']['returned'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-zinc-600 shrink-0"></span>
                                Draft Mode
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage1']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: Performance Monitoring & Coaching -->
                <div class="p-5 flex flex-col justify-between hover:bg-slate-50/40 dark:hover:bg-white/[0.015] transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 font-black text-xs flex items-center justify-center border border-amber-200/50 dark:border-amber-500/20">
                                    2
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">Monitoring &amp; Coaching</h3>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 font-medium">Execution &amp; Evidence</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-2xs font-extrabold bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-500/20">
                                Stage 2
                            </span>
                        </div>

                        <!-- Hero Number & Progress -->
                        <div class="mt-4 mb-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white"><?= $pipeline['stage2']['active_execution'] ?></span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-bold">Active Commitments</span>
                                </div>
                                <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-200/40 dark:border-amber-500/20">
                                    <?= $pipeline['stage2']['execution_rate'] ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-[#07130e] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= min(100, $pipeline['stage2']['execution_rate']) ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Row Breakdown -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#16281f] space-y-1.5 mt-2">
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50 shrink-0"></span>
                                Targets in Execution
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage2']['active_execution'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-teal-500 shadow-xs shadow-teal-500/50 shrink-0"></span>
                                MOVs Uploaded
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage2']['mov_count'] ?> files</span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shadow-xs shadow-amber-500/50 shrink-0"></span>
                                Coaching Feedback
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage2']['coaching_notes'] ?> notes</span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-zinc-600 shrink-0"></span>
                                Evidence Density
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $totalPersonnel > 0 && $pipeline['stage2']['mov_count'] > 0 ? round($pipeline['stage2']['mov_count'] / $totalPersonnel, 1) : 0 ?> / ratee</span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: Performance Review & Evaluation -->
                <div class="p-5 flex flex-col justify-between hover:bg-slate-50/40 dark:hover:bg-white/[0.015] transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 font-black text-xs flex items-center justify-center border border-sky-200/50 dark:border-sky-500/20">
                                    3
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">Review &amp; Evaluation</h3>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 font-medium">Accomplishment Phase</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-2xs font-extrabold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/20">
                                Stage 3
                            </span>
                        </div>

                        <!-- Hero Number & Progress -->
                        <div class="mt-4 mb-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white"><?= $pipeline['stage3']['completed'] ?></span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-bold">/ <?= $totalPersonnel ?> Evaluated</span>
                                </div>
                                <span class="text-xs font-extrabold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 px-2 py-0.5 rounded-md border border-sky-200/40 dark:border-sky-500/20">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-[#07130e] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Row Breakdown -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#16281f] space-y-1.5 mt-2">
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50 shrink-0"></span>
                                Approved Ratings
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage3']['completed'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-xs shadow-blue-500/50 shrink-0"></span>
                                Under Evaluation
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage3']['evaluating'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-cyan-500 shadow-xs shadow-cyan-500/50 shrink-0"></span>
                                Submitted Awaiting
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage3']['submitted'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-zinc-600 shrink-0"></span>
                                Draft Accomplishment
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage3']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 4: Performance Rewarding & Development -->
                <div class="p-5 flex flex-col justify-between hover:bg-slate-50/40 dark:hover:bg-white/[0.015] transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-black text-xs flex items-center justify-center border border-emerald-200/50 dark:border-emerald-500/20">
                                    4
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">Rewarding &amp; Development</h3>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 font-medium">Incentives &amp; HR Phase</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-2xs font-extrabold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                Stage 4
                            </span>
                        </div>

                        <!-- Hero Number & Progress -->
                        <div class="mt-4 mb-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white"><?= $pipeline['stage4']['pbb_eligible'] ?></span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-bold">PBB / Incentive Eligible</span>
                                </div>
                                <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-200/40 dark:border-emerald-500/20">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage4']['pbb_eligible'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-[#07130e] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage4']['pbb_eligible'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Row Breakdown -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#16281f] space-y-1.5 mt-2">
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50 shrink-0"></span>
                                Outstanding &amp; VS
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage4']['pbb_eligible'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-teal-500 shadow-xs shadow-teal-500/50 shrink-0"></span>
                                TWG Certified
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage4']['certified'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-xs shadow-indigo-500/50 shrink-0"></span>
                                CSC Export Ready
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage4']['export_ready'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs px-2 py-1 rounded-lg hover:bg-slate-100/60 dark:hover:bg-white/[0.025] transition-colors">
                            <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-zinc-600 shrink-0"></span>
                                Dev. Needed
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded-md bg-slate-100 dark:bg-[#16281f] text-[11px]"><?= $pipeline['stage4']['dev_needed'] ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. COLLEGE & DEPARTMENT COMPLIANCE LEADERBOARD -->
        <div class="p-6 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Department Compliance Leaderboard</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Target compliance and final evaluation completion per academic and admin unit</p>
                </div>
                <div class="relative shrink-0" style="width: 260px; max-width: 100%;">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="leaderboard-search" onkeyup="filterLeaderboard()"
                           placeholder="Search department..." 
                           class="w-full text-xs font-medium py-2 pl-9 pr-4 rounded-xl bg-white dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-2xs" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-[#1a2b22] bg-white dark:bg-[#0c1510]">
                <table class="w-full text-left border-collapse" id="leaderboard-table">
                    <thead>
                        <tr class="bg-slate-100/60 dark:bg-[#032316]/50 border-b border-slate-200 dark:border-[#1a2b22] text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-3 px-4 w-16">Rank</th>
                            <th class="py-3 px-4">College / Department</th>
                            <th class="py-3 px-4 text-center">Headcount</th>
                            <th class="py-3 px-4 text-center">Target Compliance</th>
                            <th class="py-3 px-4 text-center">Eval Completion</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($deptLeaderboard)): ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-slate-400 dark:text-slate-500">
                                    No department records found for this cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deptLeaderboard as $index => $dept): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors dept-row">
                                    <td class="py-3 px-4 font-black text-slate-400 dark:text-slate-500">#<?= $index + 1 ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white dept-name"><?= esc($dept['name']) ?></td>
                                    <td class="py-3 px-4 text-center text-slate-800 dark:text-slate-300 font-bold"><?= $dept['headcount'] ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-black text-indigo-700 dark:text-highlight-400"><?= $dept['headcount'] > 0 ? round(($dept['target_approved'] / $dept['headcount']) * 100) : 0 ?>%</span>
                                        <span class="text-xs text-slate-400 dark:text-slate-400 font-normal ml-1">(<?= $dept['target_approved'] ?>/<?= $dept['headcount'] ?>)</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-black text-emerald-700 dark:text-emerald-400"><?= $dept['compliance_pct'] ?>%</span>
                                        <span class="text-xs text-slate-400 dark:text-slate-400 font-normal ml-1">(<?= $dept['eval_completed'] ?>/<?= $dept['headcount'] ?>)</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase border <?= $dept['badge_class'] ?? 'bg-blue-50 text-blue-700 border-blue-200' ?>">
                                             <?= esc($dept['status_badge']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        </div>
        <!-- /END OF #dashboard-view-analytics -->

        <!-- 2. RATINGS MASTERLIST VIEW (CSC SLIR - SUMMARY LIST OF INDIVIDUAL RATINGS) -->
        <div id="dashboard-view-masterlist" class="hidden space-y-5">
            
            <!-- MASTERLIST HEADER & EXPORT ACTION CARD -->
            <div class="p-6 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                                CSC MC No. 6, s. 2012 Prescribed
                            </span>
                            <span class="text-xs font-semibold text-slate-400">|</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                <?= esc($selectedUnitName ?? 'University-Wide Roster') ?>
                            </span>
                        </div>
                        <h2 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Summary List of Individual Performance Ratings (Master List)
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-3xl">
                            Consolidated institutional master list of all active plantilla faculty and staff for <?= esc($activeCycle['title'] ?? 'this evaluation period') ?>. Formatted in accordance with Civil Service Commission Strategic Performance Management System guidelines.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-[#0c4a33] dark:hover:bg-emerald-700 border border-emerald-600/30 transition-all shadow-md hover:shadow-lg hover:scale-[1.01] active:scale-[0.99] cursor-pointer"
                           title="Generate and download official CSC landscape Excel workbook">
                            <svg class="w-4 h-4 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Export Master List (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Masterlist Stats Row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 mt-5 border-t border-slate-100 dark:border-[#1a2b22]">
                    <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Personnel</div>
                        <div class="text-base font-black text-slate-900 dark:text-white mt-0.5"><?= number_format(count($cycleFolders)) ?></div>
                    </div>
                    <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Permanent</div>
                        <div class="text-base font-black text-emerald-700 dark:text-emerald-400 mt-0.5"><?= number_format($empStatusCounts['permanent'] ?? 0) ?></div>
                    </div>
                    <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Temporary</div>
                        <div class="text-base font-black text-amber-600 dark:text-amber-400 mt-0.5"><?= number_format($empStatusCounts['temporary'] ?? 0) ?></div>
                    </div>
                    <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Casual &amp; Contractual</div>
                        <div class="text-base font-black text-sky-600 dark:text-sky-400 mt-0.5"><?= number_format(($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>

            <!-- SEARCH & FILTER TOOLBAR -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs space-y-4">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-lg">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="masterlist-search" onkeyup="filterMasterlist()"
                               placeholder="Search personnel name, email, department, or position..."
                               class="w-full text-xs font-medium py-2.5 pl-10 pr-4 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 shadow-2xs" />
                    </div>

                    <!-- Filter Dropdown -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <select id="masterlist-status-filter" onchange="filterMasterlist()"
                                class="text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs [color-scheme:light] dark:[color-scheme:dark]">
                            <option value="">All Appointments</option>
                            <option value="permanent">Permanent</option>
                            <option value="temporary">Temporary</option>
                            <option value="casual">Casual</option>
                            <option value="contractual">Contractual</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter Pill Buttons -->
                <div class="flex items-center gap-2 pb-1 overflow-x-auto custom-scrollbar pt-2 border-t border-slate-100 dark:border-[#1a2b22]">
                    <button type="button" onclick="setMasterlistPill('all', this)"
                            class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-600 text-white shadow-2xs cursor-pointer">
                        All Personnel (<?= count($cycleFolders) ?>)
                    </button>
                    <button type="button" onclick="setMasterlistPill('permanent', this)"
                            class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span>Permanent (<?= $empStatusCounts['permanent'] ?? 0 ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('temporary', this)"
                            class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                        <span>Temporary (<?= $empStatusCounts['temporary'] ?? 0 ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('casual', this)"
                            class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                        <span>Casual (<?= $empStatusCounts['casual'] ?? 0 ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('contractual', this)"
                            class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-purple-500 shrink-0"></span>
                        <span>Contractual (<?= $empStatusCounts['contractual'] ?? 0 ?>)</span>
                    </button>
                </div>
            </div>

            <!-- MASTERLIST DATA TABLE -->
            <div class="rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse" id="masterlist-table">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-[#032316] border-b border-slate-200 dark:border-[#1a2b22] text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                                <th class="py-3.5 px-4 w-12 text-center">#</th>
                                <th class="py-3.5 px-4">Personnel / Ratee</th>
                                <th class="py-3.5 px-4">College / Division</th>
                                <th class="py-3.5 px-4">Position</th>
                                <th class="py-3.5 px-4 text-center">Employment Status</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#1a2b22] text-xs">
                            <?php if (empty($cycleFolders)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 px-4 text-center text-slate-400 dark:text-slate-500 italic">
                                        No personnel records discovered for this evaluation period.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cycleFolders as $idx => $f): ?>
                                    <tr class="masterlist-row hover:bg-slate-50 dark:hover:bg-white/5 transition-colors"
                                        data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                        data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                        data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                        data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                        data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>">
                                        
                                        <!-- Index -->
                                        <td class="py-3.5 px-4 text-center font-bold text-slate-400 dark:text-slate-500">
                                            <?= $idx + 1 ?>
                                        </td>

                                        <!-- Personnel Name & Email -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-emerald-950/40 border border-slate-200 dark:border-emerald-800/50 flex items-center justify-center font-black text-[11px] text-slate-700 dark:text-emerald-400 shrink-0">
                                                    <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-bold text-slate-900 dark:text-white truncate">
                                                        <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 truncate">
                                                        <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- College / Department -->
                                        <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                            <span class="truncate block max-w-[220px]" title="<?= esc($f['department'] ?? '—') ?>">
                                                <?= esc($f['department'] ?? '—') ?>
                                            </span>
                                        </td>

                                        <!-- Plantilla Position -->
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 font-medium">
                                            <span class="truncate block max-w-[180px]" title="<?= esc($f['position'] ?? '—') ?>">
                                                <?= esc($f['position'] ?? '—') ?>
                                            </span>
                                        </td>

                                        <!-- Employment Status -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border <?= $f['emp_status_badge'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]' ?>">
                                                <?= esc($f['employment_status'] ?? 'Permanent') ?>
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td class="py-3.5 px-4 text-right">
                                            <?php if (!empty($f['id']) || !empty($f['folder_id'])): ?>
                                                <a href="<?= site_url('folders/view/' . ($f['id'] ?? $f['folder_id'])) ?>"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30 transition-colors shadow-2xs">
                                                    <span>Inspect</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400 dark:text-slate-600 italic">No Folder</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <tr id="masterlist-empty-row" class="hidden">
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <p class="text-xs font-semibold">No personnel records found matching your filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Masterlist Table Footer -->
                <div class="px-5 py-3 border-t border-slate-100 dark:border-[#1a2b22] bg-slate-50/50 dark:bg-white/2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        Showing <span id="masterlist-visible-count" class="font-bold text-slate-800 dark:text-slate-200"><?= count($cycleFolders) ?></span> of <span class="font-bold"><?= count($cycleFolders) ?></span> personnel
                    </div>
                    <div class="text-[11px] text-slate-400">
                        Official Personnel Master List
                    </div>
                </div>
            </div>

        </div>
        <!-- /END OF #dashboard-view-masterlist -->

    </div>
<?php endif; ?>
</div>

<script>
// --- DASHBOARD VIEW SWITCHER (Analytics vs Masterlist) ---
function switchDashboardView(view) {
    const analyticsView = document.getElementById('dashboard-view-analytics');
    const masterlistView = document.getElementById('dashboard-view-masterlist');
    const btnAnalytics = document.getElementById('btn-view-analytics');
    const btnMasterlist = document.getElementById('btn-view-masterlist');

    if (!analyticsView || !masterlistView) return;

    if (view === 'masterlist') {
        analyticsView.classList.add('hidden');
        masterlistView.classList.remove('hidden');

        if (btnAnalytics && btnMasterlist) {
            btnAnalytics.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer';
            btnMasterlist.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-emerald-600 text-slate-900 dark:text-white shadow-xs cursor-pointer';
        }
        localStorage.setItem('spms_dash_view', 'masterlist');
    } else {
        masterlistView.classList.add('hidden');
        analyticsView.classList.remove('hidden');

        if (btnAnalytics && btnMasterlist) {
            btnAnalytics.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-white dark:bg-emerald-600 text-slate-900 dark:text-white shadow-xs cursor-pointer';
            btnMasterlist.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer';
        }
        localStorage.setItem('spms_dash_view', 'analytics');
    }
}

// Restore saved view on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('spms_dash_view');
    if (savedView === 'masterlist') {
        switchDashboardView('masterlist');
    }
});

// --- MASTERLIST FILTERING ---
let currentMasterlistPill = 'all';

function setMasterlistPill(pillType, btn) {
    currentMasterlistPill = pillType;
    document.querySelectorAll('.masterlist-tab-btn').forEach(b => {
        b.className = 'masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700 cursor-pointer';
    });
    if (btn) {
        btn.className = 'masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-600 text-white shadow-2xs cursor-pointer';
    }
    filterMasterlist();
}

function filterMasterlist() {
    const searchInput  = document.getElementById('masterlist-search');
    const statusFilter  = document.getElementById('masterlist-status-filter');

    const query     = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const empStatus = statusFilter ? statusFilter.value.trim().toLowerCase() : '';

    const rows = document.querySelectorAll('.masterlist-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name         = row.getAttribute('data-name') || '';
        const email        = row.getAttribute('data-email') || '';
        const dept         = row.getAttribute('data-dept') || '';
        const pos          = row.getAttribute('data-pos') || '';
        const rowEmpStatus = (row.getAttribute('data-emp-status') || '').toLowerCase();

        // Pill filter
        let matchesPill = false;
        if (currentMasterlistPill === 'all') {
            matchesPill = true;
        } else if (currentMasterlistPill === 'permanent') {
            matchesPill = rowEmpStatus === 'permanent';
        } else if (currentMasterlistPill === 'temporary') {
            matchesPill = rowEmpStatus === 'temporary';
        } else if (currentMasterlistPill === 'casual') {
            matchesPill = rowEmpStatus === 'casual';
        } else if (currentMasterlistPill === 'contractual') {
            matchesPill = rowEmpStatus === 'contractual';
        }

        // Search match
        const matchesSearch = !query || name.includes(query) || email.includes(query) || dept.includes(query) || pos.includes(query);

        // Employment status dropdown match
        const matchesStatus = !empStatus || rowEmpStatus === empStatus;

        if (matchesPill && matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countElem = document.getElementById('masterlist-visible-count');
    if (countElem) {
        countElem.textContent = visibleCount;
    }

    const emptyRow = document.getElementById('masterlist-empty-row');
    if (emptyRow) {
        emptyRow.classList.toggle('hidden', visibleCount > 0);
    }
}

// --- ROSTER / DEAN FILTERING ---
let currentRosterFilter = 'all';

function setRosterFilter(filterType, btn) {
    currentRosterFilter = filterType;
    document.querySelectorAll('.roster-tab-btn').forEach(b => {
        b.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700';
    });
    if (btn) {
        btn.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-500 text-white shadow-2xs';
    }
    filterRoster();
}

function filterRoster() {
    const searchInput = document.getElementById('roster-search');
    const deptSelect  = document.getElementById('roster-dept-filter');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const dept  = deptSelect ? deptSelect.value.trim() : '';

    const rows = document.querySelectorAll('.roster-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowFilter = row.getAttribute('data-filter') || '';
        const rowTarget = row.getAttribute('data-target-state') || '';
        const rowEval   = row.getAttribute('data-eval-state') || '';
        const rowName   = row.getAttribute('data-name') || '';
        const rowDept   = row.getAttribute('data-dept') || '';

        let matchesTab = false;
        if (currentRosterFilter === 'all') {
            matchesTab = true;
        } else if (currentRosterFilter === 'missing') {
            matchesTab = (rowTarget === 'draft' || rowEval === 'draft' || rowFilter === 'missing');
        } else if (currentRosterFilter === 'review') {
            matchesTab = (rowTarget === 'submitted' || rowEval === 'submitted' || rowEval === 'evaluating' || rowFilter === 'review');
        } else if (currentRosterFilter === 'revision') {
            matchesTab = (rowTarget === 'returned' || rowEval === 'returned' || rowFilter === 'revision');
        } else if (currentRosterFilter === 'completed') {
            matchesTab = (rowEval === 'approved' || rowFilter === 'completed');
        }

        const matchesSearch = !query || rowName.includes(query);
        const matchesDept   = !dept || rowDept === dept;

        if (matchesTab && matchesSearch && matchesDept) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyMsg = document.getElementById('roster-empty-message');
    if (emptyMsg) {
        emptyMsg.classList.toggle('hidden', visibleCount > 0);
    }
}

function filterLeaderboard() {
    const input = document.getElementById('leaderboard-search');
    if (!input) return;
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.dept-row');

    rows.forEach(row => {
        const deptName = row.querySelector('.dept-name').textContent.toLowerCase();
        if (deptName.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function applyCollegeFilter(unitId) {
    const url = new URL(window.location.href);
    if (unitId) {
        url.searchParams.set('unit_id', unitId);
    } else {
        url.searchParams.delete('unit_id');
    }
    window.location.href = url.toString();
}
</script>

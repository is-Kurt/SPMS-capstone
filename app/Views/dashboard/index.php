<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-hidden">
    
    <!-- FOLDER / DASHBOARD HEADER -->
    <div class="px-6 lg:px-8 py-5 border-b border-surface-border shrink-0">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Executive Performance Analytics</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30">
                <?= in_array($sysRole, ['Admin', 'HR', 'TWG']) ? 'University-Wide' : ($sysRole === 'Supervisor' ? 'Departmental' : 'Personal') ?> Oversight
            </span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="min-w-0">
                <button onclick="toggleAppSidebar()" class="text-left group cursor-pointer lg:cursor-default w-full">
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <?= esc($activeCycle['title'] ?? 'Evaluation Period') ?>
                    </h1>
                </button>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <!-- College / Department Filter -->
                <div class="flex items-center gap-2">
                    <label for="college-filter" class="text-xs font-bold text-slate-500 dark:text-slate-400 hidden sm:inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Filter:</span>
                    </label>
                    <select id="college-filter" onchange="applyCollegeFilter(this.value)"
                            class="text-xs font-semibold px-3 py-2 rounded-xl bg-white dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs">
                        <option value="">🏛️ All Colleges & Offices</option>
                        <?php if (!empty($allUnits)): ?>
                            <?php foreach ($allUnits as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$u['id']) ? 'selected' : '' ?>>
                                    <?= esc($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
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

    <!-- MAIN SCROLLABLE CONTENT -->
    <div class="p-6 lg:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-5">

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

        <!-- 2. SPMS WORKFLOW PIPELINE STAGES -->
        <div class="p-6 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">SPMS 2-Phase Lifecycle</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Target commitment setting versus accomplishment evaluation progress</p>
                </div>
                <span class="self-start sm:self-auto px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-600 dark:text-slate-300 shadow-2xs">
                    Phase 1 &rarr; Phase 2
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Phase -->
                <div class="p-5 rounded-xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-2xs flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-highlight-500/20 text-indigo-600 dark:text-highlight-400 flex items-center justify-center text-xs font-black">
                                1
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-none">Target Setting</h3>
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Commitment Phase</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-highlight-500/20 dark:text-highlight-400 border border-indigo-200 dark:border-highlight-500/30">
                            <?= $pipeline['target']['approved'] ?> / <?= $totalPersonnel ?> Approved
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                            <span class="block text-xl font-black"><?= $pipeline['target']['approved'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Approved</span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                            <span class="block text-xl font-black"><?= $pipeline['target']['pending'] ?></span>
                            <span class="text-xs font-bold mt-1 block">In Review</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-100 text-slate-700 dark:bg-zinc-800/50 dark:border-zinc-700 dark:text-zinc-400 border border-slate-200">
                            <span class="block text-xl font-black"><?= $pipeline['target']['draft'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Draft</span>
                        </div>
                        <div class="p-3 rounded-xl bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:border-amber-800/80 dark:text-amber-400 border border-amber-200">
                            <span class="block text-xl font-black"><?= $pipeline['target']['returned'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Revision</span>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Phase -->
                <div class="p-5 rounded-xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-2xs flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-[#102a1e] text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-black">
                                2
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-none">Accomplishment</h3>
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Evaluation Phase</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                            <?= $pipeline['evaluation']['completed'] ?> / <?= $totalPersonnel ?> Completed
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                            <span class="block text-xl font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Approved</span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                            <span class="block text-xl font-black"><?= $pipeline['evaluation']['action'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Evaluating</span>
                        </div>
                        <div class="p-3 rounded-xl bg-cyan-50 text-cyan-800 dark:bg-cyan-950/50 dark:border-cyan-800/40 dark:text-cyan-400 border border-cyan-200">
                            <span class="block text-xl font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Submitted</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-100 text-slate-700 dark:bg-zinc-800/50 dark:border-zinc-700 dark:text-zinc-400 border border-slate-200">
                            <span class="block text-xl font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                            <span class="text-xs font-bold mt-1 block">Draft</span>
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
                            <th class="py-3 px-4 text-center">Average</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($deptLeaderboard)): ?>
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-slate-400 dark:text-slate-500">
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
                                    <td class="py-3 px-4 text-center font-black text-slate-900 dark:text-white">
                                        <?= $dept['average_rating'] > 0 ? number_format($dept['average_rating'], 2) : '--' ?>
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

        <!-- 4. RECENT PERFORMANCE ACTIVITY FEED -->
        <div class="p-6 rounded-xl bg-slate-50/70 dark:bg-[#0c1510]/50 border border-slate-200 dark:border-[#1a2b22] shadow-xs">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Recent Performance Reviews</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest employee rating updates and submissions in this cycle</p>
                </div>
                <a href="<?= site_url('ratings') ?>" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors">
                    <span>View All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-[#1a2b22] bg-white dark:bg-[#0c1510]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/60 dark:bg-[#032316]/50 border-b border-slate-200 dark:border-[#1a2b22] text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-3 px-4">Ratee & Position</th>
                            <th class="py-3 px-4">Department</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Score</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($recentFolders)): ?>
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-slate-400 dark:text-slate-500">
                                    No folder records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentFolders as $rf): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white"><?= esc($rf['full_name']) ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500 font-normal"><?= esc($rf['position']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 truncate max-w-[220px]"><?= esc($rf['department']) ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                            $st = $rf['folder_status'];
                                            $stBadgeClass = 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-[#032316] dark:text-slate-300 dark:border-[#0c4a33]';
                                            $stLabel = str_replace('_', ' ', $st);

                                            if (in_array($st, ['approved', 'twg_approved'])) {
                                                $stBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30';
                                                $stLabel = 'Approved';
                                            } elseif (in_array($st, ['target_returned', 'reevaluate', 'target_unapproved'])) {
                                                $stBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30';
                                                $stLabel = 'Needs Revision';
                                            } elseif (in_array($st, ['pending_target_approval', 'submitted'])) {
                                                $stBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-info-500/15 dark:text-blue-400 dark:border-info-500/30';
                                                $stLabel = $st === 'pending_target_approval' ? 'Target Submitted' : 'Submitted';
                                            } elseif ($st === 'to evaluate') {
                                                $stBadgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-highlight-500/20 dark:text-highlight-400 dark:border-highlight-500/30';
                                                $stLabel = 'In Evaluation';
                                            }
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $stBadgeClass ?> shadow-2xs">
                                            <?= esc($stLabel) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-black text-slate-900 dark:text-white">
                                        <?= $rf['rating_num'] !== null ? number_format($rf['rating_num'], 2) : '--' ?>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <a href="<?= site_url('ratings/show/' . $rf['folder_id']) ?>" 
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors shadow-2xs">
                                            Inspect
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function filterLeaderboard() {
    const input = document.getElementById('leaderboard-search');
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

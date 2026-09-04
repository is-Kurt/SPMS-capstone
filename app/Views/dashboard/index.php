<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-hidden">
    
    <!-- FOLDER / DASHBOARD HEADER -->
    <div class="px-6 lg:px-8 pt-6 pb-5 border-b border-surface-border shrink-0">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Executive Performance Analytics</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                <?= in_array($sysRole, ['Admin', 'HR', 'TWG']) ? 'University-Wide' : ($sysRole === 'Supervisor' ? 'Departmental' : 'Personal') ?> Oversight
            </span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="min-w-0">
                <button onclick="toggleAppSidebar()" class="text-left group cursor-pointer lg:cursor-default w-full">
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-text truncate group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                        <?= esc($activeCycle['title'] ?? 'Evaluation Period') ?>
                    </h1>
                </button>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="<?= site_url('ratings') ?>" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 transition-colors shadow-xs">
                    <span>Evaluator Queue</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN SCROLLABLE CONTENT -->
    <div class="p-6 lg:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-6">

        <!-- 1. TOP KPI SUMMARY METRIC CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Ratees -->
            <div class="p-4 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Total Ratees</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-heading text-text"><?= number_format($totalPersonnel) ?></span>
                    <span class="text-xs font-semibold text-text-muted">Personnel</span>
                </div>
                <p class="mt-1 text-[11px] text-text-muted">Active ratees in this cycle</p>
            </div>

            <!-- Card 2: Overall Average Rating -->
            <div class="p-4 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Average Rating</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5 flex-wrap">
                    <span class="text-2xl font-black font-heading text-text"><?= $overallAverage > 0 ? number_format($overallAverage, 2) : '--' ?></span>
                    <span class="text-xs font-bold text-text-muted">/ 5.00</span>
                    <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase border <?= $adjectivalBadgeClass ?>">
                        <?= esc($adjectivalLabel) ?>
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-text-muted"><?= number_format($totalRated) ?> evaluated</p>
            </div>

            <!-- Card 3: Target Compliance -->
            <div class="p-4 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Target Compliance</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-heading text-text"><?= $targetComplianceRate ?>%</span>
                    <span class="text-xs font-semibold text-text-muted">Approved</span>
                </div>
                <div class="mt-2 w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                </div>
                <p class="mt-1 text-[11px] text-text-muted"><?= number_format($pipeline['target']['approved']) ?> of <?= number_format($totalPersonnel) ?> approved</p>
            </div>

            <!-- Card 4: Cycle Completion -->
            <div class="p-4 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Cycle Completion</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black font-heading text-text"><?= $evalCompletionRate ?>%</span>
                    <span class="text-xs font-semibold text-text-muted">Finalized</span>
                </div>
                <div class="mt-2 w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                </div>
                <p class="mt-1 text-[11px] text-text-muted"><?= number_format($pipeline['evaluation']['completed']) ?> finalized • <?= number_format($pipeline['evaluation']['action']) ?> in eval</p>
            </div>

        </div>

        <!-- 2. SPMS WORKFLOW PIPELINE STAGES -->
        <div class="p-5 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-black font-heading text-text">SPMS Workflow Stages</h2>
                    <p class="text-[11px] text-text-muted">Target Commitment Setting vs. Accomplishment Evaluation Progress</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-surface border border-surface-border text-text-muted">
                    2-Phase Lifecycle
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Phase -->
                <div class="p-3.5 rounded-lg bg-surface/50 border border-surface-border">
                    <div class="flex items-center justify-between text-xs mb-2.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-400">Phase 1: Target Setting</span>
                        <span class="text-[11px] font-bold text-text"><?= $pipeline['target']['approved'] ?> / <?= $totalPersonnel ?> Approved</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold">
                        <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['target']['approved'] ?></span>
                            <span>Approved</span>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['target']['pending'] ?></span>
                            <span>In Review</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['target']['draft'] ?></span>
                            <span>Draft</span>
                        </div>
                        <div class="p-2 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['target']['returned'] ?></span>
                            <span>Revision</span>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Phase -->
                <div class="p-3.5 rounded-lg bg-surface/50 border border-surface-border">
                    <div class="flex items-center justify-between text-xs mb-2.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Phase 2: Accomplishment Evaluation</span>
                        <span class="text-[11px] font-bold text-text"><?= $pipeline['evaluation']['completed'] ?> / <?= $totalPersonnel ?> Completed</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold">
                        <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                            <span>Approved</span>
                        </div>
                        <div class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['evaluation']['action'] ?></span>
                            <span>Evaluating</span>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                            <span>Submitted</span>
                        </div>
                        <div class="p-2 rounded-lg bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
                            <span class="block text-sm font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                            <span>Draft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. COLLEGE & DEPARTMENT COMPLIANCE LEADERBOARD -->
        <div class="p-5 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-sm font-black font-heading text-text">Department Compliance Leaderboard</h2>
                    <p class="text-[11px] text-text-muted">Target compliance and final evaluation completion per unit</p>
                </div>
                <div class="relative w-full sm:w-56">
                    <input type="text" id="leaderboard-search" onkeyup="filterLeaderboard()"
                           placeholder="Search department..." 
                           class="w-full text-xs font-semibold px-3 py-1.5 pl-8 rounded-lg bg-surface border border-surface-border text-text placeholder-text-muted focus:outline-none focus:border-accent" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-text-muted absolute left-2.5 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="leaderboard-table">
                    <thead>
                        <tr class="border-b border-surface-border text-[10px] font-black uppercase text-text-muted tracking-wider">
                            <th class="pb-2.5 pl-2">Rank</th>
                            <th class="pb-2.5">College / Department</th>
                            <th class="pb-2.5 text-center">Headcount</th>
                            <th class="pb-2.5 text-center">Target Compliance</th>
                            <th class="pb-2.5 text-center">Eval Completion</th>
                            <th class="pb-2.5 text-center">Average</th>
                            <th class="pb-2.5 text-right pr-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/50 text-xs font-semibold">
                        <?php if (empty($deptLeaderboard)): ?>
                            <tr>
                                <td colspan="7" class="py-6 text-center text-text-muted">
                                    No department records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deptLeaderboard as $index => $dept): ?>
                                <tr class="hover:bg-white/5 dark:hover:bg-white/5 transition-colors dept-row">
                                    <td class="py-2.5 pl-2 font-black text-text-muted">#<?= $index + 1 ?></td>
                                    <td class="py-2.5 font-bold text-text dept-name"><?= esc($dept['name']) ?></td>
                                    <td class="py-2.5 text-center text-text font-bold"><?= $dept['headcount'] ?></td>
                                    <td class="py-2.5 text-center">
                                        <span class="font-extrabold text-purple-400"><?= $dept['headcount'] > 0 ? round(($dept['target_approved'] / $dept['headcount']) * 100) : 0 ?>%</span>
                                        <span class="text-[10px] text-text-muted font-normal">(<?= $dept['target_approved'] ?>/<?= $dept['headcount'] ?>)</span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="font-extrabold text-emerald-400"><?= $dept['compliance_pct'] ?>%</span>
                                        <span class="text-[10px] text-text-muted font-normal">(<?= $dept['eval_completed'] ?>/<?= $dept['headcount'] ?>)</span>
                                    </td>
                                    <td class="py-2.5 text-center font-extrabold text-text">
                                        <?= $dept['average_rating'] > 0 ? number_format($dept['average_rating'], 2) : '--' ?>
                                    </td>
                                    <td class="py-2.5 text-right pr-2">
                                        <?php if ($dept['compliance_pct'] >= 100): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                100% Compliant
                                            </span>
                                        <?php elseif ($dept['compliance_pct'] >= 50): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-500/15 text-blue-400 border border-blue-500/30">
                                                On Track
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                Action Needed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. RECENT PERFORMANCE ACTIVITY FEED -->
        <div class="p-5 rounded-xl bg-black/15 dark:bg-black/25 border border-surface-border shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-black font-heading text-text">Recent Performance Reviews</h2>
                    <p class="text-[11px] text-text-muted">Latest employee status updates in this cycle</p>
                </div>
                <a href="<?= site_url('ratings') ?>" class="text-xs font-bold text-accent hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-surface-border text-[10px] font-black uppercase text-text-muted tracking-wider">
                            <th class="pb-2.5 pl-2">Ratee</th>
                            <th class="pb-2.5">Position</th>
                            <th class="pb-2.5">Department</th>
                            <th class="pb-2.5 text-center">Status</th>
                            <th class="pb-2.5 text-center">Score</th>
                            <th class="pb-2.5 text-right pr-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/50 text-xs font-semibold">
                        <?php if (empty($recentFolders)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-text-muted">
                                    No folder records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentFolders as $rf): ?>
                                <tr class="hover:bg-white/5 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-2.5 pl-2 font-bold text-text"><?= esc($rf['full_name']) ?></td>
                                    <td class="py-2.5 text-text-muted"><?= esc($rf['position']) ?></td>
                                    <td class="py-2.5 text-text-muted truncate max-w-[200px]"><?= esc($rf['department']) ?></td>
                                    <td class="py-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-surface text-text border border-surface-border">
                                            <?= esc(str_replace('_', ' ', $rf['folder_status'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center font-extrabold text-text">
                                        <?= $rf['rating_num'] !== null ? number_format($rf['rating_num'], 2) : '--' ?>
                                    </td>
                                    <td class="py-2.5 text-right pr-2">
                                        <a href="<?= site_url('ratings/show/' . $rf['folder_id']) ?>" 
                                           class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold text-accent hover:bg-accent/10 transition-colors">
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
</script>

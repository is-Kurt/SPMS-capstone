<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-4 md:p-8 max-w-[1600px] mx-auto flex flex-col gap-6 md:gap-8 pb-20 lg:min-h-[calc(100vh-6rem)]">

    <!-- Header & Institutional Export -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shrink-0">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-text-muted">Security & Compliance</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Tamper-Evident Record
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-text">Audit Trail & Activity Logs</h1>
            <p class="text-xs md:text-sm text-text-muted mt-1">
                Institutional trace of sensitive target approvals, rating evaluations, TWG verifications, logins, and administrative changes.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <?php 
                $exportQuery = http_build_query([
                    'search'    => $filters['search'] ?? '',
                    'category'  => $filters['category'] ?? 'ALL',
                    'date_from' => $filters['date_from'] ?? '',
                    'date_to'   => $filters['date_to'] ?? '',
                ]);
            ?>
            <a href="<?= site_url('audit-logs/export-csv?' . $exportQuery) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-emerald-700 hover:bg-emerald-800 text-white shadow-md hover:shadow-lg transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV Report</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 shrink-0">
        <!-- Total -->
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Total Events</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-text"><?= number_format($metrics['total']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">actions</span>
            </div>
        </div>

        <!-- Auth -->
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Auth & 2FA</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-purple-700 dark:text-purple-300"><?= number_format($metrics['auth']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">sessions</span>
            </div>
        </div>

        <!-- Target Setting -->
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Target Setting</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-blue-700 dark:text-blue-300"><?= number_format($metrics['target']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">changes</span>
            </div>
        </div>

        <!-- Ratings -->
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Ratings & TWG</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-amber-700 dark:text-amber-300"><?= number_format($metrics['rating']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">evals</span>
            </div>
        </div>

        <!-- Evidence / MOV -->
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">MOV Evidence</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-teal-700 dark:text-teal-300"><?= number_format($metrics['evidence']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">files</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar & Search Form -->
    <div class="p-4 md:p-5 rounded-2xl bg-surface border border-surface-border shadow-xs shrink-0">
        <form method="GET" action="<?= site_url('audit-logs') ?>" class="flex flex-col gap-4">
            
            <!-- Category Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar text-xs font-bold">
                <span class="text-text-muted shrink-0 mr-1">Category:</span>
                <?php
                    $categories = [
                        'ALL'      => 'All Categories',
                        'AUTH'     => 'Auth & Access',
                        'TARGET'   => 'Target Setting',
                        'RATING'   => 'Rating & Scoring',
                        'ACCOUNT'  => 'Accounts & Roles',
                        'EVIDENCE' => 'MOV Evidence',
                    ];
                    $curCat = $filters['category'] ?? 'ALL';
                ?>
                <?php foreach ($categories as $catKey => $catLabel): ?>
                    <button type="submit" name="category" value="<?= $catKey ?>"
                            class="px-3 py-1.5 rounded-full transition-all duration-150 cursor-pointer shrink-0 <?= $curCat === $catKey ? 'bg-emerald-600 text-white shadow-xs' : 'bg-surface-border/40 hover:bg-surface-border text-text-muted hover:text-text' ?>">
                        <?= $catLabel ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Inputs Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="md:col-span-5 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
                           placeholder="Search by user, email, action code, details, or IP..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-bg border border-surface-border text-text placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                </div>

                <!-- Date Range From -->
                <div class="md:col-span-3 flex items-center gap-2">
                    <span class="text-[11px] font-bold text-text-muted shrink-0">From:</span>
                    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>"
                           class="w-full px-3 py-2 text-xs rounded-xl bg-bg border border-surface-border text-text focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                </div>

                <!-- Date Range To -->
                <div class="md:col-span-3 flex items-center gap-2">
                    <span class="text-[11px] font-bold text-text-muted shrink-0">To:</span>
                    <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>"
                           class="w-full px-3 py-2 text-xs rounded-xl bg-bg border border-surface-border text-text focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                </div>

                <!-- Action Buttons -->
                <div class="md:col-span-1 flex items-center gap-2 justify-end">
                    <button type="submit"
                            class="w-full py-2 px-3 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer text-center"
                            title="Apply filters">
                        Filter
                    </button>
                    <?php if (!empty($filters['search']) || ($filters['category'] ?? 'ALL') !== 'ALL' || !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                        <a href="<?= site_url('audit-logs') ?>"
                           class="py-2 px-2 text-xs font-bold rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer"
                           title="Reset all filters">
                            &times;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table Card -->
    <div class="bg-surface rounded-2xl border border-surface-border shadow-sm overflow-hidden flex flex-col flex-1">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-surface-border bg-surface-border/20 text-[11px] font-bold uppercase tracking-wider text-text-muted">
                        <th class="py-3.5 px-4">Timestamp</th>
                        <th class="py-3.5 px-4">Actor</th>
                        <th class="py-3.5 px-4">Action Code</th>
                        <th class="py-3.5 px-4">Event Details</th>
                        <th class="py-3.5 px-4">Client / IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/60">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="py-16 text-center text-text-muted">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="font-bold text-sm">No activity logs found matching your criteria.</span>
                                    <span class="text-xs">Adjust your search or filter parameters above.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                // Action code badge colors
                                $catClass = match($log['category']) {
                                    'AUTH'     => 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border-purple-500/20',
                                    'TARGET'   => 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/20',
                                    'RATING'   => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20',
                                    'ACCOUNT'  => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/20',
                                    'EVIDENCE' => 'bg-teal-500/10 text-teal-700 dark:text-teal-300 border-teal-500/20',
                                    default    => 'bg-slate-500/10 text-slate-700 dark:text-slate-300 border-slate-500/20'
                                };

                                $actionBadge = match(true) {
                                    str_contains($log['action'], 'APPROVED')   => 'text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border-emerald-500/20',
                                    str_contains($log['action'], 'RETURNED') || str_contains($log['action'], 'DISAPPROVED') || str_contains($log['action'], 'DELETED') || str_contains($log['action'], 'FAILED') => 'text-rose-700 dark:text-rose-300 bg-rose-500/10 border-rose-500/20',
                                    str_contains($log['action'], 'REVOKED') || str_contains($log['action'], 'UNAPPROVED') => 'text-amber-700 dark:text-amber-300 bg-amber-500/10 border-amber-500/20',
                                    str_contains($log['action'], 'SUBMITTED')  => 'text-blue-700 dark:text-blue-300 bg-blue-500/10 border-blue-500/20',
                                    default => 'text-slate-700 dark:text-slate-300 bg-slate-500/10 border-slate-500/20'
                                };
                            ?>
                            <tr class="hover:bg-surface-border/20 transition-colors">
                                <!-- Timestamp -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-text">
                                        <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                    </div>
                                    <div class="text-[11px] text-text-muted flex items-center gap-1.5 mt-0.5">
                                        <span><?= date('h:i:s A', strtotime($log['created_at'])) ?></span>
                                        <span class="inline-block w-1 h-1 rounded-full bg-surface-border"></span>
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400"><?= time_ago_str($log['created_at']) ?></span>
                                    </div>
                                </td>

                                <!-- Actor -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-2xs"
                                             style="background-color: <?= esc($log['avatar_color'] ?? '#10b981') ?>;">
                                            <?= esc($log['avatar_letter'] ?? strtoupper(substr($log['user_name'], 0, 1))) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-text truncate max-w-[180px]">
                                                <?= esc($log['user_name']) ?>
                                            </div>
                                            <div class="text-[11px] text-text-muted flex items-center gap-1 mt-0.5">
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-surface-border/40 text-text-muted">
                                                    <?= esc($log['role_name'] ?? 'Guest') ?>
                                                </span>
                                                <?php if (!empty($log['email'])): ?>
                                                    <span class="truncate max-w-[130px] hidden sm:inline text-text-muted/80">
                                                        <?= esc($log['email']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Action & Category -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?= $actionBadge ?>">
                                            <?= esc($log['action']) ?>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-semibold border <?= $catClass ?>">
                                            <?= esc($log['category']) ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Event Details -->
                                <td class="py-3.5 px-4">
                                    <div class="text-text font-medium leading-relaxed max-w-lg break-words">
                                        <?= esc($log['details'] ?? '—') ?>
                                    </div>
                                    <?php if (!empty($log['entity_type']) && !empty($log['entity_id'])): ?>
                                        <div class="text-[10px] font-semibold text-text-muted/80 mt-1 flex items-center gap-1">
                                            <span class="uppercase tracking-wider">Target:</span>
                                            <code class="px-1 py-0.5 rounded bg-surface-border/40 text-text-muted"><?= esc($log['entity_type']) ?> #<?= esc($log['entity_id']) ?></code>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Client IP & Agent -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-mono text-[11px] text-text">
                                        <?= esc($log['ip_address'] ?? '127.0.0.1') ?>
                                    </div>
                                    <div class="text-[10px] text-text-muted mt-0.5 truncate max-w-[140px]" title="<?= esc($log['user_agent'] ?? '') ?>">
                                        <?= esc($log['user_agent'] ? substr($log['user_agent'], 0, 30) . '...' : 'Internal Service') ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-surface-border bg-surface-border/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="text-text-muted font-medium">
                    Showing <span class="font-bold text-text"><?= number_format(($page - 1) * $perPage + 1) ?></span> to
                    <span class="font-bold text-text"><?= number_format(min($totalCount, $page * $perPage)) ?></span> of
                    <span class="font-bold text-text"><?= number_format($totalCount) ?></span> records
                </div>

                <div class="flex items-center gap-1">
                    <?php
                        $baseParams = [
                            'search'    => $filters['search'] ?? '',
                            'category'  => $filters['category'] ?? 'ALL',
                            'date_from' => $filters['date_from'] ?? '',
                            'date_to'   => $filters['date_to'] ?? '',
                        ];
                    ?>

                    <!-- Prev Page -->
                    <?php if ($page > 1): ?>
                        <a href="<?= site_url('audit-logs?' . http_build_query(array_merge($baseParams, ['page' => $page - 1]))) ?>"
                           class="px-3 py-1.5 rounded-lg border border-surface-border bg-surface hover:bg-surface-border/30 text-text font-semibold transition-colors">
                            &larr; Prev
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1.5 rounded-lg border border-surface-border/50 bg-surface/50 text-text-muted/40 font-semibold cursor-not-allowed">
                            &larr; Prev
                        </span>
                    <?php endif; ?>

                    <!-- Current Page Badge -->
                    <span class="px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">
                        <?= $page ?> / <?= $totalPages ?>
                    </span>

                    <!-- Next Page -->
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= site_url('audit-logs?' . http_build_query(array_merge($baseParams, ['page' => $page + 1]))) ?>"
                           class="px-3 py-1.5 rounded-lg border border-surface-border bg-surface hover:bg-surface-border/30 text-text font-semibold transition-colors">
                            Next &rarr;
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1.5 rounded-lg border border-surface-border/50 bg-surface/50 text-text-muted/40 font-semibold cursor-not-allowed">
                            Next &rarr;
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?= $this->endSection() ?>

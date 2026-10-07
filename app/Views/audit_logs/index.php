<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?php
if (!function_exists('parse_short_os')) {
    function parse_short_os(?string $ua): string {
        if (empty($ua)) return '';
        if (stripos($ua, 'Windows NT 10.0') !== false) return 'Win10';
        if (stripos($ua, 'Windows NT 11.0') !== false) return 'Win11';
        if (stripos($ua, 'Windows NT 6.3') !== false) return 'Win8.1';
        if (stripos($ua, 'Windows NT 6.1') !== false) return 'Win7';
        if (stripos($ua, 'Windows') !== false) return 'Win';
        if (stripos($ua, 'iPhone') !== false) return 'iOS';
        if (stripos($ua, 'iPad') !== false) return 'iPadOS';
        if (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false) return 'Mac';
        if (stripos($ua, 'Android') !== false) return 'Android';
        if (stripos($ua, 'Linux') !== false) return 'Linux';
        if (stripos($ua, 'CLI') !== false) return 'CLI';
        return '';
    }
}

$exportQuery = http_build_query(array_filter([
    'search' => $filters['search'] ?? '',
]));
?>

<div class="p-3.5 sm:p-6 md:p-8 max-w-[1600px] mx-auto flex flex-col gap-4 sm:gap-6 md:gap-8 pb-20 lg:min-h-[calc(100vh-6rem)]">

    <!-- Header & Institutional Export -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 shrink-0">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-text-muted">Security & Compliance</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Tamper-Evident Record
                </span>
            </div>
            
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-text">Audit Trail</h1>
                
                <!-- Mobile Export CSV button directly on the title row -->
                <div class="sm:hidden">
                    <a href="<?= site_url('audit-logs/export-csv?' . $exportQuery) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs bg-emerald-950/60 dark:bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 hover:bg-emerald-900/60 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <p class="hidden sm:block text-xs md:text-sm text-text-muted mt-1">
                Institutional trace of sensitive target approvals, rating evaluations, TWG verifications, logins, and administrative changes.
            </p>
        </div>

        <!-- Desktop Export Button -->
        <div class="hidden sm:flex items-center gap-3 shrink-0">
            <a href="<?= site_url('audit-logs/export-csv?' . $exportQuery) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-emerald-700 hover:bg-emerald-800 text-white shadow-md hover:shadow-lg transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV Report</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards: Mobile 2-Row Layout (sm:hidden) -->
    <div class="sm:hidden flex flex-col gap-2.5 shrink-0">
        <!-- Row 1: Total Events & Auth -->
        <div class="grid grid-cols-2 gap-2.5">
            <!-- Total Events -->
            <div class="p-3.5 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted">Total Events</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-text"><?= number_format($metrics['total']) ?></span>
                    <span class="text-[11px] font-semibold text-text-muted">actions</span>
                </div>
            </div>

            <!-- Auth & 2FA -->
            <div class="p-3.5 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Auth & 2FA</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-purple-700 dark:text-purple-300"><?= number_format($metrics['auth']) ?></span>
                    <span class="text-[11px] font-semibold text-text-muted">sessions</span>
                </div>
            </div>
        </div>

        <!-- Row 2: Targets, Ratings, Evidence -->
        <div class="grid grid-cols-3 gap-2">
            <!-- Targets -->
            <div class="p-2.5 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-0.5">
                <span class="text-[9px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 truncate">Targets</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-black text-text"><?= number_format($metrics['target']) ?></span>
                    <span class="text-[9px] font-semibold text-text-muted truncate">changes</span>
                </div>
            </div>

            <!-- Ratings -->
            <div class="p-2.5 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-0.5">
                <span class="text-[9px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 truncate">Ratings</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-black text-text"><?= number_format($metrics['rating']) ?></span>
                    <span class="text-[9px] font-semibold text-text-muted truncate">evals</span>
                </div>
            </div>

            <!-- Evidence -->
            <div class="p-2.5 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-0.5">
                <span class="text-[9px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400 truncate">Evidence</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-black text-text"><?= number_format($metrics['evidence']) ?></span>
                    <span class="text-[9px] font-semibold text-text-muted truncate">files</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards: Desktop Layout (hidden sm:grid) -->
    <div class="hidden sm:grid sm:grid-cols-5 gap-3.5 shrink-0">
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
        <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">MOV Evidence</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-teal-700 dark:text-teal-300"><?= number_format($metrics['evidence']) ?></span>
                <span class="text-[11px] font-semibold text-text-muted">files</span>
            </div>
        </div>
    </div>

    <!-- Minimalist Search Bar -->
    <div class="p-2 sm:p-2.5 rounded-2xl bg-surface border border-surface-border shadow-xs shrink-0">
        <form method="GET" action="<?= site_url('audit-logs') ?>" class="flex items-center gap-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
                       placeholder="Search by user, email, action, details, or IP address..."
                       class="w-full pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm rounded-xl bg-bg border border-surface-border text-text placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition-all">
                
                <?php if (!empty($filters['search'])): ?>
                    <a href="<?= site_url('audit-logs') ?>"
                       class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-text-muted hover:text-text cursor-pointer transition-colors"
                       title="Clear search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>

            <button type="submit"
                    class="px-4 sm:px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                Search
            </button>
        </form>
    </div>

    <!-- Section Heading: Activity Entries Count -->
    <div class="text-[11px] font-bold uppercase tracking-wider text-text-muted px-1 flex items-center justify-between">
        <span>
            <?= !empty($filters['search']) ? 'Search Results for "' . esc($filters['search']) . '"' : 'Latest Activity Entries' ?> 
            (<?= number_format($totalCount) ?>)
        </span>
        <?php if (!empty($filters['search'])): ?>
            <a href="<?= site_url('audit-logs') ?>" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline normal-case font-semibold">
                Clear search
            </a>
        <?php endif; ?>
    </div>

    <!-- Mobile Feed: Activity Cards (md:hidden) -->
    <div class="md:hidden flex flex-col gap-3">
        <?php if (empty($logs)): ?>
            <div class="p-8 rounded-2xl bg-surface border border-surface-border text-center text-text-muted">
                <svg class="w-8 h-8 opacity-40 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="font-bold text-sm">No activity logs found.</div>
                <div class="text-xs mt-0.5">Adjust your search or filter parameters above.</div>
            </div>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <?php
                    // Action code badge colors
                    $catClass = match($log['category']) {
                        'AUTH'     => 'bg-purple-500/10 text-purple-600 dark:text-purple-300 border-purple-500/20',
                        'TARGET'   => 'bg-blue-500/10 text-blue-600 dark:text-blue-300 border-blue-500/20',
                        'RATING'   => 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border-amber-500/20',
                        'ACCOUNT'  => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border-indigo-500/20',
                        'EVIDENCE' => 'bg-teal-500/10 text-teal-600 dark:text-teal-300 border-teal-500/20',
                        default    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border-zinc-500/20'
                    };

                    $actionBadge = match(true) {
                        str_contains($log['action'], 'APPROVED') || $log['action'] === 'LOGIN' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                        str_contains($log['action'], 'RETURNED') || str_contains($log['action'], 'DISAPPROVED') || str_contains($log['action'], 'DELETED') || str_contains($log['action'], 'FAILED') || $log['action'] === 'LOGOUT' => 'text-rose-600 dark:text-rose-400 bg-rose-500/10 border-rose-500/20',
                        str_contains($log['action'], 'REVOKED') || str_contains($log['action'], 'UNAPPROVED') => 'text-amber-600 dark:text-amber-400 bg-amber-500/10 border-amber-500/20',
                        str_contains($log['action'], 'SUBMITTED')  => 'text-blue-600 dark:text-blue-400 bg-blue-500/10 border-blue-500/20',
                        default => 'text-zinc-600 dark:text-zinc-400 bg-zinc-500/10 border-zinc-500/20'
                    };

                    $os = parse_short_os($log['user_agent'] ?? '');
                    $clientInfo = esc($log['ip_address'] ?? '127.0.0.1') . ($os ? " ({$os})" : '');
                ?>
                <div class="p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col gap-2.5">
                    <!-- Card Header: Avatar, Actor, Badges -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-2xs"
                                 style="background-color: <?= esc($log['avatar_color'] ?? '#10b981') ?>;">
                                <?= esc($log['avatar_letter'] ?? strtoupper(substr($log['user_name'], 0, 1))) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-text text-sm truncate">
                                    <?= esc($log['user_name']) ?>
                                </div>
                                <div class="text-[11px] text-text-muted truncate">
                                    <span><?= esc($log['role_name'] ?? 'Guest') ?></span>
                                    <?php if (!empty($log['email'])): ?>
                                        <span class="mx-1">•</span>
                                        <span><?= esc($log['email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?= $actionBadge ?>">
                                <?= esc($log['action']) ?>
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $catClass ?>">
                                <?= esc($log['category']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Event Details & Target -->
                    <div class="flex flex-col gap-1">
                        <div class="text-text font-bold text-xs sm:text-sm leading-snug break-words">
                            <?= esc($log['details'] ?? '—') ?>
                        </div>
                        <?php if (!empty($log['entity_type']) && !empty($log['entity_id'])): ?>
                            <div class="text-[11px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-wide">
                                TARGET: <?= esc($log['entity_type']) ?> #<?= esc($log['entity_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Footer: Timestamp, Relative Time, Client Info -->
                    <div class="pt-2 border-t border-surface-border/40 flex items-center justify-between gap-2 text-[11px] text-text-muted">
                        <div class="flex items-center gap-1.5 truncate">
                            <span><?= date('M d, Y, h:i A', strtotime($log['created_at'])) ?></span>
                            <span>•</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400 shrink-0"><?= time_ago_str($log['created_at']) ?></span>
                        </div>
                        <div class="font-mono text-[10px] shrink-0 text-text-muted/80">
                            <?= $clientInfo ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Desktop Table View (hidden md:flex) -->
    <div class="hidden md:flex flex-col bg-surface rounded-2xl border border-surface-border shadow-sm overflow-hidden">
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
                                    default    => 'bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border-zinc-500/20'
                                };

                                $actionBadge = match(true) {
                                    str_contains($log['action'], 'APPROVED') || $log['action'] === 'LOGIN' => 'text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border-emerald-500/20',
                                    str_contains($log['action'], 'RETURNED') || str_contains($log['action'], 'DISAPPROVED') || str_contains($log['action'], 'DELETED') || str_contains($log['action'], 'FAILED') || $log['action'] === 'LOGOUT' => 'text-rose-700 dark:text-rose-300 bg-rose-500/10 border-rose-500/20',
                                    str_contains($log['action'], 'REVOKED') || str_contains($log['action'], 'UNAPPROVED') => 'text-amber-700 dark:text-amber-300 bg-amber-500/10 border-amber-500/20',
                                    str_contains($log['action'], 'SUBMITTED')  => 'text-blue-700 dark:text-blue-300 bg-blue-500/10 border-blue-500/20',
                                    default => 'text-zinc-700 dark:text-zinc-300 bg-zinc-500/10 border-zinc-500/20'
                                };

                                $os = parse_short_os($log['user_agent'] ?? '');
                                $clientInfo = esc($log['ip_address'] ?? '127.0.0.1') . ($os ? " ({$os})" : '');
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
                                    <div class="text-[10px] text-text-muted mt-0.5 truncate max-w-[160px]" title="<?= esc($log['user_agent'] ?? '') ?>">
                                        <?= $clientInfo ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls (Shared for Mobile Cards & Desktop Table) -->
    <?php if ($totalPages > 1): ?>
        <div class="p-3 sm:p-4 rounded-2xl bg-surface border border-surface-border shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shrink-0">
            <div class="text-text-muted font-medium text-center sm:text-left text-[11px] sm:text-xs">
                Showing <span class="font-bold text-text"><?= number_format(($page - 1) * $perPage + 1) ?></span> to
                <span class="font-bold text-text"><?= number_format(min($totalCount, $page * $perPage)) ?></span> of
                <span class="font-bold text-text"><?= number_format($totalCount) ?></span> records
            </div>

            <div class="flex items-center gap-1">
                <?php
                    $baseParams = array_filter([
                        'search' => $filters['search'] ?? '',
                    ]);
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



<?= $this->endSection() ?>

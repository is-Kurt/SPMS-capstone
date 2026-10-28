<?php 
    $sysRole = $sysRole ?? session()->get('role');
    $firstPeriodKey = 'target';
    $firstTabKey = array_key_first($periods[$firstPeriodKey]['tabs']);
?>
<style>
@media (min-width: 1024px) {
    #ratings-sidebar { z-index: 0 !important; }
}
</style>

<?php if (!$activeFolder): ?>
    <button onclick="toggleAppSidebar()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-accent rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-accent/5 transition-all group cursor-pointer lg:cursor-default min-h-[400px]">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 group-hover:bg-accent/10 text-zinc-400 group-hover:text-accent dark:text-zinc-500 mb-4 shadow-sm transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text group-hover:text-accent transition-colors mb-1">Select a Folder</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
    </button>

<?php else: ?>

<div id="ratings-layout-container" class="flex flex-col lg:flex-row-reverse lg:absolute lg:inset-0 lg:min-h-[650px] bg-transparent lg:gap-6 lg:pb-6">

    <!-- MOBILE FILTER OVERLAY -->
    <div id="mobile-filter-overlay" class="fixed inset-0 z-[115] bg-black/50 hidden lg:hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- SIDEBAR FILTERS (Left on mobile, Right on desktop) -->
    <div id="ratings-sidebar" class="fixed inset-y-0 left-0 z-[120] lg:z-0 w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border border-surface-border shadow-2xl lg:shadow-none lg:rounded-2xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
        <div class="px-6 py-4 border-b border-surface-border flex justify-between items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 lg:rounded-t-2xl">
            <div class="flex items-center gap-2">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filters</h2>
                <span id="active-ratings-filter-badge" class="hidden px-1.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">0</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="reset-ratings-filters-btn" onclick="clearAllRatingsFilters()" class="text-[10px] font-bold text-text-muted hover:text-text transition-colors cursor-pointer hidden">
                    Reset all
                </button>
                <button type="button" id="close-mobile-filters" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
        
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4">
            <!-- Search -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search-rating" placeholder="Search employee name..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-9 pr-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all text-text font-bold shadow-sm">
                </div>
            </div>

            <!-- Staff Type Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group cursor-pointer" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Staff Type</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="filter_type[]" value="1" class="checkbox ratings-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                        <span>Teaching</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="filter_type[]" value="0" class="checkbox ratings-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                        <span>Non-Teaching</span>
                    </label>
                </div>
            </div>

            <!-- Dynamic Cascading Unit / Department Filter -->
            <?php if (!isset($hasLowerLevels) || !empty($hasLowerLevels)): ?>
            <div id="dynamic-unit-filter-container" class="shrink-0 pb-4 border-b border-surface-border/50 space-y-3">
                <!-- Populated dynamically by JavaScript -->
            </div>
            <?php endif; ?>

            <!-- Positions Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group cursor-pointer" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Positions</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="flex flex-col">
                    <div class="relative mb-3 shrink-0">
                        <input type="text" id="mini-search-positions" placeholder="Find position..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <div class="space-y-1 pr-1" id="positions-checkbox-list">
                        <?php foreach ($filterPositions as $pos): ?>
                            <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos)) ?>">
                                <input type="checkbox" name="filter_pos[]" value="<?= esc($pos) ?>" class="checkbox ratings-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                <span class="truncate"><?= esc($pos) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="pt-2 flex justify-between items-center bg-transparent shrink-0 hidden" id="filter-pos-pagination">
                        <div class="text-[9px] font-bold text-text-muted uppercase tracking-widest">
                            <span id="r-pos-page-start">0</span>-<span id="r-pos-page-end">0</span> of <span id="r-pos-page-total">0</span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" class="js-page-prev p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></button>
                            <button type="button" class="js-page-next p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Clear Filters Button (Sticky Bottom) -->
        <div class="p-4 border-t border-surface-border shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 lg:rounded-b-2xl">
            <button type="button" id="clear-ratings-filters" class="w-full py-2 text-xs font-bold text-text-muted hover:text-text border border-surface-border rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                Clear Filters
            </button>
        </div>
    </div>


    <!-- MAIN CONTENT (Left side now) -->
    <div class="flex-1 flex flex-col min-w-0 min-h-0 bg-transparent lg:bg-surface relative lg:static h-full lg:rounded-2xl lg:border border-surface-border shadow-sm overflow-visible lg:overflow-hidden">

        <!-- MOBILE RATINGS BAR -->
        <div class="lg:hidden flex items-center justify-between mb-3 shrink-0 px-4 pt-4">
            <h1 class="text-xl font-black text-text truncate">Ratings</h1>
            <button type="button" id="open-mobile-filters" class="flex items-center gap-2 text-xs font-bold text-accent bg-accent/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filters
            </button>
        </div>

        <!-- Mobile active folder selector dropdown -->
        <?= view('components/mobile_folder_dropdown', [
            'activeFolder'     => $activeFolder ?? null,
            'folders'          => $sidebarFolders ?? [],
            'selectedFolderId' => $activeFolder['id'] ?? null,
            'baseUrl'          => 'ratings',
            'containerClass'   => 'px-4 mb-3.5 shrink-0'
        ]) ?>

        <div class="relative mb-6 px-4 lg:px-8 pt-0 lg:pt-8 shrink-0 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="hidden lg:flex items-center">
                    <h1 class="text-3xl font-black tracking-tight text-text truncate">
                        <?= esc($activeFolder['title']) ?>
                    </h1>
                    <?php if (!empty($activeFolder['deleted_at'])): ?>
                        <span class="ml-2 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                            Archived & Frozen
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-xs font-bold text-text-muted mt-0.5 lg:mt-2 uppercase tracking-widest">
                    Manage your assigned reviews
                </p>
                <?php if ($sysRole === 'TWG'): ?>
                    <?php if (!empty($assignedUnits)): ?>
                        <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">Assigned Jurisdiction:</span>
                            <?php foreach ($assignedUnits as $au): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-accent/10 text-accent border border-accent/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <?= esc($au['unit_name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Institutional Scope: University-Wide Technical Working Group Review</span>
                        </div>
                    <?php endif; ?>
                <?php elseif ($sysRole === 'PMT'): ?>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Institutional Scope: University-Wide Calibration & Executive Committee Review
                        </span>
                    </div>

                <?php elseif (session()->get('is_president') || (str_contains(strtolower(session()->get('position') ?? ''), 'president') && !str_contains(strtolower(session()->get('position') ?? ''), 'vice')) || str_contains(strtolower(session()->get('email') ?? ''), 'president')): ?>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Office of the University President — Executive OPCR Review of Vice Presidents
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="shrink-0">
                <input type="hidden" id="period-input" value="<?= $firstPeriodKey ?>">
                <div class="inline-flex p-1 bg-surface-border/40 dark:bg-zinc-800/60 rounded-xl border border-surface-border/80 shadow-xs" id="ratings-period-subtabs">
                    <?php foreach($periods as $pKey => $period): ?>
                        <?php $isActive = ($pKey === $firstPeriodKey); ?>
                        <button type="button" 
                                id="period-subtab-btn-<?= $pKey ?>" 
                                onclick="switchPeriod('<?= $pKey ?>')"
                                class="ratings-period-tab-btn px-4 py-2 text-xs rounded-lg transition-all cursor-pointer <?= $isActive ? 'bg-accent text-zinc-950 shadow-xs font-black' : 'font-bold text-text-muted hover:text-text' ?>">
                            <?= esc($period['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($sysRole === 'Admin'): ?>
            <!-- ADMIN SCOPE SEGMENTED CONTROL: CASCADED vs OVERALL -->
            <div class="px-4 lg:px-8 pb-3 pt-1 shrink-0 flex items-center justify-between gap-3">
                <div class="inline-flex p-1 bg-surface-border/40 dark:bg-zinc-800/60 rounded-xl border border-surface-border/80 shadow-xs">
                    <button type="button" 
                            id="admin-scope-btn-cascaded" 
                            onclick="switchAdminScope('cascaded')"
                            class="admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer bg-accent text-zinc-950 shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Cascaded by Admin</span>
                        <span id="scope-count-cascaded" class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-zinc-950/15 text-zinc-950">
                            <?= ($firstPeriodKey === 'target') ? ($targetCascadedCount ?? 0) : ($evalCascadedCount ?? 0) ?>
                        </span>
                    </button>
                    <button type="button" 
                            id="admin-scope-btn-overall" 
                            onclick="switchAdminScope('overall')"
                            class="admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-text-muted hover:text-text">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <span>Overall (All Users)</span>
                        <span id="scope-count-overall" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-border text-text-muted">
                            <?= ($firstPeriodKey === 'target') ? ($targetOverallCount ?? 0) : ($evalOverallCount ?? 0) ?>
                        </span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- QUEUE NAVIGATION TABS -->
        <!-- QUEUE NAVIGATION TABS -->
        <div class="px-4 lg:px-8 border-b border-surface-border shrink-0 overflow-x-auto no-scrollbar">
            <?php foreach ($periods as $pKey => $period): ?>
                <div id="period-subtabs-<?= $pKey ?>" class="period-subtabs items-center gap-4 sm:gap-8 <?= ($pKey === $firstPeriodKey) ? 'flex' : 'hidden' ?>">
                    <?php foreach ($period['tabs'] as $key => $group): ?>
                        <?php 
                            $isActive = ($pKey === $firstPeriodKey && $key === $firstTabKey);
                            $cascadedTabCount = $group['cascaded_count'] ?? count(array_filter($group['folders'], fn($r) => !empty($r['is_cascaded_by_me'])));
                            $overallTabCount = $group['overall_count'] ?? count($group['folders']);
                            $displayTabCount = ($sysRole === 'Admin') ? $cascadedTabCount : $overallTabCount;
                            
                            $isTwgOrPmt = in_array($sysRole, ['TWG', 'PMT'], true);
                            $defaultColor = match($key) {
                                'target_all', 'eval_all'                 => 'text-sky-500',
                                'target_in_progress', 'eval_in_progress' => 'text-indigo-500',
                                'target_for_review', 'eval_for_review'   => 'text-amber-500',
                                'target_completed', 'eval_completed'     => 'text-emerald-500',
                                default => 'text-text-muted'
                            };

                            $activeBorderColor = match($key) {
                                'target_all', 'eval_all'                 => 'border-sky-500 dark:border-sky-400 text-sky-600 dark:text-sky-400',
                                'target_in_progress', 'eval_in_progress' => 'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400',
                                'target_for_review', 'eval_for_review'   => 'border-amber-500 dark:border-amber-400 text-amber-500 dark:text-amber-400',
                                'target_completed', 'eval_completed'     => 'border-emerald-500 dark:border-emerald-400 text-emerald-500 dark:text-emerald-400',
                                default => 'border-accent text-accent'
                            };

                            $activeBadgeClass = match($key) {
                                'target_all', 'eval_all'                 => 'px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/30',
                                'target_in_progress', 'eval_in_progress' => 'px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30',
                                'target_for_review', 'eval_for_review'   => 'px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30',
                                'target_completed', 'eval_completed'     => 'px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30',
                                default => 'px-2 py-0.5 rounded-full text-[10px] font-black bg-accent/20 text-accent border border-accent/30'
                            };

                            $defaultBadgeClass = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-surface-border/50 text-text-muted group-hover:text-text';

                            $shortLabel = match($key) {
                                'target_all', 'eval_all'                 => 'All',
                                'target_in_progress', 'eval_in_progress' => $isTwgOrPmt ? 'Endorsement' : 'In Progress',
                                'target_for_review', 'eval_for_review'   => $isTwgOrPmt ? 'Verify' : 'For Review',
                                'target_completed', 'eval_completed'     => $isTwgOrPmt ? 'Verified' : 'Completed',
                                default => $group['label']
                            };
                        ?>
                        <button id="tab-btn-<?= $key ?>" 
                                type="button"
                                data-tab-key="<?= $key ?>"
                                data-active-border="<?= esc($activeBorderColor) ?>"
                                data-active-badge="<?= esc($activeBadgeClass) ?>"
                                data-default-badge="<?= esc($defaultBadgeClass) ?>"
                                class="tab-btn group relative inline-flex items-center gap-1.5 sm:gap-2 pb-3.5 pt-1 text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap shrink-0 <?= $isActive ? 'active-tab font-black ' . $activeBorderColor . ' border-b-2' : 'font-semibold text-text-muted hover:text-text border-b-2 border-transparent hover:border-surface-border' ?>"
                                onclick="switchTab('<?= $key ?>', this)">
                            
                            <!-- Icon -->
                            <span class="tab-icon">
                                <?php if (in_array($key, ['target_all', 'eval_all'], true)): ?>
                                    <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110 <?= $isActive ? 'text-sky-500 dark:text-sky-400' : 'text-text-muted group-hover:text-sky-500' ?>" data-default-color="text-text-muted group-hover:text-sky-500" data-active-color="text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                <?php elseif (in_array($key, ['target_in_progress', 'eval_in_progress'], true)): ?>
                                    <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110 <?= $isActive ? 'text-indigo-500 dark:text-indigo-400' : 'text-text-muted group-hover:text-indigo-500' ?>" data-default-color="text-text-muted group-hover:text-indigo-500" data-active-color="text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                <?php elseif (in_array($key, ['target_for_review', 'eval_for_review'], true)): ?>
                                    <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110 <?= $isActive ? 'text-amber-500 dark:text-amber-400' : 'text-text-muted group-hover:text-amber-500' ?>" data-default-color="text-text-muted group-hover:text-amber-500" data-active-color="text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110 <?= $isActive ? 'text-emerald-500 dark:text-emerald-400' : 'text-text-muted group-hover:text-emerald-500' ?>" data-default-color="text-text-muted group-hover:text-emerald-500" data-active-color="text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                <?php endif; ?>
                            </span>

                            <span class="sm:hidden"><?= esc($shortLabel) ?></span>
                            <span class="hidden sm:inline"><?= esc($group['label']) ?></span>

                            <!-- Badge -->
                            <span class="tab-badge ml-0.5 <?= $isActive ? $activeBadgeClass : $defaultBadgeClass ?>"
                                  data-count-cascaded="<?= $cascadedTabCount ?>"
                                  data-count-overall="<?= $overallTabCount ?>">
                                <?= $displayTabCount ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex flex-col flex-1 min-w-0 min-h-0 lg:overflow-hidden relative pb-10 lg:pb-0">
            <!-- TABS -->
            <?php foreach ($periods as $pKey => $period): ?>
                <?php foreach ($period['tabs'] as $key => $group): ?>
                <div id="tab-content-<?= $key ?>" class="tab-content <?= ($pKey === $firstPeriodKey && $key === $firstTabKey) ? 'flex flex-col flex-1 min-w-0 min-h-0 h-full' : 'hidden' ?>">
                    
                    <?php
                        $colWidths = ($sysRole === 'Admin') ? ['28%', '20%'] : ['40%'];
                        
                        if ($pKey === 'target') {
                            $colWidths = array_merge($colWidths, ($sysRole === 'Admin') ? ['30%', '22%'] : ['38%', '22%']); // Only Folder Status and Action
                        } else {
                            $colWidths = array_merge($colWidths, ($sysRole === 'Admin') ? ['18%', '14%', '20%'] : ['20%', '18%', '22%']); // Folder Status, Score, Action
                        }
                    ?>
                    <div id="ratings-header-<?= $key ?>" class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border" data-frozen-header>
                        <table class="w-full text-left border-collapse table-fixed lg:min-w-[900px]">
                            <colgroup>
                                <?php foreach ($colWidths as $cw): ?><col style="width:<?= $cw ?>"><?php endforeach; ?>
                            </colgroup>
                            <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted">
                                <tr>
                                    <th class="px-6 py-4">User / Position</th>
                                    <?php if ($sysRole === 'Admin'): ?>
                                        <th class="px-6 py-4">Department / Unit</th>
                                    <?php endif; ?>
                                    <th class="px-6 py-4 min-w-[110px]">Folder Status</th>
                                    <?php if ($pKey !== 'target'): ?>
                                        <th class="px-6 py-4 min-w-[60px] text-center">Score</th>
                                    <?php endif; ?>
                                    <th class="px-6 py-4 min-w-[135px] text-right">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="overflow-y-auto overflow-x-auto custom-scrollbar flex-1 w-full max-w-full p-2 lg:p-0" data-frozen-body onscroll="document.getElementById('ratings-header-<?= $key ?>').scrollLeft = this.scrollLeft">
                        <table class="w-full text-left border-collapse block lg:table lg:table-fixed lg:min-w-[900px]">
                            <colgroup>
                                <?php foreach ($colWidths as $cw): ?><col style="width:<?= $cw ?>"><?php endforeach; ?>
                            </colgroup>
                            <tbody class="block lg:table-row-group divide-y lg:divide-y-0 divide-transparent lg:divide-surface-border">
                                <?php if (empty($group['folders'])): ?>
                                    <tr class="block lg:table-row">
                                        <td colspan="100%" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                            No records found in this queue.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($group['folders'] as $row): ?>
                                        
                                        <tr class="rating-card block lg:table-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none"
                                            data-name="<?= esc($row['username']) ?>" 
                                            data-unit="<?= esc($row['unit_id'] ?? '') ?>" 
                                            data-department="<?= esc($row['department'] ?? '') ?>" 
                                            data-position="<?= esc($row['position_id'] ?? '') ?>" 
                                            data-pos="<?= esc($row['position'] ?? '') ?>"
                                            data-teaching="<?= esc($row['is_teaching'] ?? 0) ?>"
                                            data-status="<?= strtolower(esc($row['folder_status'])) ?>"
                                            data-cascaded="<?= !empty($row['is_cascaded_by_me']) ? '1' : '0' ?>"> 
                                            
                                            <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                                <div class="flex justify-between items-start lg:items-center">
                                                    <div class="flex flex-col min-w-0 pr-4">
                                                        <span class="text-sm font-bold text-text truncate">
                                                            <?= esc($row['username']) ?>
                                                        </span>
                                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest <?= ($row['is_teaching'] == 1) ? 'bg-highlight-100 text-highlight-700 dark:bg-highlight-500/20 dark:text-highlight-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' ?>">
                                                                <?= ($row['is_teaching'] == 1) ? 'Teaching' : 'Non-Teaching' ?>
                                                            </span>
                                                            <?php if ($sysRole === 'Admin'): ?>
                                                                <?php if (!empty($row['is_cascaded_by_me'])): ?>
                                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/25">
                                                                        Directly Cascaded
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-500/20">
                                                                        Subordinate Review
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                                                <?= esc($row['position'] ?? 'No Position') ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="lg:hidden shrink-0 mt-0.5">
                                                        <?php 
                                                            $cStatus = \App\Enums\FolderStatus::canonical($row['folder_status'] ?? '');
                                                            $isTwgRole = in_array($sysRole, ['TWG', 'PMT'], true);
                                                            $badgeColors = [
                                                                \App\Enums\FolderStatus::TARGET_APPROVED->value => 'bg-success-100 text-success-700 border-success-200 dark:bg-success-500/20 dark:text-success-400',
                                                                \App\Enums\FolderStatus::EVAL_APPROVED->value   => 'bg-success-100 text-success-700 border-success-200 dark:bg-success-500/20 dark:text-success-400',
                                                                \App\Enums\FolderStatus::TARGET_SUBMITTED->value=> 'bg-info-100 text-info-700 border-info-200 dark:bg-info-500/20 dark:text-info-400',
                                                                \App\Enums\FolderStatus::EVAL_SUBMITTED->value  => 'bg-info-100 text-info-700 border-info-200 dark:bg-info-500/20 dark:text-info-400',
                                                                \App\Enums\FolderStatus::TARGET_MISSED->value   => 'bg-danger-100 text-danger-700 border-danger-200 dark:bg-danger-500/20 dark:text-danger-400',
                                                                \App\Enums\FolderStatus::UNEVALUATED->value     => 'bg-danger-100 text-danger-700 border-danger-200 dark:bg-danger-500/20 dark:text-danger-400',
                                                                \App\Enums\FolderStatus::DRAFT_TARGET->value    => 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400',
                                                                \App\Enums\FolderStatus::DRAFT_EVALUATION->value=> 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400',
                                                            ];
                                                            $c = $badgeColors[$cStatus->value] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400';
                                                            $displayStatus = $cStatus->label();

                                                            if ($isTwgRole && in_array($cStatus, [\App\Enums\FolderStatus::TARGET_SUBMITTED, \App\Enums\FolderStatus::EVAL_SUBMITTED], true)) {
                                                                if (!empty($row['all_supervisors_approved'])) {
                                                                    $displayStatus = 'PENDING VERIFICATION';
                                                                    $c = 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30';
                                                                } else {
                                                                    $displayStatus = 'QUEUED FOR VERIFICATION';
                                                                    $c = 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/30';
                                                                }
                                                            }
                                                        ?>
                                                        <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border <?= $c ?> whitespace-nowrap">
                                                            <?= esc($displayStatus) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <?php if ($sysRole === 'Admin'): ?>
                                                <td class="hidden lg:table-cell px-6 py-4 min-w-0 overflow-hidden">
                                                    <span class="text-xs font-semibold text-text-muted truncate block max-w-full" title="<?= esc($row['department'] ?? 'N/A') ?>"><?= esc($row['department'] ?? 'N/A') ?></span>
                                                </td>
                                            <?php endif; ?>

                                            <td class="hidden lg:table-cell px-6 py-4 lg:min-w-[110px]">
                                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?= $c ?> whitespace-nowrap">
                                                    <?= esc($displayStatus) ?>
                                                </span>
                                            </td>

                                            <?php if ($pKey !== 'target'): ?>
                                                <td class="block lg:table-cell px-0 lg:px-6 py-3 lg:py-4 text-left lg:text-center mt-2 lg:mt-0 border-t border-surface-border lg:border-none lg:min-w-[60px]">
                                                    <div class="flex items-center lg:justify-center gap-3">
                                                        <span class="lg:hidden text-[10px] font-black text-text-muted uppercase tracking-widest">Score:</span>
                                                        
                                                        <div class="relative group/score flex items-center">
                                                            <input type="number" step="0.01" min="0" max="5" 
                                                                   class="score-input bg-transparent hover:bg-surface-hover border border-transparent hover:border-surface-border focus:bg-surface focus:border-accent rounded px-2 py-1 text-sm font-black text-text w-20 text-center transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none <?= is_null($row['final_rating']) ? 'italic text-text-muted' : '' ?>" 
                                                                   value="<?= !is_null($row['final_rating']) ? number_format($row['final_rating'], 2) : '' ?>"
                                                                   placeholder="--"
                                                                   data-folder-id="<?= $row['folder_id'] ?>"
                                                                   onblur="updateFolderScore(this, <?= $row['folder_id'] ?>)"
                                                                   onkeydown="if(event.key === 'Enter') this.blur();"
                                                            >
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endif; ?>

                                            <td class="block lg:table-cell px-0 lg:px-6 pt-1 pb-0 lg:py-4 text-right lg:min-w-[135px]">
                                                <?php 
                                                    $isDirect = empty($sysRole) || $sysRole !== 'Admin' || !empty($row['is_cascaded_by_me']);
                                                    $rowBucket = $row['queue_bucket'] ?? (
                                                        in_array($key, ['target_in_progress', 'eval_in_progress'], true) ? 'in_progress' : (
                                                        in_array($key, ['target_completed', 'eval_completed'], true) ? 'completed' : 'for_review'
                                                    ));
                                                    $isInProgressTab = ($rowBucket === 'in_progress');
                                                    $isCompletedTab  = ($rowBucket === 'completed');

                                                    if ($isInProgressTab) {
                                                        $actionLabel = 'View & Comment';
                                                        $btnClass = 'bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-text font-bold border border-surface-border';
                                                    } elseif ($isCompletedTab) {
                                                        $actionLabel = 'View Form';
                                                        $btnClass = 'bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-text font-bold border border-surface-border';
                                                    } else {
                                                        if (in_array($sysRole, ['TWG', 'PMT'], true)) {
                                                            $actionLabel = ($pKey === 'target') ? 'Verify Targets' : 'Verify Evaluation';
                                                            $btnClass = 'bg-accent hover:bg-accent-hover text-zinc-950 font-black';
                                                        } else {
                                                            $actionLabel = $isDirect 
                                                                ? (($pKey === 'target') ? 'Review Targets' : 'Open Evaluation') 
                                                                : (($pKey === 'target') ? 'Monitor Targets' : 'Monitor Review');
                                                            $btnClass = $isDirect
                                                                ? 'bg-accent hover:bg-accent-hover text-zinc-950 font-black'
                                                                : 'bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-text font-bold border border-surface-border';
                                                        }
                                                    }
                                                ?>
                                                <a href="<?= site_url('ratings/show/' . $row['folder_id']) ?>" 
                                                class="w-full lg:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 lg:py-2 <?= $btnClass ?> rounded-xl text-xs transition-all shadow-sm active:scale-95 cursor-pointer whitespace-nowrap">
                                                    <?php if ($isInProgressTab): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                        </svg>
                                                    <?php elseif (!$isDirect || $isCompletedTab): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    <?php endif; ?>
                                                    <span><?= esc($actionLabel) ?></span>
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="ratings-empty-filter block lg:table-row" style="display: none;">
                                        <td colspan="100%" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                            No employees found matching the selected filter.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    .page-hidden { display: none !important; }
</style>
<script>
    class RatingsPaginator {
        constructor(config) {
            this.pageSize = config.pageSize || 20;
            this.currentPage = 1;
            this.container = document.getElementById(config.containerId);
            if (!this.container) return;
            
            this.btnPrev = this.container.querySelector('.js-page-prev');
            this.btnNext = this.container.querySelector('.js-page-next');
            this.lblStart = this.container.querySelector('[id$="-page-start"]');
            this.lblEnd = this.container.querySelector('[id$="-page-end"]');
            this.lblTotal = this.container.querySelector('[id$="-page-total"]');
            
            if (this.btnPrev) this.btnPrev.addEventListener('click', () => this.goToPage(this.currentPage - 1));
            if (this.btnNext) this.btnNext.addEventListener('click', () => this.goToPage(this.currentPage + 1));
            
            this.items = [];
            this.allRows = []; 
        }
        
        init(allRows) {
            this.allRows = allRows;
        }

        updateItems(newItems) {
            this.items = newItems;
            this.currentPage = 1;
            this.render();
        }
        
        goToPage(page) {
            this.currentPage = page;
            this.render();
        }
        
        render() {
            if (!this.container) return;
            const total = this.items.length;
            const totalPages = Math.ceil(total / this.pageSize) || 1;
            
            if (this.currentPage > totalPages) this.currentPage = totalPages;
            if (this.currentPage < 1) this.currentPage = 1;
            
            const startIdx = (this.currentPage - 1) * this.pageSize;
            const endIdx = Math.min(startIdx + this.pageSize, total);
            
            this.allRows.forEach(el => el.classList.add('page-hidden'));
            
            for(let i = startIdx; i < endIdx; i++) {
                this.items[i].classList.remove('page-hidden');
            }
            
            if (this.lblStart) this.lblStart.textContent = total === 0 ? 0 : startIdx + 1;
            if (this.lblEnd) this.lblEnd.textContent = endIdx;
            if (this.lblTotal) this.lblTotal.textContent = total + (total === 1 ? ' entry' : ' entries');
            
            if (this.btnPrev) this.btnPrev.disabled = this.currentPage === 1;
            if (this.btnNext) this.btnNext.disabled = this.currentPage === totalPages;
            
            this.container.classList.toggle('hidden', totalPages <= 1);
        }
    }

    const ratingsUnitsPaginator = new RatingsPaginator({ containerId: 'filter-units-pagination', pageSize: 6 });
    const ratingsPosPaginator = new RatingsPaginator({ containerId: 'filter-pos-pagination', pageSize: 6 });

    // System Adjectival Logic
    function getAdjectivalRating(score) {
        if (score >= 4.50) return 'O';  
        if (score >= 3.50) return 'VS'; 
        if (score >= 2.50) return 'S';  
        if (score >= 1.50) return 'US'; 
        return 'P';                     
    }

    async function updateFolderScore(inputElem, folderId) {
        let score = inputElem.value;
        if (score !== '' && isNaN(parseFloat(score))) {
            return;
        }

        const formData = new FormData();
        formData.append('folder_id', folderId);
        formData.append('score', score);

        try {
            const response = await fetch('<?= site_url('folder/update_score') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                const nullBadges = document.querySelectorAll(`.adj-badge-null-${folderId}`);
                const adjBadges = document.querySelectorAll(`.adj-badge-${folderId}`);
                const allScoreInputs = document.querySelectorAll(`.score-input[data-folder-id="${folderId}"]`);

                if (score === '') {
                    allScoreInputs.forEach(inp => {
                        inp.value = '';
                        inp.classList.add('italic', 'text-text-muted');
                    });
                    nullBadges.forEach(b => b.classList.remove('hidden'));
                    adjBadges.forEach(b => b.classList.add('hidden'));
                } else {
                    allScoreInputs.forEach(inp => {
                        inp.classList.remove('italic', 'text-text-muted');
                        inp.value = parseFloat(score).toFixed(2);
                    });
                    
                    const adj = getAdjectivalRating(parseFloat(score));
                    const styles = {
                        'O':  ['bg-success-100', 'text-success-700', 'border-success-200', 'dark:bg-success-500/20', 'dark:text-success-400'],
                        'VS': ['bg-info-100', 'text-info-700', 'border-info-200', 'dark:bg-info-500/20', 'dark:text-info-400'],
                        'S':  ['bg-warning-100', 'text-warning-700', 'border-warning-200', 'dark:bg-warning-500/20', 'dark:text-warning-400'],
                        'US': ['bg-revision-100', 'text-revision-700', 'border-revision-200', 'dark:bg-revision-500/20', 'dark:text-revision-400'],
                        'P':  ['bg-danger-100', 'text-danger-700', 'border-danger-200', 'dark:bg-danger-500/20', 'dark:text-danger-400']
                    };

                    nullBadges.forEach(b => b.classList.add('hidden'));
                    adjBadges.forEach(b => {
                        b.classList.remove('hidden');
                        b.innerText = adj;
                        b.className = `adjective-badge adj-badge-${folderId} ${b.classList.contains('px-3') ? 'px-3 py-1' : 'px-2 py-0.5'} rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent whitespace-nowrap`;
                        b.classList.add(...styles[adj]);
                    });
                }
            } else {
                if (window.appAlert) window.appAlert(data.message || 'Error updating score');
            }
        } catch (e) {
            console.error(e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Automatically calculate and style adjectives on load
        document.querySelectorAll('.adjective-badge').forEach(badge => {
            const score = parseFloat(badge.getAttribute('data-score'));
            if (!isNaN(score)) {
                const adj = getAdjectivalRating(score);
                badge.innerText = adj;
                
                // Apply specific colors based on the adjective (CSC MC No. 6, s. 2012)
                const styles = {
                    'O':  ['bg-success-100', 'text-success-700', 'border-success-200', 'dark:bg-success-500/20', 'dark:text-success-400'],
                    'VS': ['bg-info-100', 'text-info-700', 'border-info-200', 'dark:bg-info-500/20', 'dark:text-info-400'],
                    'S':  ['bg-warning-100', 'text-warning-700', 'border-warning-200', 'dark:bg-warning-500/20', 'dark:text-warning-400'],
                    'US': ['bg-revision-100', 'text-revision-700', 'border-revision-200', 'dark:bg-revision-500/20', 'dark:text-revision-400'],
                    'P':  ['bg-danger-100', 'text-danger-700', 'border-danger-200', 'dark:bg-danger-500/20', 'dark:text-danger-400']
                };
                
                if (styles[adj]) badge.classList.add(...styles[adj]);
            }
        });
    });

    window.CURRENT_ADMIN_SCOPE = '<?= (!empty($adminCascadedTotalCount) || empty($overallTotalCount)) ? 'cascaded' : 'overall' ?>';
    window.ADMIN_PERIOD_COUNTS = {
        target: {
            cascaded: <?= (int)($targetCascadedCount ?? 0) ?>,
            overall:  <?= (int)($targetOverallCount ?? 0) ?>
        },
        evaluation: {
            cascaded: <?= (int)($evalCascadedCount ?? 0) ?>,
            overall:  <?= (int)($evalOverallCount ?? 0) ?>
        }
    };

    function switchAdminScope(scope) {
        window.CURRENT_ADMIN_SCOPE = scope;

        const btnCascaded = document.getElementById('admin-scope-btn-cascaded');
        const btnOverall  = document.getElementById('admin-scope-btn-overall');
        const countCascaded = document.getElementById('scope-count-cascaded');
        const countOverall  = document.getElementById('scope-count-overall');
        const bannerCascaded = document.getElementById('admin-scope-banner-cascaded');
        const bannerOverall  = document.getElementById('admin-scope-banner-overall');

        if (btnCascaded && btnOverall) {
            if (scope === 'cascaded') {
                btnCascaded.className = 'admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer bg-accent text-zinc-950 shadow-xs';
                if (countCascaded) countCascaded.className = 'px-1.5 py-0.5 rounded-full text-[10px] font-black bg-zinc-950/15 text-zinc-950';

                btnOverall.className = 'admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-text-muted hover:text-text';
                if (countOverall) countOverall.className = 'px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-border text-text-muted';

                if (bannerCascaded) bannerCascaded.classList.remove('hidden');
                if (bannerOverall) bannerOverall.classList.add('hidden');
            } else {
                btnOverall.className = 'admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer bg-accent text-zinc-950 shadow-xs';
                if (countOverall) countOverall.className = 'px-1.5 py-0.5 rounded-full text-[10px] font-black bg-zinc-950/15 text-zinc-950';

                btnCascaded.className = 'admin-scope-tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-text-muted hover:text-text';
                if (countCascaded) countCascaded.className = 'px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-border text-text-muted';

                if (bannerCascaded) bannerCascaded.classList.add('hidden');
                if (bannerOverall) bannerOverall.classList.remove('hidden');
            }
        }

        // Update tab badges
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const badge = btn.querySelector('.tab-badge');
            if (badge) {
                const count = (scope === 'overall') ? badge.getAttribute('data-count-overall') : badge.getAttribute('data-count-cascaded');
                if (count !== null && count !== undefined) {
                    badge.textContent = count;
                }
            }
        });

        if (typeof filterRatings === 'function') {
            filterRatings();
        }
    }
    window.switchAdminScope = switchAdminScope;

    // Tab Switching Logic 
    function switchPeriod(periodKey) {
        document.querySelectorAll('.period-subtabs').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex');
        });

        // Update active styling on period subtab buttons
        document.querySelectorAll('.ratings-period-tab-btn').forEach(btn => {
            btn.className = 'ratings-period-tab-btn px-4 py-2 text-xs rounded-lg transition-all cursor-pointer font-bold text-text-muted hover:text-text';
        });
        const activePeriodBtn = document.getElementById('period-subtab-btn-' + periodKey);
        if (activePeriodBtn) {
            activePeriodBtn.className = 'ratings-period-tab-btn px-4 py-2 text-xs rounded-lg transition-all cursor-pointer bg-accent text-zinc-950 shadow-xs font-black';
        }

        const periodInput = document.getElementById('period-input');
        if (periodInput) periodInput.value = periodKey;

        // Update scope counts for this period
        if (window.ADMIN_PERIOD_COUNTS && window.ADMIN_PERIOD_COUNTS[periodKey]) {
            const counts = window.ADMIN_PERIOD_COUNTS[periodKey];
            const cEl = document.getElementById('scope-count-cascaded');
            const oEl = document.getElementById('scope-count-overall');
            if (cEl) cEl.textContent = counts.cascaded;
            if (oEl) oEl.textContent = counts.overall;
        }

        const targetSubtabs = document.getElementById('period-subtabs-' + periodKey);
        if (targetSubtabs) {
            targetSubtabs.classList.remove('hidden');
            targetSubtabs.classList.add('flex');

            // Automatically click the first subtab in this period
            const firstTabBtn = targetSubtabs.querySelector('.tab-btn');
            if (firstTabBtn) {
                firstTabBtn.click();
            }
        }
    }

    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'flex-col', 'flex-1', 'min-w-0', 'min-h-0', 'h-full');
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active-tab', 'font-black');
            btn.classList.add('font-semibold', 'text-text-muted', 'border-transparent');
            
            const activeBorder = btn.getAttribute('data-active-border');
            if (activeBorder) {
                activeBorder.split(' ').forEach(cls => {
                    if (cls.trim()) btn.classList.remove(cls.trim());
                });
            }

            const iconSvg = btn.querySelector('.tab-icon svg');
            if (iconSvg) {
                const activeColor = iconSvg.getAttribute('data-active-color');
                const defaultColor = iconSvg.getAttribute('data-default-color');
                if (activeColor) {
                    activeColor.split(' ').forEach(cls => { if (cls.trim()) iconSvg.classList.remove(cls.trim()); });
                }
                if (defaultColor) {
                    defaultColor.split(' ').forEach(cls => { if (cls.trim()) iconSvg.classList.add(cls.trim()); });
                }
            }

            const badge = btn.querySelector('.tab-badge');
            if (badge) {
                const defaultBadgeClass = btn.getAttribute('data-default-badge');
                if (defaultBadgeClass) {
                    badge.className = 'tab-badge ml-0.5 ' + defaultBadgeClass;
                }
            }
        });

        const target = document.getElementById('tab-content-' + tabId);
        if (target) {
            target.classList.remove('hidden', 'spms-content-slide');
            void target.offsetWidth;
            target.classList.add('flex', 'flex-col', 'flex-1', 'min-w-0', 'min-h-0', 'h-full', 'spms-content-slide');
        }

        if (btnElement) {
            btnElement.classList.remove('font-semibold', 'text-text-muted', 'border-transparent');
            btnElement.classList.add('active-tab', 'font-black', 'border-b-2');
            
            const activeBorder = btnElement.getAttribute('data-active-border');
            if (activeBorder) {
                activeBorder.split(' ').forEach(cls => {
                    if (cls.trim()) btnElement.classList.add(cls.trim());
                });
            }

            const iconSvg = btnElement.querySelector('.tab-icon svg');
            if (iconSvg) {
                const defaultColor = iconSvg.getAttribute('data-default-color');
                const activeColor = iconSvg.getAttribute('data-active-color');
                if (defaultColor) {
                    defaultColor.split(' ').forEach(cls => { if (cls.trim()) iconSvg.classList.remove(cls.trim()); });
                }
                if (activeColor) {
                    activeColor.split(' ').forEach(cls => { if (cls.trim()) iconSvg.classList.add(cls.trim()); });
                }
            }

            const activeBadge = btnElement.querySelector('.tab-badge');
            const activeBadgeClass = btnElement.getAttribute('data-active-badge');
            if (activeBadge && activeBadgeClass) {
                activeBadge.className = 'tab-badge ml-0.5 ' + activeBadgeClass;
            }
        }

        if (typeof filterRatings === 'function') {
            filterRatings();
        }
    }

    // Dropdown Logic
    function toggleFolderDropdown() {
        const menu = document.getElementById('folder-dropdown-menu');
        const icon = document.getElementById('folder-dropdown-icon');
        if (menu && icon) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }

    // Close dropdown if clicking outside
    document.addEventListener('click', function(event) {
        const container = document.getElementById('folder-dropdown-container');
        const menu = document.getElementById('folder-dropdown-menu');
        if (container && menu && !container.contains(event.target) && !menu.classList.contains('hidden')) {
            toggleFolderDropdown();
        }
    });

    // Sidebar Mobile Toggle Logic
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('open-mobile-filters');
        const closeBtn = document.getElementById('close-mobile-filters');
        const sidebar = document.getElementById('ratings-sidebar');
        const overlay = document.getElementById('mobile-filter-overlay');

        function openFilters() {
            if (!sidebar || !overlay) return;
            overlay.classList.remove('hidden');
            // small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                sidebar.classList.remove('-translate-x-full');
            }, 10);
        }

        function closeFilters() {
            if (!sidebar || !overlay) return;
            overlay.classList.add('opacity-0');
            sidebar.classList.add('-translate-x-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }

        if (openBtn) openBtn.addEventListener('click', openFilters);
        if (closeBtn) closeBtn.addEventListener('click', closeFilters);
        if (overlay) overlay.addEventListener('click', closeFilters);
    });

    window.SPMS_RATINGS_UNITS = <?= json_encode(array_map(fn($u) => [
        'id'        => (int)($u['id'] ?? $u['unit_id'] ?? 0),
        'name'      => $u['name'] ?? $u['unit_name'] ?? '',
        'parent_id' => !empty($u['parent_id']) ? (int)$u['parent_id'] : 0
    ], $allUnits ?? [])) ?>;
    window.SPMS_USER_ROLE = '<?= esc($sysRole) ?>';
    window.SPMS_MY_UNIT_IDS = <?= json_encode(array_map('intval', $myUnitIds ?? [])) ?>;
    window.SPMS_ASSIGNED_UNIT_IDS = <?= json_encode(array_values(array_map('intval', array_column($assignedUnits ?? [], 'unit_id') ?: array_column($assignedUnits ?? [], 'id') ?: []))) ?>;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Unit hierarchy: filtering by a parent unit should match all descendants
    const unitParents = {};
    const unitChildren = {};
    (window.SPMS_RATINGS_UNITS || []).forEach(u => {
        unitParents[u.id] = u.parent_id ? String(u.parent_id) : null;
        if (u.parent_id) {
            const pid = String(u.parent_id);
            if (!unitChildren[pid]) unitChildren[pid] = [];
            unitChildren[pid].push(String(u.id));
        }
    });

    function getDescendantUnitIds(rootId) {
        const rootStr = String(rootId);
        const result = new Set([rootStr]);
        const stack = [rootStr];
        while (stack.length) {
            const current = stack.pop();
            (unitChildren[current] || []).forEach(childId => {
                if (!result.has(childId)) {
                    result.add(childId);
                    stack.push(childId);
                }
            });
        }
        return result;
    }

    // Dynamic Cascading Unit Filters (N-Tier Drill-Down)
    const LEVEL_THEMES = [
        {
            labelColor: 'text-text-muted',
            focusRing: 'focus:border-accent focus:ring-1 focus:ring-accent/20',
            activeBorder: 'border-zinc-400 dark:border-zinc-600',
            connector: 'border-zinc-300 dark:border-zinc-700'
        }
    ];

    function getLevelTheme(level) {
        return LEVEL_THEMES[0];
    }

    function getLevelTitle(level, parentUnit) {
        if (level === 1) {
            return 'DEPARTMENT / UNIT';
        }
        return parentUnit ? escapeHtml(parentUnit.name) : 'SUB-UNIT';
    }

    function getDefaultOptionLabel(level, parentUnit) {
        if (level === 1) {
            return 'All Departments &amp; Units';
        }
        return parentUnit ? `All in ${escapeHtml(parentUnit.name)}` : 'All Departments &amp; Units';
    }

    function renderDynamicUnitLevel(level, parentUnitId, targetContainer) {
        if (!targetContainer) return;
        const wasCollapsed = level === 1 && Boolean(targetContainer.querySelector(':scope > .dynamic-unit-level > .js-unit-level-body.hidden'));
        targetContainer.innerHTML = '';

        const units = window.SPMS_RATINGS_UNITS || [];
        const unitsById = {};
        units.forEach(u => unitsById[u.id] = u);

        let availableUnits = [];
        let parentUnit = null;

        if (level === 1) {
            if (window.SPMS_USER_ROLE === 'TWG') {
                const assignedIds = window.SPMS_ASSIGNED_UNIT_IDS || [];
                availableUnits = units.filter(u => assignedIds.includes(u.id));
                if (availableUnits.length === 0) {
                    availableUnits = units.filter(u => !u.parent_id || !unitsById[u.parent_id]);
                }
            } else if (window.SPMS_USER_ROLE === 'Admin' || window.SPMS_USER_ROLE === 'PMT') {
                availableUnits = units.filter(u => !u.parent_id || !unitsById[u.parent_id]);
            } else {
                const myUnits = window.SPMS_MY_UNIT_IDS || [];
                availableUnits = units.filter(u => myUnits.includes(u.parent_id));
            }
        } else {
            const numParentId = parseInt(parentUnitId, 10);
            parentUnit = unitsById[numParentId] || null;
            availableUnits = units.filter(u => u.parent_id === numParentId);
        }

        if (availableUnits.length === 0) {
            targetContainer.classList.add('hidden');
            return;
        }

        targetContainer.classList.remove('hidden');

        const theme = getLevelTheme(level);
        const title = getLevelTitle(level, parentUnit);
        const defaultLabel = getDefaultOptionLabel(level, parentUnit);

        let optionsHtml = `<option value="">${defaultLabel}</option>`;
        availableUnits.forEach(u => {
            optionsHtml += `<option value="${u.id}">${escapeHtml(u.name)}</option>`;
        });

        const levelWrapper = document.createElement('div');
        levelWrapper.className = 'dynamic-unit-level';
        levelWrapper.dataset.level = level;

        const headerSpacing = level === 1 ? 'mb-3 tracking-widest' : 'mb-1.5 tracking-wider';
        const headerHtml = `
            <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase ${headerSpacing} ${theme.labelColor} hover:text-text focus:outline-none group cursor-pointer" onclick="
                this.nextElementSibling.classList.toggle('hidden');
                this.querySelector('svg').classList.toggle('-rotate-180');
            ">
                <span class="truncate pr-2 text-left" title="${title}">${title}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 ${wasCollapsed ? '-rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>`;

        levelWrapper.innerHTML = `
            ${headerHtml}
            <div class="js-unit-level-body ${wasCollapsed ? 'hidden' : ''}">
                <div class="relative w-full">
                    <select id="ratings-filter-unit-level-${level}"
                        class="w-full appearance-none bg-white dark:bg-zinc-900 border border-surface-border ${theme.focusRing} rounded-xl pl-3 pr-8 py-2 text-xs font-semibold text-text outline-none cursor-pointer transition-all shadow-xs [color-scheme:light] dark:[color-scheme:dark]">
                        ${optionsHtml}
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 flex items-center text-text-muted" style="right: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <div id="ratings-dynamic-unit-children-${level}" class="relative pl-3.5 ml-2.5 border-l-2 border-dashed ${theme.connector} space-y-3 pt-2 hidden"></div>
            </div>
        `;

        targetContainer.appendChild(levelWrapper);

        const selectEl = levelWrapper.querySelector(`#ratings-filter-unit-level-${level}`);
        selectEl.addEventListener('change', (e) => {
            onDynamicUnitLevelChange(level, e.target.value);
        });
    }

    function onDynamicUnitLevelChange(level, selectedUnitId) {
        const selectEl = document.getElementById(`ratings-filter-unit-level-${level}`);
        const childrenContainer = document.getElementById(`ratings-dynamic-unit-children-${level}`);
        const theme = getLevelTheme(level);

        if (selectEl) {
            const activeClasses = theme.activeBorder.split(' ');
            if (selectedUnitId) {
                selectEl.classList.add(...activeClasses);
            } else {
                selectEl.classList.remove(...activeClasses);
            }
        }

        if (childrenContainer) {
            childrenContainer.innerHTML = '';
            childrenContainer.classList.add('hidden');

            if (selectedUnitId) {
                const numId = parseInt(selectedUnitId, 10);
                const units = window.SPMS_RATINGS_UNITS || [];
                const childUnits = units.filter(u => u.parent_id === numId);

                if (childUnits.length > 0) {
                    renderDynamicUnitLevel(level + 1, selectedUnitId, childrenContainer);
                }
            }
        }

        if (typeof filterSidebarPositions === 'function') {
            filterSidebarPositions();
        }
        if (typeof filterRatings === 'function') {
            filterRatings();
        }
    }
    window.onDynamicUnitLevelChange = onDynamicUnitLevelChange;

    function initDynamicUnitFilters() {
        const rootContainer = document.getElementById('dynamic-unit-filter-container');
        if (!rootContainer) return;
        renderDynamicUnitLevel(1, null, rootContainer);
    }

    function getActiveDynamicUnitId() {
        const container = document.getElementById('dynamic-unit-filter-container');
        if (!container) return null;
        const selects = Array.from(container.querySelectorAll('select'));
        for (let i = selects.length - 1; i >= 0; i--) {
            const val = selects[i].value;
            if (val) return val;
        }
        return null;
    }

    function getActiveDeptPositions() {
        const activeUnitId = getActiveDynamicUnitId();
        if (!activeUnitId) {
            return null; // All positions are allowed when no dept is filtered
        }
        const allowedUnitIds = getDescendantUnitIds(String(activeUnitId));
        const allCards = document.querySelectorAll('.rating-card');
        const allowedPositions = new Set();

        allCards.forEach(card => {
            const cardUnit = card.getAttribute('data-unit') || '';
            const cardDept = card.getAttribute('data-department') || '';
            
            let belongsToUnit = false;
            if (cardUnit) {
                belongsToUnit = cardUnit.split(',').some(uid => allowedUnitIds.has(uid.trim()));
            } else if (cardDept) {
                const units = window.SPMS_RATINGS_UNITS || [];
                const matchingUnits = units.filter(u => allowedUnitIds.has(String(u.id)));
                belongsToUnit = matchingUnits.some(u => cardDept.toLowerCase().includes(u.name.toLowerCase()));
            }

            if (belongsToUnit) {
                const cardPos = card.getAttribute('data-pos');
                if (cardPos) {
                    cardPos.split(',').forEach(p => {
                        const trimmed = p.trim().toLowerCase();
                        if (trimmed) allowedPositions.add(trimmed);
                    });
                }
            }
        });

        return allowedPositions;
    }

    let filterSidebarPositions = function() {};

    function clearAllRatingsFilters() {
        const searchInput = document.getElementById('search-rating');
        if (searchInput) searchInput.value = '';
        document.querySelectorAll('.ratings-filter-checkbox').forEach(cb => cb.checked = false);

        initDynamicUnitFilters();

        const msPos = document.getElementById('mini-search-positions');
        if (msPos) { msPos.value = ''; }

        if (typeof filterSidebarPositions === 'function') {
            filterSidebarPositions();
        }
        if (typeof filterRatings === 'function') {
            filterRatings();
        }
    }
    window.clearAllRatingsFilters = clearAllRatingsFilters;

    function getCheckedValues(name) {
        const checked = document.querySelectorAll(`input[name="${name}"]:checked`);
        return Array.from(checked).map(cb => cb.value.toLowerCase());
    }

    function filterRatings() {
        const searchInput = document.getElementById('search-rating');
        const searchTerm   = searchInput ? searchInput.value.toLowerCase() : '';
        const checkedTypes = getCheckedValues('filter_type[]');
        const checkedPos   = getCheckedValues('filter_pos[]');

        const activeUnitId = getActiveDynamicUnitId();
        let allowedUnitIds = null;
        if (activeUnitId) {
            allowedUnitIds = getDescendantUnitIds(String(activeUnitId));
        }

        // Active filters count badge & Reset button
        let activeCount = 0;
        if (searchTerm) activeCount++;
        if (checkedTypes.length > 0) activeCount += checkedTypes.length;
        if (activeUnitId) activeCount++;
        if (checkedPos.length > 0) activeCount += checkedPos.length;

        const countBadge = document.getElementById('active-ratings-filter-badge');
        const resetBtn = document.getElementById('reset-ratings-filters-btn');
        if (countBadge) {
            countBadge.textContent = activeCount;
            countBadge.classList.toggle('hidden', activeCount === 0);
        }
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', activeCount === 0);
        }
        
        document.querySelectorAll('.tab-content').forEach(tabContent => {
            const cards = tabContent.querySelectorAll('.rating-card');
            if (cards.length === 0) return;
            let visibleCards = 0;

            cards.forEach(card => {
                const name       = (card.getAttribute('data-name') || '').toLowerCase();
                const unitData   = card.getAttribute('data-unit') || '';
                const deptName   = (card.getAttribute('data-department') || '').toLowerCase();
                const pos        = (card.getAttribute('data-pos') || '').toLowerCase();
                const type       = card.getAttribute('data-teaching') || '0';
                const isCascaded = (card.getAttribute('data-cascaded') === '1');

                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesPos    = checkedPos.length === 0 || checkedPos.some(p => pos.includes(p));
                const matchesType   = checkedTypes.length === 0 || checkedTypes.includes(type);

                const isAdmin = <?= ($sysRole === 'Admin') ? 'true' : 'false' ?>;
                const adminScope = window.CURRENT_ADMIN_SCOPE || 'cascaded';
                const matchesScope = !isAdmin || (adminScope === 'overall') || isCascaded;

                let matchesUnit = true;
                if (allowedUnitIds) {
                    if (unitData) {
                        matchesUnit = unitData.split(',').some(uid => allowedUnitIds.has(uid.trim()));
                    } else if (deptName) {
                        const units = window.SPMS_RATINGS_UNITS || [];
                        const matchingUnits = units.filter(u => allowedUnitIds.has(String(u.id)));
                        matchesUnit = matchingUnits.some(u => deptName.includes(u.name.toLowerCase()));
                    } else {
                        matchesUnit = false;
                    }
                }
                
                if (matchesSearch && matchesUnit && matchesPos && matchesType && matchesScope) {
                    card.style.display = ''; 
                    visibleCards++;
                } else {
                    card.style.display = 'none'; 
                }
            });

            const emptyRow = tabContent.querySelector('.ratings-empty-filter');
            if (emptyRow) {
                emptyRow.style.display = (visibleCards > 0) ? 'none' : '';
            }
        });
    }
    window.filterRatings = filterRatings;

    // REAL-TIME SEARCH AND FILTER LOGIC
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-rating');
        const checkboxes  = document.querySelectorAll('.ratings-filter-checkbox');
        const clearBtn    = document.getElementById('clear-ratings-filters');

        if (searchInput) searchInput.addEventListener('input', filterRatings);
        checkboxes.forEach(cb => {
            cb.addEventListener('change', filterRatings);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', clearAllRatingsFilters);
        }

        // Initialize Dynamic Unit Filters
        initDynamicUnitFilters();

        // Setup Positions filter with mini-search & paginator
        const cbPosList = document.getElementById('positions-checkbox-list');
        if (cbPosList) {
            const allRows = Array.from(cbPosList.querySelectorAll('.position-filter-label'));
            ratingsPosPaginator.init(allRows);
            
            filterSidebarPositions = () => {
                const input = document.getElementById('mini-search-positions');
                const query = input ? input.value.trim().toLowerCase() : '';
                const allowedDeptPositions = getActiveDeptPositions();

                const matchedRows = allRows.filter(row => {
                    const title = (row.dataset.title || '').trim().toLowerCase();
                    const matchesQuery = !query || title.includes(query);
                    const matchesDept = allowedDeptPositions === null || allowedDeptPositions.has(title);

                    if (!matchesDept) {
                        const cb = row.querySelector('input[type="checkbox"]');
                        if (cb && cb.checked) {
                            cb.checked = false;
                        }
                    }

                    return matchesQuery && matchesDept;
                });

                ratingsPosPaginator.updateItems(matchedRows);
            };

            const msPos = document.getElementById('mini-search-positions');
            if (msPos) msPos.addEventListener('input', filterSidebarPositions);
            filterSidebarPositions();
        }

        // Initialize admin scope UI if Admin
        <?php if ($sysRole === 'Admin'): ?>
            switchAdminScope(window.CURRENT_ADMIN_SCOPE);
        <?php else: ?>
            filterRatings();
        <?php endif; ?>
    });
</script>

<?php endif; ?>
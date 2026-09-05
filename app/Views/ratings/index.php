<?php 
    $sysRole = $sysRole ?? session()->get('role');
    $firstPeriodKey = array_key_first($periods);

    // Attempt to get a representative folder to determine the phase
    $representativeFolder = $activeFolder;
    if (!$representativeFolder) {
        foreach ($periods as $period) {
            foreach ($period['tabs'] as $tab) {
                if (!empty($tab['folders'])) {
                    $representativeFolder = $tab['folders'][0];
                    break 2;
                }
            }
        }
    }

    if ($representativeFolder) {
        $folderModel = new \App\Models\DocumentFolderModel();
        $dates = $folderModel->getFolderDates($representativeFolder);
        if ($dates && isset($dates['target_date_end'])) {
            $now = date('Y-m-d H:i:s');
            if ($now <= $dates['target_date_end']) {
                $firstPeriodKey = 'target';
            } else {
                $firstPeriodKey = 'evaluation';
            }
        }
    }

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
            <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filters</h2>
            <button type="button" id="close-mobile-filters" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4">
            <!-- Search -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <input type="text" id="search-rating" placeholder="Search employee name..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>

            <!-- Role / Type Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Role / Type</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="flex flex-col gap-1">
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

            <!-- Units Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Departments / Units</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="flex flex-col">
                    <div class="relative mb-3 shrink-0">
                        <input type="text" id="mini-search-units" placeholder="Find unit..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <div id="units-checkbox-list" class="space-y-0.5 mt-0.5 pr-1">
                        <?php foreach ($filterUnits as $unit): ?>
                            <label class="unit-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors group" data-name="<?= strtolower(esc($unit)) ?>">
                                <input type="checkbox" name="filter_unit[]" value="<?= esc($unit) ?>" class="checkbox ratings-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                <span class="truncate"><?= esc($unit) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="pt-2 flex justify-between items-center bg-transparent shrink-0 hidden" id="filter-units-pagination">
                        <div class="text-[9px] font-bold text-text-muted uppercase tracking-widest">
                            <span id="r-units-page-start">0</span>-<span id="r-units-page-end">0</span> of <span id="r-units-page-total">0</span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" class="js-page-prev p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></button>
                            <button type="button" class="js-page-next p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Positions Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
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
    <div class="flex-1 flex flex-col min-w-0 min-h-0 bg-transparent lg:bg-surface relative lg:static h-full lg:rounded-2xl lg:border border-surface-border shadow-sm overflow-hidden">

        <div class="lg:hidden flex items-center justify-between mb-4 shrink-0 px-4 pt-4">
            <h1 class="text-xl font-black text-text truncate">Ratings</h1>
            <button type="button" id="open-mobile-filters" class="flex items-center gap-2 text-xs font-bold text-accent bg-accent/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filters
            </button>
        </div>

        <div class="relative mb-6 pr-4 pt-6 px-6 lg:pt-8 lg:px-8 shrink-0 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <button onclick="toggleAppSidebar()" class="flex items-center text-left group cursor-pointer lg:cursor-default">
                    <h1 class="text-3xl font-black tracking-tight text-text truncate group-hover:text-accent lg:group-hover:text-text transition-colors">
                        <?= esc($activeFolder['title']) ?>
                    </h1>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0 text-text-muted transition-colors group-hover:text-accent lg:hidden ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <p class="text-xs font-bold text-text-muted mt-2 uppercase tracking-widest">
                    Manage your assigned reviews
                </p>
            </div>
            <div class="shrink-0">
                <div class="relative w-full lg:w-64 js-custom-select" id="period-custom-select">
                    <input type="hidden" id="period-input" value="<?= $firstPeriodKey ?>">
                    <button type="button" class="js-select-button w-full bg-surface dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold flex justify-between items-center transition-colors hover:border-accent/50 shadow-sm">
                        <span class="js-select-label"><?= esc($periods[$firstPeriodKey]['label']) ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <ul class="js-select-dropdown absolute right-0 z-50 w-full mt-2 bg-surface dark:bg-zinc-900 border border-surface-border rounded-xl shadow-lg shadow-black/5 dark:shadow-black/20 overflow-hidden hidden transform origin-top transition-all duration-200 scale-95 opacity-0">
                        <?php foreach($periods as $pKey => $period): ?>
                            <?php $isSelected = ($pKey === $firstPeriodKey); ?>
                            <li class="px-4 py-3 text-sm font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors js-select-option <?= $isSelected ? 'bg-accent/10 text-accent' : '' ?>" data-value="<?= $pKey ?>" data-label="<?= esc($period['label']) ?>">
                                <?= esc($period['label']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- QUEUE TABS -->
        <div class="flex px-6 lg:px-8 border-b border-surface-border shrink-0 overflow-x-auto custom-scrollbar">
            <?php foreach ($periods as $pKey => $period): ?>
                <div id="period-subtabs-<?= $pKey ?>" class="gap-6 period-subtabs <?= ($pKey === $firstPeriodKey) ? 'flex' : 'hidden' ?>">
                    <?php foreach ($period['tabs'] as $key => $group): ?>
                        <button id="tab-btn-<?= $key ?>" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all whitespace-nowrap <?= ($pKey === $firstPeriodKey && $key === $firstTabKey) ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> cursor-pointer"
                                onclick="switchTab('<?= $key ?>', this)">
                            <?= esc($group['label']) ?>
                            <span class="ml-1.5 px-2 py-0.5 rounded-full <?= ($pKey === $firstPeriodKey && $key === $firstTabKey) ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors">
                                <?= count($group['folders']) ?>
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
                        $colWidths = ['26%'];
                        if ($sysRole === 'Admin') { $colWidths[] = '15%'; }
                        
                        if ($pKey === 'target') {
                            $colWidths = array_merge($colWidths, ['37%', '18%']); // Only Folder Status and Action
                        } else {
                            $colWidths = array_merge($colWidths, ['15%', '10%', '12%', '18%']);
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
                                        <th class="px-6 py-4">Department</th>
                                    <?php endif; ?>
                                    <th class="px-6 py-4 min-w-[110px]">Folder Status</th>
                                    <?php if ($pKey !== 'target'): ?>
                                        <th class="px-6 py-4 min-w-[60px] text-center">Score</th>
                                        <th class="px-6 py-4 min-w-[70px] text-center">Adjectival Rating</th>
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
                                            data-unit="<?= esc($row['department'] ?? '') ?>" 
                                            data-pos="<?= esc($row['position'] ?? '') ?>"
                                            data-teaching="<?= esc($row['is_teaching'] ?? 0) ?>"> 
                                            
                                            <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                                <div class="flex justify-between items-start lg:items-center">
                                                    <div class="flex flex-col min-w-0 pr-4">
                                                        <span class="text-sm font-bold text-text truncate">
                                                            <?= esc($row['username']) ?>
                                                        </span>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest <?= ($row['is_teaching'] == 1) ? 'bg-highlight-100 text-highlight-700 dark:bg-highlight-500/20 dark:text-highlight-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' ?>">
                                                                <?= ($row['is_teaching'] == 1) ? 'Teaching' : 'Non-Teaching' ?>
                                                            </span>
                                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                                                <?= esc($row['position'] ?? 'No Position') ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="lg:hidden shrink-0 mt-0.5">
                                                        <?php 
                                                            $s = strtolower($row['folder_status']);
                                                            $badgeColors = [
                                                                'approved'    => 'bg-success-100 text-success-700 border-success-200 dark:bg-success-500/20 dark:text-success-400',
                                                                'submitted'   => 'bg-info-100 text-info-700 border-info-200 dark:bg-info-500/20 dark:text-info-400',
                                                                'reevaluate'  => 'bg-revision-100 text-revision-700 border-revision-200 dark:bg-revision-500/20 dark:text-revision-400',
                                                                'to evaluate' => 'bg-warning-100 text-warning-700 border-warning-200 dark:bg-warning-500/20 dark:text-warning-400',
                                                                'draft'       => 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400'
                                                            ];
                                                            $c = $badgeColors[$s] ?? $badgeColors['draft'];
                                                            $displayStatus = str_replace('_', ' ', $row['folder_status']);
                                                        ?>
                                                        <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border <?= $c ?> whitespace-nowrap">
                                                            <?= esc($displayStatus) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <?php if ($sysRole === 'Admin'): ?>
                                                <td class="hidden lg:table-cell px-6 py-4">
                                                    <span class="text-xs font-semibold text-text-muted"><?= esc($row['department'] ?? 'N/A') ?></span>
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

                                                        <div class="lg:hidden flex items-center gap-3 border-l border-surface-border pl-3">
                                                            <span class="adjective-badge adj-badge-<?= $row['folder_id'] ?> px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent <?= is_null($row['final_rating']) ? 'hidden' : '' ?>" data-score="<?= $row['final_rating'] ?? 0 ?>"></span>
                                                            <span class="adj-badge-null-<?= $row['folder_id'] ?> text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic <?= !is_null($row['final_rating']) ? 'hidden' : '' ?>">N/A</span>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endif; ?>

                                            <?php if ($pKey !== 'target'): ?>
                                                <td class="hidden lg:table-cell px-6 py-4 text-center lg:min-w-[70px]">
                                                    <span class="adjective-badge adj-badge-<?= $row['folder_id'] ?> px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent whitespace-nowrap <?= is_null($row['final_rating']) ? 'hidden' : '' ?>" data-score="<?= $row['final_rating'] ?? 0 ?>"></span>
                                                    <span class="adj-badge-null-<?= $row['folder_id'] ?> text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic whitespace-nowrap <?= !is_null($row['final_rating']) ? 'hidden' : '' ?>">Not Rated</span>
                                                </td>
                                            <?php endif; ?>

                                            <td class="block lg:table-cell px-0 lg:px-6 pt-1 pb-0 lg:py-4 text-right lg:min-w-[135px]">
                                                <a href="<?= site_url('ratings/show/' . $row['folder_id']) ?>" 
                                                class="w-full lg:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 lg:py-2 bg-accent hover:bg-accent-hover text-white rounded-xl font-bold text-xs transition-all shadow-md active:scale-95 cursor-pointer whitespace-nowrap">
                                                    Open Evaluation
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
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
            
            this.container.classList.toggle('hidden', total === 0);
        }
    }

    const ratingsUnitsPaginator = new RatingsPaginator({ containerId: 'filter-units-pagination', pageSize: 6 });
    const ratingsPosPaginator = new RatingsPaginator({ containerId: 'filter-pos-pagination', pageSize: 6 });

    // System Adjectival Logic
    function getAdjectivalRating(score) {
        if (score >= 4.30) return 'O';  
        if (score >= 3.54) return 'VS'; 
        if (score >= 2.70) return 'S';  
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

                if (score === '') {
                    inputElem.classList.add('italic', 'text-text-muted');
                    nullBadges.forEach(b => b.classList.remove('hidden'));
                    adjBadges.forEach(b => b.classList.add('hidden'));
                } else {
                    inputElem.classList.remove('italic', 'text-text-muted');
                    inputElem.value = parseFloat(score).toFixed(3);
                    
                    const adj = getAdjectivalRating(parseFloat(score));
                    const styles = {
                        'O':  ['bg-info-100', 'text-info-700', 'border-info-200', 'dark:bg-info-500/20', 'dark:text-info-400'],
                        'VS': ['bg-success-100', 'text-success-700', 'border-success-200', 'dark:bg-success-500/20', 'dark:text-success-400'],
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
                
                // Apply specific colors based on the adjective
                const styles = {
                    'O':  ['bg-info-100', 'text-info-700', 'border-info-200', 'dark:bg-info-500/20', 'dark:text-info-400'],
                    'VS': ['bg-success-100', 'text-success-700', 'border-success-200', 'dark:bg-success-500/20', 'dark:text-success-400'],
                    'S':  ['bg-warning-100', 'text-warning-700', 'border-warning-200', 'dark:bg-warning-500/20', 'dark:text-warning-400'],
                    'US': ['bg-revision-100', 'text-revision-700', 'border-revision-200', 'dark:bg-revision-500/20', 'dark:text-revision-400'],
                    'P':  ['bg-danger-100', 'text-danger-700', 'border-danger-200', 'dark:bg-danger-500/20', 'dark:text-danger-400']
                };
                
                if (styles[adj]) badge.classList.add(...styles[adj]);
            }
        });
    });

    // Tab Switching Logic 
    function switchPeriod(periodKey) {
        document.querySelectorAll('.period-subtabs').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex');
        });

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
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted');
            const badge = btn.querySelector('.tab-badge');
            if(badge) {
                badge.classList.remove('bg-accent/10', 'text-accent');
                badge.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
            }
        });

        const target = document.getElementById('tab-content-' + tabId);
        if (target) {
            target.classList.remove('hidden');
            target.classList.add('flex', 'flex-col', 'flex-1', 'min-w-0', 'min-h-0', 'h-full');
        }

        if (btnElement) {
            btnElement.classList.remove('border-transparent', 'text-text-muted');
            btnElement.classList.add('border-accent', 'text-accent');
            const activeBadge = btnElement.querySelector('.tab-badge');
            if(activeBadge) {
                activeBadge.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                activeBadge.classList.add('bg-accent/10', 'text-accent');
            }
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

    // REAL-TIME SEARCH AND FILTER LOGIC
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-rating');
        const checkboxes  = document.querySelectorAll('.ratings-filter-checkbox');
        const clearBtn    = document.getElementById('clear-ratings-filters');
        
        function getCheckedValues(name) {
            const checked = document.querySelectorAll(`input[name="${name}"]:checked`);
            return Array.from(checked).map(cb => cb.value.toLowerCase());
        }
        
        function filterRatings() {
            const searchTerm   = searchInput ? searchInput.value.toLowerCase() : '';
            const checkedTypes = getCheckedValues('filter_type[]');
            const checkedUnits = getCheckedValues('filter_unit[]');
            const checkedPos   = getCheckedValues('filter_pos[]');
            
            document.querySelectorAll('.rating-card').forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const unit = (card.getAttribute('data-unit') || '').toLowerCase();
                const pos  = (card.getAttribute('data-pos') || '').toLowerCase();
                const type = card.getAttribute('data-teaching') || '0';
                
                const matchesSearch = name.includes(searchTerm);
                const matchesUnit   = checkedUnits.length === 0 || checkedUnits.includes(unit);
                const matchesPos    = checkedPos.length === 0 || checkedPos.includes(pos);
                const matchesType   = checkedTypes.length === 0 || checkedTypes.includes(type);
                
                if (matchesSearch && matchesUnit && matchesPos && matchesType) {
                    card.style.display = ''; 
                } else {
                    card.style.display = 'none'; 
                }
            });
        }
        
        if (searchInput) searchInput.addEventListener('input', filterRatings);
        checkboxes.forEach(cb => {
            cb.addEventListener('change', filterRatings);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                checkboxes.forEach(cb => cb.checked = false);
                filterRatings();
                
                // Also reset sidebar mini-searches
                const msUnits = document.getElementById('mini-search-units');
                const msPos = document.getElementById('mini-search-positions');
                if (msUnits) { msUnits.value = ''; msUnits.dispatchEvent(new Event('input')); }
                if (msPos) { msPos.value = ''; msPos.dispatchEvent(new Event('input')); }
            });
        }

        // Initialize Pagination and Mini-search for sidebar filters
        function filterSidebarUnits() {
            const input = document.getElementById('mini-search-units');
            const query = input ? input.value.trim().toLowerCase() : '';
            const list = document.getElementById('units-checkbox-list');
            if (!list) return;
            const allRows = Array.from(list.querySelectorAll('.unit-filter-label'));
            
            if (ratingsUnitsPaginator.allRows.length === 0) ratingsUnitsPaginator.init(allRows);
            
            const matchedRows = allRows.filter(row => row.dataset.name && row.dataset.name.includes(query));
            ratingsUnitsPaginator.updateItems(matchedRows);
        }

        const msUnits = document.getElementById('mini-search-units');
        if (msUnits) msUnits.addEventListener('input', filterSidebarUnits);
        
        const cbUnitsList = document.getElementById('units-checkbox-list');
        if (cbUnitsList) {
            ratingsUnitsPaginator.init(Array.from(cbUnitsList.querySelectorAll('.unit-filter-label')));
            filterSidebarUnits();
        }

        function filterSidebarPositions() {
            const input = document.getElementById('mini-search-positions');
            const query = input ? input.value.trim().toLowerCase() : '';
            const list = document.getElementById('positions-checkbox-list');
            if (!list) return;
            const allRows = Array.from(list.querySelectorAll('.position-filter-label'));
            
            if (ratingsPosPaginator.allRows.length === 0) ratingsPosPaginator.init(allRows);
            
            const matchedRows = allRows.filter(row => row.dataset.title && row.dataset.title.includes(query));
            ratingsPosPaginator.updateItems(matchedRows);
        }

        const msPos = document.getElementById('mini-search-positions');
        if (msPos) msPos.addEventListener('input', filterSidebarPositions);
        
        const cbPosList = document.getElementById('positions-checkbox-list');
        if (cbPosList) {
            ratingsPosPaginator.init(Array.from(cbPosList.querySelectorAll('.position-filter-label')));
            filterSidebarPositions();
        }

        // Period Custom Dropdown Logic
        const periodSelect = document.getElementById('period-custom-select');
        if (periodSelect) {
            periodSelect.querySelectorAll('.js-select-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    switchPeriod(opt.getAttribute('data-value'));
                });
            });
        }

    });
</script>

<?php endif; ?>
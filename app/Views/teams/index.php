<style>
@media (min-width: 1024px) {
    #teams-filter-sidebar { z-index: 0 !important; }
}
</style>

<?php if (!$activeTeam): ?>
    <button onclick="toggleAppSidebar()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-accent rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-accent/5 transition-all group cursor-pointer lg:cursor-default min-h-[400px]">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 group-hover:bg-accent/10 text-zinc-400 group-hover:text-accent dark:text-zinc-500 mb-4 shadow-sm transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text group-hover:text-accent transition-colors mb-1">Select or Create a Team</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose a distribution list from the sidebar to edit its members, or create a new one.</p>
    </button>
<?php else: ?>

    <form action="<?= site_url('teams/store') ?>" method="POST" class="flex-1 flex flex-col lg:absolute lg:inset-0 lg:pb-6">
        <?= csrf_field() ?>
        <input type="hidden" name="team_id" value="<?= $activeTeam['id'] ?>">

        <!-- Team Info Inputs -->
        <div class="mb-4 shrink-0 flex flex-col sm:flex-row gap-3 lg:gap-4 bg-surface lg:bg-transparent p-4 lg:p-0 rounded-2xl lg:rounded-none border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <div class="w-full sm:w-[35%] flex flex-col justify-end">
                <label class="block text-[10px] font-black uppercase tracking-widest text-accent mb-1 ml-1 lg:ml-0">Team Title</label>
                <div class="flex items-center gap-3">
                    <div class="flex-1 flex items-center gap-2 border-b border-transparent focus-within:border-accent/30 transition-colors pb-1">
                        <input type="text" name="name" value="<?= esc($activeTeam['name']) ?>" placeholder="Team Name" class="w-full bg-transparent text-xl lg:text-2xl font-black text-text placeholder-zinc-400 dark:placeholder-zinc-600 focus:outline-none focus:ring-0 px-1">
                        
                        <button type="button" onclick="toggleAppSidebar()" class="lg:hidden shrink-0 p-1 text-text-muted hover:text-accent transition-colors cursor-pointer rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Delete button in a separate div/space -->
                    <button type="button" onclick="deleteActiveTeam()" class="shrink-0 p-2 text-zinc-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors cursor-pointer rounded-xl bg-surface border border-surface-border lg:border-none lg:bg-transparent shadow-sm lg:shadow-none mb-1" title="Delete Team">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
            <div class="w-full sm:w-[65%]">
                <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-1 ml-1 lg:ml-0">Description</label>
                <input type="text" name="description" value="<?= esc($activeTeam['description']) ?>" placeholder="Optional description..." class="w-full bg-transparent text-sm font-medium text-text-muted placeholder-zinc-300 dark:placeholder-zinc-700 focus:outline-none focus:ring-0 px-1 border-b border-transparent focus:border-accent/30 transition-colors pb-1">
            </div>
        </div>

        <!-- Main Desktop Layout: Left Col (Tabs + Panels), Right Col (Filters) -->
        <div class="flex-1 lg:min-h-[550px] relative pb-[80px] lg:pb-0 flex flex-col lg:flex-row lg:gap-6">
            
            <!-- LEFT COLUMN: Content (Available / Selected) -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Sub Tabs (Now visible on Desktop too!) -->
                <div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 overflow-x-auto custom-scrollbar">
                    <button type="button" id="tab-btn-available" class="tab-btn pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-all cursor-pointer whitespace-nowrap" onclick="switchTeamPanel('available')">
                        Available Users
                    </button>
                    <button type="button" id="tab-btn-selected" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer whitespace-nowrap" onclick="switchTeamPanel('selected')">
                        Selected Members
                        <span class="ml-1.5 px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-text-muted text-[10px] tab-badge transition-colors" id="tab-selected-count">0</span>
                    </button>
                </div>
                
                <!-- Panels Container -->
                <div class="flex-1 relative flex flex-col">
                    
                    <!-- Panel 1: Directory Search (Available Users) -->
                    <div id="panel-available" class="flex-1 flex-col bg-surface lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm lg:overflow-hidden flex lg:absolute lg:inset-0">
                        <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0 relative">
                            <div class="flex justify-between items-center">
                                <h2 class="text-xs font-black uppercase tracking-widest text-text-muted">Available Users</h2>
                                <div class="flex items-center gap-4">
                                    <button type="button" id="teams-filter-toggle-btn" onclick="toggleTeamsFilterSidebar()" class="lg:hidden text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-accent transition-colors cursor-pointer flex items-center gap-1" title="Filters">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                        </svg>
                                        Filters
                                    </button>
                                    <button type="button" onclick="selectAllVisible()" class="text-[10px] font-black uppercase tracking-widest text-accent hover:text-accent-hover transition-colors cursor-pointer flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Select Visible
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Allows natural expansion on mobile with overflow-visible -->
                        <div class="flex-1 overflow-visible lg:overflow-y-auto p-2 space-y-1 custom-scrollbar" id="directory-list">
                            <?php foreach($users as $user): ?>
                                <div class="user-card flex items-center justify-between p-3 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 cursor-pointer transition-all border border-transparent hover:border-surface-border min-w-0"
                                     data-id="<?= $user['user_id'] ?>" data-name="<?= strtolower(esc($user['first_name'] . ' ' . $user['last_name'])) ?>" data-email="<?= strtolower(esc($user['email'])) ?>" data-unit="<?= $user['unit_id'] ?>" data-position="<?= $user['position_id'] ?>" data-teaching="<?= $user['is_teaching'] ?>" onclick="moveToSelected(this)">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="size-9 rounded-full bg-accent/10 text-accent flex items-center justify-center font-black text-sm shrink-0">
                                            <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                                        </div>
                                        <div class="flex flex-col min-w-0 flex-1 pr-2">
                                            <span class="text-sm font-bold text-text truncate"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-text-muted truncate">
                                                <?= esc($user['position'] ?? 'No Position') ?> • <?= esc($user['department'] ?? 'No Unit') ?>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Plus icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-300 dark:text-zinc-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </div>
                            <?php endforeach; ?>
                            <div id="no-results" class="hidden text-center py-8 text-sm font-bold text-text-muted italic">No users match your filters.</div>
                        </div>
                    </div>

                    <!-- Panel 2: Selected Team Members -->
                    <div id="panel-selected" class="flex-1 flex-col bg-surface lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm lg:overflow-hidden hidden lg:absolute lg:inset-0">
                        <div class="border-b border-surface-border bg-success-50 dark:bg-success-500/10 shrink-0">
                            <div class="p-4 flex justify-between items-center">
                                <h2 class="text-xs font-black uppercase tracking-widest text-success-600 dark:text-success-400">Selected Team Members (<span id="selected-count">0</span>)</h2>
                                <!-- Trash icon -->
                                <button type="button" id="btn-clear-list" onclick="clearAllSelected()" class="hidden text-[10px] font-black uppercase tracking-widest text-danger-500 hover:text-danger-600 transition-colors cursor-pointer items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Clear List
                                </button>
                            </div>
                        </div>
                        <!-- Allows natural expansion on mobile with overflow-visible -->
                        <div class="flex-1 overflow-visible lg:overflow-y-auto p-2 space-y-1 bg-zinc-50/30 dark:bg-zinc-900/20 custom-scrollbar" id="selected-list">
                            <div id="empty-state" class="text-center py-12 text-sm font-bold text-text-muted italic flex flex-col items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-300 dark:text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Select users to add them here.
                            </div>
                            <div id="selected-no-results" class="hidden text-center py-12 text-sm font-bold text-text-muted italic">No selected members match your search.</div>
                        </div>
                        <!-- Desktop Save Button Location -->
                        <div class="hidden lg:block p-4 border-t border-surface-border bg-white dark:bg-zinc-800 shrink-0">
                            <button type="submit" id="btn-save-desktop" class="w-full py-2.5 rounded-xl bg-accent text-white text-sm font-bold shadow-lg shadow-accent/20 hover:bg-accent-hover transition-all active:scale-[0.98] cursor-pointer">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Filters (Desktop static sidebar, Mobile drawer) -->
            <!-- Mobile Overlay -->
            <div id="teams-filter-overlay" onclick="toggleTeamsFilterSidebar()" class="fixed inset-0 bg-black/50 z-[125] hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

            <div id="teams-filter-sidebar" class="
                fixed inset-y-0 left-0 z-[130] lg:z-0 w-80 transform -translate-x-full transition-transform duration-300 flex flex-col h-full bg-surface border-r border-surface-border shadow-2xl
                lg:static lg:w-72 lg:translate-x-0 lg:transition-none lg:shadow-none lg:border border-surface-border lg:rounded-2xl lg:overflow-hidden lg:h-auto lg:flex lg:bg-zinc-50 lg:dark:bg-zinc-800/30 shrink-0
            ">
                <div class="px-6 py-4 border-b border-surface-border flex justify-between items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 lg:rounded-t-2xl">
                    <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filters</h2>
                    <button type="button" onclick="toggleTeamsFilterSidebar()" class="text-text-muted hover:text-text p-1 cursor-pointer lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="p-4 border-b border-surface-border/50 shrink-0">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="filter-search" placeholder="Search members..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-9 pr-4 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all text-text shadow-sm">
                    </div>
                </div>
                
                <div class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4">
                    
                    <!-- Staff Type Filter -->
                    <div class="shrink-0 pb-4 border-b border-surface-border/50">
                        <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                            this.nextElementSibling.classList.toggle('hidden');
                            this.querySelector('svg').classList.toggle('-rotate-180');
                        ">
                            <span>Staff Type</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="space-y-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                                <input type="checkbox" name="filter_staff_type[]" value="teaching" class="checkbox team-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                                Teaching
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                                <input type="checkbox" name="filter_staff_type[]" value="non-teaching" class="checkbox team-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                                Non-Teaching
                            </label>
                        </div>
                    </div>

                    <!-- Units Filter with mini-search -->
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
                            <div id="units-checkbox-list" class="pr-1">
                                <?php
                                $unitTree = [];
                                foreach ($units as $u) {
                                    $pid = $u['parent_id'] ?: 0;
                                    $unitTree[$pid][] = $u;
                                }
                                $renderTree = function($parentId, $level) use (&$renderTree, &$unitTree) {
                                    if (!isset($unitTree[$parentId])) return;
                                    echo '<div class="space-y-0.5 mt-0.5">';
                                    foreach ($unitTree[$parentId] as $unit) {
                                        $hasChildren = isset($unitTree[$unit['id']]);
                                        ?>
                                        <div class="unit-node" data-name="<?= strtolower(esc($unit['name'])) ?>">
                                            <div class="flex items-center gap-1 text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors group">
                                                <?php if ($hasChildren): ?>
                                                    <button type="button" class="unit-toggle text-text-muted hover:text-text p-0.5 shrink-0 transition-transform duration-200 -rotate-90" onclick="
                                                        this.closest('.unit-node').querySelector('.unit-children').classList.toggle('hidden');
                                                        this.classList.toggle('-rotate-90');
                                                    ">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                <?php else: ?>
                                                    <div class="w-4 shrink-0"></div>
                                                <?php endif; ?>
                                                <label class="flex-1 flex items-center gap-2 cursor-pointer overflow-hidden">
                                                    <input type="checkbox" name="filter_unit[]" value="<?= esc($unit['id']) ?>" class="checkbox team-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                                    <span class="truncate"><?= esc($unit['name']) ?></span>
                                                </label>
                                            </div>
                                            <?php if ($hasChildren): ?>
                                                <div class="unit-children hidden border-l border-surface-border ml-2 pl-1 mt-0.5">
                                                    <?php $renderTree($unit['id'], $level + 1); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                    }
                                    echo '</div>';
                                };
                                $renderTree(0, 0);
                                ?>
                            </div>
                            <!-- Units Pagination Controls -->
                            <div class="pt-2 flex justify-between items-center bg-transparent shrink-0" id="filter-units-pagination">
                                <div class="text-[9px] font-bold text-text-muted uppercase tracking-widest">
                                    <span id="f-units-page-start">0</span>-<span id="f-units-page-end">0</span> of <span id="f-units-page-total">0</span>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" class="js-page-prev p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></button>
                                    <button type="button" class="js-page-next p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Positions Filter with mini-search -->
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
                                <?php foreach ($positions as $pos): ?>
                                    <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos['title'])) ?>" data-teaching="<?= $pos['is_teaching'] ?>">
                                        <input type="checkbox" name="filter_pos[]" value="<?= esc($pos['id']) ?>" class="checkbox team-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                        <span class="truncate"><?= esc($pos['title']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <!-- Positions Pagination Controls -->
                            <div class="pt-2 flex justify-between items-center bg-transparent shrink-0" id="filter-pos-pagination">
                                <div class="text-[9px] font-bold text-text-muted uppercase tracking-widest">
                                    <span id="f-pos-page-start">0</span>-<span id="f-pos-page-end">0</span> of <span id="f-pos-page-total">0</span>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" class="js-page-prev p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></button>
                                    <button type="button" class="js-page-next p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Mobile Floating Save Button Location -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 p-4 bg-surface/90 backdrop-blur-md border-t border-surface-border z-50">
            <button type="submit" id="btn-save-mobile" class="w-full py-3.5 rounded-xl bg-accent text-white text-sm font-bold shadow-lg shadow-accent/20 active:scale-95 transition-all cursor-pointer">
                Save Team Changes
            </button>
        </div>
    </form>
<?php endif; ?>

<style>
    .page-hidden { display: none !important; }
</style>
<script>
    class ClientPaginator {
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

    const filterUnitsPaginator = new ClientPaginator({ containerId: 'filter-units-pagination', pageSize: 6 });
    const filterPosPaginator = new ClientPaginator({ containerId: 'filter-pos-pagination', pageSize: 6 });

    // TEAMS FILTER RESPONSIVE LOGIC
    function toggleTeamsFilterSidebar() {
        const sidebar = document.getElementById('teams-filter-sidebar');
        const overlay = document.getElementById('teams-filter-overlay');
        
        if (sidebar && overlay) {
            // Check if mobile or desktop based on window width (lg is 1024px)
            if (window.innerWidth < 1024) {
                // Mobile: Handle left slide animation and overlay
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    overlay.classList.add('opacity-0');
                    sidebar.classList.add('-translate-x-full');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                }
            }
        }
    }

    // Close logic no longer needed for desktop since it's a static sidebar now

    // --- DELETE TEAM (AJAX) ---
    // Registered synchronously (not inside DOMContentLoaded) so this listener attaches
    // before confirmModal.js's global data-confirm auto-submit listener does -
    // stopImmediatePropagation() below then keeps that older listener from ever seeing
    // this same submit, so the confirm dialog doesn't fire twice.
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (form.dataset.ajax !== 'delete-team') return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const ok = await window.appConfirm(form.dataset.confirm, {
            title: form.dataset.confirmTitle || undefined,
            confirmText: form.dataset.confirmText || 'Delete'
        });
        if (!ok) return;

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        apiPost(form.getAttribute('action'), formData, {
            onSuccess: () => {
                const row = form.closest('[data-preset-id]');
                if (row?.dataset.active === '1') {
                    window.location.href = '<?= site_url('teams') ?>';
                } else {
                    row?.remove();
                    if (submitBtn) submitBtn.disabled = false;
                }
            },
            onError: (errMsg) => {
                window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });

    <?php if ($activeTeam): ?>
    async function deleteActiveTeam() {
        <?php
            $confirmMessage = !empty($activeTeam['in_use'])
                ? "This team is still cascaded to at least one evaluation folder. Are you sure you want to delete this team?"
                : 'Are you sure you want to delete this team?';
        ?>
        const ok = await window.appConfirm(<?= json_encode($confirmMessage) ?>, {
            title: 'Delete Team',
            confirmText: 'Delete Team'
        });
        if (!ok) return;

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('preset_id', '<?= $activeTeam['id'] ?>');

        apiPost('<?= site_url('teams/delete') ?>', formData, {
            onSuccess: () => {
                window.location.href = '<?= site_url('teams') ?>';
            },
            onError: (errMsg) => {
                window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
            }
        });
    }
    // MOBILE TABS LOGIC
    function switchTeamPanel(panel) {
        const avail = document.getElementById('panel-available');
        const selected = document.getElementById('panel-selected');
        const btnAvail = document.getElementById('tab-btn-available');
        const btnSelected = document.getElementById('tab-btn-selected');

        if (panel === 'available') {
            avail.classList.remove('hidden');
            avail.classList.add('flex');
            selected.classList.remove('flex');
            selected.classList.add('hidden');

            btnAvail.classList.add('border-accent', 'text-accent');
            btnAvail.classList.remove('border-transparent', 'text-text-muted');
            
            btnSelected.classList.remove('border-accent', 'text-accent');
            btnSelected.classList.add('border-transparent', 'text-text-muted');
            btnSelected.querySelector('.tab-badge').classList.replace('bg-accent/10', 'bg-zinc-100');
            btnSelected.querySelector('.tab-badge').classList.replace('text-accent', 'text-text-muted');
        } else {
            selected.classList.remove('hidden');
            selected.classList.add('flex');
            avail.classList.remove('flex');
            avail.classList.add('hidden');

            btnSelected.classList.add('border-accent', 'text-accent');
            btnSelected.classList.remove('border-transparent', 'text-text-muted');
            btnSelected.querySelector('.tab-badge').classList.replace('bg-zinc-100', 'bg-accent/10');
            btnSelected.querySelector('.tab-badge').classList.replace('text-text-muted', 'text-accent');

            btnAvail.classList.remove('border-accent', 'text-accent');
            btnAvail.classList.add('border-transparent', 'text-text-muted');
        }
    }

    // PRE-LOADER LOGIC
    const activeMemberIds = <?= json_encode($activeMemberIds ?? []) ?>;

    window.addEventListener('DOMContentLoaded', () => {
        activeMemberIds.forEach(id => {
            const cardEl = document.querySelector(`#directory-list .user-card[data-id="${id}"]`);
            if (cardEl) window.moveToSelected(cardEl);
        });
    });

    // Filtering Logic
    const searchInput = document.getElementById('filter-search');

    // Unit hierarchy: filtering by a parent unit should match all descendants
    const unitParents = {
        <?php foreach ($units as $u): ?>
            '<?= $u['id'] ?>': <?= $u['parent_id'] ? "'" . $u['parent_id'] . "'" : 'null' ?>,
        <?php endforeach; ?>
    };
    const unitChildren = {};
    Object.keys(unitParents).forEach(id => {
        const parentId = unitParents[id];
        if (parentId) {
            if (!unitChildren[parentId]) unitChildren[parentId] = [];
            unitChildren[parentId].push(id);
        }
    });
    function getDescendantUnitIds(rootId) {
        const result = new Set([rootId]);
        const stack = [rootId];
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

    function getCheckedValues(name) {
        return Array.from(document.querySelectorAll(`input[name="${name}"]`))
            .filter(cb => cb.checked)
            .map(cb => cb.value);
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        
        const checkedStaffTypes = getCheckedValues('filter_staff_type[]');
        const checkedUnits = getCheckedValues('filter_unit[]');
        const checkedPositions = getCheckedValues('filter_pos[]');

        // Expand checked units to include all descendants
        let allowedUnitIds = null;
        if (checkedUnits.length > 0) {
            allowedUnitIds = new Set();
            checkedUnits.forEach(uid => {
                const descendants = getDescendantUnitIds(uid);
                descendants.forEach(d => allowedUnitIds.add(d));
            });
        }

        const cards = document.querySelectorAll('#directory-list .user-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const matchSearch = card.getAttribute('data-name').includes(searchTerm) || card.getAttribute('data-email').includes(searchTerm);
            const unitData = card.getAttribute('data-unit');
            const matchUnit = !allowedUnitIds || (unitData && unitData.split(',').some(uid => allowedUnitIds.has(uid)));
            const posData = card.getAttribute('data-position');
            const matchPosition = checkedPositions.length === 0 || (posData && posData.split(',').some(p => checkedPositions.includes(p)));
            const isTeachingUser = card.getAttribute('data-teaching') === "1";
            const matchStaffType = checkedStaffTypes.length === 0 || checkedStaffTypes.includes(isTeachingUser ? 'teaching' : 'non-teaching');
            const isAlreadySelected = card.classList.contains('is-selected');

            if (matchSearch && matchUnit && matchPosition && matchStaffType && !isAlreadySelected) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noRes = document.getElementById('no-results');
        if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';

        // Apply ALL filters to selected members too
        const selectedCards = document.querySelectorAll('#selected-list .user-card');
        let selVisibleCount = 0;
        selectedCards.forEach(card => {
            const matchSearch = card.getAttribute('data-name').includes(searchTerm) || card.getAttribute('data-email').includes(searchTerm);
            const unitData = card.getAttribute('data-unit');
            const matchUnit = !allowedUnitIds || (unitData && unitData.split(',').some(uid => allowedUnitIds.has(uid)));
            const posData = card.getAttribute('data-position');
            const matchPosition = checkedPositions.length === 0 || (posData && posData.split(',').some(p => checkedPositions.includes(p)));
            const isTeachingUser = card.getAttribute('data-teaching') === "1";
            const matchStaffType = checkedStaffTypes.length === 0 || checkedStaffTypes.includes(isTeachingUser ? 'teaching' : 'non-teaching');

            if (matchSearch && matchUnit && matchPosition && matchStaffType) {
                card.style.display = 'flex';
                selVisibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const selNoRes = document.getElementById('selected-no-results');
        if (selNoRes) selNoRes.style.display = selVisibleCount === 0 && selectedCards.length > 0 ? 'block' : 'none';
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    
    // Listen to all filter checkboxes
    document.querySelectorAll('.team-filter-checkbox').forEach(cb => {
        cb.addEventListener('change', applyFilters);
    });

    // Paginators and Mini-search setup for filter sidebar
    // Units
    const cbUnitsList = document.getElementById('units-checkbox-list');
    if (cbUnitsList) {
        const allUnitRows = Array.from(cbUnitsList.querySelectorAll(':scope > div > .unit-node'));
        filterUnitsPaginator.init(allUnitRows);
        
        const filterSidebarUnits = () => {
            const input = document.getElementById('mini-search-units');
            const query = input ? input.value.trim().toLowerCase() : '';
            const matchedRows = allUnitRows.filter(row => row.dataset.name && row.dataset.name.includes(query));
            filterUnitsPaginator.updateItems(matchedRows);
        };

        const msUnits = document.getElementById('mini-search-units');
        if (msUnits) msUnits.addEventListener('input', filterSidebarUnits);
        filterSidebarUnits();
    }

    // Positions
    const cbPosList = document.getElementById('positions-checkbox-list');
    if (cbPosList) {
        const allPosRows = Array.from(cbPosList.querySelectorAll('.position-filter-label'));
        filterPosPaginator.init(allPosRows);
        
        const filterSidebarPositions = () => {
            const input = document.getElementById('mini-search-positions');
            const query = input ? input.value.trim().toLowerCase() : '';
            const matchedRows = allPosRows.filter(row => row.dataset.title && row.dataset.title.includes(query));
            filterPosPaginator.updateItems(matchedRows);
        };

        const msPos = document.getElementById('mini-search-positions');
        if (msPos) msPos.addEventListener('input', filterSidebarPositions);
        filterSidebarPositions();
    }

    // ==========================================
    // MASS SELECTION LOGIC
    // ==========================================
    window.selectAllVisible = function() {
        const cards = document.querySelectorAll('#directory-list .user-card');
        cards.forEach(card => {
            if (card.style.display !== 'none' && !card.classList.contains('is-selected')) {
                window.moveToSelected(card);
            }
        });
    };

    window.clearAllSelected = async function() {
        const ok = await window.appConfirm("Are you sure you want to clear all selected users?", { confirmText: 'Clear List' });
        if (!ok) return;
        const selectedCards = document.querySelectorAll('#selected-list .user-card');
        selectedCards.forEach(clone => {
            const id = clone.getAttribute('data-id');
            window.moveToDirectory(clone, id);
        });
    };

    // Dual-Pane Logic
    const directoryList = document.getElementById('directory-list');
    const selectedList = document.getElementById('selected-list');
    const emptyState = document.getElementById('empty-state');
    const selectedNoResults = document.getElementById('selected-no-results');
    const selectedSearchInput = document.getElementById('filter-selected-search');
    const countDisplay = document.getElementById('selected-count');
    const mobileTabCountDisplay = document.getElementById('tab-selected-count');
    const clearBtn = document.getElementById('btn-clear-list');

    let selectedIds = new Set();

    window.moveToSelected = function(el) {
        const id = el.getAttribute('data-id');
        if (selectedIds.has(id)) return;

        const clone = el.cloneNode(true);
        clone.onclick = function() { moveToDirectory(this, id); };

        clone.querySelector('svg').outerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-danger-400 hover:text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
        clone.insertAdjacentHTML('beforeend', `<input type="hidden" name="user_ids[]" value="${id}">`);

        selectedList.appendChild(clone);
        selectedIds.add(id);

        el.style.display = 'none';
        el.classList.add('is-selected');

        updateUI();
    };

    window.moveToDirectory = function(cloneEl, id) {
        cloneEl.remove();
        selectedIds.delete(id);

        const originalEl = document.querySelector(`#directory-list .user-card[data-id="${id}"]`);
        if (originalEl) {
            originalEl.classList.remove('is-selected');
            applyFilters();
        }
        updateUI();
    };

    function updateUI() {
        const count = selectedIds.size;
        countDisplay.innerText = count;
        if(mobileTabCountDisplay) mobileTabCountDisplay.innerText = count;

        emptyState.style.display = count === 0 ? 'flex' : 'none';

        applyFilters();

        if (count > 0) {
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('flex');
        } else {
            clearBtn.classList.add('hidden');
            clearBtn.classList.remove('flex');
        }
    }

<?php endif; ?>
</script>
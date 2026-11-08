<div id="tab-content-system" class="tab-content <?= $activeTab === 'system' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface">

    <?= view('components/create_position_modal', ['positionTree' => $positionTree]) ?>
    <?= view('components/edit_position_modal', ['positionTree' => $positionTree]) ?>
    <?= view('components/create_unit_modal') ?>
    <?= view('components/edit_unit_modal', ['positionTree' => $positionTree, 'hasOpenCycle' => $hasOpenCycle]) ?>
    <?= view('components/create_employment_status_modal') ?>
    
    <!-- MOBILE FILTER OVERLAY -->
    <div id="mobile-system-filter-overlay" class="fixed inset-0 z-[115] bg-black/50 hidden lg:hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- Sub-Tabs Header -->
    <div class="px-4 pt-4 shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800/30 border-b border-surface-border flex items-end justify-between gap-4">
        <div class="flex gap-6 overflow-x-auto custom-scrollbar whitespace-nowrap">
            <button type="button" id="subtab-btn-positions" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-accent text-accent-ink transition-all cursor-pointer" onclick="switchSystemTab('positions')">
                Job Positions
            </button>
            <button type="button" id="subtab-btn-units" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchSystemTab('units')">
                Departments & Units
            </button>
            <button type="button" id="subtab-btn-employment_statuses" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchSystemTab('employment_statuses')">
                Employment Statuses
            </button>
            <button type="button" id="subtab-btn-doc_rules" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchSystemTab('doc_rules')">
                Doc Type Rules
            </button>
        </div>
        <button type="button" class="btn-open-system-mobile lg:hidden flex items-center gap-1.5 text-xs font-bold text-accent-ink bg-accent/10 hover:bg-accent/20 px-3 py-1.5 rounded-lg active:scale-95 transition-colors cursor-pointer mb-2.5 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            Filters
        </button>
    </div>

    <!-- POSITIONS SUB-TAB -->
    <div id="system-panel-positions" class="system-panel flex flex-col lg:flex-row flex-1 lg:min-h-0 relative">
        <!-- Sidebar -->
        <div class="system-sidebar fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
            <div style="height: 54px; min-height: 54px; max-height: 54px;" class="px-6 border-b border-surface-border flex justify-center relative items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 box-border">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest w-full">Filters</h2>
                <button type="button" class="btn-close-system-mobile absolute right-6 lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 flex-1 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
                <!-- Search -->
                <div class="shrink-0 pb-4 border-b border-surface-border/50">
                    <input type="text" id="filter-positions" placeholder="Search positions..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm">
                </div>
                
                <!-- Add Button -->
                <div class="mt-2">
                    <button type="button" class="btn-open-modal w-full flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-accent-text px-4 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm shadow-accent/20 cursor-pointer" data-target="modal-create-position">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add New Position
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content (List) -->
        <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface">

            
            <div class="overflow-y-auto custom-scrollbar flex-1 p-0 border-t border-surface-border lg:border-t-0">
                <ul class="border-collapse divide-y divide-surface-border block lg:table lg:table-fixed w-full" id="positions-list">
                    <?php foreach ($positionTree as $pos): ?>
                        <?php
                            $kind = $pos['is_general_root'] ? 'general-root' : ($pos['is_general'] ? 'general' : ($pos['is_head'] ? 'head' : 'member'));
                        ?>
                        <li class="position-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group" data-title="<?= strtolower(esc($pos['title'])) ?>" data-id="<?= $pos['id'] ?>" data-parent="<?= $pos['parent_id'] ?: 0 ?>" style="<?= $pos['parent_id'] ? 'display: none;' : '' ?><?= $kind === 'general-root' ? 'background: color-mix(in srgb, var(--color-accent) 8%, var(--color-surface));' : '' ?>">
                            <div class="flex flex-col justify-center lg:table-cell py-4 align-middle h-[72px]" style="padding-left: <?= ($pos['level'] * 24 + 24) ?>px; padding-right: 24px;<?= $kind === 'general-root' ? ' box-shadow: inset 3px 0 0 var(--color-accent);' : '' ?>">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center gap-2 pr-2">
                                        <?php if ($pos['has_children']): ?>
                                            <button type="button" class="js-tree-toggle text-text-muted hover:text-text transition-transform transform -rotate-90 focus:outline-none p-1 cursor-pointer" onclick="togglePositionTree(this, <?= $pos['id'] ?>)" aria-label="Show or hide positions under <?= esc($pos['title'], 'attr') ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        <?php else: ?>
                                            <div class="w-6 h-4"></div>
                                        <?php endif; ?>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold <?= $kind === 'general-root' ? 'text-accent-ink' : 'text-text' ?>"><?= esc($pos['title']) ?></span>
                                        <span class="text-[9px] font-black tracking-widest uppercase text-text-muted">
                                            <?php if ($kind === 'general-root'): ?>General group &middot; its positions show for everyone below a head
                                            <?php elseif ($kind === 'general'): ?>General &middot; <?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?>
                                            <?php elseif ($kind === 'head'): ?>Head position &middot; <?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?>
                                            <?php else: ?><?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?><?= $pos['parent_id'] ? '' : ' &middot; not under any head' ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <?php if ($kind !== 'general-root'): ?>
                                        <button type="button"
                                            class="text-accent-ink hover:text-accent-hover opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 bg-accent/10 hover:bg-accent/20 lg:bg-transparent rounded-lg cursor-pointer"
                                            title="Edit / move position"
                                            onclick="openEditPositionModal(<?= $pos['id'] ?>, <?= esc(json_encode($pos['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>, <?= (int) $pos['is_teaching'] ?>, <?= $pos['parent_id'] ? (int) $pos['parent_id'] : 'null' ?>, <?= (int) $pos['is_general_root'] ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <?= form_open('account/position/delete', ['data-ajax' => 'delete-position', 'data-confirm' => 'Delete this position? Only a position nobody holds or held can be deleted.', 'data-confirm-title' => 'Delete Position']) ?>
                                            <input type="hidden" name="id" value="<?= $pos['id'] ?>">
                                            <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg cursor-pointer" title="Delete position">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        <?= form_close() ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li id="positions-empty-state" class="hidden px-4 py-16 text-center text-sm font-bold text-text-muted">No positions matched your search.</li>
                </ul>
            </div>
            
            <div class="px-6 py-4 border-t border-surface-border flex justify-between items-center bg-surface shrink-0 hidden" id="positions-pagination">
                <div class="text-xs font-bold text-text-muted">
                    Showing <span id="pos-page-start">0</span> to <span id="pos-page-end">0</span> of <span id="pos-page-total">0</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="js-page-prev px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Prev</button>
                    <button type="button" class="js-page-next px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- UNITS SUB-TAB -->
    <div id="system-panel-units" class="system-panel hidden flex-col lg:flex-row flex-1 lg:min-h-0 relative">
        <!-- Sidebar -->
        <div class="system-sidebar fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
            <div style="height: 54px; min-height: 54px; max-height: 54px;" class="px-6 border-b border-surface-border flex justify-center relative items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 box-border">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest w-full">Filters</h2>
                <button type="button" class="btn-close-system-mobile absolute right-6 lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 flex-1 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
                <!-- Search -->
                <div class="shrink-0 pb-4 border-b border-surface-border/50">
                    <input type="text" id="filter-units" placeholder="Search departments & units..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm">
                </div>
                
                <!-- Add Button -->
                <div class="mt-2">
                    <button type="button" class="btn-open-modal w-full flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-accent-text px-4 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm shadow-accent/20 cursor-pointer" data-target="modal-create-unit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Department / Unit
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content (List) -->
        <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface">


            <div class="overflow-y-auto custom-scrollbar flex-1 p-0 border-t border-surface-border lg:border-t-0">
                <?php
                $unitTree = [];
                foreach ($units as $u) {
                    $pid = $u['parent_id'] ?: 0;
                    $unitTree[$pid][] = $u;
                }
                $flattenedUnits = [];
                $flatten = function($parentId, $level) use (&$flatten, &$unitTree, &$flattenedUnits) {
                    if (!isset($unitTree[$parentId])) return;
                    foreach ($unitTree[$parentId] as $unit) {
                        $unit['level'] = $level;
                        $unit['has_children'] = isset($unitTree[$unit['id']]);
                        $flattenedUnits[] = $unit;
                        $flatten($unit['id'], $level + 1);
                    }
                };
                $flatten(0, 0);
                ?>
                <ul class="border-collapse divide-y divide-surface-border block lg:table lg:table-fixed w-full" id="units-list">
                    <?php foreach ($flattenedUnits as $unit): ?>
                        <li class="unit-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group" 
                            data-id="<?= $unit['id'] ?>"
                            data-parent="<?= $unit['parent_id'] ?: 0 ?>"
                            data-name="<?= strtolower(esc($unit['name'])) ?>"
                            style="<?= $unit['parent_id'] ? 'display: none;' : '' ?>">
                            <div class="flex flex-col justify-center lg:table-cell py-4 align-middle h-[72px]" style="padding-left: <?= ($unit['level'] * 24 + 24) ?>px; padding-right: 24px;">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center gap-2 pr-2">
                                        <?php if ($unit['has_children']): ?>
                                            <button type="button" class="js-tree-toggle text-text-muted hover:text-text transition-transform transform -rotate-90 focus:outline-none p-1 cursor-pointer" onclick="toggleUnitTree(this, <?= $unit['id'] ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        <?php else: ?>
                                            <div class="w-6 h-4"></div>
                                        <?php endif; ?>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-text"><?= esc($unit['name']) ?></span>
                                            <?php $uh = $unitHeads[$unit['id']] ?? ['titles' => [], 'linked' => false, 'holders' => []]; ?>
                                            <?php if (empty($uh['titles'])): ?>
                                                <span class="text-[9px] font-bold text-danger-500 uppercase tracking-widest mt-0.5">No head position &middot; link one (or link a parent unit)</span>
                                            <?php else: ?>
                                                <span class="text-[9px] font-bold uppercase tracking-widest mt-0.5 text-text-muted">
                                                    <?= !$unit['parent_id'] ? 'Top level &middot; ' : '' ?>Head: <?= esc(implode(' / ', $uh['titles'])) ?> &middot; <?= $uh['linked'] ? 'linked' : 'inherited' ?>
                                                    <?php $nHolders = count($uh['holders']); ?>
                                                    &middot; <span title="<?= esc(implode(', ', $uh['holders'])) ?>"><?= $nHolders . ($nHolders === 1 ? ' holder' : ' holders') ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                            class="text-accent-ink hover:text-accent-hover opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 bg-accent/10 hover:bg-accent/20 lg:bg-transparent rounded-lg cursor-pointer"
                                            title="Edit / Transfer Department / Unit"
                                            onclick="openEditUnitModal(<?= $unit['id'] ?>, '<?= esc($unit['name'], 'js') ?>', <?= $unit['parent_id'] ? (int)$unit['parent_id'] : 'null' ?>, <?= !empty($unit['head_position_id']) ? (int)$unit['head_position_id'] : 'null' ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <?= form_open('account/unit/delete', ['data-ajax' => 'delete-unit', 'data-confirm' => 'Delete this department / unit? Only a unit nobody is or was assigned to, with no sub-units, can be deleted.', 'data-confirm-title' => 'Delete Department / Unit']) ?>
                                            <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                            <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg cursor-pointer" title="Delete Department / Unit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li id="units-empty-state" class="hidden px-4 py-16 text-center text-sm font-bold text-text-muted">No departments &amp; units matched your search.</li>
                </ul>
            </div>
            
            <div class="px-6 py-4 border-t border-surface-border flex justify-between items-center bg-surface shrink-0 hidden" id="units-pagination">
                <div class="text-xs font-bold text-text-muted">
                    Showing <span id="uni-page-start">0</span> to <span id="uni-page-end">0</span> of <span id="uni-page-total">0</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="js-page-prev px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Prev</button>
                    <button type="button" class="js-page-next px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Next</button>
                </div>
            </div>
            
        </div>
    </div>

    <!-- EMPLOYMENT STATUSES SUB-TAB -->
    <div id="system-panel-employment_statuses" class="system-panel hidden flex-col lg:flex-row flex-1 lg:min-h-0 relative">
        <!-- Sidebar -->
        <div class="system-sidebar fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
            <div style="height: 54px; min-height: 54px; max-height: 54px;" class="px-6 border-b border-surface-border flex justify-center relative items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 box-border">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest w-full">Filters</h2>
                <button type="button" class="btn-close-system-mobile absolute right-6 lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 flex-1 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
                <!-- Search -->
                <div class="shrink-0 pb-4 border-b border-surface-border/50">
                    <input type="text" id="filter-employment-statuses" placeholder="Search employment statuses..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm">
                </div>
                
                <!-- Add Button -->
                <div class="mt-2">
                    <button type="button" class="btn-open-modal w-full flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-accent-text px-4 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm shadow-accent/20 cursor-pointer" data-target="modal-create-employment-status">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add New Status
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content (List) -->
        <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface">
            <div class="overflow-y-auto custom-scrollbar flex-1 p-0 border-t border-surface-border lg:border-t-0">
                <ul class="border-collapse divide-y divide-surface-border block lg:table lg:table-fixed w-full" id="employment-statuses-list">
                    <?php if (!empty($employmentStatuses)): ?>
                        <?php foreach ($employmentStatuses as $es): ?>
                            <li class="employment-status-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group" data-name="<?= strtolower(esc($es['name'])) ?>">
                                <div class="flex flex-col justify-center lg:table-cell px-6 py-4 align-middle h-[72px]">
                                    <div class="flex justify-between items-center w-full">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-text"><?= esc($es['name']) ?></span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                        <?php if (!empty($docRules['linksReady'])): ?>
                                            <div class="flex items-center gap-3" data-status-id="<?= (int) $es['id'] ?>">
                                                <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Open to</span>
                                                <?php foreach (['Employee', 'Supervisor'] as $roleName):
                                                    $openList = $docRules['open'][$roleName] ?? null;
                                                    $isOpen = $openList === null || in_array(strtolower($es['name']), array_map('strtolower', $openList), true); ?>
                                                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text font-bold">
                                                        <input type="checkbox" class="js-status-role checkbox rounded border-surface-border text-accent-ink focus:ring-accent cursor-pointer" value="<?= $roleName ?>" <?= $isOpen ? 'checked' : '' ?>>
                                                        <?= staff_role_short($roleName) ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?= form_open('account/employment-status/delete', ['data-ajax' => 'delete-employment-status', 'data-confirm' => 'Delete this employment status? People who have it keep it until you change it.', 'data-confirm-title' => 'Delete Employment Status']) ?>
                                            <input type="hidden" name="id" value="<?= $es['id'] ?>">
                                            <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg cursor-pointer" title="Delete Employment Status">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        <?= form_close() ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li id="employment-statuses-empty-state" class="hidden px-4 py-16 text-center text-sm font-bold text-text-muted">No employment statuses matched your search.</li>
                </ul>
            </div>
            
            <div class="px-6 py-4 border-t border-surface-border flex justify-between items-center bg-surface shrink-0 hidden" id="employment-statuses-pagination">
                <div class="text-xs font-bold text-text-muted">
                    Showing <span id="es-page-start">0</span> to <span id="es-page-end">0</span> of <span id="es-page-total">0</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="js-page-prev px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Prev</button>
                    <button type="button" class="js-page-next px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DOC TYPE RULES SUB-TAB (2026-11-07): which doc types each role and each employment status allows -->
    <div id="system-panel-doc_rules" class="system-panel hidden flex-col flex-1 lg:min-h-0 relative">
        <div class="doc-rules-panel overflow-y-auto custom-scrollbar flex-1 p-4 lg:p-6">
                <p class="text-xs text-text-muted font-medium shrink-0">A person may use the doc types allowed by <strong class="text-text">both</strong> their role and their employment status: one allowed is set automatically, several means they pick. Keep at least one ticked; all four means no limit. Which statuses each role may have is set in Employment Statuses. A rule change never changes anyone's doc type; people it no longer fits are flagged in the User Directory.</p>
            <div class="doc-rules-cols">
                <!-- Left: the rules (only the employment statuses list grows, so only it scrolls) -->
                <div id="doc-rules-swap" class="doc-rules-col">
                <?= form_open('account/doc-rules/update', ['id' => 'form-doc-rules', 'data-ajax' => 'save-doc-rules', 'class' => 'doc-rules-col']) ?>
                <?php foreach ([['Roles', 'role', $docRules['roles'], 'name'], ['Employment statuses', 'status', $docRules['statuses'], 'name']] as [$heading, $kind, $rows, $label]): ?>
                <div class="rounded-xl border border-surface-border overflow-hidden<?= $kind === 'status' ? ' doc-rules-grow' : '' ?>">
                    <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted"><?= $heading ?></div>
                    <div class="divide-y divide-surface-border<?= $kind === 'status' ? ' doc-rules-scroll custom-scrollbar' : '' ?>">
                        <?php foreach ($rows as $r): ?>
                            <div class="js-rule-row px-4 py-3 flex flex-col lg:flex-row lg:items-center gap-3" data-kind="<?= $kind ?>" data-name="<?= esc($r[$label]) ?>">
                                <input type="hidden" name="<?= $kind ?>_present[<?= $r['id'] ?>]" value="1">
                                <span class="text-sm font-bold text-text w-full shrink-0" style="max-width: 9rem;"><?= esc($kind === 'role' ? staff_role_short($r[$label]) : $r[$label]) ?></span>
                                <div class="flex flex-wrap items-center gap-4">
                                    <?php foreach ($docTypes as $t): ?>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-text font-bold">
                                            <input type="checkbox" class="js-rule-type checkbox rounded border-surface-border text-accent-ink focus:ring-accent cursor-pointer" name="<?= $kind ?>_types[<?= $r['id'] ?>][]" value="<?= $t ?>" <?= ($r['allowed'] === null || in_array($t, $r['allowed'], true)) ? 'checked' : '' ?>>
                                            <?= $t ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <span class="js-rule-hint text-[10px] font-bold uppercase tracking-widest text-text-muted"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="flex justify-end shrink-0">
                    <button type="submit" class="w-full lg:w-auto bg-accent hover:bg-accent-hover text-accent-text text-xs font-bold py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all cursor-pointer">Save Rules</button>
                </div>
                <?= form_close() ?>
    <script>
    (function () {
        const DOC_TYPES = <?= json_encode($docTypes) ?>;
        const roleShort = r => ({ Supervisor: 'Unit head', Employee: 'Member' })[r] || r;
        const PREVIEW = <?= json_encode($docRules['preview'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const form = document.getElementById('form-doc-rules');
        if (!form) return;
        const allowedOf = (kind, name) => {
            const row = Array.from(form.querySelectorAll('.js-rule-row')).find(r => r.dataset.kind === kind && r.dataset.name.toLowerCase() === String(name).toLowerCase());
            if (!row) return null;
            return Array.from(row.querySelectorAll('.js-rule-type:checked')).map(c => c.value);   // all four = no limit
        };
        const esc = v => { const d = document.createElement('div'); d.textContent = v; return d.innerHTML; };
        function render() {
            const body = document.getElementById('doc-rules-preview');
            body.innerHTML = PREVIEW.map(p => {
                const r = allowedOf('role', p.role), st = allowedOf('status', p.status);
                const allowed = DOC_TYPES.filter(t => (r === null || r.includes(t)) && (st === null || st.includes(t)));
                if (p.open === false) {
                    // The role may not have this status (Employment Statuses tab): everyone in it is flagged.
                    return `<tr class="opacity-60"><td class="px-4 py-2.5 font-bold text-text-muted">${esc(roleShort(p.role))}</td><td class="px-4 py-2.5 text-text-muted">${esc(p.status)}</td>`
                         + `<td class="px-4 py-2.5 text-text-muted">Not open to ${esc(roleShort(p.role).toLowerCase())}s</td><td class="px-4 py-2.5 text-text-muted">${p.people}</td>`
                         + `<td class="px-4 py-2.5 font-bold ${p.people ? 'text-warning-500' : 'text-text-muted'}">${p.people}</td></tr>`;
                }
                const flagged = Object.entries(p.by_type || {}).reduce((n, [t, c]) => n + (allowed.includes(t) ? 0 : c), 0);
                const empty = allowed.length === 0;
                return `<tr><td class="px-4 py-2.5 font-bold text-text">${esc(roleShort(p.role))}</td><td class="px-4 py-2.5 text-text">${esc(p.status)}</td>`
                     + `<td class="px-4 py-2.5 font-bold ${empty ? 'text-danger-500' : 'text-text'}">${empty ? 'Nothing allowed' : allowed.join(', ') + (allowed.length === 1 ? ' (set automatically)' : ' (person picks)')}</td>`
                     + `<td class="px-4 py-2.5 text-text-muted">${p.people}</td>`
                     + `<td class="px-4 py-2.5 font-bold ${flagged ? 'text-warning-500' : 'text-text-muted'}">${flagged}</td></tr>`;
            }).join('');
        }
        // At least one doc type stays ticked in every row (all four = no limit).
        form.addEventListener('change', (e) => {
            const box = e.target.closest('.js-rule-type');
            if (box && !box.checked && !box.closest('.js-rule-row').querySelector('.js-rule-type:checked')) {
                box.checked = true;
                const hint = box.closest('.js-rule-row').querySelector('.js-rule-hint');
                hint.textContent = 'Keep at least one';
                clearTimeout(hint._t); hint._t = setTimeout(() => { hint.textContent = ''; }, 2000);
            }
            render();
        });
        // The preview table comes after this script on the page: wait for it on first load.
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
    })();
    </script>
                </div>
                <!-- Right: default weights (fixed size) and the preview (scrolls) -->
                <div class="doc-rules-col">
                    <?= $this->include('accounts/tabs/_default_weights') ?>
                <div class="rounded-xl border border-surface-border overflow-hidden doc-rules-grow">
                    <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted">Preview: every role and status</div>
                    <div class="overflow-x-auto custom-scrollbar doc-rules-scroll">
                        <table class="w-full text-left border-collapse">
                            <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border sticky top-0 z-10 bg-surface">
                                <tr><th class="px-4 py-2.5">Role</th><th class="px-4 py-2.5">Employment status</th><th class="px-4 py-2.5">Allowed doc types</th><th class="px-4 py-2.5">People</th><th class="px-4 py-2.5">Would be flagged</th></tr>
                            </thead>
                            <tbody id="doc-rules-preview" class="divide-y divide-surface-border text-sm"></tbody>
                        </table>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>

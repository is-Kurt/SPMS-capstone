import os

filepath = r'c:\laragon\www\SPMS\app\Views\accounts\tabs\system.php'

content = """<div id="tab-content-system" class="tab-content <?= $activeTab === 'system' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface">

    <!-- Sub-Tabs Header -->
    <div class="p-0 lg:p-4 mb-4 lg:mb-0 border-b-0 lg:border-b border-surface-border bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800/30 shrink-0">
        <div class="flex gap-6 border-b border-surface-border px-2 overflow-x-auto custom-scrollbar whitespace-nowrap">
            <button type="button" id="subtab-btn-positions" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-all cursor-pointer" onclick="switchSystemTab('positions')">
                Job Positions
            </button>
            <button type="button" id="subtab-btn-units" class="subtab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchSystemTab('units')">
                Departments & Units
            </button>
        </div>
    </div>

    <!-- POSITIONS SUB-TAB -->
    <div id="system-panel-positions" class="system-panel flex flex-col lg:flex-row flex-1 lg:min-h-0 relative">
        <!-- Sidebar -->
        <div class="w-full lg:w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border-r border-surface-border shrink-0 flex flex-col h-auto lg:h-full pb-6 lg:pb-0 order-2 lg:order-1 mt-4 lg:mt-0">
            <div class="p-4 border-t lg:border-t-0 border-b border-surface-border bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 shrink-0">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Add Position</h2>
            </div>
            <div class="p-4 flex-1">
                <?= form_open('account/position/add', ['class' => 'flex flex-col gap-4', 'data-ajax' => 'add-position']) ?>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Position Title</label>
                        <input type="text" name="title" required placeholder="e.g. Guidance Counselor" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text">
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text-muted font-bold p-1 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors w-max">
                        <input type="checkbox" name="is_teaching" value="1" class="checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Is Teaching Staff?
                    </label>
                    <button type="submit" class="w-full bg-accent hover:bg-accent-hover text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-colors shadow-sm shadow-accent/20 mt-2">Create Position</button>
                <?= form_close() ?>
            </div>
        </div>

        <!-- Main Content (List) -->
        <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface order-1 lg:order-2">
            <div class="p-4 mb-2 lg:mb-0 border-b-0 lg:border-b border-surface-border bg-transparent lg:bg-surface shrink-0">
                <input type="text" id="filter-positions" placeholder="Search positions..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 lg:py-2 text-sm lg:text-xs focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm lg:shadow-none">
            </div>
            <div class="overflow-y-auto custom-scrollbar flex-1 p-0">
                <ul class="divide-y divide-surface-border block lg:table lg:table-fixed w-full" id="positions-list">
                    <?php foreach ($positions as $pos): ?>
                        <li class="position-item block lg:table-row bg-surface p-4 lg:p-0 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group" data-title="<?= strtolower(esc($pos['title'])) ?>">
                            <div class="block lg:table-cell px-6 py-4 align-middle">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-text"><?= esc($pos['title']) ?></span>
                                        <span class="text-[9px] font-black tracking-widest uppercase <?= $pos['is_teaching'] ? 'text-warning-500' : 'text-zinc-400' ?>"><?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?></span>
                                    </div>
                                    <?= form_open('account/position/delete', ['data-ajax' => 'delete-position', 'data-confirm' => 'Delete this position? Staff currently assigned to it will keep their account but lose that designation.', 'data-confirm-title' => 'Delete Position']) ?>
                                        <input type="hidden" name="id" value="<?= $pos['id'] ?>">
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li id="positions-empty-state" class="hidden px-4 py-16 text-center text-sm font-bold text-text-muted">No positions matched your search.</li>
                </ul>
            </div>
            
            <!-- Pagination could go here -->
            <div id="positions-pagination" class="p-3 border-t border-surface-border flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/30 shrink-0">
                <button type="button" id="pos-prev" class="text-xs font-bold text-accent hover:text-accent-hover px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <span id="pos-page-info" class="text-[10px] font-black tracking-widest text-text-muted uppercase">Page 1 of 1</span>
                <button type="button" id="pos-next" class="text-xs font-bold text-accent hover:text-accent-hover px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>

    <!-- UNITS SUB-TAB -->
    <div id="system-panel-units" class="system-panel hidden flex-col lg:flex-row flex-1 lg:min-h-0 relative">
        <!-- Sidebar -->
        <div class="w-full lg:w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 lg:border-r border-surface-border shrink-0 flex flex-col h-auto lg:h-full pb-6 lg:pb-0 order-2 lg:order-1 mt-4 lg:mt-0">
            <div class="p-4 border-t lg:border-t-0 border-b border-surface-border bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800 shrink-0">
                <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Add Unit</h2>
            </div>
            <div class="p-4 flex-1">
                <?= form_open('account/unit/add', ['class' => 'flex flex-col gap-4', 'data-ajax' => 'add-unit']) ?>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Unit Name</label>
                        <input type="text" name="name" required placeholder="e.g. IT Department" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Parent Unit</label>
                        <select name="parent_id" id="select-unit-parent" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs text-text focus:border-accent outline-none cursor-pointer">
                            <option value="">No Parent (Top Level)</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-accent hover:bg-accent-hover text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-colors shadow-sm shadow-accent/20 mt-2">Create Unit</button>
                <?= form_close() ?>
            </div>
        </div>

        <!-- Main Content (List) -->
        <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface order-1 lg:order-2">
            <div class="p-4 mb-2 lg:mb-0 border-b-0 lg:border-b border-surface-border bg-transparent lg:bg-surface shrink-0">
                <input type="text" id="filter-units" placeholder="Search units..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 lg:py-2 text-sm lg:text-xs focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm lg:shadow-none">
            </div>
            <div class="overflow-y-auto custom-scrollbar flex-1 p-0">
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
                <ul class="divide-y divide-surface-border block lg:table lg:table-fixed w-full" id="units-list">
                    <?php foreach ($flattenedUnits as $unit): ?>
                        <li class="unit-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group" 
                            data-id="<?= $unit['id'] ?>"
                            data-parent="<?= $unit['parent_id'] ?: 0 ?>"
                            data-name="<?= strtolower(esc($unit['name'])) ?>"
                            style="<?= $unit['parent_id'] ? 'display: none;' : '' ?>">
                            <div class="block lg:table-cell py-4 align-middle" style="padding-left: <?= ($unit['level'] * 24 + 24) ?>px; padding-right: 24px;">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center gap-2 pr-2">
                                        <?php if ($unit['has_children']): ?>
                                            <button type="button" class="js-tree-toggle text-text-muted hover:text-text transition-transform transform -rotate-90 focus:outline-none p-1" onclick="toggleUnitTree(this, <?= $unit['id'] ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        <?php else: ?>
                                            <div class="w-6 h-4"></div>
                                        <?php endif; ?>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-text"><?= esc($unit['name']) ?></span>
                                            <?php if (!$unit['parent_id']): ?>
                                                <span class="text-[9px] font-bold text-info-500 uppercase tracking-widest mt-0.5">Top Level Node</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?= form_open('account/unit/delete', ['data-ajax' => 'delete-unit', 'data-confirm' => 'Delete this unit? Staff currently assigned to it will keep their account but lose that unit. Sub-units under it will be deleted too.', 'data-confirm-title' => 'Delete Unit']) ?>
                                        <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li id="units-empty-state" class="hidden px-4 py-16 text-center text-sm font-bold text-text-muted">No units matched your search.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
"""

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated system tab layout")

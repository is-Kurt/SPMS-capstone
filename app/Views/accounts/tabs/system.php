<div id="tab-content-system" class="tab-content <?= $activeTab === 'system' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar lg:bg-zinc-50/50 lg:dark:bg-zinc-900/30">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-0 lg:p-6 lg:h-full">

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col lg:h-full lg:overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0 space-y-3">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Job Positions</h2>
                        <input type="text" id="filter-positions" placeholder="Search positions..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text">
                    </div>
                    <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border" id="positions-list">
                            <?php foreach ($positions as $pos): ?>
                                <li class="position-item flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group" data-title="<?= strtolower(esc($pos['title'])) ?>">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-text"><?= esc($pos['title']) ?></span>
                                        <span class="text-[9px] font-black tracking-widest uppercase <?= $pos['is_teaching'] ? 'text-warning-500' : 'text-zinc-400' ?>"><?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?></span>
                                    </div>
                                    <?= form_open('account/position/delete', ['data-ajax' => 'delete-position', 'data-confirm' => 'Delete this position? Staff currently assigned to it will keep their account but lose that designation.', 'data-confirm-title' => 'Delete Position']) ?>
                                        <input type="hidden" name="id" value="<?= $pos['id'] ?>">
                                        <!-- Trash icon (icon-only button): deletes this position. Any staff assigned to it get their position un-set, not blocked -->
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                            <li id="positions-empty-state" class="hidden px-4 py-8 text-center text-xs font-bold text-text-muted italic">No positions matched your search.</li>
                        </ul>
                    </div>
                    <div id="positions-pagination" class="p-3 border-t border-surface-border flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/30 shrink-0">
                        <button type="button" id="pos-prev" class="text-xs font-bold text-accent disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <span id="pos-page-info" class="text-[10px] font-black tracking-widest text-text-muted uppercase">Page 1 of 1</span>
                        <button type="button" id="pos-next" class="text-xs font-bold text-accent disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                    <?= form_open('account/position/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0', 'data-ajax' => 'add-position']) ?>
                        <input type="text" name="title" required placeholder="New Position Title" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2.5 lg:py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-text-muted font-bold">
                                <input type="checkbox" name="is_teaching" value="1" class="checkbox">
                                Is Teaching Staff?
                            </label>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2.5 lg:py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col lg:h-full lg:overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0 space-y-3">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Departments & Units</h2>
                        <input type="text" id="filter-units" placeholder="Search units..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text">
                    </div>
                    <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-2">
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
                        <ul class="divide-y divide-surface-border" id="units-list">
                            <?php foreach ($flattenedUnits as $unit): ?>
                                <li class="unit-item flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group" 
                                    data-id="<?= $unit['id'] ?>"
                                    data-parent="<?= $unit['parent_id'] ?: 0 ?>"
                                    data-name="<?= strtolower(esc($unit['name'])) ?>"
                                    style="padding-left: <?= ($unit['level'] * 24 + 16) ?>px; <?= $unit['parent_id'] ? 'display: none;' : '' ?>">
                                    <div class="flex items-center gap-2 pr-2">
                                        <?php if ($unit['has_children']): ?>
                                            <button type="button" class="js-tree-toggle text-text-muted hover:text-text transition-transform transform -rotate-90 focus:outline-none" onclick="toggleUnitTree(this, <?= $unit['id'] ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        <?php else: ?>
                                            <div class="w-4 h-4"></div>
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
                                        <!-- Trash icon (icon-only button): deletes this unit. Any staff assigned to it get their unit un-set, not blocked. Sub-units are cascade-deleted -->
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                            <li id="units-empty-state" class="hidden px-4 py-8 text-center text-xs font-bold text-text-muted italic">No units matched your search.</li>
                        </ul>
                    </div>
                    <?= form_open('account/unit/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0', 'data-ajax' => 'add-unit']) ?>
                        <input type="text" name="name" required placeholder="New Unit Name" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2.5 lg:py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex gap-2">
                            <select name="parent_id" id="select-unit-parent" class="flex-1 bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2 py-2.5 lg:py-2 text-xs text-text outline-none cursor-pointer">
                                <option value="">No Parent (Top Level)</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2.5 lg:py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>

            </div>
        </div>
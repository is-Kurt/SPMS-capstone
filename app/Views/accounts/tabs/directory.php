<div id="tab-content-directory" class="tab-content <?= $activeTab === 'directory' ? 'flex' : 'hidden' ?> flex-col lg:flex-row lg:absolute lg:inset-0 bg-transparent lg:bg-surface">
    
    <!-- MOBILE FILTER OVERLAY -->
    <div id="mobile-filter-overlay" class="fixed inset-0 z-[115] bg-black/50 hidden lg:hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- SIDEBAR FILTERS (Left) -->
    <div id="directory-sidebar" class="fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
        <div class="px-6 py-4 border-b border-surface-border flex justify-between items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800">
            <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filters</h2>
            <button type="button" id="close-mobile-filters" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4">
            <!-- Search -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <input type="text" id="filter-search" placeholder="Search users by name or email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>

            <!-- Roles Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Roles</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <?php foreach ($roles as $role): ?>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                            <input type="checkbox" name="filter_role[]" value="<?= esc($role['name']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                            <?= esc($role['name']) ?>
                        </label>
                    <?php endforeach; ?>
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
                                            <input type="checkbox" name="filter_dept[]" value="<?= esc($unit['name']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
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
                            <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos['title'])) ?>">
                                <input type="checkbox" name="filter_pos[]" value="<?= esc($pos['title']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
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

            <!-- Status Filter -->
            <div class="shrink-0">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Status</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="filter_status[]" value="1" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Active Accounts
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="filter_status[]" value="0" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Disabled Accounts
                    </label>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-surface-border shrink-0 bg-surface lg:bg-transparent">
            <button type="button" id="clear-directory-filters" class="w-full py-2 text-xs font-bold text-text-muted hover:text-text border border-surface-border rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                Clear All Filters
            </button>
        </div>
    </div>

    <!-- MAIN CONTENT (Right) -->
    <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface relative">
        <!-- Mobile Filter Toggle Bar -->
        <div class="lg:hidden p-4 mb-2 bg-surface border-b border-surface-border shrink-0 flex items-center justify-between">
            <span class="text-xs font-bold text-text">Directory <span class="text-text-muted font-normal">(<span id="mobile-directory-count"><?= count($users) ?></span> users)</span></span>
            <button type="button" id="open-mobile-filters" class="flex items-center gap-2 text-xs font-bold text-accent bg-accent/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filters
            </button>
        </div>

        <div class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border shadow-sm" data-frozen-header>
                <table class="w-full text-left border-collapse table-fixed">
                    <colgroup>
                        <col style="width:30%">
                        <col style="width:16%">
                        <col style="width:28%">
                        <col style="width:12%">
                        <col style="width:14%">
                    </colgroup>
                    <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted">
                        <tr>
                            <th class="px-6 py-4">Name / Email</th>
                            <th class="px-6 py-4 min-w-[90px]">Role</th>
                            <th class="px-6 py-4">Department / Position</th>
                            <th class="px-6 py-4 min-w-[80px] text-center">Status</th>
                            <th class="px-6 py-4 min-w-[190px] text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 lg:bg-surface" data-frozen-body>
                <table class="w-full text-left border-collapse block lg:table lg:table-fixed">
                    <colgroup>
                        <col style="width:30%">
                        <col style="width:16%">
                        <col style="width:28%">
                        <col style="width:12%">
                        <col style="width:14%">
                    </colgroup>
                    <tbody class="block lg:table-row-group divide-y divide-surface-border" id="user-table-body">
                        <?php foreach ($users as $u): ?>
                            <tr class="user-dir-row block lg:table-row bg-surface p-4 lg:p-0 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors group <?= (isset($u['is_active']) && $u['is_active'] == 0) ? 'opacity-60' : '' ?>"
                                data-name="<?= strtolower(esc($u['first_name'] . ' ' . $u['last_name'])) ?>"
                                data-email="<?= strtolower(esc($u['email'])) ?>"
                                data-role="<?= esc($u['role_name'] ?? 'No Role') ?>"
                                data-dept="<?= esc($u['department'] ?? 'No Unit') ?>"
                                data-position="<?= esc($u['position'] ?? 'No Position') ?>"
                                data-status="<?= $u['is_active'] ?? 1 ?>">
                                
                                <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                    <div class="flex flex-col min-w-0 pr-4">
                                        <span class="text-sm font-bold text-text truncate"><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                        <span class="text-[10px] font-bold text-text-muted tracking-widest truncate"><?= esc($u['email']) ?></span>
                                    </div>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 lg:min-w-[90px]">
                                    <div class="flex justify-between items-center lg:block gap-4">
                                        <div>
                                            <?php if ($u['id'] != session()->get('user_id')): ?>
                                                <?php $currentRoleId = explode(',', $u['role_id'] ?? '')[0] ?? null; ?>
                                                <?= form_open('account/update-role', ['class' => 'inline-block', 'data-ajax' => 'change-role']) ?>
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <div class="relative inline-block w-max">
                                                        <select name="role_id" onchange="this.form.requestSubmit()" data-previous="<?= esc($currentRoleId) ?>"
                                                                class="js-role-select appearance-none pl-3 pr-7 py-1.5 rounded-lg text-[10px] font-black bg-surface lg:bg-white dark:bg-zinc-900 [color-scheme:light] dark:[color-scheme:dark] border border-surface-border uppercase tracking-widest text-text cursor-pointer focus:outline-none focus:border-accent">
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?= $role['id'] ?>" <?= $currentRoleId == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-text-muted">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                        </div>
                                                    </div>
                                                <?= form_close() ?>
                                            <?php else: ?>
                                                <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest lg:w-auto">
                                                    <?= esc($u['role_name'] ?? 'No Role') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-col min-w-0 lg:hidden text-right">
                                            <span class="text-xs font-bold text-text truncate"><?= esc($u['department'] ?? 'No Unit') ?></span>
                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position'] ?? 'No Position') ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden lg:table-cell px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-text truncate"><?= esc($u['department'] ?? 'No Unit') ?></span>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position'] ?? 'No Position') ?></span>
                                    </div>
                                </td>

                                <td class="hidden lg:table-cell px-6 py-4 text-center lg:min-w-[80px]">
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <?= form_open('account/toggle', ['class' => 'inline', 'data-ajax' => 'toggle-status']) ?>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest cursor-pointer transition-colors focus:outline-none <?= $u['is_active'] == 1 ? 'text-success-500 hover:text-success-600' : 'text-danger-500 hover:text-danger-600' ?>">
                                                <?= $u['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                            </button>
                                        <?= form_close() ?>
                                    <?php else: ?>
                                        <span class="js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest <?= $u['is_active'] == 1 ? 'text-success-500' : 'text-danger-500' ?>">
                                            <?= $u['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right lg:min-w-[100px]">
                                    <div class="flex items-center justify-between lg:justify-end gap-2 w-full">
                                        <div class="flex-1 lg:hidden">
                                            <?php if ($u['id'] != session()->get('user_id')): ?>
                                                <?= form_open('account/toggle', ['class' => 'w-full', 'data-ajax' => 'toggle-status']) ?>
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="js-status-badge-mobile w-full px-3 py-2.5 rounded-lg text-xs font-bold border border-transparent transition-colors cursor-pointer text-center <?= $u['is_active'] == 1 ? 'text-success-600 dark:text-success-500 bg-success-50 dark:bg-success-500/10 hover:bg-success-100 dark:hover:bg-success-500/20' : 'text-danger-500 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-100 dark:hover:bg-danger-500/20' ?>">
                                                        <?= $u['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                                    </button>
                                                <?= form_close() ?>
                                            <?php else: ?>
                                                <div class="js-status-badge-mobile w-full px-3 py-2.5 rounded-lg text-xs font-bold text-center border border-transparent <?= $u['is_active'] == 1 ? 'text-success-600 dark:text-success-500 bg-success-50 dark:bg-success-500/10' : 'text-danger-500 bg-danger-50 dark:bg-danger-500/10' ?>">
                                                    <?= $u['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($u['id'] != session()->get('user_id')): ?>
                                            <?= form_open('account', [
                                                'class' => 'flex-1 lg:flex-none inline-block',
                                                'data-ajax' => 'delete-user',
                                                'data-confirm' => 'Are you sure you want to permanently delete this user?',
                                                'data-confirm-title' => 'Delete User Account',
                                                'data-confirm-text' => 'Delete Permanently'
                                            ]) ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="w-full lg:w-auto px-3 py-2.5 lg:p-2 rounded-lg text-xs font-bold text-danger-500 lg:text-danger-400 lg:hover:text-danger-600 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-100 dark:hover:bg-danger-500/20 lg:bg-transparent dark:lg:bg-transparent lg:hover:bg-danger-100 dark:lg:hover:bg-danger-500/20 border border-transparent opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-all cursor-pointer text-center flex justify-center items-center gap-1" title="Delete User">
                                                    <span class="lg:hidden">Delete</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden lg:block h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            <?= form_close() ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr id="empty-filter-state" class="lg:table-row" style="display:none;">
                            <td colspan="5" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic bg-surface">
                                No users matched your specific search criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Controls -->
            <div class="px-6 py-4 border-t border-surface-border flex justify-between items-center bg-surface shrink-0 hidden" id="directory-pagination">
                <div class="text-xs font-bold text-text-muted">
                    Showing <span id="dir-page-start">0</span> to <span id="dir-page-end">0</span> of <span id="dir-page-total">0</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="js-page-prev px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Prev</button>
                    <button type="button" class="js-page-next px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">Next</button>
                </div>
            </div>
            
        </div>
    </div>

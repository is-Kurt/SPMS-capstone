import os
import re

filepath = r'c:\laragon\www\SPMS\app\Views\accounts\tabs\directory.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Make all filters Accordions.
# Also remove the max-heights and internal scrollbars completely.

# 1. Search (leave as is, but maybe add border-b for separation)
search_target = """            <!-- Search -->
            <div class="shrink-0">
                <input type="text" id="filter-search" placeholder="Search users by name or email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>"""
search_repl = """            <!-- Search -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <input type="text" id="filter-search" placeholder="Search users by name or email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>"""
content = content.replace(search_target, search_repl)

# 2. Roles Accordion
roles_regex = r'<!-- Roles Filter -->.*?</div>\s*</div>'
roles_repl = """<!-- Roles Filter -->
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
            </div>"""
content = re.sub(roles_regex, roles_repl, content, flags=re.DOTALL)

# 3. Units Accordion (remove max-h, keep tree logic)
units_regex = r'<!-- Units Filter with mini-search -->.*?<!-- Positions Filter with mini-search -->'
units_repl = """<!-- Units Filter with mini-search -->
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
                    <div id="units-checkbox-list">
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
                </div>
            </div>

            <!-- Positions Filter with mini-search -->"""
content = re.sub(units_regex, units_repl, content, flags=re.DOTALL)

# 4. Positions Accordion
positions_regex = r'<!-- Positions Filter with mini-search -->.*?<!-- Status Filter -->'
positions_repl = """<!-- Positions Filter with mini-search -->
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
                    <div class="space-y-1" id="positions-checkbox-list">
                        <?php foreach ($positions as $pos): ?>
                            <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos['title'])) ?>">
                                <input type="checkbox" name="filter_pos[]" value="<?= esc($pos['title']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                <span class="truncate"><?= esc($pos['title']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Status Filter -->"""
content = re.sub(positions_regex, positions_repl, content, flags=re.DOTALL)

# 5. Status Accordion
status_regex = r'<!-- Status Filter -->.*?</div>\s*</div>'
status_repl = """<!-- Status Filter -->
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
            </div>"""
content = re.sub(status_regex, status_repl, content, flags=re.DOTALL)

# Remove the `space-y-6` from the main sidebar wrapper and make it `space-y-4` since we have `pb-4` borders now.
# Wait, I added `pb-4 border-b` to the divs themselves. If I keep `space-y-6`, the gaps will be massive. Let's just remove `space-y-6`.
content = content.replace('p-4 flex-1 overflow-y-auto custom-scrollbar space-y-6', 'p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-4')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated to accordions.")

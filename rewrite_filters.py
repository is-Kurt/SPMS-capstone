import os
import re

filepath = r'c:\laragon\www\SPMS\app\Views\accounts\tabs\directory.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the sidebar layout to ensure it doesn't scroll itself, but the children scroll.
# Actually, the sidebar container is `flex-1 overflow-y-auto custom-scrollbar space-y-6`.
# We'll leave `overflow-y-auto` on it just in case screen is super small, but we will constrain the children.
# Roles filter:
roles_target = """<div class="space-y-1">
                    <?php foreach ($roles as $role): ?>"""
roles_repl = """<div class="space-y-1 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                    <?php foreach ($roles as $role): ?>"""
content = content.replace(roles_target, roles_repl)

# Units filter: Replace the entire units block.
units_block_regex = r'<!-- Units Filter with mini-search -->.*?<!-- Positions Filter with mini-search -->'
units_replacement = """<!-- Units Filter with mini-search -->
            <div class="flex flex-col h-full max-h-64">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2 shrink-0">Departments / Units</h3>
                <div class="relative mb-2 shrink-0">
                    <input type="text" id="mini-search-units" placeholder="Find unit..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <div class="overflow-y-auto custom-scrollbar pr-1 flex-1" id="units-checkbox-list">
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
                                        <button type="button" class="unit-toggle text-text-muted hover:text-text p-0.5 shrink-0 transition-transform duration-200" onclick="
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
                                    <div class="unit-children border-l border-surface-border ml-2 pl-1 mt-0.5">
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

            <!-- Positions Filter with mini-search -->"""
content = re.sub(units_block_regex, units_replacement, content, flags=re.DOTALL)

# Positions filter: Make it a flex column that has its own scrolling.
pos_block_regex = r'<!-- Positions Filter with mini-search -->.*?<!-- Status Filter -->'
pos_replacement = """<!-- Positions Filter with mini-search -->
            <div class="flex flex-col h-full max-h-64">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2 shrink-0">Positions</h3>
                <div class="relative mb-2 shrink-0">
                    <input type="text" id="mini-search-positions" placeholder="Find position..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <div class="overflow-y-auto custom-scrollbar pr-1 flex-1 space-y-1" id="positions-checkbox-list">
                    <?php foreach ($positions as $pos): ?>
                        <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos['title'])) ?>">
                            <input type="checkbox" name="filter_pos[]" value="<?= esc($pos['title']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                            <span class="truncate"><?= esc($pos['title']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status Filter -->"""
content = re.sub(pos_block_regex, pos_replacement, content, flags=re.DOTALL)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated directory.php")

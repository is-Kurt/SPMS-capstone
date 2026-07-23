import os

filepath = r'c:\laragon\www\SPMS\app\Views\accounts\tabs\directory.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the start of the table header
header_start = content.find('<div class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border shadow-sm" data-frozen-header>')

if header_start == -1:
    print("Failed to find header_start")
    exit(1)

new_sidebar_html = """<div id="tab-content-directory" class="tab-content <?= $activeTab === 'directory' ? 'flex' : 'hidden' ?> flex-col lg:flex-row lg:absolute lg:inset-0 bg-transparent lg:bg-surface">
    
    <!-- MOBILE FILTER OVERLAY -->
    <div id="mobile-filter-overlay" class="fixed inset-0 z-[60] bg-black/50 hidden lg:hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- SIDEBAR FILTERS (Left) -->
    <div id="directory-sidebar" class="fixed inset-y-0 left-0 z-[70] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static lg:block shrink-0 flex flex-col h-full">
        <div class="p-4 border-b border-surface-border flex justify-between items-center shrink-0">
            <h2 class="text-sm font-black text-text uppercase tracking-widest">Filters</h2>
            <button type="button" id="close-mobile-filters" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar space-y-6">
            <!-- Search -->
            <div>
                <input type="text" id="filter-search" placeholder="Search users by name or email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>

            <!-- Roles Filter -->
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Roles</h3>
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
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Departments / Units</h3>
                <div class="relative mb-2">
                    <input type="text" id="mini-search-units" placeholder="Find unit..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar" id="units-checkbox-list">
                    <?php
                    $unitTree = [];
                    foreach ($units as $u) {
                        $pid = $u['parent_id'] ?: 0;
                        $unitTree[$pid][] = $u;
                    }
                    $flatten = function($parentId, $level) use (&$flatten, &$unitTree) {
                        if (!isset($unitTree[$parentId])) return;
                        foreach ($unitTree[$parentId] as $unit) {
                            $padding = $level * 12; // 12px per level
                            ?>
                            <label class="unit-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-name="<?= strtolower(esc($unit['name'])) ?>">
                                <div style="width: <?= $padding ?>px" class="shrink-0"></div>
                                <input type="checkbox" name="filter_dept[]" value="<?= esc($unit['name']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                                <span class="truncate"><?= esc($unit['name']) ?></span>
                            </label>
                            <?php
                            $flatten($unit['id'], $level + 1);
                        }
                    };
                    $flatten(0, 0);
                    ?>
                </div>
            </div>

            <!-- Positions Filter with mini-search -->
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Positions</h3>
                <div class="relative mb-2">
                    <input type="text" id="mini-search-positions" placeholder="Find position..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar" id="positions-checkbox-list">
                    <?php foreach ($positions as $pos): ?>
                        <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos['title'])) ?>">
                            <input type="checkbox" name="filter_pos[]" value="<?= esc($pos['title']) ?>" class="checkbox directory-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                            <span class="truncate"><?= esc($pos['title']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Status</h3>
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

        """

new_content = new_sidebar_html + content[header_start:]

# Close the new MAIN CONTENT wrapper at the very end of the file.
# The file originally ended with:
#                     </tbody>
#                 </table>
#             </div>
#         </div>
# We need to add one more </div> to close <div class="flex-1 flex flex-col...">
new_content += "\n    </div>\n"

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Success")

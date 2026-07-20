    <?php 
    $sysRole = $sysRole ?? session()->get('role');
    $firstTabKey = array_key_first($tabs);
?>

<?php if (!$activeFolder): ?>
    <div class="flex-1 border-2 border-dashed border-surface-border rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text mb-1">Select a Folder</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
    </div>

<?php else: ?>

<div class="relative flex flex-col shrink-0 mb-6 px-1" id="folder-dropdown-container">
    
    <button onclick="toggleFolderDropdown()" class="flex items-center justify-between w-full text-left group cursor-pointer lg:cursor-default">
        <h1 class="text-3xl font-black tracking-tight text-text truncate group-hover:text-accent lg:group-hover:text-text transition-colors">
            <?= esc($activeFolder['title']) ?>
        </h1>
        <svg id="folder-dropdown-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0 text-text-muted transition-transform lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    
    <p class="text-[10px] md:text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">
        Manage your assigned reviews
    </p>

    <div id="folder-dropdown-menu" class="hidden absolute top-full left-0 mt-2 w-full max-w-sm bg-surface border border-surface-border rounded-xl shadow-xl z-[40] max-h-64 overflow-y-auto lg:hidden">
        <?= view('document/_folder_rows', ['folders' => $sidebarFolders ?? [], 'selectedFolderId' => null]) ?>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-3 mb-6 px-1 shrink-0">
    
    <div class="flex-1 relative min-w-0">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input type="text" id="search-rating" placeholder="Search employee name..." 
            class="w-full bg-surface border border-surface-border shadow-sm rounded-xl pl-10 pr-4 py-2.5 text-sm focus:border-accent focus:ring-1 focus:outline-none text-text transition-all">
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0">
        <div class="w-full sm:w-48 relative shrink-0">
            <select id="filter-unit" class="w-full bg-surface border border-surface-border shadow-sm rounded-xl pl-4 pr-10 py-2.5 text-sm focus:border-accent outline-none text-text appearance-none cursor-pointer truncate">
                <option value="">All Units / Depts</option>
                <?php foreach($filterUnits as $unit): ?>
                    <option value="<?= esc($unit) ?>"><?= esc($unit) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
        </div>

        <div class="w-full sm:w-48 relative shrink-0">
            <select id="filter-pos" class="w-full bg-surface border border-surface-border shadow-sm rounded-xl pl-4 pr-10 py-2.5 text-sm focus:border-accent outline-none text-text appearance-none cursor-pointer truncate">
                <option value="">All Positions</option>
                <?php foreach($filterPositions as $pos): ?>
                    <option value="<?= esc($pos) ?>"><?= esc($pos) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
        </div>
    </div>

    <div class="w-full sm:w-40 relative shrink-0">
            <select id="filter-type" class="w-full bg-surface border border-surface-border shadow-sm rounded-xl pl-4 pr-10 py-2.5 text-sm focus:border-accent outline-none text-text appearance-none cursor-pointer truncate">
                <option value="">All Types</option>
                <option value="1">Teaching</option>
                <option value="0">Non-Teaching</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
        </div>
</div>

<div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 overflow-x-auto custom-scrollbar">
    <?php foreach ($tabs as $key => $group): ?>
        <button id="tab-btn-<?= $key ?>" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all whitespace-nowrap <?= ($key === $firstTabKey) ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> cursor-pointer"
                onclick="switchTab('<?= $key ?>', this)">
            <?= esc($group['label']) ?>
            <span class="ml-1.5 px-2 py-0.5 rounded-full <?= ($key === $firstTabKey) ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors">
                <?= count($group['folders']) ?>
            </span>
        </button>
    <?php endforeach; ?>
</div>

<div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 lg:min-h-[400px] lg:overflow-hidden relative pb-10 lg:pb-0">
    
    <?php foreach ($tabs as $key => $group): ?>
        <div id="tab-content-<?= $key ?>" class="tab-content <?= ($key === $firstTabKey) ? 'flex flex-col lg:absolute lg:inset-0' : 'hidden' ?>">
            
            <?php
                $colWidths = ['26%'];
                if ($sysRole === 'Admin') { $colWidths[] = '15%'; }
                $colWidths = array_merge($colWidths, ['15%', '10%', '12%', '18%']);
            ?>
            <div class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border shadow-sm" data-frozen-header>
                <table class="w-full text-left border-collapse table-fixed">
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
                            <th class="px-6 py-4 min-w-[60px] text-center">Score</th>
                            <th class="px-6 py-4 min-w-[70px] text-center">Adjectival Rating</th>
                            <th class="px-6 py-4 min-w-[135px] text-right">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 w-full p-2 lg:p-0" data-frozen-body>
                <table class="w-full text-left border-collapse block lg:table lg:table-fixed">
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
                                                ?>
                                                <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border <?= $c ?> whitespace-nowrap">
                                                    <?= esc($row['folder_status']) ?>
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
                                            <?= esc($row['folder_status']) ?>
                                        </span>
                                    </td>

                                    <td class="block lg:table-cell px-0 lg:px-6 py-3 lg:py-4 text-left lg:text-center mt-2 lg:mt-0 border-t border-surface-border lg:border-none lg:min-w-[60px]">
                                        <div class="flex items-center lg:justify-center gap-3">
                                            <span class="lg:hidden text-[10px] font-black text-text-muted uppercase tracking-widest">Score:</span>
                                            
                                            <?php if (!is_null($row['final_rating'])): ?>
                                                <span class="text-sm font-black text-text">
                                                    <?= number_format($row['final_rating'], 3) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">--</span>
                                            <?php endif; ?>

                                            <div class="lg:hidden flex items-center gap-3 border-l border-surface-border pl-3">
                                                <?php if (!is_null($row['final_rating'])): ?>
                                                    <span class="adjective-badge px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent" data-score="<?= $row['final_rating'] ?>"></span>
                                                <?php else: ?>
                                                    <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">N/A</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="hidden lg:table-cell px-6 py-4 text-center lg:min-w-[70px]">
                                        <?php if (!is_null($row['final_rating'])): ?>
                                            <span class="adjective-badge px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent whitespace-nowrap" data-score="<?= $row['final_rating'] ?>"></span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic whitespace-nowrap">Not Rated</span>
                                        <?php endif; ?>
                                    </td>

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
</div>

<script>
    // System Adjectival Logic
    function getAdjectivalRating(score) {
        if (score >= 4.30) return 'O';  
        if (score >= 3.54) return 'VS'; 
        if (score >= 2.70) return 'S';  
        if (score >= 1.50) return 'US'; 
        return 'P';                     
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
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'flex-col', 'lg:absolute', 'lg:inset-0');
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
            target.classList.add('flex', 'flex-col', 'lg:absolute', 'lg:inset-0');
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

    // REAL-TIME SEARCH AND FILTER LOGIC
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-rating');
        const unitSelect  = document.getElementById('filter-unit');
        const posSelect   = document.getElementById('filter-pos');
        const typeSelect  = document.getElementById('filter-type'); // <-- Grab the new dropdown
        
        function filterRatings() {
            const searchTerm   = searchInput.value.toLowerCase();
            const selectedUnit = unitSelect.value.toLowerCase();
            const selectedPos  = posSelect.value.toLowerCase();
            const selectedType = typeSelect.value; // <-- Grab the selected type ("1", "0", or "")
            
            document.querySelectorAll('.rating-card').forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const unit = (card.getAttribute('data-unit') || '').toLowerCase();
                const pos  = (card.getAttribute('data-pos') || '').toLowerCase();
                const type = card.getAttribute('data-teaching') || '0';
                
                const matchesSearch = name.includes(searchTerm);
                const matchesUnit   = selectedUnit === "" || unit.includes(selectedUnit);
                const matchesPos    = selectedPos === ""  || pos.includes(selectedPos);
                
                // If typeSelect is empty (""), show all. Otherwise, exact match the 1 or 0
                const matchesType   = selectedType === "" || type === selectedType;
                
                if (matchesSearch && matchesUnit && matchesPos && matchesType) {
                    card.style.display = ''; 
                } else {
                    card.style.display = 'none'; 
                }
            });
        }
        
        searchInput.addEventListener('input', filterRatings);
        unitSelect.addEventListener('change', filterRatings);
        posSelect.addEventListener('change', filterRatings);
        typeSelect.addEventListener('change', filterRatings); // <-- Listen for changes
    });
</script>

<?php endif; ?>
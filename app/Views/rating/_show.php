<?php 
    $viewerRole = session()->get('role');
    $currentQuery = current_url(true)->getQuery();

    // 1. Define the Evaluation Tiers based on your DB Enums
    $opcrRoles = ['Vice President', 'Campus Administrator'];
    $dpcrRoles = ['Dean', 'Director', 'Head of Office'];
    $ipcrRoles = ['Employee'];

    // 2. Initialize the Tab Groups
    $tabGroups = [
        'opcr' => ['label' => 'OPCR', 'sub' => 'VPs & Admins', 'users' => []],
        'dpcr' => ['label' => 'DPCR', 'sub' => 'Deans & Directors', 'users' => []],
        'ipcr' => ['label' => 'IPCR', 'sub' => 'Employees', 'users' => []]
    ];

    // 3. Sort fetched users and extract unique departments
    $uniqueDepartments = [];
    if (!empty($userRows)) {
        foreach ($userRows as $userId => $data) {
            $uRole = $data['info']['role'] ?? '';
            $dept  = $data['info']['department'] ?? '';
            
            // Tally up users per department
            if ($dept) {
                $uniqueDepartments[$dept] = ($uniqueDepartments[$dept] ?? 0) + 1;
            }

            // Group into tiers
            if (in_array($uRole, $opcrRoles)) {
                $tabGroups['opcr']['users'][$userId] = $data;
            } elseif (in_array($uRole, $dpcrRoles)) {
                $tabGroups['dpcr']['users'][$userId] = $data;
            } else {
                $tabGroups['ipcr']['users'][$userId] = $data;
            }
        }
    }
    ksort($uniqueDepartments); // Alphabetize the departments

    // 4. Determine which tabs to render
    $visibleTabs = [];
    if ($viewerRole === 'Admin') {
        $visibleTabs['opcr'] = $tabGroups['opcr'];
        $visibleTabs['dpcr'] = $tabGroups['dpcr'];
        $visibleTabs['ipcr'] = $tabGroups['ipcr'];
    } elseif (in_array($viewerRole, $opcrRoles)) {
        $visibleTabs['dpcr'] = $tabGroups['dpcr'];
    } else {
        $visibleTabs['ipcr'] = $tabGroups['ipcr'];
    }

    $firstTabKey = array_key_first($visibleTabs);
    $showDepartmentFilter = in_array($viewerRole, array_merge(['Admin'], $opcrRoles)) && !empty($uniqueDepartments);
?>

<?php if (!$activeFolder): ?>
    <div class="flex-1 border-2 border-dashed border-surface-border rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text mb-1">Select an Evaluation Batch</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose a folder from the sidebar to view user submissions for that batch.</p>
    </div>
<?php else: ?>

    <div class="flex items-center gap-4 shrink-0 mb-6">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-text"><?= esc($activeFolder['title']) ?></h1>
            <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest"><?= esc($viewTitle) ?></p>
        </div>
    </div>

    <?php if (count($visibleTabs) > 1): ?>
        <div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 overflow-x-auto custom-scrollbar">
            <?php foreach ($visibleTabs as $key => $group): ?>
                <button id="tab-btn-<?= $key ?>" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all whitespace-nowrap <?= ($key === $firstTabKey) ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> cursor-pointer"
                        onclick="switchTab('<?= $key ?>', this)">
                    <?= $group['label'] ?>
                    <span class="ml-1.5 px-2 py-0.5 rounded-full <?= ($key === $firstTabKey) ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors">
                        <?= count($group['users']) ?>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden relative">
        
        <?php foreach ($visibleTabs as $key => $group): ?>
            <div id="tab-content-<?= $key ?>" class="tab-content <?= ($key === $firstTabKey) ? 'flex flex-col absolute inset-0' : 'hidden' ?>">
                
                <?php if ($showDepartmentFilter && ($key === 'ipcr' || $key === 'dpcr')): ?>
                    <div class="flex items-center gap-2 px-6 py-3 border-b border-surface-border bg-zinc-50/50 dark:bg-zinc-800/30 overflow-x-auto custom-scrollbar shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest text-text-muted mr-2 shrink-0">Filter:</span>
                        
                        <button onclick="applyDeptFilter('all', this, '<?= $key ?>')" 
                                class="dept-pill-<?= $key ?> px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 cursor-pointer shadow-sm">
                            All Departments
                        </button>
                        
                        <?php foreach($uniqueDepartments as $dept => $count): ?>
                            <button onclick="applyDeptFilter('<?= esc(addslashes($dept)) ?>', this, '<?= $key ?>')" 
                                    class="dept-pill-<?= $key ?> px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 bg-white border border-surface-border text-text hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 cursor-pointer">
                                <?= esc($dept) ?> <span class="opacity-50 ml-1"><?= $count ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-y-auto overflow-x-auto custom-scrollbar flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-20 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border shadow-sm">
                            <tr>
                                <th class="px-6 py-4 min-w-[250px]">User / Position</th>
                                <?php if ($viewerRole === 'Admin'): ?>
                                    <th class="px-6 py-4 min-w-[150px]">Department</th>
                                <?php endif; ?>
                                <?php foreach ($docHeaders as $header): ?>
                                    <th class="px-6 py-4 text-center min-w-[150px]"><?= esc($header['title']) ?></th>
                                <?php endforeach; ?>
                                <th class="px-6 py-4 text-center min-w-[150px] border-l border-surface-border text-emerald-700 dark:text-emerald-400">Final Rating</th>
                                <th class="px-6 py-4 text-right min-w-[300px]">Admin Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border" id="tbody-<?= $key ?>">
                            <?php if (empty($group['users'])): ?>
                                <tr>
                                    <td colspan="100%" class="px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                        No users found for the <?= $group['label'] ?> evaluation tier.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($group['users'] as $userId => $data): ?>
                                    <tr class="user-row-<?= $key ?> hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors" data-department="<?= esc($data['info']['department'] ?? '') ?>">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col min-w-0">
                                                <a href="<?= site_url('ratings?folder_id=' . $activeFolder['id'] . '&sub_folder=' . $data['info']['folder_id']) ?>" class="text-sm font-bold text-text hover:text-accent hover:underline transition-colors truncate">
                                                    <?= esc($data['info']['username']) ?>
                                                </a>
                                                <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                                    <?= esc($data['info']['position'] ?? 'No Position') ?>
                                                </span>
                                            </div>
                                        </td>

                                        <?php if ($viewerRole === 'Admin'): ?>
                                            <td class="px-6 py-4">
                                                <span class="text-xs font-semibold text-text-muted"><?= esc($data['info']['department'] ?? 'N/A') ?></span>
                                            </td>
                                        <?php endif; ?>

                                        <?php foreach ($docHeaders as $header): ?>
                                            <?php 
                                                $scoreData = $data['scores'][$header['title']] ?? null; 
                                                $status = $scoreData['status'] ?? 'draft';
                                            ?>
                                            <td class="px-6 py-4 text-center">
                                                <?php if ($status === 'draft'): ?>
                                                    <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">
                                                        Not Submitted
                                                    </span>
                                                <?php else: ?>
                                                    <a href="<?= site_url('submission?Id=' . $scoreData['doc_id'] . '&return=' . urlencode($currentQuery)) ?>" 
                                                    class="inline-flex flex-col items-center gap-1 group/badge transition-all">
                                                        <?php if ($status === 'evaluated'): ?>
                                                            <span class="px-3 py-1 rounded-lg text-xs font-black bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-500/20 group-hover/badge:bg-emerald-600 group-hover/badge:text-white transition-colors">
                                                                <?= number_format($scoreData['rating'], 3) ?>
                                                            </span>
                                                            <span class="rating-badge opacity-60 text-[9px]" data-score="<?= $scoreData['rating'] ?>"></span>
                                                        <?php else: ?>
                                                            <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-amber-50 dark:bg-amber-500/10 text-amber-600 border border-amber-200 dark:border-amber-500/20 group-hover/badge:scale-105 transition-transform uppercase tracking-tighter">
                                                                Review & Rate
                                                            </span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td class="px-6 py-4 text-center border-l border-surface-border bg-emerald-50/30 dark:bg-emerald-500/5">
                                            <?php if (isset($data['info']['final_rating'])): ?>
                                                <div class="flex flex-col items-center gap-1 group/final">
                                                    <span class="px-3 py-1.5 rounded-lg text-sm font-black bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                                        <?= number_format($data['info']['final_rating'], 3) ?>
                                                    </span>
                                                    <span class="rating-badge opacity-60 text-[10px] font-bold uppercase tracking-widest" data-score="<?= $data['info']['final_rating'] ?>"></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">N/A</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-6 py-4 relative group">
                                            <div class="flex items-center gap-2">
                                                <input type="text" 
                                                    value="" 
                                                    placeholder="Add remark..." 
                                                    class="w-full bg-transparent border-b border-transparent focus:border-accent text-sm text-text transition-all px-1 py-1">
                                            </div>
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
    
    <script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const badges = document.querySelectorAll('.rating-badge');
            badges.forEach(badge => {
                const score = parseFloat(badge.getAttribute('data-score'));
                if (!isNaN(score) && typeof getAdjectivalRating === 'function') {
                    const arLabel = getAdjectivalRating(score);
                    badge.innerText = arLabel;
                    const styles = {
                        'O':  ['text-blue-600', 'dark:text-blue-400'],
                        'VS': ['text-emerald-600', 'dark:text-emerald-400'],
                        'S':  ['text-amber-600', 'dark:text-amber-400'],
                        'US': ['text-orange-600', 'dark:text-orange-400'],
                        'P':  ['text-red-600', 'dark:text-red-400']
                    };
                    if (styles[arLabel]) badge.classList.add(...styles[arLabel]);
                    badge.classList.remove('opacity-0');
                }
            });
        });

        // Smart UI Logic
        function switchTab(tabId, btnElement) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex', 'flex-col', 'absolute', 'inset-0');
            });
            
            // Reset all top-level tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-accent', 'text-accent');
                btn.classList.add('border-transparent', 'text-text-muted');
                const badge = btn.querySelector('.tab-badge');
                if(badge) {
                    badge.classList.remove('bg-accent/10', 'text-accent');
                    badge.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                }
            });

            // Activate Target Content
            const target = document.getElementById('tab-content-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('flex', 'flex-col', 'absolute', 'inset-0');
            }

            // Activate Tab Button
            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'text-text-muted');
                btnElement.classList.add('border-accent', 'text-accent');
                const activeBadge = btnElement.querySelector('.tab-badge');
                if(activeBadge) {
                    activeBadge.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                    activeBadge.classList.add('bg-accent/10', 'text-accent');
                }
            }

            // Optional: Reset department filters when switching tabs
            const allPill = document.querySelector(`.dept-pill-${tabId}`);
            if (allPill) {
                applyDeptFilter('all', allPill, tabId);
            }
        }

        function applyDeptFilter(deptName, clickedBtn, tabKey) {
            // 1. Reset all pills in THIS tab only
            document.querySelectorAll(`.dept-pill-${tabKey}`).forEach(btn => {
                btn.classList.remove('bg-zinc-800', 'text-white', 'dark:bg-zinc-100', 'dark:text-zinc-900', 'shadow-sm');
                btn.classList.add('bg-white', 'border', 'border-surface-border', 'text-text', 'hover:bg-zinc-100', 'dark:bg-zinc-800', 'dark:hover:bg-zinc-700');
            });

            // 2. Highlight clicked pill
            clickedBtn.classList.remove('bg-white', 'border', 'border-surface-border', 'text-text', 'hover:bg-zinc-100', 'dark:bg-zinc-800', 'dark:hover:bg-zinc-700');
            clickedBtn.classList.add('bg-zinc-800', 'text-white', 'dark:bg-zinc-100', 'dark:text-zinc-900', 'shadow-sm');

            // 3. Filter rows in THIS tab only
            document.querySelectorAll(`.user-row-${tabKey}`).forEach(row => {
                if (deptName === 'all' || row.getAttribute('data-department') === deptName) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
<?php endif; ?>
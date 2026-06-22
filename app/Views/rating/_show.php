<?php 
    $sysRole = $sysRole ?? session()->get('role');
    $firstTabKey = array_key_first($tabs);
?>

<div class="flex items-center gap-4 shrink-0 mb-6 px-1">
    <div>
        <h1 class="text-3xl font-black tracking-tight text-text">Collaborative Evaluation Queue</h1>
        <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Manage your assigned reviews</p>
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

<div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden relative">
    
    <?php foreach ($tabs as $key => $group): ?>
        <div id="tab-content-<?= $key ?>" class="tab-content <?= ($key === $firstTabKey) ? 'flex flex-col absolute inset-0' : 'hidden' ?>">
            
            <div class="overflow-y-auto overflow-x-auto custom-scrollbar flex-1">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="sticky top-0 z-20 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border shadow-sm">
                        <tr>
                            <th class="px-6 py-4">User / Position</th>
                            <?php if ($sysRole === 'Admin'): ?>
                                <th class="px-6 py-4">Department</th>
                            <?php endif; ?>
                            <th class="px-6 py-4">Folder Status</th>
                            <th class="px-6 py-4 text-center">Score</th>
                            <th class="px-6 py-4 text-center">Adjectival Rating</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border">
                        <?php if (empty($group['folders'])): ?>
                            <tr>
                                <td colspan="100%" class="px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                    No records found in this queue.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($group['folders'] as $row): ?>
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-bold text-text truncate">
                                                <?= esc($row['username']) ?>
                                            </span>
                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                                <?= esc($row['position'] ?? 'No Position') ?>
                                            </span>
                                        </div>
                                    </td>

                                    <?php if ($sysRole === 'Admin'): ?>
                                        <td class="px-6 py-4">
                                            <span class="text-xs font-semibold text-text-muted"><?= esc($row['department'] ?? 'N/A') ?></span>
                                        </td>
                                    <?php endif; ?>

                                    <td class="px-6 py-4">
                                        <?php 
                                            $s = strtolower($row['folder_status']);
                                            $badgeColors = [
                                                'approved'    => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400',
                                                'submitted'   => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400',
                                                'reevaluate'  => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-500/20 dark:text-orange-400',
                                                'to evaluate' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/20 dark:text-amber-400',
                                                'draft'       => 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400'
                                            ];
                                            $c = $badgeColors[$s] ?? $badgeColors['draft'];
                                        ?>
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?= $c ?>">
                                            <?= esc($row['folder_status']) ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <?php if (!is_null($row['final_rating'])): ?>
                                            <span class="text-sm font-black text-text">
                                                <?= number_format($row['final_rating'], 3) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">--</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <?php if (!is_null($row['final_rating'])): ?>
                                            <span class="adjective-badge px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm border border-transparent" 
                                                  data-score="<?= $row['final_rating'] ?>">
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">Not Rated</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="<?= site_url('ratings/show/' . $row['folder_id']) ?>" 
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-accent hover:bg-accent-hover text-white rounded-xl font-bold text-xs transition-all shadow-md active:scale-95 cursor-pointer">
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
                    'O':  ['bg-blue-100', 'text-blue-700', 'border-blue-200', 'dark:bg-blue-500/20', 'dark:text-blue-400'],
                    'VS': ['bg-emerald-100', 'text-emerald-700', 'border-emerald-200', 'dark:bg-emerald-500/20', 'dark:text-emerald-400'],
                    'S':  ['bg-amber-100', 'text-amber-700', 'border-amber-200', 'dark:bg-amber-500/20', 'dark:text-amber-400'],
                    'US': ['bg-orange-100', 'text-orange-700', 'border-orange-200', 'dark:bg-orange-500/20', 'dark:text-orange-400'],
                    'P':  ['bg-red-100', 'text-red-700', 'border-red-200', 'dark:bg-red-500/20', 'dark:text-red-400']
                };
                
                if (styles[adj]) badge.classList.add(...styles[adj]);
            }
        });
    });

    // Tab Switching Logic
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'flex-col', 'absolute', 'inset-0');
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
            target.classList.add('flex', 'flex-col', 'absolute', 'inset-0');
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
</script>
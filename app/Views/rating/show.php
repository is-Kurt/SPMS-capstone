<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-6 h-[calc(100vh-6rem)]">
    
    <div class="flex items-center gap-4 shrink-0">
        <a href="<?= site_url('rating/departments?Id=' . $rating['id']) ?>" class="p-2 rounded-xl bg-surface border border-surface-border text-text-muted hover:text-accent hover:border-accent/30 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-text"><?= esc($rating['title'] ?? 'Evaluation Batch') ?> - <?= esc($department) ?></h1>
            <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Department Evaluation</p>
        </div>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden">
        
        <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0">
            <div class="col-span-3">User / Position</div>
            <div class="col-span-2">Date Submitted</div>
            <div class="col-span-2">Rating Score</div>
            <div class="col-span-5">Admin Remarks</div>
        </div>

        <div class="overflow-y-auto custom-scrollbar flex-1 relative">
            <div class="divide-y divide-surface-border">
                
                <?php if (empty($userRatings)): ?>
                    <div class="p-8 text-center text-sm text-text-muted italic">No records found.</div>
                <?php else: ?>
                
                    <?php foreach ($userRatings as $row): ?>
                        <div class="grid grid-cols-12 gap-4 px-6 py-3 items-center hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                            
                            <div class="col-span-3 flex flex-col min-w-0">
                                <span class="text-sm font-bold text-text truncate"><?= esc($row['username']) ?></span>
                                <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                    <?= esc($row['position'] ?? 'No Position') ?>
                                </span>
                            </div>

                            <div class="col-span-2 text-xs font-semibold text-text-muted">
                                <?= $row['submitted_at'] ? date('M d, Y', strtotime($row['submitted_at'])) : '<span class="text-zinc-400 italic">Not Submitted</span>' ?>
                            </div>

                            <div class="col-span-2 flex items-center gap-2">
                                <?php if ($row['is_rated']): ?>
                                    <?php $score = (float)($row['final_rating'] ?? 0); ?>
                                    
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-zinc-100 dark:bg-zinc-800 text-text">
                                        <?= number_format($score, 3) ?>
                                    </span>
                                    
                                    <span class="rating-badge px-2.5 py-1 rounded-lg text-xs font-black opacity-0 transition-opacity duration-300" data-score="<?= $score ?>">
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-zinc-200 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">
                                        Unrated
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="col-span-5 relative">
                                <input type="text" 
                                       value="<?= esc($row['remarks'] ?? '') ?>" 
                                       placeholder="Add a remark..."
                                       class="w-full bg-transparent border-b border-transparent hover:border-zinc-300 dark:hover:border-zinc-700 focus:border-accent focus:ring-0 text-sm text-text transition-all px-1 py-1"
                                       onblur="saveRemark(<?= $row['ur_id'] ?>, this)">
                                
                                <span id="save-indicator-<?= $row['ur_id'] ?>" class="absolute right-2 top-2 text-[10px] font-bold text-emerald-500 opacity-0 transition-opacity">Saved ✓</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                    
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<script>
    const AppConfig = {
        baseUrl: '<?= site_url('rating') ?>',
    };

    document.addEventListener('DOMContentLoaded', () => {
        const badges = document.querySelectorAll('.rating-badge');
        
        badges.forEach(badge => {
            const score = parseFloat(badge.getAttribute('data-score'));
            const arLabel = getAdjectivalRating(score);
            badge.innerText = arLabel;
            
            switch(arLabel) {
                case 'O':  badge.classList.add('bg-blue-100', 'text-blue-600', 'dark:bg-blue-500/20', 'dark:text-blue-400'); break;
                case 'VS': badge.classList.add('bg-emerald-100', 'text-emerald-600', 'dark:bg-emerald-500/20', 'dark:text-emerald-400'); break;
                case 'S':  badge.classList.add('bg-amber-100', 'text-amber-600', 'dark:bg-amber-500/20', 'dark:text-amber-400'); break;
                case 'US': badge.classList.add('bg-orange-100', 'text-orange-600', 'dark:bg-orange-500/20', 'dark:text-orange-400'); break;
                case 'P':  badge.classList.add('bg-red-100', 'text-red-600', 'dark:bg-red-500/20', 'dark:text-red-400'); break;
            }
            
            badge.classList.remove('opacity-0');
        });
    });

    window.addEventListener('beforeunload', () => {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.tagName === 'INPUT' && activeElement.hasAttribute('onblur')) {
            activeElement.blur(); 
        }
    });
</script>

<?= $this->endSection() ?>
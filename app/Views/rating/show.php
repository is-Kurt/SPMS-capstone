
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    // Navigation logic: Determine where the 'Back' button goes based on role
    $role = session()->get('role');
    $backUrl = ($role === 'supervisor') 
        ? site_url('ratings') 
        : site_url('rating/departments?Id=' . $rating['id']);

    // Capture current query (Id and dept) to pass as a return parameter to the editor
    $currentQuery = current_url(true)->getQuery();
?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-6 h-[calc(100vh-6rem)]">
    
    <div class="flex items-center gap-4 shrink-0">
        <a href="<?= $backUrl ?>" class="p-2 rounded-xl bg-surface border border-surface-border text-text-muted hover:text-accent hover:border-accent/30 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-text"><?= esc($rating['title'] ?? 'Evaluation Batch') ?> - <?= esc($department) ?></h1>
            <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Department Evaluation Dashboard</p>
        </div>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar flex-1 relative">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-20 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border">
                    <tr>
                        <th class="px-6 py-4 min-w-[250px]">User / Position</th>
                        <?php foreach ($docHeaders as $header): ?>
                            <th class="px-6 py-4 text-center min-w-[150px]"><?= esc($header['title']) ?></th>
                        <?php endforeach; ?>
                        <th class="px-6 py-4 text-right min-w-[300px]">Admin Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border">
                    <?php foreach ($userRows as $userId => $data): ?>
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold text-text truncate"><?= esc($data['info']['username']) ?></span>
                                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">
                                        <?= esc($data['info']['position']) ?>
                                    </span>
                                </div>
                            </td>

                            <?php foreach ($docHeaders as $header): ?>
                                <?php 
                                    $scoreData = $data['scores'][$header['title']] ?? null; 
                                    // Existence in submission table check[cite: 31]
                                    $isSubmitted = !empty($scoreData['sub_id']); 
                                    $isRated = $scoreData['rated'] ?? false;
                                ?>

                                <td class="px-6 py-4 text-center">
                                    <?php if (!$isSubmitted): ?>
                                        <!-- State 1: Not Submitted -->
                                        <span class="text-[10px] font-bold text-zinc-300 dark:text-zinc-700 uppercase tracking-widest italic">
                                            Not Submitted
                                        </span>
                                    <?php else: ?>
                                        <!-- State 2 & 3: Submitted. Link to the review page -->
                                        <a href="<?= site_url('submission?Id=' . $scoreData['doc_id'] . '&return=' . urlencode($currentQuery)) ?>" 
                                        class="inline-flex flex-col items-center gap-1 group/badge transition-all">
                                            
                                            <?php if ($isRated): ?>
                                                <!-- Rated: Show the numeric score -->
                                                <span class="px-3 py-1 rounded-lg text-xs font-black bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 border border-emerald-200 dark:border-emerald-500/20 group-hover/badge:bg-emerald-600 group-hover/badge:text-white transition-colors">
                                                    <?= number_format($scoreData['rating'], 3) ?>
                                                </span>
                                                <span class="rating-badge opacity-60 text-[9px]" data-score="<?= $scoreData['rating'] ?>"></span>
                                            <?php else: ?>
                                                <!-- Submitted but Pending: "Review & Rate" action[cite: 20] -->
                                                <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-amber-50 dark:bg-amber-500/10 text-amber-600 border border-amber-200 dark:border-amber-500/20 group-hover/badge:scale-105 transition-transform uppercase tracking-tighter">
                                                    Review & Rate
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>

                            <td class="px-6 py-4 relative group">
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                           value="<?= esc($data['info']['remarks'] ?? '') ?>" 
                                           placeholder="Add remark..." 
                                           class="w-full bg-transparent border-b border-transparent focus:border-accent text-sm text-text transition-all px-1 py-1"
                                           onblur="saveRemark(<?= $data['info']['ur_id'] ?>, this)">
                                    
                                    <span id="save-indicator-<?= $data['info']['ur_id'] ?>" class="text-[9px] font-bold text-emerald-500 opacity-0 transition-opacity shrink-0">SAVED ✓</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
            const arLabel = getAdjectivalRating(score); // Global function from functions.js
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
        });
    });

    // Ensure save triggers before leaving if an input is active[cite: 20]
    window.addEventListener('beforeunload', () => {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.tagName === 'INPUT' && activeElement.hasAttribute('onblur')) {
            activeElement.blur(); 
        }
    });
</script>

<?= $this->endSection() ?>
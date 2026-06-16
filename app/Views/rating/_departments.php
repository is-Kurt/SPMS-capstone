<div class="mb-8 shrink-0 flex items-center gap-4">
    <a href="<?= site_url('ratings') ?>" class="p-2 rounded-xl bg-surface border border-surface-border text-text-muted hover:text-accent hover:border-accent/30 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>
    <div>
        <h1 class="text-3xl font-black tracking-tight text-text"><?= esc($rating['title']) ?></h1>
        <p class="text-sm text-text-muted mt-1 font-medium italic">Select a department to view its employee ratings.</p>
    </div>
</div>

<div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-10">
    <?php if (empty($departments)): ?>
        <div class="p-24 text-center bg-surface border border-surface-border rounded-2xl">
            <p class="text-sm text-text-muted font-bold">No regular users found in any departments for this batch.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach($departments as $dept): ?>
                
                <a href="<?= site_url('rating/show?Id=' . $rating['id'] . '&dept=' . urlencode($dept['department'])) ?>"
                    class="relative flex items-center bg-surface border border-surface-border rounded-2xl shadow-sm hover:shadow-md hover:border-accent/30 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-all group p-5 cursor-pointer">
                    
                    <div class="p-3 rounded-xl bg-accent/10 text-accent border border-accent/20 transition-transform group-hover:scale-110 shrink-0 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>

                    <div class="flex flex-col min-w-0 pr-2">
                        <h2 class="text-sm font-bold text-text group-hover:text-accent transition-colors truncate">
                            <?= esc($dept['department']) ?>
                        </h2>
                        <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase mt-0.5">
                            View Users
                        </span>
                    </div>
                </a>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
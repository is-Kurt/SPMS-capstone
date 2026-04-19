<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?= view('components/delete_modal') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col h-[calc(100vh-6rem)]">
    
    <div class="mb-8 shrink-0">
        <h1 class="text-3xl font-black tracking-tight text-text">Rating Directories</h1>
        <p class="text-sm text-text-muted mt-1 font-medium italic">Select an evaluation batch to view employee ratings.</p>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-10">
        <?php if (empty($ratings)): ?>
            <div class="p-24 text-center bg-surface border border-surface-border rounded-2xl">
                <p class="text-sm text-text-muted font-bold">No rating directories found. Use the "Send to All" feature in Documents to create one.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach($ratings as $rating): ?>
                    
                    <div data-doc-id="<?= $rating['id'] ?>" class="relative flex items-center bg-surface border border-surface-border rounded-2xl shadow-sm hover:shadow-md hover:border-accent/30 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-all group">
                        
                        <a href="<?= site_url('rating?Id=' . $rating['id']) ?>" class="flex-1 flex items-center gap-4 p-5 cursor-pointer">
                            <div class="p-3 rounded-xl bg-accent/10 text-accent border border-accent/20 transition-transform group-hover:scale-110 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>

                            <div class="flex flex-col min-w-0 pr-10">
                                <h2 class="text-sm font-bold text-text group-hover:text-accent transition-colors truncate">
                                    <?= esc($rating['title']) ?>
                                </h2>
                                <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase mt-0.5">
                                    Created: <?= date('M d, Y', strtotime($rating['created_at'])) ?>
                                </span>
                            </div>
                        </a>

                        <button class="btn-trigger-delete absolute right-4 p-2 rounded-lg text-red-400 opacity-0 group-hover:opacity-100 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer z-10"
                                data-id="<?= $rating['id'] ?>" 
                                data-title="<?= esc($rating['title']) ?>" 
                                title="Delete Rating Batch">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>

                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>

<script>
    const AppConfig = {
        baseUrl: '<?= site_url('rating') ?>',
    };
</script>

<?= $this->endSection() ?>
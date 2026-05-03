<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<!-- Modals for document-level actions -->
<?= view('components/share_modal') ?>
<?= view('components/delete_modal') ?>
<?= view('components/create_modal') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-6 h-[calc(100vh-6rem)]">
    
    <!-- Breadcrumb / Header Section -->
    <div class="flex items-center gap-4 shrink-0">
        <a href="<?= site_url('documents') ?>" class="p-2 rounded-xl bg-surface border border-surface-border text-text-muted hover:text-accent hover:border-accent/30 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                <span class="text-zinc-400">/</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-accent">ID: <?= esc($folder['id']) ?></span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-text"><?= esc($folder['title']) ?></h1>
        </div>

        <button id="btn-open-create" class="ml-auto flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent-hover text-white rounded-2xl font-bold text-sm transition-all shadow-xl shadow-accent/20 active:scale-95 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Document
        </button>
    </div>

    <!-- Documents Table -->
    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-0">
        <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0">
            <div class="col-span-6">Document Name</div>
            <div class="col-span-4">Last Activity</div>
            <div class="col-span-2 text-right">Actions</div>
        </div>

        <div class="overflow-y-auto custom-scrollbar flex-1 relative">
            <div class="divide-y divide-surface-border mx-2">
                <?php if (empty($docs)): ?>
                    <div class="p-24 text-center">
                        <p class="text-sm text-text-muted font-medium italic">This folder is currently empty.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($docs as $doc): ?>
                        <div data-doc-id="<?= $doc['id'] ?>" class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-all group">
                            
                            <div class="col-span-6 flex items-center gap-4">
                                <div class="bg-accent/10 p-1.5 rounded-xl text-accent border border-accent/20 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <!-- Direct link to editor -->
                                    <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-accent transition-colors truncate">
                                        <?= esc($doc['title']) ?>
                                    </a>
                                    <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase">IPCR Template</span>
                                </div>
                            </div>

                            <div class="col-span-4 text-xs font-semibold text-text-muted">
                                <?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
                            </div>

                            <div class="col-span-2 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                <button onclick="openShareModal('<?= $doc['id'] ?>')" class="p-2 rounded-lg text-text-muted hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-text transition-all" title="Share Access">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                </button>
                                
                                <button class="btn-trigger-delete p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer"
                                        data-id="<?= $doc['id'] ?>" 
                                        data-title="<?= esc($doc['title']) ?>" title="Delete Document">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/shareModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/createModal.js') ?>"></script>

<script>
    const AppConfig = {
        baseUrl: '<?= site_url('document') ?>',
        currentFolderId: '<?= $folder['id'] ?>' 
    };
</script>

<?= $this->endSection() ?>
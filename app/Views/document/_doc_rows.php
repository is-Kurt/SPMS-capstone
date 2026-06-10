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
    <div class="flex justify-between items-end mb-6 shrink-0 px-1">
        <div class="min-w-0 pr-4">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                <span class="text-zinc-400">/</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-accent">ID: <?= esc($activeFolder['id']) ?></span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-text truncate">
                <?= esc($activeFolder['title']) ?>
            </h1>
        </div>
        
        <div class="flex gap-2 shrink-0 items-center">
            <?php if (session()->get('role') === 'admin' && $activeFolder['user_id'] == session()->get('user_id')): ?>
                <button class="btn-trigger-send-all flex items-center justify-center p-3 rounded-xl text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100 cursor-pointer"
                    data-id="<?= $activeFolder['id'] ?>" 
                    data-title="<?= esc($activeFolder['title']) ?>" 
                    title="Distribute Folder Batch">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>

                <button class="btn-delete-modal flex items-center justify-center p-3 rounded-xl text-red-400 hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 cursor-pointer"
                    data-id="<?= $activeFolder['id'] ?>"
                    data-desc="<?= esc($activeFolder['title']) ?>"
                    data-url="<?= site_url('folder') ?>" 
                    data-title="Delete Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            <?php endif; ?>

            <button id="btn-open-create-file" 
                data-folder-id="<?= esc($activeFolder['id']) ?>" 
                class="flex items-center gap-2 px-5 py-2.5 bg-accent hover:bg-accent-hover text-white rounded-xl font-bold text-sm transition-all shadow-md shadow-accent/20 active:scale-95 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
                Add Document
            </button>
        </div>
    </div>

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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
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
                                <button class="btn-delete-modal p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer"
                                        data-id="<?= $doc['id'] ?>" 
                                        data-desc="<?= esc($doc['title']) ?>"
                                        data-url="<?= site_url('document') ?>" 
                                        data-title="Delete Document">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
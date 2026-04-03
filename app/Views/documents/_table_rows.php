<?php if (empty($docs)): ?>
    <div class="p-24 text-center">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <p class="text-sm text-text-muted font-medium italic">No documents found in this folder.</p>
    </div>
<?php else: ?>
    <?php foreach ($docs as $doc): ?>
        <div data-doc-id="<?= $doc['id'] ?>" class="doc-row grid grid-cols-13 gap-4 px-8 py-4 items-center hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-all group">
            
            <div class="col-span-5 flex items-center gap-4">
                <div class="bg-accent/10 p-1.5 rounded-xl text-accent border border-accent/20 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <path d="M14 2v6h6"/>
                    </svg>
                </div>
                <div class="flex flex-col min-w-0">
                    <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-accent transition-colors truncate max-w-xs md:max-w-md">
                        <?= esc($doc['title']) ?>
                    </a>
                    <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase">IPCR Template</span>
                </div>
            </div>

            <div class="col-span-3 text-xs font-semibold text-text-muted">
                <?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
            </div>

            <div class="col-span-4 text-xs font-semibold text-text-muted flex flex-row items-center gap-2">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                     alt="User" class="size-8 rounded-full object-cover ring-1 ring-surface-border" />
                <p><?= esc($doc['email'] ?? 'Unknown User') ?></p>
            </div>

            <div class="col-span-1 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                <button onclick="openShareModal('<?= $doc['id'] ?>')" 
                        class="p-2 rounded-lg text-text-muted hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-text transition-all" title="Share Access">
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
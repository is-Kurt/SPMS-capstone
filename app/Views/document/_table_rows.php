<div id="empty-doc" class="p-24 text-center">
    <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    </div>
    <p class="text-sm text-text-muted font-medium italic">No documents found in this folder.</p>
</div>

<!-- app/Views/document/_table_rows.php -->
<?php foreach ($folders as $folder): ?>
    <div data-doc-id="<?= $folder['id'] ?>" 
         class="doc-row grid grid-cols-13 gap-4 px-8 py-4 items-center hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-all group">
        
        <!-- Col 1-5: Folder Name -->
        <div class="col-span-5 flex items-center gap-4">
            <div class="bg-amber-500/10 p-1.5 rounded-xl text-amber-500 border border-amber-500/20 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <div class="flex flex-col min-w-0">
                <a href="<?= site_url('folder?Id=' . $folder['id']) ?>" class="font-bold text-sm text-text hover:text-accent transition-colors truncate">
                    <?= esc($folder['title']) ?>
                </a>
                <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase">Evaluation Folder</span>
            </div>
        </div>
        
        <!-- Col 6-8: Activity -->
        <div class="col-span-3 text-xs font-semibold text-text-muted">
            <?= date('M d, Y', strtotime($folder['updated_at'] ?? $folder['created_at'])) ?>
        </div>

        <!-- Col 9-12: Owner (Restored) -->
        <div class="col-span-4 text-xs font-semibold text-text-muted flex flex-row items-center gap-2">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($folder['email'] ?? 'U') ?>&background=random" 
                 alt="User" class="size-8 rounded-full object-cover ring-1 ring-surface-border" />
            <p><?= esc($folder['email'] ?? 'Unknown User') ?></p>
        </div>

        <!-- Col 13: Actions (Consolidated) -->
        <div class="col-span-1 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
            <?php if (session()->get('role') === 'admin' && $folder['user_id'] == session()->get('user_id')): ?>
                <!-- Send Button -->
                <button class="btn-trigger-send-all p-2 rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-600 transition-all cursor-pointer"
                        data-id="<?= $folder['id'] ?>" 
                        data-title="<?= esc($folder['title']) ?>" title="Distribute Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>

                <!-- Delete Folder Button -->
                <button class="btn-trigger-delete p-2 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-all cursor-pointer"
                        data-id="<?= $folder['id'] ?>" 
                        data-title="<?= esc($folder['title']) ?>" title="Delete Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
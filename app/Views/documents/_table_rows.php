<?php if (empty($docs)): ?>
    <div class="p-20 text-center text-sub-text italic">
        No documents found in your account.
    </div>
<?php else: ?>
    <div class="divide-y divide-main-border">
        <?php foreach ($docs as $doc): ?>
            <div data-doc-id="<?= $doc['id'] ?>" class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-main-border/5 transition-colors group">
                <div class="col-span-7 flex items-center gap-4">
                    <div class="bg-highlight/10 p-2 rounded text-highlight">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-medium text-sm hover:text-highlight transition-colors truncate max-w-xs md:max-w-md">
                            <?= esc($doc['title']) ?>
                        </a>
                        <span class="text-[10px] text-sub-text uppercase">IPCR Document</span>
                    </div>
                </div>

                <div class="col-span-3 text-sm text-sub-text">
                    <?= date('M d, Y', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
                </div>

                <div class="col-span-2 flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="openShareModal('<?= $doc['id'] ?>')" title="Share" class="text-sub-text hover:text-highlight">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" y1="8" x2="19" y2="14"/>
                            <line x1="16" y1="11" x2="22" y2="11"/>
                        </svg>
                    </button>
                    <button onclick="deleteDocument('<?= $doc['id'] ?>')" title="Delete" class="text-sub-text hover:text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
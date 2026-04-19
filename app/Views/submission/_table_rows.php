<div id="empty-doc" class="p-24 text-center">
    <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    </div>
    <p class="text-sm text-text-muted font-medium italic">No documents found in this folder.</p>
</div>

<?php foreach ($docs as $doc):
    $isLocked = strtotime($doc['eval_date_start']) > time();
    $isOverdue = strtotime($doc['eval_date_end']) < time() && !$doc['is_rated'];
?>
    <div data-doc-id="<?= $doc['document_id'] ?>" class="doc-row grid grid-cols-13 gap-4 px-8 py-4 items-center hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-all group">
        
        <div class="col-span-4 flex items-center gap-4">
            <div class="p-1.5 rounded-xl border transition-transform <?= $isLocked ? 'bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 border-zinc-200 dark:border-zinc-700' : 'bg-accent/10 text-accent border-accent/20 group-hover:scale-110' ?>">
                <?php if ($isLocked): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <path d="M14 2v6h6"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="flex flex-col min-w-0">
                <a href="<?= $isLocked ? '#' : site_url('submission?Id=' . $doc['document_id']) ?>" 
                    class="font-bold text-sm <?= $isLocked ? 'text-zinc-400 dark:text-zinc-500 cursor-not-allowed' : 'text-text hover:text-accent' ?> transition-colors truncate max-w-xs md:max-w-[200px]">
                    <?= esc($doc['title']) ?>
                </a>
                <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase">IPCR Submission</span>
            </div>
        </div>

        <div class="col-span-5">
            <div class="text-xs font-semibold text-text-muted">
                <?= date('M d, Y g:ia', strtotime($doc['eval_date_start'])) ?> 
                <span class="px-1.5 text-zinc-300 dark:text-zinc-700">—</span> 
                <?= date('M d, Y g:ia', strtotime($doc['eval_date_end'])) ?>
            </div>
        </div>

        <div class="col-span-3 flex items-center">
            <?php if ($isLocked): ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800/80 text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Locked</span>
            <?php elseif ($doc['is_rated']): ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-accent/10 dark:bg-accent/20 text-accent uppercase tracking-widest">Evaluated</span>
            <?php elseif ($isOverdue): ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 uppercase tracking-widest">Unevaluated</span>
            <?php else: ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 uppercase tracking-widest">Pending</span>
            <?php endif; ?>
        </div>

        <div class="col-span-1 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
            <button class="btn-trigger-delete p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer"
                data-id="<?= $doc['document_id'] ?>" 
                data-title="<?= esc($doc['title']) ?>" 
                title="Unsubmit Document">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
            </button>
        </div>
    </div> 
<?php endforeach; ?>

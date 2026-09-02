<?php   
    $isArchivedRoute = (service('uri')->getSegment(2) === 'archived');
    $currentBaseUrl = (service('uri')->getSegment(1) === 'ratings') ? 'ratings' : 
                      ($isArchivedRoute ? 'folders/archived' : 'folders');
?>

<nav id="sidebar-nav" class="flex flex-col gap-2 h-full justify-between">
    <div class="flex flex-col gap-2">
        <?php if ($isArchivedRoute): ?>
            <a href="<?= site_url('folders') ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all flex items-center gap-2 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Active Folders</span>
            </a>
            <h5 class="px-3 text-[10px] font-black uppercase tracking-wider text-text-muted">Archived Folders</h5>
        <?php endif; ?>

        <?php if (empty($folders)): ?>
            <p class="px-3 text-xs text-text-muted italic">No <?= $isArchivedRoute ? 'archived' : '' ?> folders found.</p>
        <?php else: ?>
            <?php foreach ($folders as $folder): ?>
                <?php $isActive = ($selectedFolderId == $folder['id']); ?>
                
                <a href="<?= site_url($currentBaseUrl . '/' . $folder['id']) ?>"
                   class="px-3.5 py-3 rounded-xl text-xs font-bold transition-all flex items-center gap-2.5 group
                    <?= $isActive ?
                       'bg-white dark:bg-[#052e1d] text-slate-900 dark:text-white border-2 border-[#064e3b] dark:border-emerald-500 shadow-xs' : 
                       'text-[#3d5a47] dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5' ?>">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 <?= $isActive ? 'text-[#064e3b] dark:text-emerald-400' : 'text-[#5a7b65] dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span class="truncate"><?= esc($folder['title']) ?></span>
                </a>
                
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Archive Folder Link -->
    <div class="pt-6">
        <a href="<?= site_url($isArchivedRoute ? 'folders' : 'folders/archived') ?>" 
           class="px-3.5 py-2.5 rounded-xl text-xs font-bold <?= $isArchivedRoute ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?> transition-all flex items-center gap-2.5 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span><?= $isArchivedRoute ? 'Active Folders' : 'Archived Folders' ?></span>
        </a>
    </div>
</nav>
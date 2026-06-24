<?php   
    $currentBaseUrl = (service('uri')->getSegment(1) === 'ratings') ? 'ratings' : 'folders';
?>

<nav id="sidebar-nav" class="flex flex-col gap-1.5">
    <?php if (empty($folders)): ?>
        <p class="px-4 text-xs text-text-muted italic">No folders found.</p>
    <?php else: ?>
        <?php foreach ($folders as $folder): ?>
            <?php $isActive = ($selectedFolderId == $folder['id']); ?>
            
            <a href="<?= site_url($currentBaseUrl . '/' . $folder['id']) ?>"
               class="px-4 py-3 rounded-xl text-sm font-bold transition-all flex justify-between items-center group
               <?= $isActive ? 'bg-accent text-white shadow-lg shadow-accent/20' : 'text-text-muted hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-text' ?>">
                
                <div class="flex items-center gap-3 truncate">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 <?= $isActive ? 'text-white' : 'text-text-muted group-hover:text-accent' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span class="truncate"><?= esc($folder['title']) ?></span>
                </div>
            </a>
            
        <?php endforeach; ?>
    <?php endif; ?>
</nav>
<?php   
    $isArchivedRoute = (service('uri')->getSegment(2) === 'archived');
    $firstSegment = service('uri')->getSegment(1);
    $currentBaseUrl = ($firstSegment === 'ratings') ? 'ratings' : 
                      (($firstSegment === 'dashboard') ? 'dashboard' : 
                      ($isArchivedRoute ? 'folders/archived' : 'folders'));
?>

<nav id="sidebar-nav" class="flex flex-col gap-2 h-full justify-between">
    <div class="flex flex-col gap-2">
        <?php if ($isArchivedRoute): ?>
            <a href="<?= site_url('folders') ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all flex items-center gap-2 mb-1">
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
            <div id="sidebar-folders-list" class="flex flex-col gap-2">
                <?php foreach ($folders as $index => $folder): ?>
                    <?php $isActive = ($selectedFolderId == $folder['id']); ?>
                    
                    <a href="<?= site_url($currentBaseUrl . '/' . $folder['id']) ?>"
                       class="sidebar-folder-item relative px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2.5 group
                        <?= $isActive ?
                           'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700/60 shadow-xs' : 
                           'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40' ?>"
                       data-is-active="<?= $isActive ? '1' : '0' ?>"
                       data-index="<?= $index ?>">
                        
                        <?php if ($isActive): ?>
                            <span class="absolute left-0 inset-y-2.5 w-1 bg-amber-500 rounded-r-full"></span>
                        <?php endif; ?>

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 <?= $isActive ? 'text-zinc-900 dark:text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span class="truncate"><?= esc($folder['title']) ?></span>
                    </a>
                    
                <?php endforeach; ?>
            </div>

            <?php if (count($folders) > 5): ?>
                <!-- Minimalist Sidebar Paginator (Only appears when > 5 folders) -->
                <div id="sidebar-folder-pagination" class="flex items-center justify-between px-3 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400 select-none mt-1">
                    <button type="button" id="btn-sidebar-prev" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-white dark:hover:bg-zinc-800 disabled:opacity-25 disabled:hover:bg-transparent disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs" 
                            title="Previous 5 Folders">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-1.5 font-bold text-[10px] tracking-wider text-zinc-500 dark:text-zinc-400">
                        <span>Page</span>
                        <span id="sidebar-page-indicator" class="px-2 py-0.5 rounded bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white font-extrabold">1 / <?= ceil(count($folders) / 5) ?></span>
                    </div>

                    <button type="button" id="btn-sidebar-next" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-white dark:hover:bg-zinc-800 disabled:opacity-25 disabled:hover:bg-transparent disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs" 
                            title="Next 5 Folders">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Archive Folder Link (Admin Only) -->
    <?php if (session()->get('role') === 'Admin'): ?>
    <div class="pt-4 border-t border-zinc-200/50 dark:border-zinc-800/60">
        <a href="<?= site_url($isArchivedRoute ? 'folders' : 'folders/archived') ?>" 
           class="px-3.5 py-2.5 rounded-xl text-xs font-bold <?= $isArchivedRoute ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40' ?> transition-all flex items-center gap-2.5 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-zinc-400 dark:text-zinc-400 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span><?= $isArchivedRoute ? 'Active Folders' : 'Archived Folders' ?></span>
        </a>
    </div>
    <?php endif; ?>
</nav>

<script>
    (function() {
        const pageSize = 5;
        const items = Array.from(document.querySelectorAll('.sidebar-folder-item'));
        if (items.length <= pageSize) return;

        // Automatically locate which page contains the currently active folder
        const activeIndex = items.findIndex(el => el.getAttribute('data-is-active') === '1');
        const totalPages = Math.ceil(items.length / pageSize);
        let currentPage = activeIndex >= 0 ? Math.floor(activeIndex / pageSize) + 1 : 1;

        const prevBtn = document.getElementById('btn-sidebar-prev');
        const nextBtn = document.getElementById('btn-sidebar-next');
        const indicator = document.getElementById('sidebar-page-indicator');

        function renderSidebarPage(page) {
            currentPage = Math.max(1, Math.min(page, totalPages));
            const startIdx = (currentPage - 1) * pageSize;
            const endIdx = startIdx + pageSize;

            items.forEach((item, idx) => {
                if (idx >= startIdx && idx < endIdx) {
                    item.classList.remove('hidden');
                    item.style.display = '';
                } else {
                    item.classList.add('hidden');
                    item.style.display = 'none';
                }
            });

            if (indicator) {
                indicator.textContent = `${currentPage} / ${totalPages}`;
            }
            if (prevBtn) {
                prevBtn.disabled = (currentPage <= 1);
            }
            if (nextBtn) {
                nextBtn.disabled = (currentPage >= totalPages);
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                renderSidebarPage(currentPage - 1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                renderSidebarPage(currentPage + 1);
            });
        }

        renderSidebarPage(currentPage);
    })();
</script>
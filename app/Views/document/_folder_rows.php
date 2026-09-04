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
            <div id="sidebar-folders-list" class="flex flex-col gap-2">
                <?php foreach ($folders as $index => $folder): ?>
                    <?php $isActive = ($selectedFolderId == $folder['id']); ?>
                    
                    <a href="<?= site_url($currentBaseUrl . '/' . $folder['id']) ?>"
                       class="sidebar-folder-item relative px-3.5 py-3 rounded-xl text-xs font-bold transition-all flex items-center gap-2.5 group
                        <?= $isActive ?
                           'bg-white dark:bg-[#121d17] text-slate-900 dark:text-white border border-slate-200 dark:border-[#1e382b] shadow-xs' : 
                           'text-[#3d5a47] dark:text-[#8ea396] hover:text-slate-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5' ?>"
                       data-is-active="<?= $isActive ? '1' : '0' ?>"
                       data-index="<?= $index ?>">
                        
                        <?php if ($isActive): ?>
                            <span class="absolute left-0 inset-y-2.5 w-1 bg-emerald-500 rounded-r-full"></span>
                        <?php endif; ?>

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 <?= $isActive ? 'text-[#064e3b] dark:text-emerald-400' : 'text-[#5a7b65] dark:text-[#8ea396] group-hover:text-slate-900 dark:group-hover:text-white' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span class="truncate"><?= esc($folder['title']) ?></span>
                    </a>
                    
                <?php endforeach; ?>
            </div>

            <?php if (count($folders) > 5): ?>
                <!-- Minimalist Sidebar Paginator (Only appears when > 5 folders) -->
                <div id="sidebar-folder-pagination" class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] mt-1 text-xs text-text-muted select-none">
                    <button type="button" id="btn-sidebar-prev" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-white dark:hover:bg-white/10 hover:text-slate-900 dark:hover:text-white disabled:opacity-25 disabled:hover:bg-transparent disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs" 
                            title="Previous 5 Folders">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-1.5 font-bold text-[10px] tracking-wider text-slate-600 dark:text-[#8ea396]">
                        <span>Page</span>
                        <span id="sidebar-page-indicator" class="px-1.5 py-0.5 rounded bg-white dark:bg-[#13271b] border border-slate-200 dark:border-[#1e422f] text-slate-900 dark:text-emerald-400 font-extrabold">1 / <?= ceil(count($folders) / 5) ?></span>
                    </div>

                    <button type="button" id="btn-sidebar-next" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-white dark:hover:bg-white/10 hover:text-slate-900 dark:hover:text-white disabled:opacity-25 disabled:hover:bg-transparent disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs" 
                            title="Next 5 Folders">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Archive Folder Link -->
    <div class="pt-4 border-t border-slate-200/50 dark:border-white/5">
        <a href="<?= site_url($isArchivedRoute ? 'folders' : 'folders/archived') ?>" 
           class="px-3.5 py-2.5 rounded-xl text-xs font-bold <?= $isArchivedRoute ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?> transition-all flex items-center gap-2.5 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span><?= $isArchivedRoute ? 'Active Folders' : 'Archived Folders' ?></span>
        </a>
    </div>
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
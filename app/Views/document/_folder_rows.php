<?php   
    $totalSegments = service('uri')->getTotalSegments();
    $isArchivedRoute = ($totalSegments >= 2 && service('uri')->getSegment(2) === 'archived');
    $firstSegment = ($totalSegments >= 1) ? service('uri')->getSegment(1) : '';
    $currentBaseUrl = ($firstSegment === 'ratings') ? 'ratings' : 
                      (($firstSegment === 'dashboard') ? 'dashboard' : 
                      ($isArchivedRoute ? 'folders/archived' : 'folders'));
    $isFoldersRoute = ($firstSegment === 'folders');
?>

<nav id="sidebar-nav" class="flex flex-col gap-2 h-full justify-between">
    <div class="flex flex-col gap-2">
        <?php if ($isFoldersRoute && session()->get('role') === 'Admin'): ?>
            <div class="grid grid-cols-2 p-1 bg-surface-border/40 dark:bg-zinc-800/60 rounded-xl border border-surface-border/80 shadow-xs mb-1.5">
                <a href="<?= site_url('folders') ?>" 
                   class="folder-tab-toggle inline-flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-lg text-xs transition-colors cursor-pointer <?= !$isArchivedRoute ? 'bg-accent text-zinc-950 shadow-xs font-black' : 'font-bold text-text-muted hover:text-text hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50' ?>"
                   data-type="active">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 <?= !$isArchivedRoute ? 'text-zinc-950' : 'text-zinc-400 dark:text-zinc-500' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span>Active</span>
                </a>
                <a href="<?= site_url('folders/archived') ?>" 
                   class="folder-tab-toggle inline-flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-lg text-xs transition-colors cursor-pointer <?= $isArchivedRoute ? 'bg-accent text-zinc-950 shadow-xs font-black' : 'font-bold text-text-muted hover:text-text hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50' ?>"
                   data-type="archived">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 <?= $isArchivedRoute ? 'text-zinc-950' : 'text-zinc-400 dark:text-zinc-500' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <span>Archived</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($folders)): ?>
            <p class="px-3 text-xs text-text-muted italic">No <?= $isArchivedRoute ? 'archived' : '' ?> folders found.</p>
        <?php else: ?>
            <div id="sidebar-folders-list" class="flex flex-col gap-2">
                <?php foreach ($folders as $index => $folder): ?>
                    <?php $isActive = ($selectedFolderId == $folder['id']); ?>
                    
                    <a href="<?= site_url($currentBaseUrl . '/' . $folder['id']) ?>"
                       class="sidebar-folder-item relative px-3.5 py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-2.5 group
                        <?= $isActive ?
                           'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700/60 shadow-xs' : 
                           'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40' ?>"
                       data-is-active="<?= $isActive ? '1' : '0' ?>"
                       data-folder-id="<?= $folder['id'] ?>"
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
                <!-- Sidebar Pagination -->
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

    // --- Seamless Folder Workspace Switcher ---
    (function() {
        let isSwitchingFolder = false;

        function executeScripts(container) {
            if (!container) return;
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        async function loadFolderWorkspace(url, updateHistory = true) {
            const container = document.getElementById('main-content-container');
            if (!container || isSwitchingFolder) return;
            
            isSwitchingFolder = true;

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-SPMS-Scope': 'workspace',
                        'Accept': 'text/html'
                    }
                });

                if (!res.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await res.text();
                const newCsrf = res.headers.get('x-csrf-token') || res.headers.get('X-CSRF-TOKEN');
                if (newCsrf) {
                    document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', newCsrf);
                    document.querySelectorAll('input[name="csrf_test_name"]').forEach(i => i.value = newCsrf);
                }
                container.innerHTML = html;
                executeScripts(container);

                if (updateHistory) {
                    history.pushState({ spmsFolderUrl: url, scope: 'workspace' }, '', url);
                }

                // Close mobile sidebar if open
                if (typeof toggleAppSidebar === 'function' && window.isAppSidebarOpen) {
                    toggleAppSidebar();
                }
            } catch (err) {
                console.error('Seamless switch failed, falling back to full navigation:', err);
                window.location.href = url;
            } finally {
                isSwitchingFolder = false;
            }
        }

        async function loadFolderTab(url, updateHistory = true) {
            if (isSwitchingFolder) return;
            isSwitchingFolder = true;

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-SPMS-Scope': 'tab',
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    window.location.href = url;
                    return;
                }

                const data = await res.json();
                const newCsrf = res.headers.get('x-csrf-token') || res.headers.get('X-CSRF-TOKEN');
                if (newCsrf) {
                    document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', newCsrf);
                    document.querySelectorAll('input[name="csrf_test_name"]').forEach(i => i.value = newCsrf);
                }
                const sidebarContainer = document.getElementById('sidebar-content-container');
                const mainContainer = document.getElementById('main-content-container');

                if (sidebarContainer && data.sidebar) {
                    sidebarContainer.innerHTML = data.sidebar;
                    executeScripts(sidebarContainer);
                }
                if (mainContainer && data.main) {
                    mainContainer.innerHTML = data.main;
                    executeScripts(mainContainer);
                }

                const finalUrl = data.url || url;
                if (updateHistory) {
                    history.pushState({ spmsFolderUrl: finalUrl, scope: 'tab' }, '', finalUrl);
                }

                // Close mobile sidebar if open
                if (typeof toggleAppSidebar === 'function' && window.isAppSidebarOpen) {
                    toggleAppSidebar();
                }
            } catch (err) {
                console.error('Seamless tab switch failed, falling back to full navigation:', err);
                window.location.href = url;
            } finally {
                isSwitchingFolder = false;
            }
        }

        function updateActiveSidebarItem(targetItem) {
            const items = document.querySelectorAll('.sidebar-folder-item');
            items.forEach(el => {
                el.setAttribute('data-is-active', '0');
                el.className = 'sidebar-folder-item relative px-3.5 py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-2.5 group text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40';
                const amber = el.querySelector('span.bg-amber-500');
                if (amber) amber.remove();
                const icon = el.querySelector('svg');
                if (icon) {
                    icon.className = 'w-4 h-4 flex-shrink-0 text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white';
                }
            });

            if (targetItem) {
                targetItem.setAttribute('data-is-active', '1');
                targetItem.className = 'sidebar-folder-item relative px-3.5 py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center gap-2.5 group bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700/60 shadow-xs';
                
                const amberPill = document.createElement('span');
                amberPill.className = 'absolute left-0 inset-y-2.5 w-1 bg-amber-500 rounded-r-full';
                targetItem.prepend(amberPill);

                const icon = targetItem.querySelector('svg');
                if (icon) {
                    icon.className = 'w-4 h-4 flex-shrink-0 text-zinc-900 dark:text-white';
                }
            }
        }

        // Bind folder clicks
        document.querySelectorAll('.sidebar-folder-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
                e.preventDefault();

                const url = this.getAttribute('href');
                if (!url) return;

                updateActiveSidebarItem(this);
                loadFolderWorkspace(url, true);
            });
        });

        // Bind Active / Archived tab clicks
        document.querySelectorAll('.folder-tab-toggle').forEach(tabBtn => {
            tabBtn.addEventListener('click', function(e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
                e.preventDefault();

                const url = this.getAttribute('href');
                if (!url) return;

                loadFolderTab(url, true);
            });
        });

        // Handle browser Back / Forward buttons seamlessly
        window.addEventListener('popstate', function(e) {
            const url = window.location.href;
            const isArchivedUrl = url.includes('/folders/archived');
            const currentIsArchived = document.querySelector('.folder-tab-toggle[data-type="archived"]')?.classList.contains('bg-accent');
            
            if (isArchivedUrl !== Boolean(currentIsArchived)) {
                loadFolderTab(url, false);
            } else {
                const matchingItem = Array.from(document.querySelectorAll('.sidebar-folder-item')).find(el => el.getAttribute('href') === url);
                if (matchingItem) {
                    updateActiveSidebarItem(matchingItem);
                }
                loadFolderWorkspace(url, false);
            }
        });

        window.refreshFolderWorkspace = function(folderId) {
            const currentUrl = window.location.href;
            loadFolderWorkspace(currentUrl, false);
        };

        window.loadFolderTab = function(url, updateHistory = true) {
            return loadFolderTab(url, updateHistory);
        };
    })();
</script>
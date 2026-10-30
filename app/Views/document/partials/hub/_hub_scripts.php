    <script>
        function openAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.remove('hidden');
        }
        function closeAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.add('hidden');
        }

        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        // Bottom Sheet Logic
        let isSheetOpen = false;
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottom-sheet');
            const overlay = document.getElementById('bottom-sheet-overlay');
            if (!sheet) return;
            isSheetOpen = !isSheetOpen;
            
            if (isSheetOpen) {
                sheet.classList.remove('spms-bottom-sheet-collapsed');
                sheet.classList.add('spms-bottom-sheet-expanded');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sheet.classList.add('spms-bottom-sheet-collapsed');
                sheet.classList.remove('spms-bottom-sheet-expanded');
                if (overlay) overlay.classList.add('hidden');
            }
        }

        // Real-time Search and Filtering
        function filterDocuments() {
            const searchInput = document.getElementById('doc-search-input');
            const statusSelect = document.getElementById('doc-filter-status');
            if (!searchInput && !statusSelect) return;

            const query = (searchInput?.value || '').toLowerCase().trim();
            const statusFilter = statusSelect?.value || 'all';

            const rows = document.querySelectorAll('#tab-content-mine .doc-row-item');
            let visibleCount = 0;
            rows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = !query || title.includes(query) || status.includes(query);
                const matchesStatus = statusFilter === 'all' || status.toLowerCase().includes(statusFilter.toLowerCase());

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const emptyMsg = document.getElementById('search-empty-doc');
            if (emptyMsg) {
                emptyMsg.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
            }
        }

        // Action Menu toggle
        let activeMenuId = null;
        function toggleActionMenu(menuId) {
            const menu = document.getElementById(menuId);
            if (!menu) return;
            if (activeMenuId && activeMenuId !== menuId) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
            }
            menu.classList.toggle('hidden');
            activeMenuId = menu.classList.contains('hidden') ? null : menuId;
        }

        document.addEventListener('click', function(e) {
            if (activeMenuId && !e.target.closest('.group\\/actions')) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
                activeMenuId = null;
            }
        });

        async function submitTargetFolder(folderId, btn) {
            const ok = await window.appConfirm("Submit these targets for approval? You won't be able to edit them until returned.", {
                title: 'Submit Targets',
                confirmText: 'Submit Targets',
                cancelText: 'Keep Editing',
                variant: 'primary'
            });
            if (!ok) return;

            if (btn) {
                btn.innerText = 'Submitting...';
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/submit_target') ?>', formData, {
                onSuccess: () => (typeof window.refreshFolderWorkspace === 'function' ? window.refreshFolderWorkspace(folderId) : window.location.reload()),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) {
                        btn.innerText = 'Submit Target';
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            });
        }


        const isUserAdmin = <?= json_encode((session()->get('role') === 'Admin')) ?>;
        let sending = false;

        function triggerCascade(folderId) {
            if (sending) return;

            const selectEl = document.getElementById('team-cascade-select');
            const teamId = selectEl ? selectEl.value : '';
            if (!teamId) {
                window.appAlert(isUserAdmin ? "Please select a staff list to release the cycle to." : "Please select a staff list to cascade to.");
                return;
            }

            sending = true;
            
            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);

            apiPost('<?= site_url('folder/cascade-team') ?>', formData, {
                onSuccess: () => (typeof window.refreshFolderWorkspace === 'function' ? window.refreshFolderWorkspace(folderId) : window.location.reload()),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                }
            });
        }

        async function triggerUncascade(folderId) {
            if (sending) return;

            const confirmMessage = isUserAdmin
                ? "Are you sure you want to revoke the cycle release? All released recipient folders and their assigned draft evaluation forms will be permanently removed."
                : "Are you sure you want to revoke the cascade for this evaluation cycle? All cascaded subordinate folders and their assigned draft evaluation forms will be permanently removed.";
            const confirmTitle = isUserAdmin ? 'Revoke Cycle Release' : 'Revoke Cascade';
            const confirmBtn = isUserAdmin ? 'Revoke Release' : 'Revoke Cascade';
            const cancelBtn = isUserAdmin ? 'Keep Released' : 'Keep Cascaded';

            const ok = await window.appConfirm(confirmMessage, {
                title: confirmTitle,
                confirmText: confirmBtn,
                cancelText: cancelBtn,
                variant: 'danger'
            });
            if (!ok) return;

            sending = true;

            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/uncascade-team') ?>', formData, {
                onSuccess: () => (typeof window.refreshFolderWorkspace === 'function' ? window.refreshFolderWorkspace(folderId) : window.location.reload()),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                }
            });
        }

        function triggerSyncCascade(folderId) {
            if (sending) return;
            const btn = document.getElementById('btn-sync-cascade');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Syncing Staff...</span>
                `;
            }
            sending = true;

            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/sync-team-cascade') ?>', formData, {
                onSuccess: (res) => {
                    if (typeof window.refreshFolderWorkspace === 'function') {
                        window.refreshFolderWorkspace(folderId);
                    } else {
                        window.location.reload();
                    }
                },
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred while syncing staff members.");
                    sending = false;
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Sync New Members
                        `;
                    }
                }
            });
        }

        let currentSubPage = 1;
        const subPageSize = 6;

        function renderSubordinatesPage(page) {
            const cards = document.querySelectorAll('.subordinate-card');
            if (!cards || cards.length === 0) return;

            const total = cards.length;
            const totalPages = Math.ceil(total / subPageSize) || 1;

            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            currentSubPage = page;

            const start = (page - 1) * subPageSize;
            const end = start + subPageSize;

            cards.forEach((card, idx) => {
                if (idx >= start && idx < end) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            const pageInfo = document.getElementById('sub-page-info');
            if (pageInfo) {
                const from = Math.min(start + 1, total);
                const to = Math.min(end, total);
                pageInfo.textContent = `${from}–${to} of ${total}`;
            }

            const indicator = document.getElementById('sub-page-indicator');
            if (indicator) {
                indicator.textContent = `${page} / ${totalPages}`;
            }

            const prevBtn = document.getElementById('sub-prev-btn');
            const nextBtn = document.getElementById('sub-next-btn');
            if (prevBtn) prevBtn.disabled = (page <= 1);
            if (nextBtn) nextBtn.disabled = (page >= totalPages);

            const pagination = document.getElementById('subordinates-pagination');
            if (pagination) {
                if (totalPages <= 1) {
                    pagination.classList.add('hidden');
                } else {
                    pagination.classList.remove('hidden');
                }
            }
        }

        function changeSubPage(delta) {
            renderSubordinatesPage(currentSubPage + delta);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => renderSubordinatesPage(1));
        } else {
            renderSubordinatesPage(1);
        }

        function triggerRemoveCascadedSubordinate(childFolderId, subordinateName, parentFolderId) {
            if (sending) return;
            const confirmTitle = isUserAdmin ? 'Revoke Recipient Cycle Release' : 'Remove Subordinate from Cycle';
            const confirmMsg = isUserAdmin
                ? `Are you sure you want to revoke the cycle release for ${subordinateName}? This will delete their evaluation folder for this cycle.`
                : `Are you sure you want to remove ${subordinateName} from this evaluation cycle? This will delete their target folder and evaluation paper for this cycle.`;
            const confirmBtn = isUserAdmin ? 'Revoke Release' : 'Remove Subordinate';

            window.appConfirm({
                title: confirmTitle,
                message: confirmMsg,
                confirmText: confirmBtn,
                variant: 'danger'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('child_folder_id', childFolderId);
                formData.append('parent_folder_id', parentFolderId);

                apiPost('<?= site_url('folder/remove-subordinate-cascade') ?>', formData, {
                    onSuccess: (res) => {
                        window.location.reload();
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "An error occurred while removing subordinate.");
                        sending = false;
                    }
                });
            });
        }

        function archiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Close & Archive Evaluation Cycle',
                message: `Are you sure you want to close and archive "${folderTitle}"? This will archive the cycle and all subordinate ratee folders, freezing all scores and ratings against further edits.`,
                confirmText: 'Close & Archive Cycle',
                variant: 'danger'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/archive') ?>', formData, {
                    onSuccess: () => {
                        sending = false;
                        if (typeof window.loadFolderTab === 'function') {
                            window.loadFolderTab('<?= site_url('folders') ?>', true);
                        } else {
                            window.location.href = '<?= site_url('folders') ?>';
                        }
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to archive folder.");
                        sending = false;
                    }
                });
            });
        }

        function unarchiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Restore Folder',
                message: `Restore "${folderTitle}" to your active folders?`,
                confirmText: 'Restore Folder',
                variant: 'info'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/unarchive') ?>', formData, {
                    onSuccess: () => {
                        sending = false;
                        if (typeof window.loadFolderTab === 'function') {
                            window.loadFolderTab('<?= site_url('folders') ?>/' + folderId, true);
                        } else {
                            window.location.href = '<?= site_url('folders') ?>/' + folderId;
                        }
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to restore folder.");
                        sending = false;
                    }
                });
            });
        }

        function switchDocTab(tabId) {
            document.querySelectorAll('.tab-content-doc').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
            
            document.querySelectorAll('.tab-btn-doc').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                btn.classList.add('border-transparent', 'text-text-muted');
                const badge = btn.querySelector('.tab-badge');
                if(badge) {
                    badge.classList.remove('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                    badge.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                }
            });

            const target = document.getElementById('tab-content-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('flex');
            }

            const btnElement = document.getElementById('tab-btn-' + tabId);
            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'text-text-muted');
                btnElement.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                const activeBadge = btnElement.querySelector('.tab-badge');
                if(activeBadge) {
                    activeBadge.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                    activeBadge.classList.add('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                }
            }
        }

        async function unsubmitTargetFolder(folderId, btn) {
            const ok = await window.appConfirm("Revoke target submission and return this folder to draft?", {
                title: 'Revoke Submission',
                variant: 'undo',
                confirmText: 'Revoke Submission',
                cancelText: 'Keep Submitted'
            });
            if (!ok) return;

            if (btn) btn.disabled = true;
            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/unsubmit_target') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) btn.disabled = false;
                }
            });
        }

        async function unsubmitEvaluationFolder(folderId, btn) {
            const ok = await window.appConfirm("Are you sure you want to revoke your self-rating submission? This will return your evaluation to drafting status so you can edit your ratings and accomplishments.", {
                title: 'Revoke Self-Rating',
                variant: 'undo',
                confirmText: 'Revoke Self-Rating',
                cancelText: 'Keep Submitted'
            });
            if (!ok) return;

            if (btn) btn.disabled = true;
            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) btn.disabled = false;
                }
            });
        }
    </script>

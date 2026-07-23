<script>
    // ==========================================
    // AJAX FORM HANDLING (no more full-page reloads for accounts actions)
    // Registered synchronously (not inside DOMContentLoaded) so this listener
    // attaches before confirmModal.js's global data-confirm auto-submit
    // listener does - stopImmediatePropagation() below then keeps that older
    // listener from ever seeing these same submits.
    // ==========================================
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        const action = form.dataset.ajax;
        if (!action) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        if (form.dataset.confirm) {
            const ok = await window.appConfirm(form.dataset.confirm, {
                title: form.dataset.confirmTitle || undefined,
                confirmText: form.dataset.confirmText || 'Delete'
            });
            if (!ok) return;
        }

        if (action === 'change-role') {
            const select = form.querySelector('.js-role-select');
            const newLabel = select.options[select.selectedIndex].text;
            const ok = await window.appConfirm(`Change this user's role to "${newLabel}"? This updates their system permissions immediately.`, {
                title: 'Change Role',
                confirmText: 'Change Role',
                variant: 'warning'
            });
            if (!ok) {
                select.value = select.dataset.previous;
                return;
            }
        }

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        apiPost(form.getAttribute('action'), formData, {
            onSuccess: (data) => {
                handleAccountsAjaxSuccess(action, form, data);
                if (submitBtn) submitBtn.disabled = false;
            },
            onError: (errMsg) => {
                if (action === 'change-role') {
                    const select = form.querySelector('.js-role-select');
                    select.value = select.dataset.previous;
                }
                window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function handleAccountsAjaxSuccess(action, form, data) {
        if (['add-position', 'delete-position', 'add-unit', 'delete-unit', 'add-role', 'delete-role'].includes(action)) {
            window.systemDataChanged = true;
        }
        switch (action) {
            case 'toggle-status': {
                const row = form.closest('.user-dir-row');
                const isActive = data.is_active == 1;
                row.dataset.status = isActive ? '1' : '0';
                row.classList.toggle('opacity-60', !isActive);

                row.querySelectorAll('.js-status-badge-desktop').forEach(el => {
                    el.textContent = isActive ? 'Active' : 'Disabled';
                    el.className = `js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest ${isActive ? 'text-success-500' : 'text-danger-500'}`;
                });
                row.querySelectorAll('.js-status-badge-mobile').forEach(el => {
                    el.textContent = isActive ? 'Active' : 'Disabled';
                    el.className = `js-status-badge-mobile px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border ${isActive ? 'bg-success-50 text-success-600 border-success-200' : 'bg-danger-50 text-danger-600 border-danger-200'}`;
                });

                const btn = form.querySelector('.js-toggle-btn');
                btn.textContent = isActive ? 'Disable' : 'Enable';
                btn.className = `js-toggle-btn text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg border border-transparent transition-colors cursor-pointer focus:outline-none ${isActive ? 'text-warning-600 lg:hover:bg-warning-50 lg:dark:hover:bg-warning-500/10' : 'text-success-600 lg:hover:bg-success-50 lg:dark:hover:bg-success-500/10'}`;
                break;
            }

            case 'delete-user': {
                form.closest('.user-dir-row').remove();
                document.getElementById('directory-count').textContent = document.querySelectorAll('.user-dir-row').length;
                break;
            }

            case 'change-role': {
                form.closest('.user-dir-row').dataset.role = data.role_name;
                const select = form.querySelector('.js-role-select');
                select.dataset.previous = select.value;
                window.appAlert(`Role updated to "${data.role_name}".`, { title: 'Role Updated', variant: 'success' });
                break;
            }

            case 'delete-position': {
                form.closest('li').remove();
                if (typeof filterPositions === 'function') filterPositions();
                break;
            }

            case 'delete-unit': {
                const li = form.closest('li');
                if (li) {
                    const unitId = li.dataset.id;
                    const parentId = li.dataset.parent;

                    // Remove all descendants recursively
                    const removeDescendants = (pId) => {
                        document.querySelectorAll(`.unit-item[data-parent="${pId}"]`).forEach(child => {
                            const childId = child.dataset.id;
                            removeDescendants(childId);
                            
                            // Remove from dropdown
                            const opt = document.querySelector(`#select-unit-parent option[value="${childId}"]`);
                            if (opt) opt.remove();
                            
                            child.remove();
                        });
                    };
                    removeDescendants(unitId);

                    // Remove from dropdown
                    const opt = document.querySelector(`#select-unit-parent option[value="${unitId}"]`);
                    if (opt) opt.remove();

                    li.remove();

                    // If parent has no children left, remove its toggle
                    if (parentId && parentId !== "0") {
                        const siblings = document.querySelectorAll(`.unit-item[data-parent="${parentId}"]`);
                        if (siblings.length === 0) {
                            const parentLi = document.querySelector(`.unit-item[data-id="${parentId}"]`);
                            if (parentLi) {
                                const toggleBtn = parentLi.querySelector('.js-tree-toggle');
                                if (toggleBtn) {
                                    toggleBtn.remove();
                                    const flexContainer = parentLi.querySelector('.flex.items-center.gap-2.pr-2');
                                    if (flexContainer) {
                                        const div = document.createElement('div');
                                        div.className = 'w-4 h-4';
                                        flexContainer.prepend(div);
                                    }
                                }
                            }
                        }
                    }

                    const unitInput = document.getElementById('filter-units');
                    if (unitInput) unitInput.dispatchEvent(new Event('input'));
                }
                break;
            }

            case 'add-position': {
                const li = document.createElement('li');
                li.className = 'position-item flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group';
                li.dataset.title = data.item.title.toLowerCase();
                const isTeaching = data.item.is_teaching == 1;
                li.innerHTML = `
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-text">${escapeHtml(data.item.title)}</span>
                        <span class="text-[9px] font-black tracking-widest uppercase ${isTeaching ? 'text-warning-500' : 'text-zinc-400'}">${isTeaching ? 'Teaching' : 'Non-Teaching'}</span>
                    </div>
                    <form action="${form.getAttribute('action').replace('/add', '/delete')}" data-ajax="delete-position" data-confirm="Delete this position? Staff currently assigned to it will keep their account but lose that designation." data-confirm-title="Delete Position">
                        <input type="hidden" name="id" value="${data.item.id}">
                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                `;
                document.getElementById('positions-list').appendChild(li);
                form.reset();
                if (typeof filterPositions === 'function') filterPositions();
                break;
            }

            case 'add-unit': {
                const item = data.item;
                const parentId = item.parent_id || 0;
                let level = 0;
                let insertAfterNode = null;
                
                if (parentId !== 0) {
                    const parentLi = document.querySelector(`.unit-item[data-id="${parentId}"]`);
                    if (parentLi) {
                        const paddingLeft = parseInt(parentLi.style.paddingLeft || '16', 10);
                        level = (paddingLeft - 16) / 24 + 1;
                        
                        const flexContainer = parentLi.querySelector('.flex.items-center.gap-2.pr-2');
                        if (flexContainer && !flexContainer.querySelector('.js-tree-toggle')) {
                            const emptyDiv = flexContainer.querySelector('.w-4.h-4');
                            if (emptyDiv) emptyDiv.remove();
                            
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'js-tree-toggle text-text-muted hover:text-text transition-transform transform -rotate-90 focus:outline-none';
                            btn.setAttribute('onclick', `toggleUnitTree(this, ${parentId})`);
                            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>`;
                            flexContainer.prepend(btn);
                        }
                        
                        insertAfterNode = parentLi;
                        let nextNode = parentLi.nextElementSibling;
                        while (nextNode && nextNode.classList.contains('unit-item')) {
                            const nextPadding = parseInt(nextNode.style.paddingLeft || '16', 10);
                            const nextLevel = (nextPadding - 16) / 24;
                            if (nextLevel <= level - 1) break; 
                            insertAfterNode = nextNode;
                            nextNode = nextNode.nextElementSibling;
                        }
                    }
                }

                const li = document.createElement('li');
                li.className = 'unit-item flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors group';
                li.dataset.id = item.id;
                li.dataset.parent = parentId;
                li.dataset.name = item.name.toLowerCase();
                li.style.paddingLeft = `${level * 24 + 16}px`;

                let display = '';
                if (parentId !== 0) {
                    const parentLi = document.querySelector(`.unit-item[data-id="${parentId}"]`);
                    if (parentLi) {
                        const toggleBtn = parentLi.querySelector('.js-tree-toggle');
                        if (toggleBtn && toggleBtn.classList.contains('-rotate-90')) {
                            display = 'none';
                        } else if (parentLi.style.display === 'none') {
                            display = 'none';
                        }
                    }
                }
                li.style.display = display;

                li.innerHTML = `
                    <div class="flex items-center gap-2 pr-2">
                        ${parentId !== 0 ? '' : ''}
                        <div class="w-4 h-4"></div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-text">${escapeHtml(item.name)}</span>
                            ${parentId === 0 ? '<span class="text-[9px] font-bold text-info-500 uppercase tracking-widest mt-0.5">Top Level Node</span>' : ''}
                        </div>
                    </div>
                    <form action="${form.getAttribute('action').replace('/add', '/delete')}" data-ajax="delete-unit" data-confirm="Delete this unit? Staff currently assigned to it will keep their account but lose that unit. Sub-units under it will be deleted too." data-confirm-title="Delete Unit">
                        <input type="hidden" name="id" value="${item.id}">
                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                `;

                if (insertAfterNode) {
                    insertAfterNode.after(li);
                } else {
                    const emptyState = document.getElementById('units-empty-state');
                    if (emptyState) {
                        emptyState.before(li);
                    } else {
                        document.getElementById('units-list').appendChild(li);
                    }
                }

                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                document.getElementById('select-unit-parent').appendChild(opt);

                form.reset();
                const unitInput = document.getElementById('filter-units');
                if (unitInput) unitInput.dispatchEvent(new Event('input'));
                break;
            }

            case 'send-invites': {
                form.reset();

                const emptyState = document.getElementById('invitations-empty-state');
                if (emptyState) emptyState.remove();

                const tbody = document.getElementById('invitations-table-body');
                (data.invitations || []).forEach((inv) => {
                    const tr = document.createElement('tr');
                    tr.className = 'invite-row block lg:table-row bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors';
                    tr.dataset.id = inv.id;
                    tr.dataset.email = inv.email.toLowerCase();
                    tr.dataset.status = 'pending';
                    tr.dataset.expired = '0';
                    tr.dataset.role = inv.role_name;
                    tr.innerHTML = `
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                            <div class="flex flex-col min-w-0 pr-4">
                                <span class="text-sm font-bold text-text truncate">${escapeHtml(inv.email)}</span>
                                <span class="text-[10px] font-bold text-text-muted tracking-widest truncate">Invited ${escapeHtml(inv.created_display)}</span>
                            </div>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                            <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest">
                                ${escapeHtml(inv.role_name)}
                            </span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 text-left lg:text-center">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-warning-500">Pending</span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4">
                            <span class="text-xs font-bold text-text">${escapeHtml(inv.expires_display)}</span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                            <form action="/account/invite/delete" data-ajax="delete-invite" data-confirm="Delete this invitation? If it hasn't been accepted yet, the invite link will stop working." data-confirm-title="Delete Invitation">
                                <input type="hidden" name="id" value="${inv.id}">
                                <button type="submit" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg border border-transparent text-danger-500 lg:hover:bg-danger-50 lg:dark:hover:bg-danger-500/10 focus:outline-none transition-colors cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </td>
                    `;
                    tbody.prepend(tr);
                });

                filterInvitations();

                window.appAlert(data.message, { title: 'Invitations Sent', variant: 'info' });
                break;
            }

            case 'delete-invite': {
                form.closest('.invite-row').remove();
                filterInvitations();
                break;
            }
        }
    }

    // Note: the email queue poller used to live here (processEmailQueue()), but it's
    // now handled globally by header.js's processBackgroundEmails() on every
    // authenticated page, not just this one - removed to stop this page from
    // double-polling /account/process-queue alongside the global poller.

    // --- TAB SWITCHING LOGIC (Updated to toggle lg:absolute) ---
    // Reflect the active tab in the URL (/accounts/<tab>, or bare /accounts for the
    // default directory tab) so a refresh lands back on the same tab instead of
    // always resetting to User Directory.
    const ACCOUNTS_BASE_URL = "<?= site_url('accounts') ?>";

    function switchUserTab(tabId, pushUrl = true) {
        if (tabId === 'directory' && window.systemDataChanged) {
            window.location.href = ACCOUNTS_BASE_URL;
            return;
        }

        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'lg:absolute', 'lg:inset-0');
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted');
        });

        document.getElementById('tab-content-' + tabId).classList.remove('hidden');
        document.getElementById('tab-content-' + tabId).classList.add('flex', 'lg:absolute', 'lg:inset-0');

        document.getElementById('tab-btn-' + tabId).classList.remove('border-transparent', 'text-text-muted');
        document.getElementById('tab-btn-' + tabId).classList.add('border-accent', 'text-accent');

        if (pushUrl) {
            const url = tabId === 'directory' ? ACCOUNTS_BASE_URL : ACCOUNTS_BASE_URL + '/' + tabId;
            history.pushState({ tab: tabId }, '', url);
        }
    }

    // The initial page load's history entry has no pushState-set state yet - give
    // it one so navigating back to it (after switching tabs) restores the right tab.
    history.replaceState({ tab: '<?= $activeTab ?>' }, '', window.location.href);

    window.addEventListener('popstate', (e) => {
        switchUserTab((e.state && e.state.tab) || 'directory', false);
    });

    // --- SEARCH AND FILTER LOGIC (Updated for Sidebar Checkboxes & Mobile Overlay) ---
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('filter-search');
        const rows        = document.querySelectorAll('.user-dir-row');
        const emptyState  = document.getElementById('empty-filter-state');
        const countBadge  = document.getElementById('directory-count');
        const mobileCountBadge = document.getElementById('mobile-directory-count');
        const checkboxes  = document.querySelectorAll('.directory-filter-checkbox');
        const clearBtn    = document.getElementById('clear-directory-filters');

        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('directory-sidebar');
        const overlay = document.getElementById('mobile-filter-overlay');
        const openBtn = document.getElementById('open-mobile-filters');
        const closeBtn = document.getElementById('close-mobile-filters');

        function toggleMobileSidebar(show) {
            if (show) {
                overlay.classList.remove('hidden');
                // Small delay to allow display block to apply before opacity transition
                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    sidebar.classList.remove('-translate-x-full');
                });
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.add('opacity-0');
                sidebar.classList.add('-translate-x-full');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300); // match transition duration
                document.body.style.overflow = '';
            }
        }

        if (openBtn) openBtn.addEventListener('click', () => toggleMobileSidebar(true));
        if (closeBtn) closeBtn.addEventListener('click', () => toggleMobileSidebar(false));
        if (overlay) overlay.addEventListener('click', () => toggleMobileSidebar(false));

        // Get checked values for a specific name
        function getCheckedValues(name) {
            return Array.from(document.querySelectorAll(`input[name="${name}[]"]:checked`)).map(cb => cb.value);
        }

        function filterUsers() {
            const query = searchInput ? searchInput.value.toLowerCase() : '';
            const checkedRoles = getCheckedValues('filter_role');
            const checkedDepts = getCheckedValues('filter_dept');
            const checkedPos   = getCheckedValues('filter_pos');
            const checkedStatus = getCheckedValues('filter_status');
            
            let visibleCount = 0;

            rows.forEach(row => {
                const rowName   = row.getAttribute('data-name');
                const rowEmail  = row.getAttribute('data-email');
                const rowRole   = row.getAttribute('data-role');
                const rowDept   = row.getAttribute('data-dept');
                const rowPos    = row.getAttribute('data-position');
                const rowStatus = row.getAttribute('data-status');

                const matchesSearch = rowName.includes(query) || rowEmail.includes(query);
                const matchesRole   = checkedRoles.length === 0 || checkedRoles.includes(rowRole);
                const matchesDept   = checkedDepts.length === 0 || checkedDepts.includes(rowDept);
                const matchesPos    = checkedPos.length === 0 || checkedPos.includes(rowPos);
                const matchesStatus = checkedStatus.length === 0 || checkedStatus.includes(rowStatus);

                if (matchesSearch && matchesRole && matchesDept && matchesPos && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (countBadge) countBadge.textContent = visibleCount;
            if (mobileCountBadge) mobileCountBadge.textContent = visibleCount;
            if (emptyState) emptyState.style.display = visibleCount === 0 ? '' : 'none';
        }

        if (searchInput) searchInput.addEventListener('input', filterUsers);
        checkboxes.forEach(cb => {
            cb.addEventListener('change', (e) => {
                const name = e.target.name;
                if (name === 'filter_role[]' || name === 'filter_status[]') {
                    if (e.target.checked) {
                        document.querySelectorAll(`input[name="${name}"]`).forEach(otherCb => {
                            if (otherCb !== e.target) otherCb.checked = false;
                        });
                    }
                }
                filterUsers();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                checkboxes.forEach(cb => cb.checked = false);
                filterUsers();
            });
        }

        // Mini-search logic for Units
        const miniSearchUnits = document.getElementById('mini-search-units');
        if (miniSearchUnits) {
            miniSearchUnits.addEventListener('input', (e) => {
                const q = e.target.value.toLowerCase();
                document.querySelectorAll('.unit-node').forEach(node => {
                    const name = node.getAttribute('data-name');
                    const header = node.firstElementChild;
                    if (name.includes(q)) {
                        header.style.display = '';
                    } else {
                        header.style.display = 'none';
                    }
                });
            });
        }

        // Mini-search logic for Positions
        const miniSearchPositions = document.getElementById('mini-search-positions');
        if (miniSearchPositions) {
            miniSearchPositions.addEventListener('input', (e) => {
                const q = e.target.value.toLowerCase();
                document.querySelectorAll('.position-filter-label').forEach(label => {
                    const title = label.getAttribute('data-title');
                    label.style.display = title.includes(q) ? '' : 'none';
                });
            });
        }
    });

    // --- INVITATIONS TAB: SEARCH, STATUS/EXPIRY/ROLE FILTERS, AND "DELETE FILTERED" ---
    function filterInvitations() {
        const searchInput = document.getElementById('invite-filter-search');
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        const checkedRoles = getCheckedValues('invite_filter_role');
        const checkedStatus = getCheckedValues('invite_filter_status');
        const checkedExpiry = getCheckedValues('invite_filter_expiry');

        const rows = document.querySelectorAll('.invite-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const matchesSearch = row.dataset.email.includes(query);
            const matchesRole = checkedRoles.length === 0 || checkedRoles.includes(row.dataset.role);
            const matchesStatus = checkedStatus.length === 0 || checkedStatus.includes(row.dataset.status);
            
            // Expiry logic: 'expired' or 'not-expired'
            const isExpired = row.dataset.expired === '1';
            let matchesExpiry = true;
            if (checkedExpiry.length > 0) {
                if (isExpired && !checkedExpiry.includes('expired')) matchesExpiry = false;
                if (!isExpired && !checkedExpiry.includes('not-expired')) matchesExpiry = false;
            }

            if (matchesSearch && matchesRole && matchesStatus && matchesExpiry) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('invitations-count');
        if (countBadge) countBadge.textContent = visibleCount;
        
        const mobileCountBadge = document.getElementById('mobile-invitations-count');
        if (mobileCountBadge) mobileCountBadge.textContent = visibleCount;

        const filteredCountLabel = document.getElementById('invite-filtered-count');
        if (filteredCountLabel) filteredCountLabel.textContent = visibleCount;

        const filterEmptyState = document.getElementById('invitations-filter-empty-state');
        if (filterEmptyState) {
            filterEmptyState.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const inviteSearch = document.getElementById('invite-filter-search');
        if (inviteSearch) inviteSearch.addEventListener('input', filterInvitations);

        const inviteCheckboxes = document.querySelectorAll('.invite-filter-checkbox');
        inviteCheckboxes.forEach(cb => {
            cb.addEventListener('change', (e) => {
                const name = e.target.name;
                if (name === 'invite_filter_role[]' || name === 'invite_filter_status[]' || name === 'invite_filter_expiry[]') {
                    if (e.target.checked) {
                        document.querySelectorAll(`input[name="${name}"]`).forEach(otherCb => {
                            if (otherCb !== e.target) otherCb.checked = false;
                        });
                    }
                }
                filterInvitations();
            });
        });

        const btnDeleteFiltered = document.getElementById('btn-delete-filtered');
        // Delete filtered logic ... (we will keep the old event listener below)
        
        // --- MOBILE INVITE FILTERS TOGGLE ---
        const openMobileInviteFilters = document.getElementById('open-mobile-invite-filters');
        const closeMobileInviteFilters = document.getElementById('close-mobile-invite-filters');
        const inviteSidebar = document.getElementById('invitations-sidebar');
        const mobileInviteOverlay = document.getElementById('mobile-invite-filter-overlay');

        function toggleMobileInviteFilters(show) {
            if (!inviteSidebar || !mobileInviteOverlay) return;
            if (show) {
                mobileInviteOverlay.classList.remove('hidden');
                // trigger reflow
                void mobileInviteOverlay.offsetWidth;
                mobileInviteOverlay.classList.add('opacity-100');
                inviteSidebar.classList.remove('-translate-x-full');
            } else {
                mobileInviteOverlay.classList.remove('opacity-100');
                inviteSidebar.classList.add('-translate-x-full');
                setTimeout(() => {
                    mobileInviteOverlay.classList.add('hidden');
                }, 300);
            }
        }

        if (openMobileInviteFilters) {
            openMobileInviteFilters.addEventListener('click', () => toggleMobileInviteFilters(true));
        }
        if (closeMobileInviteFilters) {
            closeMobileInviteFilters.addEventListener('click', () => toggleMobileInviteFilters(false));
        }
        if (mobileInviteOverlay) {
            mobileInviteOverlay.addEventListener('click', () => toggleMobileInviteFilters(false));
        }

        if (btnDeleteFiltered) {
            btnDeleteFiltered.addEventListener('click', async () => {
                const visibleRows = Array.from(document.querySelectorAll('.invite-row')).filter(row => row.style.display !== 'none');
                if (visibleRows.length === 0) {
                    window.appAlert('No invitations match the current filter.', { variant: 'warning' });
                    return;
                }

                const ok = await window.appConfirm(
                    `Delete these ${visibleRows.length} invitation(s)? Any that haven't been accepted yet will stop working.`,
                    { title: 'Delete Filtered Invitations', confirmText: 'Delete All' }
                );
                if (!ok) return;

                btnDeleteFiltered.disabled = true;

                const formData = new FormData();
                visibleRows.forEach(row => formData.append('ids[]', row.dataset.id));

                apiPost('/account/invite/delete-bulk', formData, {
                    onSuccess: () => {
                        visibleRows.forEach(row => row.remove());
                        filterInvitations();
                        window.appAlert('Deleted successfully.', { title: 'Invitations Deleted', variant: 'info' });
                        btnDeleteFiltered.disabled = false;
                    },
                    onError: (errMsg) => {
                        window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                        btnDeleteFiltered.disabled = false;
                    }
                });
            });
        }

        filterInvitations();
    });

    // --- SYSTEM DATA TAB: JOB POSITIONS PAGINATION & SEARCH ---
    let posCurrentPage = 1;
    const posPerPage = 10;
    let posFilteredRows = [];

    function renderPositionsPage() {
        const list = document.getElementById('positions-list');
        if (!list) return;
        const allRows = Array.from(list.querySelectorAll('li.position-item'));
        
        allRows.forEach(row => row.style.display = 'none');
        
        const start = (posCurrentPage - 1) * posPerPage;
        const end = start + posPerPage;
        posFilteredRows.slice(start, end).forEach(row => row.style.display = '');
        
        const totalPages = Math.ceil(posFilteredRows.length / posPerPage) || 1;
        document.getElementById('pos-page-info').textContent = `Page ${posCurrentPage} of ${totalPages}`;
        document.getElementById('pos-prev').disabled = posCurrentPage === 1;
        document.getElementById('pos-next').disabled = posCurrentPage === totalPages || posFilteredRows.length === 0;
        
        const emptyState = document.getElementById('positions-empty-state');
        if (emptyState) emptyState.style.display = posFilteredRows.length === 0 ? '' : 'none';
    }

    function filterPositions() {
        const input = document.getElementById('filter-positions');
        if (!input) return;
        const query = input.value.trim().toLowerCase();
        const list = document.getElementById('positions-list');
        const allRows = Array.from(list.querySelectorAll('li.position-item'));
        
        posFilteredRows = allRows.filter(row => row.dataset.title.includes(query));
        posCurrentPage = 1;
        renderPositionsPage();
    }

    const posInput = document.getElementById('filter-positions');
    if (posInput) posInput.addEventListener('input', filterPositions);
    
    const posPrevBtn = document.getElementById('pos-prev');
    if (posPrevBtn) posPrevBtn.addEventListener('click', () => { if (posCurrentPage > 1) { posCurrentPage--; renderPositionsPage(); } });
    
    const posNextBtn = document.getElementById('pos-next');
    if (posNextBtn) posNextBtn.addEventListener('click', () => { 
        if (posCurrentPage < Math.ceil(posFilteredRows.length / posPerPage)) { posCurrentPage++; renderPositionsPage(); } 
    });

    const positionsList = document.getElementById('positions-list');
    if (positionsList) {
        posFilteredRows = Array.from(positionsList.querySelectorAll('li.position-item'));
        renderPositionsPage();
    }

    // --- SYSTEM DATA TAB: DEPARTMENTS & UNITS TREE VIEW ---
    function toggleUnitTree(btn, parentId) {
        const isCollapsed = btn.classList.contains('-rotate-90');
        if (isCollapsed) {
            btn.classList.remove('-rotate-90');
            document.querySelectorAll(`.unit-item[data-parent="${parentId}"]`).forEach(li => {
                li.style.display = '';
            });
        } else {
            btn.classList.add('-rotate-90');
            hideDescendants(parentId);
        }
    }

    function hideDescendants(parentId) {
        document.querySelectorAll(`.unit-item[data-parent="${parentId}"]`).forEach(li => {
            li.style.display = 'none';
            const childId = li.dataset.id;
            const btn = li.querySelector('.js-tree-toggle');
            if (btn) btn.classList.add('-rotate-90');
            hideDescendants(childId);
        });
    }

    const unitInput = document.getElementById('filter-units');
    if (unitInput) {
        unitInput.addEventListener('input', () => {
            const query = unitInput.value.trim().toLowerCase();
            const list = document.getElementById('units-list');
            let visibleCount = 0;

            if (query === '') {
                list.querySelectorAll('.unit-item').forEach(li => {
                    li.style.display = li.dataset.parent === "0" ? '' : 'none';
                    const btn = li.querySelector('.js-tree-toggle');
                    if (btn) {
                        btn.classList.add('-rotate-90');
                        btn.style.visibility = ''; 
                    }
                });
            } else {
                list.querySelectorAll('.unit-item').forEach(li => li.style.display = 'none');
                list.querySelectorAll('.unit-item').forEach(li => {
                    if (li.dataset.name.includes(query)) {
                        li.style.display = '';
                        visibleCount++;
                        let pId = li.dataset.parent;
                        while (pId !== "0" && pId !== "") {
                            const parentLi = document.querySelector(`.unit-item[data-id="${pId}"]`);
                            if (parentLi) {
                                parentLi.style.display = '';
                                const pBtn = parentLi.querySelector('.js-tree-toggle');
                                if (pBtn) pBtn.classList.remove('-rotate-90'); 
                                pId = parentLi.dataset.parent;
                            } else {
                                break;
                            }
                        }
                    }
                    const btn = li.querySelector('.js-tree-toggle');
                    if (btn) btn.style.visibility = 'hidden'; 
                });
            }
            const emptyState = document.getElementById('units-empty-state');
            if (emptyState) emptyState.style.display = query !== '' && visibleCount === 0 ? '' : 'none';
        });
    }
</script>
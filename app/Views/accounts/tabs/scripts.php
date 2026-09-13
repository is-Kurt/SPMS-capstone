<style>
    .page-hidden { display: none !important; }
</style>
<script>
    class ClientPaginator {
        constructor(config) {
            this.pageSize = config.pageSize || 10;
            this.currentPage = 1;
            this.containerId = config.containerId;
            this.scrollTargetSelector = config.scrollTargetSelector || null;
            this.items = [];
            this.allRows = [];
            this.initContainer();
        }

        initContainer() {
            if (!this.container || !this.container.isConnected) {
                this.container = document.getElementById(this.containerId);
            }
            if (!this.container) return;
            
            this.btnFirst = this.container.querySelector('.js-page-first');
            this.btnPrev = this.container.querySelector('.js-page-prev');
            this.btnNext = this.container.querySelector('.js-page-next');
            this.btnLast = this.container.querySelector('.js-page-last');
            this.pageNumbersContainer = this.container.querySelector('.js-page-numbers');
            this.pageSizeSelect = this.container.querySelector('.js-page-size');

            this.lblStart = this.container.querySelector('[id$="-page-start"]');
            this.lblEnd = this.container.querySelector('[id$="-page-end"]');
            this.lblTotal = this.container.querySelector('[id$="-page-total"]');
            
            if (this.btnFirst && !this.btnFirst._paginatorBound) {
                this.btnFirst._paginatorBound = true;
                this.btnFirst.addEventListener('click', () => this.goToPage(1));
            }
            if (this.btnPrev && !this.btnPrev._paginatorBound) {
                this.btnPrev._paginatorBound = true;
                this.btnPrev.addEventListener('click', () => this.goToPage(this.currentPage - 1));
            }
            if (this.btnNext && !this.btnNext._paginatorBound) {
                this.btnNext._paginatorBound = true;
                this.btnNext.addEventListener('click', () => this.goToPage(this.currentPage + 1));
            }
            if (this.btnLast && !this.btnLast._paginatorBound) {
                this.btnLast._paginatorBound = true;
                this.btnLast.addEventListener('click', () => {
                    const totalPages = Math.ceil(this.items.length / this.pageSize) || 1;
                    this.goToPage(totalPages);
                });
            }
            if (this.pageSizeSelect && !this.pageSizeSelect._paginatorBound) {
                this.pageSizeSelect._paginatorBound = true;
                this.pageSizeSelect.addEventListener('change', (e) => {
                    const val = e.target.value;
                    this.pageSize = val === 'all' ? 999999 : (parseInt(val, 10) || 10);
                    this.currentPage = 1;
                    this.render();
                });
            }
        }
        
        init(allRows) {
            this.allRows = allRows || [];
            this.initContainer();
        }

        updateItems(newItems) {
            this.items = newItems || [];
            this.currentPage = 1;
            this.render();
        }
        
        goToPage(page) {
            const total = this.items.length;
            const totalPages = Math.ceil(total / this.pageSize) || 1;
            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            this.currentPage = page;
            this.render();

            if (this.scrollTargetSelector) {
                const scrollEl = document.querySelector(this.scrollTargetSelector);
                if (scrollEl) {
                    scrollEl.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
        
        render() {
            this.initContainer();
            const total = this.items.length;
            const totalPages = Math.ceil(total / this.pageSize) || 1;
            
            if (this.currentPage > totalPages) this.currentPage = totalPages;
            if (this.currentPage < 1) this.currentPage = 1;
            
            const startIdx = (this.currentPage - 1) * this.pageSize;
            const endIdx = Math.min(startIdx + this.pageSize, total);
            
            // Hide all rows in this list using both CSS class and direct inline style
            if (this.allRows && this.allRows.length > 0) {
                this.allRows.forEach(el => {
                    if (el) {
                        el.classList.add('page-hidden');
                        el.style.display = 'none';
                    }
                });
            }
            
            // Show current page items
            for (let i = startIdx; i < endIdx; i++) {
                if (this.items[i]) {
                    this.items[i].classList.remove('page-hidden');
                    this.items[i].style.display = '';
                }
            }
            
            if (this.container) {
                if (this.lblStart) this.lblStart.textContent = total === 0 ? 0 : startIdx + 1;
                if (this.lblEnd) this.lblEnd.textContent = endIdx;
                if (this.lblTotal) {
                    if (this.lblTotal.id && this.lblTotal.id.startsWith('f-')) {
                        this.lblTotal.textContent = total;
                    } else if (this.containerId === 'invitations-pagination') {
                        this.lblTotal.textContent = total + (total === 1 ? ' invitation' : ' invitations');
                    } else if (this.containerId === 'directory-pagination') {
                        this.lblTotal.textContent = total + (total === 1 ? ' user' : ' users');
                    } else {
                        this.lblTotal.textContent = total + (total === 1 ? ' entry' : ' entries');
                    }
                }
                
                if (this.btnFirst) this.btnFirst.disabled = this.currentPage <= 1;
                if (this.btnPrev) this.btnPrev.disabled = this.currentPage <= 1;
                if (this.btnNext) this.btnNext.disabled = this.currentPage >= totalPages;
                if (this.btnLast) this.btnLast.disabled = this.currentPage >= totalPages;
                
                if (this.pageNumbersContainer) {
                    this.renderPageNumbers(totalPages);
                }

                this.container.classList.toggle('hidden', total === 0);
            }
        }

        renderPageNumbers(totalPages) {
            this.pageNumbersContainer.innerHTML = '';
            if (totalPages <= 1) return;

            const pages = [];
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                let left = Math.max(2, this.currentPage - 1);
                let right = Math.min(totalPages - 1, this.currentPage + 1);

                if (this.currentPage <= 3) {
                    left = 2;
                    right = 4;
                } else if (this.currentPage >= totalPages - 2) {
                    left = totalPages - 3;
                    right = totalPages - 1;
                }

                if (left > 2) pages.push('...');
                for (let i = left; i <= right; i++) pages.push(i);
                if (right < totalPages - 1) pages.push('...');
                pages.push(totalPages);
            }

            pages.forEach(p => {
                if (p === '...') {
                    const span = document.createElement('span');
                    span.className = 'w-6 text-center text-xs text-text-muted font-bold select-none';
                    span.textContent = '...';
                    this.pageNumbersContainer.appendChild(span);
                } else {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const isActive = p === this.currentPage;
                    btn.className = isActive
                        ? 'min-w-[32px] h-8 px-2.5 rounded-lg bg-emerald-600 text-white font-black text-xs shadow-sm flex items-center justify-center cursor-default'
                        : 'min-w-[32px] h-8 px-2.5 rounded-lg border border-surface-border bg-surface text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 font-bold text-xs transition-colors flex items-center justify-center cursor-pointer shadow-sm';
                    btn.textContent = p;
                    if (!isActive) {
                        btn.addEventListener('click', () => this.goToPage(p));
                    }
                    this.pageNumbersContainer.appendChild(btn);
                }
            });
        }
    }
    
    // Global paginator instances
    const dirPaginator = new ClientPaginator({ containerId: 'directory-pagination', pageSize: 10 });
    const invPaginator = new ClientPaginator({ containerId: 'invitations-pagination', pageSize: 10 });
    const posPaginator = new ClientPaginator({ containerId: 'positions-pagination', pageSize: 10 });
    const uniPaginator = new ClientPaginator({ containerId: 'units-pagination', pageSize: 15 });
    window.dirPaginator = dirPaginator;
    window.invPaginator = invPaginator;

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

    async function handleAccountsAjaxSuccess(action, form, data) {
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
                    if (el.tagName === 'BUTTON') {
                        el.className = `js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest cursor-pointer transition-colors focus:outline-none ${isActive ? 'text-success-500 hover:text-success-600' : 'text-danger-500 hover:text-danger-600'}`;
                    } else {
                        el.className = `js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest ${isActive ? 'text-success-500' : 'text-danger-500'}`;
                    }
                });
                row.querySelectorAll('.js-status-badge-mobile').forEach(el => {
                    el.textContent = isActive ? 'Active' : 'Disabled';
                    if (el.tagName === 'BUTTON') {
                        el.className = `js-status-badge-mobile w-full px-3 py-2.5 rounded-lg text-xs font-bold border border-transparent transition-colors cursor-pointer text-center ${isActive ? 'text-success-600 dark:text-success-500 bg-success-50 dark:bg-success-500/10 hover:bg-success-100 dark:hover:bg-success-500/20' : 'text-danger-500 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-100 dark:hover:bg-danger-500/20'}`;
                    } else {
                        el.className = `js-status-badge-mobile w-full px-3 py-2.5 rounded-lg text-xs font-bold text-center border border-transparent ${isActive ? 'text-success-600 dark:text-success-500 bg-success-50 dark:bg-success-500/10' : 'text-danger-500 bg-danger-50 dark:bg-danger-500/10'}`;
                    }
                });
                break;
            }

            case 'delete-user': {
                form.closest('.user-dir-row').remove();
                const dirTableBody = document.getElementById('user-table-body');
                if (dirTableBody) {
                    dirPaginator.init(Array.from(dirTableBody.querySelectorAll('.user-dir-row')));
                }
                if (typeof filterUsers === 'function') filterUsers();
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
                    const opt1 = document.querySelector(`#parent-options-list li[data-value="${childId}"]`);
                    if (opt1) opt1.remove();
                            
                            child.remove();
                        });
                    };
                    removeDescendants(unitId);

                    // Remove from dropdown
                    const opt2 = document.querySelector(`#parent-options-list li[data-value="${unitId}"]`);
                    if (opt2) opt2.remove();

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

            case 'update-unit': {
                const modal = document.getElementById('modal-edit-unit');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                await window.appAlert('Unit updated successfully!', { title: 'Unit Updated', variant: 'success' });
                window.location.reload();
                break;
            }

            case 'add-position': {
                const li = document.createElement('li');
                li.className = 'position-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group';
                li.dataset.title = data.item.title.toLowerCase();
                const isTeaching = data.item.is_teaching == 1;
                li.innerHTML = `
                    <div class="flex flex-col justify-center lg:table-cell px-6 py-4 align-middle h-[72px]">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-text">${escapeHtml(data.item.title)}</span>
                                <span class="text-[9px] font-black tracking-widest uppercase ${isTeaching ? 'text-warning-500' : 'text-zinc-400'}">${isTeaching ? 'Teaching' : 'Non-Teaching'}</span>
                            </div>
                            <form action="${form.getAttribute('action').replace('/add', '/delete')}" method="post" data-ajax="delete-position" data-confirm="Delete this position? Staff currently assigned to it will keep their account but lose that designation." data-confirm-title="Delete Position">
                                <input type="hidden" name="id" value="${data.item.id}">
                                <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
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
                li.className = 'unit-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group';
                li.dataset.id = item.id;
                li.dataset.parent = parentId;
                li.dataset.name = item.name.toLowerCase();

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
                    <div class="flex flex-col justify-center lg:table-cell py-4 align-middle h-[72px]" style="padding-left: ${level * 24 + 24}px; padding-right: 24px;">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-2 pr-2">
                                ${parentId !== 0 ? '' : ''}
                                <div class="w-6 h-4"></div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-text">${escapeHtml(item.name)}</span>
                                    ${parentId === 0 ? '<span class="text-[9px] font-bold text-info-500 uppercase tracking-widest mt-0.5">Top Level Node</span>' : ''}
                                </div>
                            </div>
                            <form action="${form.getAttribute('action').replace('/add', '/delete')}" method="post" data-ajax="delete-unit" data-confirm="Delete this unit? Staff currently assigned to it will keep their account but lose that unit. Sub-units under it will be deleted too." data-confirm-title="Delete Unit">
                                <input type="hidden" name="id" value="${item.id}">
                                <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
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

                const ul = document.getElementById('parent-options-list');
                if (ul) {
                    const liOpt = document.createElement('li');
                    liOpt.className = 'parent-option px-3 py-2 text-sm text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg cursor-pointer transition-colors truncate';
                    liOpt.dataset.value = item.id;
                    liOpt.textContent = item.name;
                    ul.appendChild(liOpt);
                    
                    // Re-bind the click event to the newly added option
                    liOpt.addEventListener('click', () => {
                        const hiddenInput = document.getElementById('hidden-parent-id');
                        const searchInput = document.getElementById('parent-search-input');
                        if (hiddenInput) hiddenInput.value = item.id;
                        if (searchInput) searchInput.value = item.name;
                        
                        const allOptions = ul.querySelectorAll('.parent-option');
                        allOptions.forEach(o => {
                            o.classList.remove('bg-accent/10', 'text-accent', 'font-bold');
                            o.classList.add('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                        });
                        liOpt.classList.remove('text-text', 'hover:bg-zinc-50', 'dark:hover:bg-zinc-800/50');
                        liOpt.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                    });
                }

                form.querySelector('input[name="name"]').value = '';
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
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4">
                            <div class="flex justify-between items-center lg:block">
                                <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest">
                                    ${escapeHtml(inv.role_name)}
                                </span>
                                <span class="lg:hidden px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border bg-zinc-50 dark:bg-zinc-800/50 border-surface-border text-warning-500 border-warning-200/50 dark:border-warning-500/30">
                                    Pending
                                </span>
                            </div>
                        </td>
                        <td class="hidden lg:table-cell px-0 lg:px-6 py-2 lg:py-4 text-left lg:text-center">
                            <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border bg-zinc-50 dark:bg-zinc-800/50 border-surface-border text-warning-500 border-warning-200/50 dark:border-warning-500/30">
                                Pending
                            </span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4">
                            <div class="flex items-center gap-2 text-xs font-bold text-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>${escapeHtml(inv.expires_display)}</span>
                            </div>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right">
                            <div class="flex items-center justify-between lg:justify-end gap-2 w-full">
                                <form action="/account/invite/resend" method="post" class="flex-1 lg:flex-none inline-block" data-ajax="resend-invitation">
                                    <input type="hidden" name="id" value="${inv.id}">
                                    <button type="submit" class="w-full px-3 py-2.5 lg:py-1.5 rounded-lg text-xs font-bold text-accent bg-accent/10 lg:bg-transparent hover:bg-accent/20 lg:hover:bg-accent/10 border border-transparent transition-colors cursor-pointer text-center" title="Resend Email">Resend</button>
                                </form>
                                <form action="/account/invite/delete" method="post" class="flex-1 lg:flex-none inline-block" data-ajax="delete-invitation" data-confirm="Revoke this invitation? The link will immediately stop working." data-confirm-title="Revoke Invitation">
                                    <input type="hidden" name="id" value="${inv.id}">
                                    <button type="submit" class="w-full px-3 py-2.5 lg:py-1.5 rounded-lg text-xs font-bold text-danger-500 bg-danger-50 dark:bg-danger-500/10 lg:bg-transparent dark:lg:bg-transparent hover:bg-danger-100 dark:hover:bg-danger-500/20 border border-transparent transition-colors cursor-pointer text-center" title="Revoke Invitation">Revoke</button>
                                </form>
                            </div>
                        </td>
                    `;
                    tbody.prepend(tr);
                });

                filterInvitations();

                window.appAlert(data.message, { title: 'Invitations Sent', variant: 'info' });
                break;
            }

            case 'delete-invite':
            case 'delete-invitation': {
                form.closest('.invite-row').remove();
                filterInvitations();
                break;
            }
            
            case 'resend-invitation': {
                window.appAlert(data.message, { title: 'Invitation Resent', variant: 'info' });
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
    window.SPMS_UNITS_MAP = <?= json_encode(array_map(function($u) {
        return [
            'id' => (int)$u['id'],
            'name' => $u['name'],
            'parent_id' => $u['parent_id'] ? (int)$u['parent_id'] : 0
        ];
    }, $units ?? [])) ?>;

    // Build descendants lookup: given checked unit names, return a Set of those units plus all their child/descendant units
    function getExpandedUnitNames(checkedNames) {
        if (!checkedNames || checkedNames.length === 0) return new Set();
        const units = window.SPMS_UNITS_MAP || [];
        const nameToIds = new Map();
        const childrenMap = new Map();

        units.forEach(u => {
            const lower = (u.name || '').trim().toLowerCase();
            if (!nameToIds.has(lower)) nameToIds.set(lower, []);
            nameToIds.get(lower).push(u.id);
            const pid = u.parent_id || 0;
            if (!childrenMap.has(pid)) childrenMap.set(pid, []);
            childrenMap.get(pid).push(u);
        });

        const result = new Set();

        function addDescendants(unitId) {
            const children = childrenMap.get(unitId) || [];
            children.forEach(child => {
                result.add((child.name || '').trim().toLowerCase());
                addDescendants(child.id);
            });
        }

        checkedNames.forEach(name => {
            const lower = (name || '').trim().toLowerCase();
            result.add(lower);
            const ids = nameToIds.get(lower) || [];
            ids.forEach(uId => {
                addDescendants(uId);
            });
        });

        return result;
    }
    window.getExpandedUnitNames = getExpandedUnitNames;

    // Tree checkbox synchronization: when checking a parent college or unit, cascade checked state to its child departments
    function handleUnitCheckboxChange(checkbox) {
        if (!checkbox) return;
        const unitNode = checkbox.closest('.unit-node');
        if (unitNode) {
            // Cascade down: check/uncheck all descendants
            const childrenContainer = unitNode.querySelector(':scope > .unit-children');
            if (childrenContainer) {
                const childCheckboxes = childrenContainer.querySelectorAll('.js-unit-checkbox');
                childCheckboxes.forEach(childCb => {
                    childCb.checked = checkbox.checked;
                });
            }
        }
        // Cascade up: if this checkbox was unchecked, uncheck ancestor checkboxes
        if (!checkbox.checked && unitNode) {
            let parentNode = unitNode.parentElement.closest('.unit-node');
            while (parentNode) {
                const parentCb = parentNode.querySelector(':scope > div .js-unit-checkbox');
                if (parentCb) {
                    parentCb.checked = false;
                }
                parentNode = parentNode.parentElement.closest('.unit-node');
            }
        }
    }
    window.handleUnitCheckboxChange = handleUnitCheckboxChange;

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

        const targetContent = document.getElementById('tab-content-' + tabId);
        if (targetContent) {
            targetContent.classList.remove('hidden');
            targetContent.classList.add('flex', 'lg:absolute', 'lg:inset-0');
        }

        const targetBtn = document.getElementById('tab-btn-' + tabId);
        if (targetBtn) {
            targetBtn.classList.remove('border-transparent', 'text-text-muted');
            targetBtn.classList.add('border-accent', 'text-accent');
        }

        if (pushUrl && window.history && window.history.pushState) {
            const url = tabId === 'directory' ? ACCOUNTS_BASE_URL : ACCOUNTS_BASE_URL + '/' + tabId;
            history.pushState({ tab: tabId, spmsRoot: true }, '', url);
        }
    }
    window.switchUserTab = switchUserTab;

    // The initial page load's history entry
    try {
        history.replaceState({ tab: '<?= $activeTab ?>', spmsRoot: true }, '', window.location.href);
    } catch (e) {}

    window.addEventListener('popstate', (e) => {
        const tab = (e.state && e.state.tab) || '<?= $activeTab ?>' || 'directory';
        switchUserTab(tab, false);
    });

    // --- SYSTEM DATA SUB-TABS ---
    function switchSystemTab(tabId) {
        // Update Buttons
        document.querySelectorAll('.subtab-btn').forEach(btn => {
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted', 'hover:text-text', 'hover:border-surface-border');
        });
        const activeBtn = document.getElementById('subtab-btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-text-muted', 'hover:text-text', 'hover:border-surface-border');
            activeBtn.classList.add('border-accent', 'text-accent');
        }

        // Update Panels
        document.querySelectorAll('.system-panel').forEach(panel => {
            panel.classList.add('hidden');
            panel.classList.remove('flex');
        });
        const activePanel = document.getElementById('system-panel-' + tabId);
        if (activePanel) {
            activePanel.classList.remove('hidden');
            activePanel.classList.add('flex');
        }
    }
    window.switchSystemTab = switchSystemTab;

    // Get checked values for a specific name
    function getCheckedValues(name) {
        const checkboxes = document.querySelectorAll(`input[name="${name}[]"]:checked, input[name="${name}"]:checked`);
        return Array.from(checkboxes).map(cb => cb.value);
    }
    window.getCheckedValues = getCheckedValues;

    // --- CASCADING COLLEGE & SUB-DEPARTMENT HANDLERS ---
    function onCollegeFilterChange(collegeId) {
        const deptSelect = document.getElementById('filter-dept-select');
        const countPill = document.getElementById('subdept-count-pill');
        if (!deptSelect) return;

        if (!collegeId) {
            deptSelect.innerHTML = '<option value="">All Departments</option>';
            deptSelect.value = '';
            deptSelect.disabled = true;
            deptSelect.classList.add('opacity-60');
            deptSelect.classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-400');
            if (countPill) countPill.textContent = '(All)';
            filterUsers();
            return;
        }

        const numericCollegeId = parseInt(collegeId, 10);
        const units = window.SPMS_UNITS_MAP || [];
        const college = units.find(u => u.id === numericCollegeId);
        const collegeName = college ? college.name : '';

        // Find children of this college
        const children = units.filter(u => u.parent_id === numericCollegeId);

        // Form college-specific label
        let shortName = collegeName.replace(/^College of\s+/i, '').trim();
        let allLabel = shortName ? `All ${shortName} Departments` : 'All Departments';

        let html = `<option value="">${allLabel}</option>`;
        children.forEach(child => {
            html += `<option value="${escapeHtml(child.name)}">${escapeHtml(child.name)}</option>`;
        });

        deptSelect.innerHTML = html;
        deptSelect.value = '';
        deptSelect.disabled = false;
        deptSelect.classList.remove('opacity-60');
        deptSelect.classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-400');

        if (countPill) {
            countPill.textContent = `(${children.length} options)`;
        }

        filterUsers();
    }
    window.onCollegeFilterChange = onCollegeFilterChange;

    function onDeptFilterChange(deptName) {
        const deptSelect = document.getElementById('filter-dept-select');
        if (deptSelect) {
            if (deptName) {
                deptSelect.classList.add('border-amber-500', 'text-amber-600', 'dark:text-amber-400');
            } else {
                deptSelect.classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-400');
            }
        }
        filterUsers();
    }
    window.onDeptFilterChange = onDeptFilterChange;

    // --- USER DIRECTORY FILTERING ---
    function filterUsers() {
        const searchInput = document.getElementById('filter-search');
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const checkedRoles  = getCheckedValues('filter_role');
        const checkedPos    = getCheckedValues('filter_pos');

        // Cascading College & Sub-department dropdown filters
        const collegeSelect = document.getElementById('filter-college-select');
        const deptSelect    = document.getElementById('filter-dept-select');

        const selectedCollegeId = collegeSelect ? collegeSelect.value : '';
        const selectedDept      = (deptSelect ? deptSelect.value : '').trim();

        let filterByUnit = false;
        let allowedUnitNames = new Set();

        if (selectedDept) {
            // Specific department chosen
            filterByUnit = true;
            allowedUnitNames.add(selectedDept.toLowerCase());
            const descendants = getExpandedUnitNames([selectedDept]);
            descendants.forEach(d => allowedUnitNames.add(d.toLowerCase()));
        } else if (selectedCollegeId) {
            // College chosen with "Show All" sub-departments
            filterByUnit = true;
            const numericCollegeId = parseInt(selectedCollegeId, 10);
            const units = window.SPMS_UNITS_MAP || [];
            const college = units.find(u => u.id === numericCollegeId);
            if (college) {
                allowedUnitNames.add((college.name || '').trim().toLowerCase());
                units.filter(u => u.parent_id === numericCollegeId).forEach(child => {
                    allowedUnitNames.add((child.name || '').trim().toLowerCase());
                    const childDesc = getExpandedUnitNames([child.name]);
                    childDesc.forEach(cd => allowedUnitNames.add(cd.toLowerCase()));
                });
            }
        }

        // Active filters counter & Reset all toggle
        let activeCount = 0;
        if (query) activeCount++;
        if (checkedRoles.length > 0) activeCount += checkedRoles.length;
        if (selectedCollegeId) activeCount++;
        if (selectedDept) activeCount++;
        if (checkedPos.length > 0) activeCount += checkedPos.length;

        const countBadge = document.getElementById('active-filter-count-badge');
        const resetBtn   = document.getElementById('reset-all-filters-btn');
        if (countBadge) {
            countBadge.textContent = activeCount;
            countBadge.classList.toggle('hidden', activeCount === 0);
        }
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', activeCount === 0);
        }

        const rows = document.querySelectorAll('.user-dir-row');
        const matchedRows = [];

        rows.forEach(row => {
            const rowName   = (row.dataset.name || row.getAttribute('data-name') || '').toLowerCase();
            const rowEmail  = (row.dataset.email || row.getAttribute('data-email') || '').toLowerCase();
            const rowRole   = (row.dataset.role || row.getAttribute('data-role') || '').toLowerCase();
            const rowDept   = (row.dataset.dept || row.getAttribute('data-dept') || '').toLowerCase();
            const rowPos    = (row.dataset.position || row.getAttribute('data-position') || '').toLowerCase();

            const matchesSearch = !query || rowName.includes(query) || rowEmail.includes(query);
            const matchesRole   = checkedRoles.length === 0 || checkedRoles.some(r => rowRole.includes(r.toLowerCase()));
            
            let matchesDept = true;
            if (filterByUnit) {
                const userDepts = rowDept.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                matchesDept = userDepts.some(ud => {
                    if (allowedUnitNames.has(ud)) return true;
                    for (const allowed of allowedUnitNames) {
                        if (ud.includes(allowed) || allowed.includes(ud)) return true;
                    }
                    return false;
                });
            }

            const matchesPos = checkedPos.length === 0 || checkedPos.some(p => rowPos.includes(p.toLowerCase()));

            if (matchesSearch && matchesRole && matchesDept && matchesPos) {
                matchedRows.push(row);
            } else {
                row.classList.add('page-hidden');
                row.style.display = 'none';
            }
        });

        dirPaginator.updateItems(matchedRows);

        const dirCountBadge = document.getElementById('directory-count');
        const mobileCountBadge = document.getElementById('mobile-directory-count');
        const emptyState = document.getElementById('empty-filter-state');

        if (dirCountBadge) dirCountBadge.textContent = matchedRows.length;
        if (mobileCountBadge) mobileCountBadge.textContent = matchedRows.length;
        if (emptyState) {
            emptyState.style.display = (matchedRows.length === 0 ? '' : 'none');
        }
    }
    window.filterUsers = filterUsers;

    // --- DIRECTORY SIDEBAR: UNITS FILTER ---
    function filterSidebarUnits() {
        const input = document.getElementById('mini-search-units');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('units-checkbox-list');
        if (!list) return;

        const allUnitNodes = Array.from(list.querySelectorAll('.unit-node'));

        if (query === '') {
            allUnitNodes.forEach(node => {
                node.classList.remove('page-hidden');
                node.style.display = '';
                const children = node.querySelector(':scope > .unit-children');
                if (children) children.classList.add('hidden');
                const toggleBtn = node.querySelector(':scope > div .unit-toggle');
                if (toggleBtn) toggleBtn.classList.add('-rotate-90');
            });
        } else {
            allUnitNodes.forEach(node => {
                node.classList.add('page-hidden');
                node.style.display = 'none';
            });

            allUnitNodes.forEach(node => {
                const name = (node.dataset.name || '').toLowerCase();
                if (name.includes(query)) {
                    node.classList.remove('page-hidden');
                    node.style.display = '';

                    let parent = node.parentElement;
                    while (parent && parent !== list) {
                        if (parent.classList.contains('unit-children')) {
                            parent.classList.remove('hidden');
                        }
                        if (parent.classList.contains('unit-node')) {
                            parent.classList.remove('page-hidden');
                            parent.style.display = '';
                            const toggleBtn = parent.querySelector(':scope > div .unit-toggle');
                            if (toggleBtn) toggleBtn.classList.remove('-rotate-90');
                        }
                        parent = parent.parentElement;
                    }
                }
            });
        }
    }
    window.filterSidebarUnits = filterSidebarUnits;

    // --- DIRECTORY SIDEBAR: POSITIONS FILTER ---
    function filterSidebarPositions() {
        const input = document.getElementById('mini-search-positions');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('positions-checkbox-list');
        if (!list) return;

        const allRows = Array.from(list.querySelectorAll('.position-filter-label'));
        allRows.forEach(row => {
            const title = (row.dataset.title || row.textContent || '').trim().toLowerCase();
            const matches = !query || title.includes(query);
            row.classList.toggle('page-hidden', !matches);
            row.style.display = matches ? '' : 'none';
        });
    }
    window.filterSidebarPositions = filterSidebarPositions;

    // --- INVITATIONS TAB FILTERING ---
    function filterInvitations() {
        const searchInput = document.getElementById('invite-filter-search');
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const checkedRoles = getCheckedValues('invite_filter_role');
        const checkedStatus = getCheckedValues('invite_filter_status');
        const checkedExpiry = getCheckedValues('invite_filter_expiry');

        const rows = document.querySelectorAll('.invite-row');
        const matchedRows = [];

        rows.forEach(row => {
            const email = (row.dataset.email || '').toLowerCase();
            const role = (row.dataset.role || '').toLowerCase();
            const status = (row.dataset.status || '').toLowerCase();
            const isExpired = row.dataset.expired === '1';

            const matchesSearch = !query || email.includes(query);
            const matchesRole = checkedRoles.length === 0 || checkedRoles.some(r => role.includes(r.toLowerCase()));
            const matchesStatus = checkedStatus.length === 0 || checkedStatus.includes(status);

            let matchesExpiry = true;
            if (checkedExpiry.length > 0) {
                if (isExpired && !checkedExpiry.includes('expired')) matchesExpiry = false;
                if (!isExpired && !checkedExpiry.includes('not-expired')) matchesExpiry = false;
            }

            if (matchesSearch && matchesRole && matchesStatus && matchesExpiry) {
                matchedRows.push(row);
            } else {
                row.classList.add('page-hidden');
                row.style.display = 'none';
            }
        });

        invPaginator.updateItems(matchedRows);

        const countBadge = document.getElementById('invitations-count');
        if (countBadge) countBadge.textContent = matchedRows.length;

        const mobileCountBadge = document.getElementById('mobile-invitations-count');
        if (mobileCountBadge) mobileCountBadge.textContent = matchedRows.length;

        const filteredCountLabel = document.getElementById('invite-filtered-count');
        if (filteredCountLabel) filteredCountLabel.textContent = matchedRows.length;

        const filterEmptyState = document.getElementById('invitations-filter-empty-state');
        if (filterEmptyState) {
            filterEmptyState.style.display = (matchedRows.length === 0 && rows.length > 0) ? '' : 'none';
        }
    }
    window.filterInvitations = filterInvitations;

    // --- SYSTEM DATA: JOB POSITIONS PAGINATION & SEARCH ---
    function filterPositions() {
        const input = document.getElementById('filter-positions');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('positions-list');
        if (!list) return;

        const allRows = Array.from(list.querySelectorAll('li.position-item'));
        posPaginator.init(allRows);

        const matchedRows = allRows.filter(row => {
            const title = (row.dataset.title || row.textContent || '').trim().toLowerCase();
            return !query || title.includes(query);
        });

        posPaginator.updateItems(matchedRows);

        const emptyState = document.getElementById('positions-empty-state');
        if (emptyState) emptyState.style.display = matchedRows.length === 0 ? '' : 'none';
    }
    window.filterPositions = filterPositions;

    // --- SYSTEM DATA: DEPARTMENTS & UNITS TREE VIEW ---
    function toggleUnitTree(btn, parentId) {
        if (!btn) return;
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
    window.toggleUnitTree = toggleUnitTree;

    function hideDescendants(parentId) {
        document.querySelectorAll(`.unit-item[data-parent="${parentId}"]`).forEach(li => {
            li.style.display = 'none';
            const childId = li.dataset.id;
            const btn = li.querySelector('.js-tree-toggle');
            if (btn) btn.classList.add('-rotate-90');
            hideDescendants(childId);
        });
    }
    window.hideDescendants = hideDescendants;

    function filterUnits() {
        const input = document.getElementById('filter-units');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('units-list');
        if (!list) return;

        let visibleCount = 0;

        if (query === '') {
            list.querySelectorAll('.unit-item').forEach(li => {
                li.style.display = li.dataset.parent === "0" ? '' : 'none';
                const btn = li.querySelector('.js-tree-toggle');
                if (btn) {
                    btn.classList.add('-rotate-90');
                    btn.style.visibility = '';
                }
                visibleCount++;
            });
        } else {
            list.querySelectorAll('.unit-item').forEach(li => li.style.display = 'none');
            list.querySelectorAll('.unit-item').forEach(li => {
                const name = (li.dataset.name || li.textContent || '').toLowerCase();
                if (name.includes(query)) {
                    li.style.display = '';
                    visibleCount++;
                    let pId = li.dataset.parent;
                    while (pId !== "0" && pId !== "" && pId !== null && pId !== undefined) {
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
    }
    window.filterUnits = filterUnits;

    // --- SYSTEM SIDEBAR & MODALS ---
    function openSystemSidebar() {
        const sysMobileOverlay = document.getElementById('mobile-system-filter-overlay');
        if (sysMobileOverlay) {
            sysMobileOverlay.classList.remove('hidden');
            setTimeout(() => sysMobileOverlay.classList.remove('opacity-0'), 10);
        }
        const activeSidebar = document.querySelector('.system-panel:not(.hidden) .system-sidebar');
        if (activeSidebar) {
            activeSidebar.classList.remove('-translate-x-full');
            activeSidebar.classList.add('translate-x-0');
        }
    }
    window.openSystemSidebar = openSystemSidebar;

    function closeSystemSidebar() {
        const sysMobileOverlay = document.getElementById('mobile-system-filter-overlay');
        if (sysMobileOverlay) {
            sysMobileOverlay.classList.add('opacity-0');
            setTimeout(() => sysMobileOverlay.classList.add('hidden'), 300);
        }
        document.querySelectorAll('.system-sidebar').forEach(sidebar => {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        });
    }
    window.closeSystemSidebar = closeSystemSidebar;

    // --- DIRECTORY FILTER: CLEAR ALL ---
    function clearAllDirectoryFilters() {
        const sInput = document.getElementById('filter-search');
        if (sInput) sInput.value = '';
        document.querySelectorAll('.directory-filter-checkbox').forEach(cb => {
            cb.checked = false;
        });

        const collegeSelect = document.getElementById('filter-college-select');
        if (collegeSelect) collegeSelect.value = '';

        const deptSelect = document.getElementById('filter-dept-select');
        if (deptSelect) {
            deptSelect.innerHTML = '<option value="">All Departments</option>';
            deptSelect.value = '';
            deptSelect.disabled = true;
            deptSelect.classList.add('opacity-60');
            deptSelect.classList.remove('border-amber-500', 'text-amber-600', 'dark:text-amber-400');
        }

        const countPill = document.getElementById('subdept-count-pill');
        if (countPill) countPill.textContent = '(All)';

        const msPos = document.getElementById('mini-search-positions');
        if (msPos) msPos.value = '';
        filterSidebarPositions();

        filterUsers();
    }
    window.clearAllDirectoryFilters = clearAllDirectoryFilters;

    // ==========================================
    // UNIFIED PAGE INITIALIZATION
    // ==========================================
    function initAccountsPage() {
        // --- 1. Directory Filter Listeners ---
        const searchInput = document.getElementById('filter-search');
        if (searchInput) searchInput.addEventListener('input', filterUsers);

        document.querySelectorAll('.directory-filter-checkbox').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const name = e.target.name;
                if (name === 'filter_role[]') {
                    if (e.target.checked) {
                        document.querySelectorAll(`input[name="${name}"]`).forEach(otherCb => {
                            if (otherCb !== e.target) otherCb.checked = false;
                        });
                    }
                }
                filterUsers();
            });
        });

        const clearBtn = document.getElementById('clear-directory-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearAllDirectoryFilters);
        }

        // Mobile Directory Sidebar
        const dirSidebar = document.getElementById('directory-sidebar');
        const dirOverlay = document.getElementById('mobile-filter-overlay');
        const openDirBtn = document.getElementById('open-mobile-filters');
        const closeDirBtn = document.getElementById('close-mobile-filters');

        function toggleMobileDirSidebar(show) {
            if (!dirSidebar || !dirOverlay) return;
            if (show) {
                dirOverlay.classList.remove('hidden');
                requestAnimationFrame(() => {
                    dirOverlay.classList.remove('opacity-0');
                    dirSidebar.classList.remove('-translate-x-full');
                });
                document.body.style.overflow = 'hidden';
            } else {
                dirOverlay.classList.add('opacity-0');
                dirSidebar.classList.add('-translate-x-full');
                setTimeout(() => {
                    dirOverlay.classList.add('hidden');
                }, 300);
                document.body.style.overflow = '';
            }
        }
        if (openDirBtn) openDirBtn.addEventListener('click', () => toggleMobileDirSidebar(true));
        if (closeDirBtn) closeDirBtn.addEventListener('click', () => toggleMobileDirSidebar(false));
        if (dirOverlay) dirOverlay.addEventListener('click', () => toggleMobileDirSidebar(false));

        // Directory sidebar mini-searches
        const msUnits = document.getElementById('mini-search-units');
        if (msUnits) msUnits.addEventListener('input', filterSidebarUnits);

        const msPos = document.getElementById('mini-search-positions');
        if (msPos) msPos.addEventListener('input', filterSidebarPositions);

        // Initialize paginators
        const dirTableBody = document.getElementById('user-table-body');
        if (dirTableBody) {
            dirPaginator.init(Array.from(dirTableBody.querySelectorAll('.user-dir-row')));
        }

        const invTableBody = document.getElementById('invitations-table-body');
        if (invTableBody) {
            invPaginator.init(Array.from(invTableBody.querySelectorAll('.invite-row')));
        }

        // Run directory filter initializations
        filterUsers();
        filterSidebarUnits();
        filterSidebarPositions();

        // --- 2. Invitations Filter Listeners ---
        const inviteSearch = document.getElementById('invite-filter-search');
        if (inviteSearch) inviteSearch.addEventListener('input', filterInvitations);

        document.querySelectorAll('.invite-filter-checkbox').forEach(cb => {
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

        const openMobileInviteFilters = document.getElementById('open-mobile-invite-filters');
        const closeMobileInviteFilters = document.getElementById('close-mobile-invite-filters');
        const inviteSidebar = document.getElementById('invitations-sidebar');
        const mobileInviteOverlay = document.getElementById('mobile-invite-filter-overlay');

        function toggleMobileInviteFilters(show) {
            if (!inviteSidebar || !mobileInviteOverlay) return;
            if (show) {
                mobileInviteOverlay.classList.remove('hidden');
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
        if (openMobileInviteFilters) openMobileInviteFilters.addEventListener('click', () => toggleMobileInviteFilters(true));
        if (closeMobileInviteFilters) closeMobileInviteFilters.addEventListener('click', () => toggleMobileInviteFilters(false));
        if (mobileInviteOverlay) mobileInviteOverlay.addEventListener('click', () => toggleMobileInviteFilters(false));

        const btnDeleteFiltered = document.getElementById('btn-delete-filtered');
        if (btnDeleteFiltered) {
            btnDeleteFiltered.addEventListener('click', async () => {
                const visibleRows = Array.from(document.querySelectorAll('.invite-row')).filter(row => row.style.display !== 'none' && !row.classList.contains('page-hidden'));
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

        // --- 3. System Data Tab Listeners ---
        const sysUnitInput = document.getElementById('filter-units');
        if (sysUnitInput) sysUnitInput.addEventListener('input', filterUnits);

        const sysPosInput = document.getElementById('filter-positions');
        if (sysPosInput) sysPosInput.addEventListener('input', filterPositions);

        filterUnits();
        filterPositions();

        // --- 4. Modals & System Mobile Sidebar ---
        document.querySelectorAll('.btn-open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.target;
                const modal = document.getElementById(targetId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
        });

        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.target;
                const modal = document.getElementById(targetId);
                if (modal) {
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                }
            });
        });

        [document.getElementById('modal-create-position'), document.getElementById('modal-create-unit')].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                    }
                });
            }
        });

        document.querySelectorAll('.btn-open-system-mobile').forEach(btn => {
            btn.addEventListener('click', openSystemSidebar);
        });

        document.querySelectorAll('.btn-close-system-mobile').forEach(btn => {
            btn.addEventListener('click', closeSystemSidebar);
        });

        const sysMobileOverlay = document.getElementById('mobile-system-filter-overlay');
        if (sysMobileOverlay) {
            sysMobileOverlay.addEventListener('click', closeSystemSidebar);
        }
    }

    if (document.readyState !== 'loading') {
        initAccountsPage();
    } else {
        document.addEventListener('DOMContentLoaded', initAccountsPage);
    }



</script>
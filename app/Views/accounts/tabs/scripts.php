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
    const esPaginator  = new ClientPaginator({ containerId: 'employment-statuses-pagination', pageSize: 10 });
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
        if (['add-position', 'delete-position', 'add-unit', 'delete-unit', 'add-role', 'delete-role', 'add-employment-status', 'delete-employment-status'].includes(action)) {
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

            case 'delete-employment-status': {
                form.closest('li').remove();
                if (typeof filterEmploymentStatuses === 'function') filterEmploymentStatuses();
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
                await window.appAlert('Department / unit updated successfully!', { title: 'Department / Unit Updated', variant: 'success' });
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

            case 'add-employment-status': {
                const li = document.createElement('li');
                li.className = 'employment-status-item block lg:table-row bg-surface hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group';
                li.dataset.name = data.item.name.toLowerCase();
                li.innerHTML = `
                    <div class="flex flex-col justify-center lg:table-cell px-6 py-4 align-middle h-[72px]">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-text">${escapeHtml(data.item.name)}</span>
                                <span class="text-[9px] font-black tracking-widest uppercase text-emerald-500">Employment Status</span>
                            </div>
                            <form action="${form.getAttribute('action').replace('/add', '/delete')}" method="post" data-ajax="delete-employment-status" data-confirm="Delete this employment status? Plantilla assignments with this status will remain intact." data-confirm-title="Delete Employment Status">
                                <input type="hidden" name="id" value="${data.item.id}">
                                <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-2 -mr-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 dark:hover:bg-danger-500/20 lg:bg-transparent rounded-lg cursor-pointer" title="Delete Employment Status">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                `;
                document.getElementById('employment-statuses-list').appendChild(li);
                form.reset();
                const modal = document.getElementById('modal-create-employment-status');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                if (typeof filterEmploymentStatuses === 'function') filterEmploymentStatuses();
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
                            <form action="${form.getAttribute('action').replace('/add', '/delete')}" method="post" data-ajax="delete-unit" data-confirm="Delete this department / unit? Staff currently assigned to it will keep their account but lose that designation. Sub-units under it will be deleted too." data-confirm-title="Delete Department / Unit">
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

    // --- DYNAMIC CASCADING UNIT FILTERS (USER DIRECTORY) ---
    const DIR_LEVEL_THEMES = [
        {
            labelColor: 'text-text-muted',
            focusRing: 'focus:border-accent focus:ring-1 focus:ring-accent/20',
            activeBorder: 'border-zinc-400 dark:border-zinc-600',
            connector: 'border-zinc-300 dark:border-zinc-700'
        }
    ];

    function getDirLevelTheme(level) {
        return DIR_LEVEL_THEMES[0];
    }

    function getDirLevelTitle(level, parentUnit) {
        if (level === 1) {
            return 'DEPARTMENT / UNIT';
        }
        return parentUnit ? escapeHtml(parentUnit.name) : 'SUB-UNIT';
    }

    function getDirDefaultOptionLabel(level, parentUnit) {
        if (level === 1) {
            return 'All Departments &amp; Units';
        }
        return parentUnit ? `All in ${escapeHtml(parentUnit.name)}` : 'All Options';
    }

    function getDescendantUnitIds(rootId) {
        const units = window.SPMS_UNITS_MAP || [];
        const childrenMap = new Map();
        units.forEach(u => {
            const pid = String(u.parent_id || 0);
            if (!childrenMap.has(pid)) childrenMap.set(pid, []);
            childrenMap.get(pid).push(String(u.id));
        });

        const result = new Set([String(rootId)]);
        const stack = [String(rootId)];
        while (stack.length) {
            const current = stack.pop();
            (childrenMap.get(current) || []).forEach(childId => {
                if (!result.has(childId)) {
                    result.add(childId);
                    stack.push(childId);
                }
            });
        }
        return result;
    }

    function renderDynamicDirectoryUnitLevel(level, parentUnitId, targetContainer) {
        if (!targetContainer) return;
        const wasCollapsed = level === 1 && Boolean(targetContainer.querySelector(':scope > .dynamic-unit-level > .js-unit-level-body.hidden'));
        targetContainer.innerHTML = '';

        const units = window.SPMS_UNITS_MAP || [];
        const unitsById = {};
        units.forEach(u => unitsById[u.id] = u);

        let availableUnits = [];
        let parentUnit = null;

        if (level === 1) {
            // Root units for directory (OVPAA, OVPAF, etc.)
            availableUnits = units.filter(u => !u.parent_id || !unitsById[u.parent_id]);
        } else {
            const numParentId = parseInt(parentUnitId, 10);
            parentUnit = unitsById[numParentId] || null;
            availableUnits = units.filter(u => u.parent_id === numParentId);
        }

        if (availableUnits.length === 0) {
            targetContainer.classList.add('hidden');
            return;
        }

        targetContainer.classList.remove('hidden');

        const theme = getDirLevelTheme(level);
        const title = getDirLevelTitle(level, parentUnit);
        const defaultLabel = getDirDefaultOptionLabel(level, parentUnit);

        let optionsHtml = `<option value="">${defaultLabel}</option>`;
        availableUnits.forEach(u => {
            optionsHtml += `<option value="${u.id}">${escapeHtml(u.name)}</option>`;
        });

        const levelWrapper = document.createElement('div');
        levelWrapper.className = 'dynamic-unit-level';
        levelWrapper.dataset.level = level;

        const headerSpacing = level === 1 ? 'mb-3 tracking-widest' : 'mb-1.5 tracking-wider';
        const headerHtml = `
            <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase ${headerSpacing} ${theme.labelColor} hover:text-text focus:outline-none group cursor-pointer" onclick="
                this.nextElementSibling.classList.toggle('hidden');
                this.querySelector('svg').classList.toggle('-rotate-180');
            ">
                <span class="truncate pr-2 text-left" title="${title}">${title}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 ${wasCollapsed ? '-rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>`;

        levelWrapper.innerHTML = `
            ${headerHtml}
            <div class="js-unit-level-body ${wasCollapsed ? 'hidden' : ''}">
                <div class="relative w-full">
                    <select id="filter-dir-unit-level-${level}"
                        class="w-full appearance-none bg-white dark:bg-zinc-900 border border-surface-border ${theme.focusRing} rounded-xl pl-3 pr-8 py-2 text-xs font-semibold text-text outline-none cursor-pointer transition-all shadow-xs [color-scheme:light] dark:[color-scheme:dark]">
                        ${optionsHtml}
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 flex items-center text-text-muted" style="right: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <div id="dynamic-dir-unit-children-${level}" class="relative pl-3.5 ml-2.5 border-l-2 border-dashed ${theme.connector} space-y-3 pt-2 hidden"></div>
            </div>
        `;

        targetContainer.appendChild(levelWrapper);

        const selectEl = levelWrapper.querySelector(`#filter-dir-unit-level-${level}`);
        selectEl.addEventListener('change', (e) => {
            onDynamicDirectoryUnitLevelChange(level, e.target.value);
        });
    }

    function onDynamicDirectoryUnitLevelChange(level, selectedUnitId) {
        const selectEl = document.getElementById(`filter-dir-unit-level-${level}`);
        const childrenContainer = document.getElementById(`dynamic-dir-unit-children-${level}`);
        const theme = getDirLevelTheme(level);

        if (selectEl) {
            const activeClasses = theme.activeBorder.split(' ');
            if (selectedUnitId) {
                selectEl.classList.add(...activeClasses);
            } else {
                selectEl.classList.remove(...activeClasses);
            }
        }

        if (childrenContainer) {
            childrenContainer.innerHTML = '';
            childrenContainer.classList.add('hidden');

            if (selectedUnitId) {
                const numId = parseInt(selectedUnitId, 10);
                const units = window.SPMS_UNITS_MAP || [];
                const childUnits = units.filter(u => u.parent_id === numId);

                if (childUnits.length > 0) {
                    renderDynamicDirectoryUnitLevel(level + 1, selectedUnitId, childrenContainer);
                }
            }
        }

        filterSidebarPositions();
        filterUsers();
    }
    window.onDynamicDirectoryUnitLevelChange = onDynamicDirectoryUnitLevelChange;

    function initDynamicDirectoryUnitFilters() {
        const rootContainer = document.getElementById('dynamic-directory-unit-filter-container');
        if (!rootContainer) return;
        renderDynamicDirectoryUnitLevel(1, null, rootContainer);
        filterSidebarPositions();
    }
    window.initDynamicDirectoryUnitFilters = initDynamicDirectoryUnitFilters;

    function getActiveDirectoryDynamicUnitId() {
        const container = document.getElementById('dynamic-directory-unit-filter-container');
        if (!container) return null;
        const selects = Array.from(container.querySelectorAll('select'));
        for (let i = selects.length - 1; i >= 0; i--) {
            const val = selects[i].value;
            if (val) return val;
        }
        return null;
    }

    // --- USER DIRECTORY FILTERING ---
    function filterUsers() {
        const searchInput = document.getElementById('filter-search');
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const checkedRoles  = getCheckedValues('filter_role');
        const checkedPos    = getCheckedValues('filter_pos');

        const activeUnitId = getActiveDirectoryDynamicUnitId();
        let allowedUnitIds = null;
        let allowedUnitNames = new Set();
        if (activeUnitId) {
            allowedUnitIds = getDescendantUnitIds(String(activeUnitId));
            const units = window.SPMS_UNITS_MAP || [];
            units.forEach(u => {
                if (allowedUnitIds.has(String(u.id))) {
                    allowedUnitNames.add((u.name || '').trim().toLowerCase());
                }
            });
        }

        // Active filters counter & Reset all toggle
        let activeCount = 0;
        if (query) activeCount++;
        if (checkedRoles.length > 0) activeCount += checkedRoles.length;
        if (activeUnitId) activeCount++;
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
            if (allowedUnitIds) {
                const rowUnitIds = (row.dataset.unit || row.getAttribute('data-unit') || '').split(',').map(s => s.trim()).filter(Boolean);
                if (rowUnitIds.length > 0) {
                    matchesDept = rowUnitIds.some(uid => allowedUnitIds.has(uid));
                } else {
                    const userDepts = rowDept.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                    matchesDept = userDepts.some(ud => {
                        if (allowedUnitNames.has(ud)) return true;
                        for (const allowed of allowedUnitNames) {
                            if (ud.includes(allowed) || allowed.includes(ud)) return true;
                        }
                        return false;
                    });
                }
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
    function getActiveDirectoryDeptPositions() {
        const activeUnitId = getActiveDirectoryDynamicUnitId();
        if (!activeUnitId) {
            return null; // All positions are allowed when no department filter is active
        }
        const allowedUnitIds = getDescendantUnitIds(String(activeUnitId));
        const units = window.SPMS_UNITS_MAP || [];
        const allowedUnitNames = new Set();
        units.forEach(u => {
            if (allowedUnitIds.has(String(u.id))) {
                allowedUnitNames.add((u.name || '').trim().toLowerCase());
            }
        });

        const rows = document.querySelectorAll('.user-dir-row');
        const allowedPositions = new Set();

        rows.forEach(row => {
            const rowUnitIds = (row.dataset.unit || row.getAttribute('data-unit') || '').split(',').map(s => s.trim()).filter(Boolean);
            let matchesDept = false;
            if (rowUnitIds.length > 0) {
                matchesDept = rowUnitIds.some(uid => allowedUnitIds.has(uid));
            } else {
                const userDepts = (row.dataset.dept || '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
                matchesDept = userDepts.some(ud => {
                    if (allowedUnitNames.has(ud)) return true;
                    for (const allowed of allowedUnitNames) {
                        if (ud.includes(allowed) || allowed.includes(ud)) return true;
                    }
                    return false;
                });
            }

            if (matchesDept) {
                const posStr = row.dataset.position || '';
                posStr.split(',').forEach(p => {
                    const trimmed = p.trim().toLowerCase();
                    if (trimmed && trimmed !== 'no position') {
                        allowedPositions.add(trimmed);
                    }
                });
            }
        });

        return allowedPositions;
    }

    function filterSidebarPositions() {
        const input = document.getElementById('mini-search-positions');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('positions-checkbox-list');
        if (!list) return;

        const allowedDeptPositions = getActiveDirectoryDeptPositions();
        const allRows = Array.from(list.querySelectorAll('.position-label, .position-filter-label'));
        let visibleCount = 0;

        allRows.forEach(row => {
            const title = (row.dataset.name || row.dataset.title || row.textContent || '').trim().toLowerCase();
            const matchesQuery = !query || title.includes(query);
            const matchesDept = allowedDeptPositions === null || allowedDeptPositions.has(title);

            if (!matchesDept) {
                const cb = row.querySelector('input[type="checkbox"]');
                if (cb && cb.checked) {
                    cb.checked = false;
                }
            }

            const isVisible = matchesQuery && matchesDept;
            row.classList.toggle('page-hidden', !isVisible);
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        let emptyMsg = list.querySelector('.no-positions-msg');
        if (visibleCount === 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.className = 'no-positions-msg text-center py-3 text-[11px] font-bold text-text-muted italic';
                emptyMsg.textContent = 'No positions in selected unit';
                list.appendChild(emptyMsg);
            }
            emptyMsg.style.display = '';
        } else if (emptyMsg) {
            emptyMsg.style.display = 'none';
        }
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

    // --- SYSTEM DATA: EMPLOYMENT STATUSES PAGINATION & SEARCH ---
    function filterEmploymentStatuses() {
        const input = document.getElementById('filter-employment-statuses');
        const query = (input ? input.value : '').trim().toLowerCase();
        const list = document.getElementById('employment-statuses-list');
        if (!list) return;

        const allRows = Array.from(list.querySelectorAll('li.employment-status-item'));
        esPaginator.init(allRows);

        const matchedRows = allRows.filter(row => {
            const name = (row.dataset.name || row.textContent || '').trim().toLowerCase();
            return !query || name.includes(query);
        });

        esPaginator.updateItems(matchedRows);

        const emptyState = document.getElementById('employment-statuses-empty-state');
        if (emptyState) emptyState.style.display = matchedRows.length === 0 ? '' : 'none';
    }
    window.filterEmploymentStatuses = filterEmploymentStatuses;

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

        initDynamicDirectoryUnitFilters();

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
        // Initialize dynamic unit cascading filters for user directory
        initDynamicDirectoryUnitFilters();

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

        const btnDeleteFiltered = document.getElementById('delete-filtered-invites') || document.getElementById('btn-delete-filtered');
        if (btnDeleteFiltered) {
            btnDeleteFiltered.addEventListener('click', async () => {
                const searchInput = document.getElementById('invite-filter-search');
                const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
                const checkedRoles = getCheckedValues('invite_filter_role');
                const checkedStatus = getCheckedValues('invite_filter_status');
                const checkedExpiry = getCheckedValues('invite_filter_expiry');

                const allRows = Array.from(document.querySelectorAll('.invite-row'));
                const visibleRows = allRows.filter(row => {
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

                    return matchesSearch && matchesRole && matchesStatus && matchesExpiry;
                });

                if (visibleRows.length === 0) {
                    window.appAlert('No invitations match the current filter.', { variant: 'warning' });
                    return;
                }

                const ok = await window.appConfirm(
                    `Delete these ${visibleRows.length} invitation(s)? Any that haven't been accepted yet will stop working.`,
                    { title: 'Delete Filtered Invitations', confirmText: 'Delete All', variant: 'danger' }
                );
                if (!ok) return;

                btnDeleteFiltered.disabled = true;

                const formData = new FormData();
                visibleRows.forEach(row => formData.append('ids[]', row.dataset.id));

                apiPost('/account/invite/delete-bulk', formData, {
                    onSuccess: () => {
                        visibleRows.forEach(row => row.remove());
                        if (window.invPaginator) {
                            window.invPaginator.init(Array.from(document.querySelectorAll('.invite-row')));
                        }
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

        const sysEsInput = document.getElementById('filter-employment-statuses');
        if (sysEsInput) sysEsInput.addEventListener('input', filterEmploymentStatuses);

        filterUnits();
        filterPositions();
        filterEmploymentStatuses();

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



    // ==========================================
    // TWG ASSIGNMENTS JAVASCRIPT LOGIC
    // ==========================================
    const twgState = (() => {
        const el = document.getElementById('twg-initial-state');
        let parsed = {};
        if (el) {
            try {
                parsed = JSON.parse(el.textContent || '{}');
            } catch (e) {
                parsed = {};
            }
        }
        return {
            unitCoverageMeta: parsed.unitCoverageMeta || {},
            assignmentsGrouped: parsed.assignmentsGrouped || {},
            twgUsers: parsed.twgUsers || {},
            activeAssignModalUserId: null,
            activePositionsModalUserId: null,
            activePositionsModalTab: 'positions',
        };
    })();

    function getAssignedUnitIdsForUser(userId) {
        const list = twgState.assignmentsGrouped[userId] || twgState.assignmentsGrouped[String(userId)] || [];
        return list.map(item => Number(item.unit_id));
    }

    function computeTwgCoverageDetails(userId, unitIdsOverride = null) {
        const uidNum = Number(userId);
        const unitIds = Array.isArray(unitIdsOverride)
            ? unitIdsOverride.map(Number)
            : getAssignedUnitIdsForUser(uidNum);

        const offices = [];
        const positionsMap = new Map();
        const uniqueStaffIds = new Set();
        const searchTokens = new Set();

        unitIds.forEach(unitId => {
            const meta = twgState.unitCoverageMeta[unitId] || twgState.unitCoverageMeta[String(unitId)];
            if (!meta) return;

            const coReviewers = (meta.reviewers || []).filter(r => Number(r.id) !== uidNum);
            const personnel = (meta.personnel || []).filter(p => Number(p.user_id) !== uidNum);

            searchTokens.add(String(meta.unit_name || '').toLowerCase());

            const officePositionsMap = new Map();
            personnel.forEach(p => {
                uniqueStaffIds.add(Number(p.user_id));
                const posTitle = String(p.position || 'Unassigned Position').trim() || 'Unassigned Position';
                const posKey = posTitle.toLowerCase();
                searchTokens.add(posKey);

                if (!officePositionsMap.has(posKey)) {
                    officePositionsMap.set(posKey, {
                        title: posTitle,
                        is_teaching: Number(p.is_teaching || 0),
                        staff: []
                    });
                }
                officePositionsMap.get(posKey).staff.push(p);

                if (!positionsMap.has(posKey)) {
                    positionsMap.set(posKey, {
                        key: posKey,
                        title: posTitle,
                        is_teaching: Number(p.is_teaching || 0),
                        staffById: new Map(),
                        unitsMap: new Map(),
                    });
                }
                const entry = positionsMap.get(posKey);
                if (!entry.staffById.has(Number(p.user_id))) {
                    entry.staffById.set(Number(p.user_id), {
                        user_id: Number(p.user_id),
                        name: p.name,
                        email: p.email,
                        units: [meta.unit_name]
                    });
                } else {
                    const existingStaff = entry.staffById.get(Number(p.user_id));
                    if (!existingStaff.units.includes(meta.unit_name)) {
                        existingStaff.units.push(meta.unit_name);
                    }
                }
                if (!entry.unitsMap.has(unitId)) {
                    entry.unitsMap.set(unitId, {
                        unit_id: unitId,
                        unit_name: meta.unit_name,
                        coReviewers
                    });
                }
            });

            offices.push({
                unit_id: unitId,
                unit_name: meta.unit_name,
                path_str: meta.path_str || meta.unit_name,
                tier: Number(meta.tier || 1),
                coReviewers,
                personnel,
                positions: Array.from(officePositionsMap.values()).sort((a, b) => a.title.localeCompare(b.title)),
            });
        });

        offices.sort((a, b) => a.unit_name.localeCompare(b.unit_name));

        const positions = Array.from(positionsMap.values())
            .map(entry => ({
                key: entry.key,
                title: entry.title,
                is_teaching: entry.is_teaching,
                staff: Array.from(entry.staffById.values()).sort((a, b) => a.name.localeCompare(b.name)),
                units: Array.from(entry.unitsMap.values()).sort((a, b) => a.unit_name.localeCompare(b.unit_name)),
            }))
            .sort((a, b) => a.title.localeCompare(b.title));

        return {
            offices,
            positions,
            officeCount: offices.length,
            positionCount: positions.length,
            staffCount: uniqueStaffIds.size,
            searchTerms: Array.from(searchTokens).join(' '),
        };
    }

    function refreshAllTwgUi() {
        // 1. Update top institutional coverage bar
        const allMeta = Object.values(twgState.unitCoverageMeta || {});
        let coveredUnits = 0;
        let sharedUnits = 0;
        const coveredPositions = new Set();
        const coveredStaff = new Set();

        allMeta.forEach(uMeta => {
            const revCount = (uMeta.reviewers || []).length;
            if (revCount > 0) coveredUnits++;
            if (revCount > 1) sharedUnits++;
            (uMeta.personnel || []).forEach(p => {
                if (revCount > 0) {
                    const pKey = String(p.position || '').trim().toLowerCase();
                    if (pKey) coveredPositions.add(pKey);
                    if (p.user_id) coveredStaff.add(Number(p.user_id));
                }
            });
        });

        const unassignedUnits = Math.max(0, allMeta.length - coveredUnits);
        const covEl = document.getElementById('twg-stat-covered-units');
        const unassEl = document.getElementById('twg-stat-unassigned-units');
        const unassCard = document.getElementById('twg-stat-unassigned-card');
        const unassLabel = document.getElementById('twg-stat-unassigned-label');
        const unassSub = document.getElementById('twg-stat-unassigned-sub');
        const sharedEl = document.getElementById('twg-stat-shared-units');
        const posEl = document.getElementById('twg-stat-covered-positions');
        const staffEl = document.getElementById('twg-stat-covered-staff');

        if (covEl) covEl.textContent = coveredUnits;
        if (unassEl) unassEl.textContent = unassignedUnits;
        if (unassSub) unassSub.textContent = unassignedUnits === 0 ? 'All covered' : 'Need TWG';
        if (unassCard) {
            unassCard.className = `px-3.5 py-2.5 rounded-xl border flex items-center justify-between gap-2 transition-colors ${unassignedUnits > 0 ? 'bg-amber-500/5 border-amber-500/30' : 'bg-input/60 border-surface-border'}`;
        }
        if (unassLabel) {
            unassLabel.className = `text-[10px] font-black uppercase tracking-wider ${unassignedUnits > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted'}`;
        }
        if (sharedEl) sharedEl.textContent = sharedUnits;
        if (posEl) posEl.textContent = coveredPositions.size;
        if (staffEl) staffEl.textContent = coveredStaff.size;

        // 2. Refresh every TWG reviewer card (so shared badges update across co-reviewers too)
        document.querySelectorAll('.twg-reviewer-card').forEach(card => {
            const uid = Number(card.dataset.userId);
            if (!uid) return;
            const details = computeTwgCoverageDetails(uid);
            card.dataset.coverage = details.searchTerms;

            const countLabel = document.getElementById(`twg-count-label-${uid}`);
            if (countLabel) {
                countLabel.textContent = details.officeCount > 0
                    ? (details.officeCount === 1 ? '1 Department / Unit' : `${details.officeCount} Departments & Units`)
                    : '0 Assigned';
                countLabel.className = details.officeCount > 0
                    ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent/15 border border-accent/30 text-amber-800 dark:text-accent js-assigned-count-label'
                    : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 js-assigned-count-label';
            }

            const subMeta = document.getElementById(`twg-submeta-${uid}`);
            if (subMeta) {
                subMeta.textContent = `${details.staffCount} staff covered`;
            }

            const chipsContainer = document.getElementById(`twg-chips-${uid}`);
            if (chipsContainer) {
                if (details.officeCount > 0) {
                    chipsContainer.innerHTML = details.offices.map(off => {
                        const isShared = off.coReviewers.length > 0;
                        const sharedTip = isShared
                            ? `Shared with: ${off.coReviewers.map(r => r.name).join(', ')}`
                            : '';
                        const fullTip = `${off.path_str}${sharedTip ? ' • ' + sharedTip : ''}`;
                        const staffBadge = off.personnel.length > 0
                            ? `<span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-surface border border-surface-border text-text-muted">${off.personnel.length}</span>`
                            : '';
                        const sharedBadge = isShared
                            ? `<span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-info-500/10 text-info-600 dark:text-info-400 border border-info-500/25" title="${escapeHtml(sharedTip)}">Shared</span>`
                            : '';
                        return `
                            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-lg text-xs font-bold bg-input border border-surface-border text-text hover:border-accent/40 transition-colors"
                                  title="${escapeHtml(fullTip)}">
                                <span class="truncate max-w-[210px]">${escapeHtml(off.unit_name)}</span>
                                ${staffBadge}
                                ${sharedBadge}
                                <button type="button"
                                        onclick="quickRemoveTwgUnit(event, ${uid}, ${off.unit_id})"
                                        class="w-4 h-4 rounded flex items-center justify-center text-text-muted hover:text-danger-500 hover:bg-danger-500/15 transition-colors cursor-pointer shrink-0"
                                        title="Remove ${escapeHtml(off.unit_name)}">&times;</button>
                            </span>
                        `;
                    }).join('');
                } else {
                    chipsContainer.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>No departments &amp; units assigned (Review queue is empty)</span>
                        </span>
                    `;
                }
            }
        });

        filterTwgReviewers();
    }

    function updateUnitCoverageBadgeOnCard(card, activeUserId) {
        const badgeContainer = card.querySelector('.js-unit-coverage-badge');
        if (!badgeContainer) return;

        const unitId = Number(card.dataset.id);
        const cb = card.querySelector('.js-unit-checkbox');
        const isChecked = !!(cb && cb.checked);
        const meta = twgState.unitCoverageMeta[unitId] || twgState.unitCoverageMeta[String(unitId)] || {};
        const otherReviewers = (meta.reviewers || []).filter(r => Number(r.id) !== Number(activeUserId));

        card.dataset.hasOthers = otherReviewers.length > 0 ? '1' : '0';

        if (otherReviewers.length > 0) {
            const names = otherReviewers.map(r => r.name).join(', ');
            const shortLabel = otherReviewers.length === 1
                ? otherReviewers[0].initials
                : `${otherReviewers.length} TWGs`;
            badgeContainer.innerHTML = `<span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-info-500/10 text-info-600 dark:text-info-400 border border-info-500/25" title="Also assigned to: ${escapeHtml(names)}">Shared &middot; ${escapeHtml(shortLabel)}</span>`;
        } else if (!isChecked) {
            badgeContainer.innerHTML = `<span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Unassigned</span>`;
        } else {
            badgeContainer.innerHTML = '';
        }
    }

    function syncUnitCardVisual(card, isChecked) {
        const indicator = card.querySelector('.unit-check-indicator');
        const icon = card.querySelector('.check-icon');
        const nameText = card.querySelector('.unit-name-text');
        const isTier1 = card.dataset.tier === '1';

        if (isChecked) {
            card.classList.add('border-accent', 'bg-accent/10', 'ring-1', 'ring-accent/35');
            card.classList.remove('border-transparent', 'border-surface-border/80');
            if (indicator) {
                indicator.classList.add('bg-accent', 'border-accent');
                indicator.classList.remove('bg-input', 'border-surface-border');
            }
            if (icon) icon.classList.remove('hidden');
            if (nameText) {
                nameText.classList.add('text-amber-800', 'dark:text-accent');
                nameText.classList.remove('text-text-muted', 'text-text');
            }
        } else {
            card.classList.remove('border-accent', 'bg-accent/10', 'ring-1', 'ring-accent/35');
            if (isTier1) {
                card.classList.add('border-surface-border/80');
            } else {
                card.classList.add('border-transparent');
            }
            if (indicator) {
                indicator.classList.remove('bg-accent', 'border-accent');
                indicator.classList.add('bg-input', 'border-surface-border');
            }
            if (icon) icon.classList.add('hidden');
            if (nameText) {
                nameText.classList.remove('text-amber-800', 'dark:text-accent');
                if (card.dataset.tier === '3') {
                    nameText.classList.add('text-text-muted');
                } else {
                    nameText.classList.add('text-text');
                }
            }
        }

        if (twgState.activeAssignModalUserId) {
            updateUnitCoverageBadgeOnCard(card, twgState.activeAssignModalUserId);
        }
    }

    function syncTier3SectionCounters() {
        document.querySelectorAll('.tier2-wrapper').forEach(wrapper => {
            const t3Section = wrapper.querySelector('.tier3-section');
            if (!t3Section) return;

            const t3Checkboxes = Array.from(t3Section.querySelectorAll('.tier3-container .js-unit-checkbox'));
            const checkedT3 = t3Checkboxes.filter(cb => cb.checked).length;
            const totalT3 = t3Checkboxes.length;

            const countPill = t3Section.querySelector('.js-tier3-selected-count');
            if (countPill) {
                if (checkedT3 > 0) {
                    countPill.textContent = `${checkedT3}/${totalT3}`;
                    countPill.classList.remove('hidden');
                } else {
                    countPill.classList.add('hidden');
                }
            }

            const parentCb = wrapper.querySelector(':scope > .modal-unit-item .js-unit-checkbox');
            const allBranchChecked = (parentCb ? parentCb.checked : true) && checkedT3 === totalT3 && totalT3 > 0;
            const branchBtn = t3Section.querySelector('.js-branch-toggle-btn');
            if (branchBtn) {
                branchBtn.textContent = allBranchChecked ? 'Clear Branch' : '+ Sub-units';
            }
        });
    }

    function setTier3Expanded(t3Section, expand) {
        if (!t3Section) return;
        const container = t3Section.querySelector('.tier3-container');
        const chevron = t3Section.querySelector('.js-tier3-chevron');
        if (!container) return;

        if (expand) {
            container.classList.remove('hidden');
            if (chevron) chevron.classList.remove('-rotate-90');
        } else {
            container.classList.add('hidden');
            if (chevron) chevron.classList.add('-rotate-90');
        }
    }

    function toggleTier3Collapse(btn, event) {
        if (event) event.stopPropagation();
        const t3Section = btn.closest('.tier3-section');
        if (!t3Section) return;
        const container = t3Section.querySelector('.tier3-container');
        const isHidden = container ? container.classList.contains('hidden') : true;
        setTier3Expanded(t3Section, isHidden);
    }
    window.toggleTier3Collapse = toggleTier3Collapse;

    function toggleBranchUnits(btn, event) {
        if (event) event.stopPropagation();
        const wrapper = btn.closest('.tier2-wrapper');
        if (!wrapper) return;

        const cards = Array.from(wrapper.querySelectorAll('.modal-unit-item'));
        const allChecked = cards.every(c => {
            const cb = c.querySelector('.js-unit-checkbox');
            return cb && cb.checked;
        });
        const targetState = !allChecked;

        cards.forEach(c => {
            const cb = c.querySelector('.js-unit-checkbox');
            if (!cb) return;
            cb.checked = targetState;
            syncUnitCardVisual(c, targetState);
        });

        const t3Section = wrapper.querySelector('.tier3-section');
        if (targetState && t3Section) {
            setTier3Expanded(t3Section, true);
        }

        updateModalSelectedBadge();
    }
    window.toggleBranchUnits = toggleBranchUnits;

    function toggleUnitCard(card) {
        const cb = card.querySelector('.js-unit-checkbox');
        if (!cb) return;
        cb.checked = !cb.checked;
        syncUnitCardVisual(card, cb.checked);
        updateModalSelectedBadge();
        if (document.getElementById('modal-coverage-filter')?.value !== 'all') {
            filterModalUnits();
        }
    }
    window.toggleUnitCard = toggleUnitCard;

    function openTwgAssignmentModal(userId, fallbackName = '', fallbackEmail = '', fallbackIds = null) {
        const modal = document.getElementById('twg-assignment-modal');
        if (!modal) return;

        const uid = Number(userId);
        twgState.activeAssignModalUserId = uid;

        const userInfo = twgState.twgUsers[uid] || twgState.twgUsers[String(uid)] || {};
        const name = userInfo.name || fallbackName || 'TWG Reviewer';
        const email = userInfo.email || fallbackEmail || '';
        const assignedIds = Array.isArray(fallbackIds) ? fallbackIds.map(Number) : getAssignedUnitIdsForUser(uid);

        document.getElementById('modal-user-id').value = uid;
        const reviewerNameEl = document.getElementById('modal-reviewer-name');
        if (reviewerNameEl) reviewerNameEl.textContent = name;
        const reviewerEmailEl = document.getElementById('modal-reviewer-email');
        if (reviewerEmailEl) reviewerEmailEl.textContent = email ? `(${email})` : '';

        const filterInput = document.getElementById('modal-unit-filter');
        if (filterInput) filterInput.value = '';
        const tierFilter = document.getElementById('modal-tier-filter');
        if (tierFilter) tierFilter.value = 'all';
        const covFilter = document.getElementById('modal-coverage-filter');
        if (covFilter) covFilter.value = 'all';

        document.querySelectorAll('.modal-unit-item').forEach(card => {
            const cb = card.querySelector('.js-unit-checkbox');
            if (!cb) return;
            cb.checked = assignedIds.includes(Number(cb.value));
            syncUnitCardVisual(card, cb.checked);
        });

        // Auto-expand Tier 3 sections that contain checked items; collapse others for clean scanning
        document.querySelectorAll('.tier3-section').forEach(sec => {
            const hasChecked = Array.from(sec.querySelectorAll('.tier3-container .js-unit-checkbox')).some(cb => cb.checked);
            setTier3Expanded(sec, hasChecked);
        });

        filterModalUnits();
        updateModalSelectedBadge();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    window.openTwgAssignmentModal = openTwgAssignmentModal;

    function closeTwgAssignmentModal() {
        const modal = document.getElementById('twg-assignment-modal');
        if (!modal) return;
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        twgState.activeAssignModalUserId = null;
    }
    window.closeTwgAssignmentModal = closeTwgAssignmentModal;

    function filterModalUnits() {
        const query = (document.getElementById('modal-unit-filter')?.value || '').toLowerCase().trim();
        const tier = document.getElementById('modal-tier-filter')?.value || 'all';
        const coverage = document.getElementById('modal-coverage-filter')?.value || 'all';

        let visibleCount = 0;

        document.querySelectorAll('.modal-unit-item').forEach(item => {
            const name = (item.dataset.name || '').toLowerCase();
            const path = (item.dataset.path || '').toLowerCase();
            const positions = (item.dataset.positions || '').toLowerCase();
            const itemTier = item.dataset.tier || '';
            const cb = item.querySelector('.js-unit-checkbox');
            const isChecked = !!(cb && cb.checked);
            const hasOthers = item.dataset.hasOthers === '1';

            const matchQuery = !query || name.includes(query) || path.includes(query) || positions.includes(query);
            const matchTier = (tier === 'all') || (itemTier === tier);

            let matchCoverage = true;
            if (coverage === 'unassigned') {
                matchCoverage = !isChecked && !hasOthers;
            } else if (coverage === 'selected') {
                matchCoverage = isChecked;
            } else if (coverage === 'shared') {
                matchCoverage = hasOthers;
            }

            const isVisible = matchQuery && matchTier && matchCoverage;
            item.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        const hasActiveFilter = !!query || tier !== 'all' || coverage !== 'all';

        document.querySelectorAll('.tier2-wrapper').forEach(wrapper => {
            const t3Section = wrapper.querySelector('.tier3-section');
            if (t3Section) {
                const visibleT3 = Array.from(t3Section.querySelectorAll('.tier3-container .modal-unit-item')).some(it => it.style.display !== 'none');
                t3Section.style.display = visibleT3 ? '' : 'none';
                if (hasActiveFilter && visibleT3) {
                    setTier3Expanded(t3Section, true);
                }
            }
            const hasVisibleChild = Array.from(wrapper.querySelectorAll('.modal-unit-item')).some(it => it.style.display !== 'none');
            wrapper.style.display = hasVisibleChild ? '' : 'none';
        });

        document.querySelectorAll('.tree-children-container').forEach(container => {
            const hasVisibleChild = Array.from(container.querySelectorAll('.modal-unit-item')).some(it => it.style.display !== 'none');
            container.style.display = hasVisibleChild ? '' : 'none';
        });

        document.querySelectorAll('.tree-branch-container').forEach(branch => {
            const hasVisibleItem = Array.from(branch.querySelectorAll('.modal-unit-item')).some(it => it.style.display !== 'none');
            branch.style.display = hasVisibleItem ? '' : 'none';
        });

        const emptyMsg = document.getElementById('modal-units-empty-msg');
        if (emptyMsg) {
            emptyMsg.style.display = (visibleCount === 0) ? 'flex' : 'none';
        }
    }
    window.filterModalUnits = filterModalUnits;

    async function selectModalUnits(type) {
        const allCards = Array.from(document.querySelectorAll('.modal-unit-item'));

        if (typeof window.appConfirm === 'function') {
            if (type === 'visible') {
                const visibleCards = allCards.filter(card => card.style.display !== 'none');
                const ok = await window.appConfirm(
                    `Select all ${visibleCards.length} currently visible ${visibleCards.length === 1 ? 'department / unit' : 'departments & units'}?`,
                    {
                        title: 'Select Visible Departments & Units',
                        confirmText: 'Select Visible',
                        cancelText: 'Cancel',
                        variant: 'warning'
                    }
                );
                if (!ok) return;
            } else if (type === 'clear-visible') {
                const visibleChecked = allCards.filter(card => card.style.display !== 'none' && card.querySelector('.js-unit-checkbox')?.checked);
                const msg = visibleChecked.length > 0
                    ? `Clear ${visibleChecked.length} selected visible ${visibleChecked.length === 1 ? 'department / unit' : 'departments & units'}?`
                    : 'Clear all selections among currently visible departments & units?';
                const ok = await window.appConfirm(msg, {
                    title: 'Clear Visible Departments & Units',
                    confirmText: 'Clear Visible',
                    cancelText: 'Cancel',
                    variant: 'danger'
                });
                if (!ok) return;
            } else if (type === 'none') {
                const totalChecked = allCards.filter(card => card.querySelector('.js-unit-checkbox')?.checked);
                const msg = totalChecked.length > 0
                    ? `Clear all ${totalChecked.length} selected ${totalChecked.length === 1 ? 'department / unit' : 'departments & units'}?`
                    : 'Clear all selected departments & units?';
                const ok = await window.appConfirm(msg, {
                    title: 'Clear All Selections',
                    confirmText: 'Clear All',
                    cancelText: 'Cancel',
                    variant: 'danger'
                });
                if (!ok) return;
            }
        }

        allCards.forEach(card => {
            const cb = card.querySelector('.js-unit-checkbox');
            if (!cb) return;

            if (type === 'visible') {
                if (card.style.display !== 'none') cb.checked = true;
            } else if (type === 'clear-visible') {
                if (card.style.display !== 'none') cb.checked = false;
            } else if (type === 'none') {
                cb.checked = false;
            }
            syncUnitCardVisual(card, cb.checked);
        });
        updateModalSelectedBadge();
        if (document.getElementById('modal-coverage-filter')?.value !== 'all') {
            filterModalUnits();
        }
    }
    window.selectModalUnits = selectModalUnits;

    function updateModalSelectedBadge() {
        const checkedBoxes = Array.from(document.querySelectorAll('.js-unit-checkbox:checked'));
        const selectedIds = checkedBoxes.map(cb => Number(cb.value));
        const activeUid = twgState.activeAssignModalUserId || Number(document.getElementById('modal-user-id')?.value || 0);
        const details = computeTwgCoverageDetails(activeUid, selectedIds);

        const badge = document.getElementById('modal-selected-badge');
        if (badge) badge.textContent = selectedIds.length;

        const staffBadge = document.getElementById('modal-selected-staff-badge');
        if (staffBadge) staffBadge.textContent = details.staffCount;

        syncTier3SectionCounters();
    }
    window.updateModalSelectedBadge = updateModalSelectedBadge;

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('js-unit-checkbox')) {
            updateModalSelectedBadge();
        }
    });

    function saveTwgUnitsAjax(userId, unitIds, { onDone = null, showSuccessModal = false } = {}) {
        const formData = new FormData();
        formData.append('user_id', String(userId));
        unitIds.forEach(id => formData.append('unit_ids[]', String(id)));

        apiPost('<?= base_url('account/twg-assignments/update') ?>', formData, {
            onSuccess: (data) => {
                if (data.unitCoverageMeta) {
                    twgState.unitCoverageMeta = data.unitCoverageMeta;
                }
                if (data.allAssignmentsGrouped) {
                    twgState.assignmentsGrouped = data.allAssignmentsGrouped;
                } else {
                    twgState.assignmentsGrouped[userId] = data.assigned || [];
                }

                refreshAllTwgUi();
                if (typeof onDone === 'function') onDone(true, data);

                if (showSuccessModal) {
                    window.appAlert(data.message || 'Assigned departments & units updated successfully!', {
                        title: 'Assignments Saved',
                        variant: 'success'
                    });
                }
            },
            onError: (errMsg) => {
                if (typeof onDone === 'function') onDone(false, null);
                window.appAlert(errMsg || 'Failed to save assignments.', { variant: 'danger' });
            }
        });
    }

    async function submitTwgAssignments(event) {
        event.preventDefault();
        const form = event.target;
        const submitBtn = document.getElementById('btn-save-twg-assignments');
        const userId = Number(document.getElementById('modal-user-id')?.value || 0);
        if (!userId) return;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-black inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Saving...</span>
            `;
        }

        const checkedIds = Array.from(form.querySelectorAll('.js-unit-checkbox:checked')).map(cb => Number(cb.value));

        saveTwgUnitsAjax(userId, checkedIds, {
            showSuccessModal: true,
            onDone: (ok) => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75');
                    submitBtn.innerHTML = '<span>Save Assignments</span>';
                }
                if (ok) {
                    closeTwgAssignmentModal();
                }
            }
        });
    }
    window.submitTwgAssignments = submitTwgAssignments;

    function quickRemoveTwgUnit(event, userId, unitId) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const currentIds = getAssignedUnitIdsForUser(userId);
        const remainingIds = currentIds.filter(id => Number(id) !== Number(unitId));
        saveTwgUnitsAjax(userId, remainingIds, { showSuccessModal: false });
    }
    window.quickRemoveTwgUnit = quickRemoveTwgUnit;

    function filterTwgReviewers() {
        const query = (document.getElementById('twg-search-input')?.value || '').toLowerCase().trim();
        let visibleCards = 0;
        document.querySelectorAll('.twg-reviewer-card').forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const email = (card.dataset.email || '').toLowerCase();
            const coverage = (card.dataset.coverage || '').toLowerCase();
            const match = !query || name.includes(query) || email.includes(query) || coverage.includes(query);
            card.style.display = match ? '' : 'none';
            if (match) visibleCards++;
        });
        const emptyEl = document.getElementById('twg-reviewers-empty-search');
        if (emptyEl) {
            emptyEl.style.display = (visibleCards === 0) ? 'flex' : 'none';
        }
    }
    window.filterTwgReviewers = filterTwgReviewers;

    // Close modal on backdrop click or ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const confirmModal = document.getElementById('confirm-modal');
            if (confirmModal && !confirmModal.classList.contains('hidden')) {
                document.getElementById('btn-confirm-cancel')?.click();
                return;
            }
            closeTwgAssignmentModal();
        }
    });

    const twgModal = document.getElementById('twg-assignment-modal');
    if (twgModal) {
        twgModal.addEventListener('click', (e) => {
            if (e.target === twgModal) {
                closeTwgAssignmentModal();
            }
        });
    }
</script>
class CustomSelect {
    constructor(selectElement) {
        this.select = selectElement;
        
        // Prevent double initialization
        if (this.select.nextElementSibling && this.select.nextElementSibling.classList.contains('custom-select-wrapper')) {
            return;
        }

        // Hide original select but keep it focusable for HTML5 validation
        this.select.style.opacity = '0';
        this.select.style.position = 'absolute';
        this.select.style.zIndex = '-1';
        this.select.style.height = '0';
        this.select.style.width = '0';
        this.select.style.pointerEvents = 'none';
        this.currentPage = 1;
        this.pageSize = 10;
        
        this.createUI();
        this.setupEvents();
        this.updateSelectedText();
    }

    createUI() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'custom-select-wrapper relative w-full';

        // Button mimicking a standard button instead of a dropdown
        this.button = document.createElement('button');
        this.button.type = 'button';
        this.button.className = this.select.className.replace('appearance-none', '') + ' flex items-center justify-center gap-2 text-center w-full transition-all';
        
        this.textSpan = document.createElement('span');
        this.textSpan.className = 'truncate block';
        
        this.icon = document.createElement('div');
        this.icon.innerHTML = `<svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>`;
        
        this.button.appendChild(this.textSpan);
        this.button.appendChild(this.icon);

        // Dropdown container (now a fixed Modal)
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'fixed inset-0 z-[200] hidden items-center justify-center px-4 py-6 bg-black/60 backdrop-blur-sm transition-opacity';
        
        // Modal content box
        this.modalBox = document.createElement('div');
        this.modalBox.className = 'bg-white dark:bg-zinc-900 border border-surface-border rounded-2xl shadow-2xl flex flex-col w-full max-w-sm max-h-[80vh] overflow-hidden transform transition-all';
        
        // Search header
        this.searchWrapper = document.createElement('div');
        this.searchWrapper.className = 'p-4 border-b border-surface-border/50 bg-zinc-50 dark:bg-zinc-800/30 shrink-0 flex items-center gap-3';
        
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.placeholder = 'Search...';
        this.searchInput.className = 'w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:outline-none text-text shadow-sm';
        
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'p-2 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 text-text-muted transition-colors';
        closeBtn.innerHTML = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.close();
        });

        this.searchWrapper.appendChild(this.searchInput);
        this.searchWrapper.appendChild(closeBtn);

        // Options list
        this.optionsList = document.createElement('div');
        this.optionsList.className = 'flex-1 overflow-y-auto custom-scrollbar p-2';

        // Pagination footer
        this.paginationFooter = document.createElement('div');
        this.paginationFooter.className = 'p-3 border-t border-surface-border/50 bg-zinc-50 dark:bg-zinc-800/30 shrink-0 flex justify-between items-center hidden';
        
        this.pageInfo = document.createElement('div');
        this.pageInfo.className = 'text-[9px] font-bold text-text-muted uppercase tracking-widest';
        
        const pageBtns = document.createElement('div');
        pageBtns.className = 'flex gap-1';
        
        this.btnPrev = document.createElement('button');
        this.btnPrev.type = 'button';
        this.btnPrev.className = 'p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer';
        this.btnPrev.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>`;
        
        this.btnNext = document.createElement('button');
        this.btnNext.type = 'button';
        this.btnNext.className = 'p-1 rounded border border-surface-border text-xs text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer';
        this.btnNext.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>`;
        
        pageBtns.appendChild(this.btnPrev);
        pageBtns.appendChild(this.btnNext);
        
        this.paginationFooter.appendChild(this.pageInfo);
        this.paginationFooter.appendChild(pageBtns);

        this.modalBox.appendChild(this.searchWrapper);
        this.modalBox.appendChild(this.optionsList);
        this.modalBox.appendChild(this.paginationFooter);
        this.dropdown.appendChild(this.modalBox);

        this.wrapper.appendChild(this.button);
        // Append modal to body so it isn't constrained by parent relative/overflow contexts
        document.body.appendChild(this.dropdown);


        // Insert into DOM
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        
        // Remove the hardcoded absolute caret if it exists (like in the existing profile view)
        const oldCaret = this.wrapper.parentNode.querySelector('.absolute.inset-y-0');
        if (oldCaret && oldCaret !== this.wrapper && oldCaret.parentNode === this.wrapper.parentNode) {
            oldCaret.style.display = 'none';
        }
    }

    renderOptions(filter = '', resetPage = false) {
        if (resetPage) this.currentPage = 1;
        this.optionsList.innerHTML = '';
        
        let matches = [];

        Array.from(this.select.options).forEach((opt, index) => {
            if (opt.disabled) return;
            
            if (opt.text.toLowerCase().includes(filter.toLowerCase())) {
                matches.push({ opt, index });
            }
        });
        
        const totalItems = matches.length;
        const totalPages = Math.ceil(totalItems / this.pageSize) || 1;
        
        if (this.currentPage > totalPages) this.currentPage = totalPages;
        if (this.currentPage < 1) this.currentPage = 1;
        
        const startIndex = (this.currentPage - 1) * this.pageSize;
        const endIndex = startIndex + this.pageSize;
        
        const paginated = matches.slice(startIndex, endIndex);

        if (totalItems > 0) {
            paginated.forEach(({opt, index}) => {
                const item = document.createElement('div');
                item.className = 'px-3 py-2 text-sm text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg cursor-pointer transition-colors flex items-center gap-2';
                
                const level = parseInt(opt.dataset.level || '0');
                if (level > 0) {
                    item.style.paddingLeft = `calc(0.75rem + ${level * 1.25}rem)`;
                    const indicator = document.createElement('span');
                    indicator.className = 'text-surface-border shrink-0';
                    indicator.innerHTML = '&#8627;';
                    item.appendChild(indicator);
                }
                
                if (this.select.selectedIndex === index) {
                    item.classList.add('bg-accent/10', 'text-accent', 'font-bold');
                }
                
                const textNode = document.createElement('span');
                textNode.textContent = opt.text;
                textNode.className = 'truncate block';
                item.appendChild(textNode);

                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.select.selectedIndex = index;
                    this.select.dispatchEvent(new Event('change'));
                    this.updateSelectedText();
                    this.close();
                });
                
                this.optionsList.appendChild(item);
            });
            
            if (totalItems > this.pageSize) {
                this.paginationFooter.classList.remove('hidden');
                const startLabel = startIndex + 1;
                const endLabel = Math.min(endIndex, totalItems);
                this.pageInfo.innerHTML = `<span>${startLabel}</span>-<span>${endLabel}</span> of <span>${totalItems}</span>`;
                
                this.btnPrev.disabled = this.currentPage === 1;
                this.btnNext.disabled = this.currentPage === totalPages;
            } else {
                this.paginationFooter.classList.add('hidden');
            }
        } else {
            this.paginationFooter.classList.add('hidden');
            const noRes = document.createElement('div');
            noRes.className = 'px-3 py-4 text-center text-xs text-text-muted italic';
            noRes.textContent = 'No results found.';
            this.optionsList.appendChild(noRes);
        }
    }

    updateSelectedText() {
        const selected = this.select.options[this.select.selectedIndex];
        if (selected && selected.text) {
            this.textSpan.textContent = selected.text;
            if (selected.disabled) {
                this.textSpan.classList.add('text-text-muted');
            } else {
                this.textSpan.classList.remove('text-text-muted');
            }
        } else {
            this.textSpan.textContent = 'Select...';
        }
    }

    open() {
        this.dropdown.classList.remove('hidden');
        this.dropdown.classList.add('flex');
        
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
        
        this.searchInput.value = '';
        this.renderOptions('', true);
        
        // Focus the input slightly later to allow the modal render
        setTimeout(() => this.searchInput.focus(), 50);
    }

    close() {
        this.dropdown.classList.add('hidden');
        this.dropdown.classList.remove('flex');
        
        // Restore body scroll
        document.body.style.overflow = '';
    }

    setupEvents() {
        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.open();
        });

        this.dropdown.addEventListener('click', (e) => {
            // If they click the backdrop (not the modalBox), close it
            if (e.target === this.dropdown) {
                this.close();
            }
        });
        
        this.btnPrev.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (this.currentPage > 1) {
                this.currentPage--;
                this.renderOptions(this.searchInput.value, false);
            }
        });
        
        this.btnNext.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.currentPage++;
            this.renderOptions(this.searchInput.value, false);
        });
        
        this.searchInput.addEventListener('input', (e) => {
            this.renderOptions(e.target.value, true);
        });

        // Prevent form submission on enter in search
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Optionally select the first item
                const firstItem = this.optionsList.querySelector('.cursor-pointer');
                if (firstItem) firstItem.click();
            }
        });
        
        // Listen to external changes on the select
        this.select.addEventListener('change', () => this.updateSelectedText());
    }
}

function initCustomSelects() {
    document.querySelectorAll('select[data-custom-select]').forEach(select => {
        new CustomSelect(select);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCustomSelects();

    // Logic for .js-custom-select (simple HTML dropdowns)
    document.querySelectorAll('.js-custom-select').forEach(wrapper => {
        const btn = wrapper.querySelector('.js-select-button');
        const dropdown = wrapper.querySelector('.js-select-dropdown');
        const label = wrapper.querySelector('.js-select-label');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const options = wrapper.querySelectorAll('.js-select-option');
        const icon = btn.querySelector('svg');
        
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const isExpanded = dropdown.classList.contains('opacity-100');
            
            document.querySelectorAll('.js-select-dropdown').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('opacity-100', 'scale-100');
                    d.classList.add('opacity-0', 'scale-95');
                    d.hideTimeout = setTimeout(() => d.classList.add('hidden'), 200);
                }
            });
            document.querySelectorAll('.js-select-button svg').forEach(svg => {
                if (svg !== icon) {
                    svg.classList.remove('rotate-180');
                }
            });
            
            if (!isExpanded) {
                if (dropdown.hideTimeout) clearTimeout(dropdown.hideTimeout);
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.remove('opacity-0', 'scale-95');
                    dropdown.classList.add('opacity-100', 'scale-100');
                    icon.classList.add('rotate-180');
                }, 10);
            } else {
                dropdown.classList.remove('opacity-100', 'scale-100');
                dropdown.classList.add('opacity-0', 'scale-95');
                dropdown.hideTimeout = setTimeout(() => dropdown.classList.add('hidden'), 200);
                icon.classList.remove('rotate-180');
            }
        });
        
        options.forEach(opt => {
            opt.addEventListener('click', () => {
                hiddenInput.value = opt.dataset.value;
                hiddenInput.dispatchEvent(new Event('change'));
                label.textContent = opt.dataset.label;
                
                options.forEach(o => o.classList.remove('bg-accent/10', 'text-accent'));
                opt.classList.add('bg-accent/10', 'text-accent');
                
                dropdown.classList.remove('opacity-100', 'scale-100');
                dropdown.classList.add('opacity-0', 'scale-95');
                dropdown.hideTimeout = setTimeout(() => dropdown.classList.add('hidden'), 200);
                icon.classList.remove('rotate-180');
            });
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.js-custom-select')) {
            document.querySelectorAll('.js-select-dropdown').forEach(d => {
                if (!d.classList.contains('hidden')) {
                    d.classList.remove('opacity-100', 'scale-100');
                    d.classList.add('opacity-0', 'scale-95');
                    d.hideTimeout = setTimeout(() => d.classList.add('hidden'), 200);
                }
            });
            document.querySelectorAll('.js-select-button svg').forEach(svg => {
                svg.classList.remove('rotate-180');
            });
        }
    });
});

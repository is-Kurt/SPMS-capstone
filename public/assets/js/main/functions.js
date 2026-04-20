function pagination() {
    let currentPage = 1;
    const rowsPerPage = 10;
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const scrollContainer = document.querySelector('.custom-scrollbar');

    function updatePagination() {
        const rows = document.querySelectorAll('.doc-row');
        const emptyState = document.getElementById('empty-doc');
        const paginationUi = document.getElementById('pagination-ui');

        if (rows.length === 0) {
            if (emptyState) {
                emptyState.classList.remove('hidden');
                paginationUi.classList.add('hidden');
            };
            return; 
        }
        if (emptyState) {
            emptyState.classList.add('hidden');
            paginationUi.classList.remove('hidden');
        }

        const totalPages = Math.ceil(rows.length / rowsPerPage);

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = ''; 
            } else {
                row.style.display = 'none'; 
            }
        });

        const currentEnd = Math.min(end, rows.length);
        if (pageInfo) {
            pageInfo.innerHTML = `Showing <span class="text-text">${start + 1} to ${currentEnd}</span> of <span class="text-text">${rows.length}</span>`;
        }

        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
                if (scrollContainer) scrollContainer.scrollTop = 0;
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const rows = document.querySelectorAll('.doc-row');
            if (currentPage < Math.ceil(rows.length / rowsPerPage)) {
                currentPage++;
                updatePagination();
                if (scrollContainer) scrollContainer.scrollTop = 0;
            }
        });
    }
    
    updatePagination();

    document.addEventListener('item-deleted', () => {
        syncSidebarCounts();
        updatePagination();
    });
}

function syncSidebarCounts() {
    apiGet(`${AppConfig.baseUrl}/count`, {}, {
        onSuccess: (response) => {
            const counts = response.counts;
            
            const mapping = {
                'all_docs': 'documents',
                'owned': 'docs=owned',
                'shared': 'docs=shared',
                
                'all_submissions': 'submissions',
                'locked': 'docs=locked',
                'pending': 'docs=pending',
                'unevaluated': 'docs=unevaluated',
                'evaluated': 'docs=evaluated'
            };

            const sidebar = document.getElementById('sidebar-nav');
            if (!sidebar) return;

            Object.entries(mapping).forEach(([key, searchString]) => {
                const navLink = sidebar.querySelector(`a[href*="${searchString}"]`);
                
                if (navLink) {
                    const span = navLink.querySelector('span:last-child');
                    if (span) {
                        const newCount = counts[key] || 0;
                        span.innerText = newCount;
                        
                        if (newCount === 0) {
                            span.classList.add('invisible');
                        } else {
                            span.classList.remove('invisible');
                        }
                    }
                }
            });
        }
    });
}

function saveRemark(userRatingId, inputElement) {
    const remarks = inputElement.value.trim();
    const indicator = document.getElementById(`save-indicator-${userRatingId}`);
    
    const formData = new FormData();
    formData.append('ur_id', userRatingId);
    formData.append('remarks', remarks);

    // Uses the apiPost loaded globally by main.php
    apiPost(`${AppConfig.baseUrl}/save`, formData, {
        onSuccess: () => {
            inputElement.classList.remove('border-red-500', 'text-red-500');
            indicator.classList.remove('opacity-0');
            setTimeout(() => indicator.classList.add('opacity-0'), 2000);
        },
        onError: () => {
            inputElement.classList.add('border-red-500', 'text-red-500');
        }
    });
}

function getAdjectivalRating(score) {
    if (score >= 4.30) return 'O';  // Outstanding
    if (score >= 3.54) return 'VS'; // Very Satisfactory
    if (score >= 2.70) return 'S';  // Satisfactory
    if (score >= 1.50) return 'US'; // Unsatisfactory (Standard scale continuation)
    return 'P';                     // Poor (Standard scale continuation)
}

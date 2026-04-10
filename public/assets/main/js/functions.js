function pagination() {
    // .doc-row
    let currentPage = 1;
    const rowsPerPage = 10;
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const scrollContainer = document.querySelector('.custom-scrollbar');

    function updatePagination() {
        const rows = document.querySelectorAll('.doc-row');
        const emptyState = document.getElementById('empty-doc');

        if (rows.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return; 
        }
        if (emptyState) emptyState.classList.add('hidden');

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

    document.addEventListener('item-deleted', () => {
        updatePagination();
    });
    
    updatePagination();
}

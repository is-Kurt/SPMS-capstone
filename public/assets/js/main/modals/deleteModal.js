import Modal from './Modal.js';

let deleteTargetId = null;
let deleteTargetUrl = null;
let isDeleting = false;

const deleteModal = new Modal('delete-modal', {
    onClose: () => {
        deleteTargetId = null;
        deleteTargetUrl = null;
    }
});

document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.btn-delete-modal');
    if (deleteBtn) {
        const { id, url, title, desc } = deleteBtn.dataset;
        
        deleteTargetId = id;
        deleteTargetUrl = url;
        
        document.getElementById('delete-doc-desc').innerText = `"${desc}"`;
        document.getElementById('delete-doc-title').innerText = title;
        
        deleteModal.open();
    }
});

const btnConfirmDelete = document.getElementById('btn-confirm-delete');
if (btnConfirmDelete) {
    btnConfirmDelete.addEventListener('click', () => {
        if (!deleteTargetId || !deleteTargetUrl || isDeleting) return;

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('doc_id', deleteTargetId);
        
        isDeleting = true;
        apiPost(deleteTargetUrl, formData, {
            onSuccess: () => {
                // 1. SMART REDIRECT: Check if the item we just deleted is in the current URL
                if (window.location.href.includes(deleteTargetId)) {
                    // If we deleted a folder, redirect back to the main /folders page
                    if (deleteTargetUrl.includes('folder')) {
                        window.location.href = deleteTargetUrl + 's'; // converts '/folder' to '/folders'
                    } else {
                        // Fallback for documents or anything else
                        window.location.href = '/folders'; 
                    }
                    return; // Stop execution here so it doesn't try to reload the dead page
                }

                // 2. STANDARD BEHAVIOR: If we are not on the deleted item's specific page, remove row or reload
                const row = document.querySelector(`[data-doc-id="${deleteTargetId}"]`);
                if (row) {
                    row.remove();
                    document.dispatchEvent(new CustomEvent('item-deleted'));
                } else {
                    window.location.reload(); 
                }
                
                isDeleting = false;
                deleteModal.close();
            },
            onError: () => { isDeleting = false; }
        });
    });
}

document.getElementById('btn-close-delete').addEventListener('click', () => deleteModal.close());
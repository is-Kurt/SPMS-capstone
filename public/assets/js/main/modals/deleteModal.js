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

        // Capture targets locally BEFORE modal close resets the outer state
        const targetUrl = deleteTargetUrl;
        const targetId = deleteTargetId;

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('doc_id', targetId);
        
        isDeleting = true;
        apiPost(targetUrl, formData, {
            onSuccess: () => {
                isDeleting = false;
                deleteModal.close();

                // 1. SMART REDIRECT FOR FOLDERS: Seamlessly refresh the workspace and sidebar
                if (targetUrl && targetUrl.includes('folder')) {
                    const isArchived = window.location.href.includes('folders/archived') || window.location.href.includes('/archived');
                    const redirectUrl = isArchived ? '/folders/archived' : '/folders';
                    if (typeof window.loadFolderTab === 'function') {
                        window.loadFolderTab(redirectUrl, true);
                        return;
                    }
                    window.location.href = redirectUrl;
                    return;
                }

                // 2. SMART REDIRECT FOR DOCUMENTS: Check if the item we just deleted is in current URL
                if (targetId && window.location.href.includes(targetId)) {
                    window.location.href = '/folders'; 
                    return;
                }

                // 3. STANDARD BEHAVIOR: Remove row if present in document table
                const row = document.querySelector(`[data-doc-id="${targetId}"]`);
                if (row) {
                    row.remove();
                    document.dispatchEvent(new CustomEvent('item-deleted'));
                } else {
                    window.location.reload(); 
                }
            },
            onError: (errorMsg) => {
                isDeleting = false;
                if (typeof window.showToast === 'function') {
                    window.showToast(errorMsg || 'Failed to delete item.', 'danger');
                }
            }
        });
    });
}

document.getElementById('btn-close-delete').addEventListener('click', () => deleteModal.close());
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
                const row = document.querySelector(`[data-doc-id="${deleteTargetId}"]`);
                if (row) {
                    row.remove();
                    document.dispatchEvent(new CustomEvent('item-deleted'));
                } else {
                    window.location.href = '?'; 
                }
                
                isDeleting = false;
                deleteModal.close();
            },
            onError: () => { isDeleting = false; }
        });
    });
}

document.getElementById('btn-close-delete').addEventListener('click', () => deleteModal.close());
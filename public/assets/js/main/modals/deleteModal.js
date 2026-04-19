import Modal from './Modal.js';

let deleteTargetId = null;
let isDeleting = false;

const deleteModal = new Modal('delete-modal', {
    onClose: () => {
        deleteTargetId = null;
    }
});

document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.btn-trigger-delete');
    if (deleteBtn) {
        deleteTargetId = deleteBtn.getAttribute('data-id');
        const title = deleteBtn.getAttribute('data-title');
        
        document.getElementById('delete-doc-title').innerText = `"${title}"`;
        deleteModal.open();
    }
});

async function confirmDelete() {
    if (!deleteTargetId || isDeleting) return;

    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('doc_id', deleteTargetId);
    
    apiPost(AppConfig.baseUrl, formData, {
        onSuccess: () => {
            const row = document.querySelector(`[data-doc-id="${deleteTargetId}"]`);
            if (row) {
                row.remove();
                document.dispatchEvent(new CustomEvent('item-deleted'));
            }
            
            isDeleting = false;
            deleteModal.close();
        },
        onError: () => { isDeleting = false; }
    }
    )
}

document.getElementById('btn-confirm-delete').addEventListener('click', confirmDelete);
document.getElementById('btn-close-delete').addEventListener('click', () => deleteModal.close());
import Modal from './Modal.js';

let deleteTargetId = null;

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
    if (!deleteTargetId) return;

    const formData = new FormData();
    formData.append('id', deleteTargetId);
    formData.append('_method', 'DELETE'); 
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try {
        const response = await axios.post(AppConfig.baseUrl, formData);
        
        if (response.data.status === 'success') {
            AppConfig.csrfHash = response.data.csrfHash;
            
            const row = document.querySelector(`[data-doc-id="${deleteTargetId}"]`);
            if (row) {
                row.remove();
                document.dispatchEvent(new CustomEvent('item-deleted'));
            }
            
            deleteModal.close();
        }
    } catch (error) {
        console.error('Delete failed:', error);
    }
}

document.getElementById('btn-confirm-delete').addEventListener('click', confirmDelete);
document.getElementById('btn-close-delete').addEventListener('click', () => deleteModal.close());
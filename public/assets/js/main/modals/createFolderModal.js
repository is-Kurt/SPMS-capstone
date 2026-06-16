import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');

let isCreatingFolder = false;

const btnCreateFolder = document.getElementById('btn-create-folder-modal');
if (btnCreateFolder) {
    btnCreateFolder.addEventListener('click', () => {
        folderModal.open();
    });
}

const formCreateFolder = document.getElementById('form-create-folder');
if (formCreateFolder) {
    formCreateFolder.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isCreatingFolder) return;
        
        const formData = new FormData(e.target);

        isCreatingFolder = true;
        apiPost('folder', formData, {
            onSuccess: (response) => {
                isCreatingFolder = false;
                folderModal.close();
                window.location.href = `folders?folder_id=${response.id}`;
            },
            onError: () => {
                isCreatingFolder = false;
            }
        });
    });
}

document.getElementById('btn-close-folder').addEventListener('click', () => folderModal.close());
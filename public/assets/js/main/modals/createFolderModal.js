import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');

let isCreatingFolder = false;

document.getElementById('btn-open-folder-create').addEventListener('click', () => {
    folderModal.open();
});

document.getElementById('form-create-folder').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (isCreatingFolder) return;

    const form = e.target;
    const formData = new FormData(form);

    isCreatingFolder = true;
    apiPost(`${AppConfig.baseUrl}/createFolder`, formData, {
        onSuccess: () => {
            isCreatingFolder = false;
            folderModal.close();
        },
        onError: () => {
            isCreatingFolder = false;
        }
    });
});

window.closeFolderModal = () => {
    folderModal.close();
};
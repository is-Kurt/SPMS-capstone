import Modal from './Modal.js';

const createModal = new Modal('create-file-modal');

let isCreating = false;
let activeFolderId = null;

const btnOpenFile = document.getElementById('btn-open-create-file');
if (btnOpenFile) {
    btnOpenFile.addEventListener('click', (e) => {
        activeFolderId = e.currentTarget.getAttribute('data-folder-id');
        createModal.open();
    });
}

const formCreatFile = document.getElementById('form-create-file');
if (formCreatFile) {
    formCreatFile.addEventListener('submit', (e) => {
        e.preventDefault();
        if (isCreating || !activeFolderId) return;

        const formData = new FormData(e.target);
        
        formData.append('folder_id', activeFolderId);

        isCreating = true;
        apiPost('/document', formData, {
            onSuccess: (response) => {
                isCreating = false;
                createModal.close();
                window.location.reload();
            },
            onError: () => {
                isCreating = false;
            }
        });
    });
}

document.getElementById('btn-close-file').addEventListener('click', () => createModal.close());
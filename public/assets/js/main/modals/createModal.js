import Modal from './Modal.js';

const createModal = new Modal('create-modal');

let isCreating = false;

async function createDocument() {
    if (isCreating) return;

    const title = document.getElementById('new-doc-title').value;
    const template = document.getElementById('new-doc-template').value;

    const formData = new FormData();
    formData.append('title', title);
    formData.append('template', template);

    isCreating = true;
    apiPost(AppConfig.baseUrl, formData, {
        onSuccess: (response) => { 
            window.location.href = `${AppConfig.baseUrl}?Id=${response.id}`;
        },
        onError: () => {
            isCreating = false;
        }
    });
}

document.getElementById('btn-open-create').addEventListener('click', () => {
    createModal.open();
});

document.getElementById('btn-create').addEventListener('click', () => {
    createDocument();
});
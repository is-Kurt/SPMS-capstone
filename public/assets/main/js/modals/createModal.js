import Modal from './Modal.js';

const createModal = new Modal('create-modal');

async function createDocument() {
    const title = document.getElementById('new-doc-title').value;
    const template = document.getElementById('new-doc-template').value;
    const formData = new FormData();
    formData.append('title', title);
    formData.append('template', template);
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try {
        const response = await axios.post(AppConfig.createUrl, formData);

        if (response.data.status === 'success') {
            window.location.href = `${AppConfig.createUrl}?Id=${response.data.id}`;
        }
    } catch (error) {
        console.error("Creation failed", error);
    }
}

document.getElementById('btn-open-create').addEventListener('click', () => {
    createModal.open();
});

document.getElementById('btn-create').addEventListener('click', () => {
    createDocument();
});
import Modal from './Modal.js';

const folderModal = new Modal('edit-folder-modal');
let isSavingFolder = false;

const submitBtn = document.getElementById('btn-submit-edit-folder');
const idInput = document.getElementById('edit-folder-id');
const titleInput = document.getElementById('edit-folder-title');
const dateStart = document.getElementById('edit-folder-date-start');
const dateEnd = document.getElementById('edit-folder-date-end');

// Validation logic: End Date cannot be before Start Date
function handleDateConstraints() {
    if (dateStart.value) {
        dateEnd.min = dateStart.value; // Prevent user from selecting older dates in UI
        if (dateEnd.value && dateEnd.value < dateStart.value) {
            dateEnd.value = dateStart.value; // Auto-correct if they type it manually
        }
    }
}

if (dateStart && dateEnd) {
    dateStart.addEventListener('input', handleDateConstraints);
    dateEnd.addEventListener('input', handleDateConstraints);
}

window.openEditFolderModal = function(id, title, start, end) {
    idInput.value = id;
    titleInput.value = title;

    // Convert Database format (YYYY-MM-DD HH:MM:SS) to Input format (YYYY-MM-DDTHH:MM)
    let safeStart = start ? start.replace(' ', 'T').slice(0, 16) : '';
    let safeEnd = end ? end.replace(' ', 'T').slice(0, 16) : '';

    // Sanitize any lingering '24:00' times from the database
    dateStart.value = safeStart.replace('T24:00', 'T23:59');
    dateEnd.value = safeEnd.replace('T24:00', 'T23:59');

    handleDateConstraints();

    folderModal.open();
};

const formEditFolder = document.getElementById('form-edit-folder');
if (formEditFolder) {
    formEditFolder.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isSavingFolder) return;

        const formData = new FormData(e.target);

        isSavingFolder = true;
        submitBtn.innerText = 'Saving...';

        apiPost('/folder/update', formData, {
            onSuccess: () => {
                isSavingFolder = false;
                folderModal.close();
                window.location.reload();
            },
            onError: () => {
                isSavingFolder = false;
                submitBtn.innerText = 'Save Changes';
            }
        });
    });
}

document.getElementById('btn-close-edit-folder').addEventListener('click', () => folderModal.close());

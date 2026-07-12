import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');
let isCreatingFolder = false;

const submitBtn = document.getElementById('btn-submit-create-folder');
const titleInput = document.getElementById('create-folder-title');
const dateStart = document.getElementById('create-folder-date-start');
const dateEnd = document.getElementById('create-folder-date-end');

// Helper to correctly format JS dates for <input type="datetime-local">
function formatForDateTimeLocal(dateObj) {
    const d = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60000));
    return d.toISOString().slice(0, 16);
}

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

const btnCreateFolder = document.getElementById('btn-create-folder-modal');
if (btnCreateFolder) {
    btnCreateFolder.addEventListener('click', () => {
        titleInput.value = '';

        // Default Logic: Today @ 23:59 -> Tomorrow @ 23:59
        const today = new Date();
        today.setHours(23, 59, 0, 0);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        dateStart.value = formatForDateTimeLocal(today);
        dateEnd.value = formatForDateTimeLocal(tomorrow);
        handleDateConstraints();

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
        submitBtn.innerText = 'Creating...';

        apiPost('/folder', formData, {
            onSuccess: (response) => {
                isCreatingFolder = false;
                folderModal.close();

                if (response.id) {
                    window.location.href = `/folders/${response.id}`;
                } else {
                    window.location.reload();
                }
            },
            onError: () => {
                isCreatingFolder = false;
                submitBtn.innerText = 'Create Folder';
            }
        });
    });
}

document.getElementById('btn-close-create-folder').addEventListener('click', () => folderModal.close());

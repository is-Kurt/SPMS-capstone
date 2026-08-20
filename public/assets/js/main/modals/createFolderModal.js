import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');
let isCreatingFolder = false;

const submitBtn = document.getElementById('btn-submit-create-folder');
const titleInput = document.getElementById('create-folder-title');
const dateStart = document.getElementById('create-folder-date-start');
const dateEnd = document.getElementById('create-folder-date-end');
const targetStart = document.getElementById('create-folder-target-start');
const targetEnd = document.getElementById('create-folder-target-end');

// Helper to correctly format JS dates for <input type="datetime-local">
function formatForDateTimeLocal(dateObj) {
    const d = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60000));
    return d.toISOString().slice(0, 16);
}

// Validation logic: End Date cannot be before Start Date
function handleDateConstraints() {
    if (targetStart.value) {
        targetEnd.min = targetStart.value;
        if (targetEnd.value && targetEnd.value < targetStart.value) {
            targetEnd.value = targetStart.value;
        }
    }
    
    if (dateStart.value) {
        dateEnd.min = dateStart.value; 
        if (dateEnd.value && dateEnd.value < dateStart.value) {
            dateEnd.value = dateStart.value; 
        }
    }
    
    // Evaluation start cannot be before Target end
    if (targetEnd.value) {
        dateStart.min = targetEnd.value;
        if (dateStart.value && dateStart.value < targetEnd.value) {
            dateStart.value = targetEnd.value;
        }
    }
}

if (dateStart && dateEnd && targetStart && targetEnd) {
    dateStart.addEventListener('input', handleDateConstraints);
    dateEnd.addEventListener('input', handleDateConstraints);
    targetStart.addEventListener('input', handleDateConstraints);
    targetEnd.addEventListener('input', handleDateConstraints);
}

const btnCreateFolder = document.getElementById('btn-create-folder-modal');
if (btnCreateFolder) {
    btnCreateFolder.addEventListener('click', () => {
        titleInput.value = '';

        // Default Logic: Target = Today to Tomorrow; Eval = Tomorrow to +1 week
        const today = new Date();
        today.setHours(23, 59, 0, 0);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const nextWeek = new Date(tomorrow);
        nextWeek.setDate(nextWeek.getDate() + 7);

        targetStart.value = formatForDateTimeLocal(today);
        targetEnd.value = formatForDateTimeLocal(tomorrow);
        dateStart.value = formatForDateTimeLocal(tomorrow);
        dateEnd.value = formatForDateTimeLocal(nextWeek);
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

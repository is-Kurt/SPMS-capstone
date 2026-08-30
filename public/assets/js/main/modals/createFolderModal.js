import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');
let isCreatingFolder = false;

const submitBtn = document.getElementById('btn-submit-create-folder');
const titleInput = document.getElementById('create-folder-title');
const docTypes = ['ipcr', 'dpcr', 'opcr', 'iperf'];

// Helper to correctly format JS dates for <input type="datetime-local">
function formatForDateTimeLocal(dateObj) {
    const d = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60000));
    return d.toISOString().slice(0, 16);
}

// Validation logic for each doctype: End Date cannot be before Start Date
function handleDateConstraints(docType) {
    const targetStart = document.getElementById(`create-folder-${docType}-target-start`);
    const targetEnd = document.getElementById(`create-folder-${docType}-target-end`);
    const dateStart = document.getElementById(`create-folder-${docType}-eval-start`);
    const dateEnd = document.getElementById(`create-folder-${docType}-eval-end`);

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

docTypes.forEach(docType => {
    const targetStart = document.getElementById(`create-folder-${docType}-target-start`);
    const targetEnd = document.getElementById(`create-folder-${docType}-target-end`);
    const dateStart = document.getElementById(`create-folder-${docType}-eval-start`);
    const dateEnd = document.getElementById(`create-folder-${docType}-eval-end`);

    if (dateStart && dateEnd && targetStart && targetEnd) {
        dateStart.addEventListener('input', () => handleDateConstraints(docType));
        dateEnd.addEventListener('input', () => handleDateConstraints(docType));
        targetStart.addEventListener('input', () => handleDateConstraints(docType));
        targetEnd.addEventListener('input', () => handleDateConstraints(docType));
    }
});

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

        docTypes.forEach(docType => {
            const targetStart = document.getElementById(`create-folder-${docType}-target-start`);
            const targetEnd = document.getElementById(`create-folder-${docType}-target-end`);
            const dateStart = document.getElementById(`create-folder-${docType}-eval-start`);
            const dateEnd = document.getElementById(`create-folder-${docType}-eval-end`);
            
            if(targetStart) targetStart.value = formatForDateTimeLocal(today);
            if(targetEnd) targetEnd.value = formatForDateTimeLocal(tomorrow);
            if(dateStart) dateStart.value = formatForDateTimeLocal(tomorrow);
            if(dateEnd) dateEnd.value = formatForDateTimeLocal(nextWeek);
            
            handleDateConstraints(docType);
        });

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

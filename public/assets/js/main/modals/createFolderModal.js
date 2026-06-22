import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');
let isCreatingFolder = false;
let currentAction = 'create'; // Tracks if we are POSTing to /folder or /folder/update

const heading = document.getElementById('folder-modal-heading');
const subheading = document.getElementById('folder-modal-subheading');
const submitBtn = document.getElementById('btn-submit-folder');
const idInput = document.getElementById('folder-id-input');
const titleInput = document.getElementById('folder-title-input');
const dateStart = document.getElementById('folder-date-start');
const dateEnd = document.getElementById('folder-date-end');

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

// --- OPEN IN CREATE MODE ---
const btnCreateFolder = document.getElementById('btn-create-folder-modal');
if (btnCreateFolder) {
    btnCreateFolder.addEventListener('click', () => {
        currentAction = 'create';
        heading.innerText = 'Create Evaluation Folder';
        subheading.innerText = 'New Rating Batch';
        submitBtn.innerText = 'Create Folder';
        
        idInput.value = '';
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

// --- OPEN IN EDIT MODE ---
window.openEditFolderModal = function(id, title, start, end) {
    currentAction = 'edit';
    heading.innerText = 'Edit Evaluation Folder';
    subheading.innerText = 'Update Settings';
    submitBtn.innerText = 'Save Changes';
    
    idInput.value = id;
    titleInput.value = title;
    
    // Convert Database format (YYYY-MM-DD HH:MM:SS) to Input format (YYYY-MM-DDTHH:MM)
    let safeStart = start ? start.replace(' ', 'T').slice(0, 16) : '';
    let safeEnd = end ? end.replace(' ', 'T').slice(0, 16) : '';

    // FIXED: Sanitize any lingering '24:00' times from the database!
    dateStart.value = safeStart.replace('T24:00', 'T23:59');
    dateEnd.value = safeEnd.replace('T24:00', 'T23:59');
    
    handleDateConstraints();

    folderModal.open();
};

const formCreateFolder = document.getElementById('form-create-folder');
if (formCreateFolder) {
    formCreateFolder.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isCreatingFolder) return;
        
        const formData = new FormData(e.target);
        const targetUrl = currentAction === 'create' ? 'folder' : 'folder/update';

        isCreatingFolder = true;
        submitBtn.innerText = currentAction === 'create' ? 'Creating...' : 'Saving...';

        apiPost(targetUrl, formData, {
            onSuccess: (response) => {
                isCreatingFolder = false;
                folderModal.close();
                
                // If creating, we want to jump to the new folder. If updating, just reload the page.
                if (currentAction === 'create' && response.id) {
                    window.location.href = `folders?folder_id=${response.id}`;
                } else {
                    window.location.reload();
                }
            },
            onError: () => {
                isCreatingFolder = false;
                submitBtn.innerText = currentAction === 'create' ? 'Create Folder' : 'Save Changes';
            }
        });
    });
}

document.getElementById('btn-close-folder').addEventListener('click', () => folderModal.close());
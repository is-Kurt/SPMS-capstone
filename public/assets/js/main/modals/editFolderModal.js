import Modal from './Modal.js';

const folderModal = new Modal('edit-folder-modal');
let isSavingFolder = false;

const submitBtn = document.getElementById('btn-submit-edit-folder');
const idInput = document.getElementById('edit-folder-id');
const titleInput = document.getElementById('edit-folder-title');

const docTypes = ['ipcr', 'dpcr', 'opcr', 'iperf'];
const inputs = {};

docTypes.forEach(type => {
    inputs[type] = {
        targetStart: document.getElementById(`edit-folder-${type}-target-start`),
        targetEnd: document.getElementById(`edit-folder-${type}-target-end`),
        evalStart: document.getElementById(`edit-folder-${type}-eval-start`),
        evalEnd: document.getElementById(`edit-folder-${type}-eval-end`),
    };
});

function handleDateConstraints(type) {
    const { targetStart, targetEnd, evalStart, evalEnd } = inputs[type];

    if (targetStart?.value) {
        if (targetEnd) {
            targetEnd.min = targetStart.value;
            if (targetEnd.value && targetEnd.value < targetStart.value) {
                targetEnd.value = targetStart.value;
            }
        }
    }

    if (evalStart?.value) {
        if (evalEnd) {
            evalEnd.min = evalStart.value;
            if (evalEnd.value && evalEnd.value < evalStart.value) {
                evalEnd.value = evalStart.value;
            }
        }
    }

    if (targetEnd?.value) {
        if (evalStart) {
            evalStart.min = targetEnd.value;
            if (evalStart.value && evalStart.value < targetEnd.value) {
                evalStart.value = targetEnd.value;
            }
        }
    }
}

docTypes.forEach(type => {
    Object.values(inputs[type]).forEach(input => {
        if (input) {
            input.addEventListener('input', () => handleDateConstraints(type));
        }
    });
});

window.openEditFolderModal = function(id, title, dates = {}) {
    idInput.value = id;
    titleInput.value = title;

    docTypes.forEach(type => {
        ['target_start', 'target_end', 'eval_start', 'eval_end'].forEach(phase => {
            const rawDate = dates[`${type}_${phase}`];
            const safeDate = rawDate ? String(rawDate).replace(' ', 'T').slice(0, 16).replace('T24:00', 'T23:59') : '';
            const inputField = inputs[type][phase.replace(/_([a-z])/g, (g) => g[1].toUpperCase())];
            if (inputField) {
                inputField.value = safeDate;
            }
        });
        handleDateConstraints(type);
    });

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

document.getElementById('btn-close-edit-folder')?.addEventListener('click', () => folderModal.close());

import Modal from './Modal.js';

const folderModal = new Modal('create-folder-modal');
let isCreatingFolder = false;

const submitBtn = document.getElementById('btn-submit-create-folder');
const titleInput = document.getElementById('create-folder-title');
const docTypes = ['opcr', 'cdpcr', 'dpcr', 'ipcr', 'iperf'];

// Mode Elements
const modeGeneralBtn = document.getElementById('create-mode-general-btn');
const modeStaggeredBtn = document.getElementById('create-mode-staggered-btn');
const scheduleModeInput = document.getElementById('create-schedule-mode');
const generalPanel = document.getElementById('create-general-schedule-panel');
const staggeredPanel = document.getElementById('create-staggered-schedule-panel');
const syncAllBtn = document.getElementById('create-sync-all-btn');

// General Input Elements
const genTargetStart = document.getElementById('create-general-target-start');
const genTargetEnd = document.getElementById('create-general-target-end');
const genEvalStart = document.getElementById('create-general-eval-start');
const genEvalEnd = document.getElementById('create-general-eval-end');

const genTargetDuration = document.getElementById('create-gen-target-duration');
const genEvalDuration = document.getElementById('create-gen-eval-duration');
const timelineTargetDates = document.getElementById('create-timeline-target-dates');
const timelineEvalDates = document.getElementById('create-timeline-eval-dates');
const timelineSummary = document.getElementById('create-timeline-summary');

// Helper to correctly format JS dates for <input type="datetime-local">
function formatForDateTimeLocal(dateObj) {
    const d = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60000));
    return d.toISOString().slice(0, 16);
}

// Pretty date formatter for timeline preview
function formatPrettyDateRange(startStr, endStr) {
    if (!startStr || !endStr) return 'Not Configured';
    try {
        const s = new Date(startStr);
        const e = new Date(endStr);
        if (isNaN(s.getTime()) || isNaN(e.getTime())) return 'Invalid Date';
        const sMonth = s.toLocaleString('default', { month: 'short' });
        const eMonth = e.toLocaleString('default', { month: 'short' });
        const sDay = s.getDate();
        const eDay = e.getDate();
        const year = e.getFullYear();

        if (sMonth === eMonth && s.getFullYear() === e.getFullYear()) {
            return `${sMonth} ${sDay} – ${eDay}, ${year}`;
        }
        return `${sMonth} ${sDay} – ${eMonth} ${eDay}, ${year}`;
    } catch {
        return 'Not Configured';
    }
}

// Calculate days between two date strings
function getDaysBetween(startStr, endStr) {
    if (!startStr || !endStr) return null;
    const s = new Date(startStr);
    const e = new Date(endStr);
    if (isNaN(s.getTime()) || isNaN(e.getTime())) return null;
    const diffTime = e.getTime() - s.getTime();
    return Math.max(1, Math.round(diffTime / (1000 * 60 * 60 * 24)));
}

// Synchronize general date values across all paper types
function syncGeneralToAllDocTypes() {
    if (!genTargetStart) return;
    const tStart = genTargetStart.value;
    const tEnd = genTargetEnd?.value || '';
    const eStart = genEvalStart?.value || '';
    const eEnd = genEvalEnd?.value || '';

    docTypes.forEach(docType => {
        const dtTargetStart = document.getElementById(`create-folder-${docType}-target-start`);
        const dtTargetEnd = document.getElementById(`create-folder-${docType}-target-end`);
        const dtEvalStart = document.getElementById(`create-folder-${docType}-eval-start`);
        const dtEvalEnd = document.getElementById(`create-folder-${docType}-eval-end`);

        if (dtTargetStart) dtTargetStart.value = tStart;
        if (dtTargetEnd) dtTargetEnd.value = tEnd;
        if (dtEvalStart) dtEvalStart.value = eStart;
        if (dtEvalEnd) dtEvalEnd.value = eEnd;

        handleDateConstraints(docType);
    });
}

// Update the timeline preview and duration badges
function updateGeneralTimelinePreview() {
    if (!genTargetStart || !genTargetEnd || !genEvalStart || !genEvalEnd) return;

    const tDays = getDaysBetween(genTargetStart.value, genTargetEnd.value);
    const eDays = getDaysBetween(genEvalStart.value, genEvalEnd.value);

    if (genTargetDuration) {
        genTargetDuration.innerText = tDays ? `${tDays} Days Window` : 'Set Dates';
    }
    if (genEvalDuration) {
        genEvalDuration.innerText = eDays ? `${eDays} Days Window` : 'Set Dates';
    }

    if (timelineTargetDates) {
        timelineTargetDates.innerText = formatPrettyDateRange(genTargetStart.value, genTargetEnd.value);
    }
    if (timelineEvalDates) {
        timelineEvalDates.innerText = formatPrettyDateRange(genEvalStart.value, genEvalEnd.value);
    }
    if (timelineSummary) {
        timelineSummary.innerText = (tDays && eDays) 
            ? `${tDays + eDays} Total Calendar Days` 
            : 'Timeline Synchronized';
    }
}

// Constraint validation for General Dates
function handleGeneralDateConstraints() {
    if (!genTargetStart || !genTargetEnd || !genEvalStart || !genEvalEnd) return;

    if (genTargetStart.value) {
        genTargetEnd.min = genTargetStart.value;
        if (genTargetEnd.value && genTargetEnd.value < genTargetStart.value) {
            genTargetEnd.value = genTargetStart.value;
        }
    }

    if (genTargetEnd.value) {
        genEvalStart.min = genTargetEnd.value;
        if (genEvalStart.value && genEvalStart.value < genTargetEnd.value) {
            genEvalStart.value = genTargetEnd.value;
        }
    }

    if (genEvalStart.value) {
        genEvalEnd.min = genEvalStart.value;
        if (genEvalEnd.value && genEvalEnd.value < genEvalStart.value) {
            genEvalEnd.value = genEvalStart.value;
        }
    }

    updateGeneralTimelinePreview();

    // If currently in general mode, mirror to all docTypes automatically
    if (scheduleModeInput?.value === 'general') {
        syncGeneralToAllDocTypes();
    }
}

// Validation logic for each doctype: End Date cannot be before Start Date
function handleDateConstraints(docType) {
    const targetStart = document.getElementById(`create-folder-${docType}-target-start`);
    const targetEnd = document.getElementById(`create-folder-${docType}-target-end`);
    const dateStart = document.getElementById(`create-folder-${docType}-eval-start`);
    const dateEnd = document.getElementById(`create-folder-${docType}-eval-end`);

    if (targetStart?.value) {
        targetEnd.min = targetStart.value;
        if (targetEnd.value && targetEnd.value < targetStart.value) {
            targetEnd.value = targetStart.value;
        }
    }
    
    if (dateStart?.value) {
        dateEnd.min = dateStart.value; 
        if (dateEnd.value && dateEnd.value < dateStart.value) {
            dateEnd.value = dateStart.value; 
        }
    }
    
    if (targetEnd?.value) {
        dateStart.min = targetEnd.value;
        if (dateStart.value && dateStart.value < targetEnd.value) {
            dateStart.value = targetEnd.value;
        }
    }
}

// Switch between 'general' and 'staggered' mode
function setScheduleMode(mode) {
    if (!scheduleModeInput) return;
    scheduleModeInput.value = mode;

    const activeClasses = ['bg-white', 'dark:bg-zinc-800', 'text-emerald-700', 'dark:text-emerald-400', 'shadow-xs'];
    const inactiveClasses = ['text-text-muted', 'hover:text-text'];

    if (mode === 'general') {
        modeGeneralBtn?.classList.remove(...inactiveClasses);
        modeGeneralBtn?.classList.add(...activeClasses);
        modeStaggeredBtn?.classList.remove(...activeClasses);
        modeStaggeredBtn?.classList.add(...inactiveClasses);

        generalPanel?.classList.remove('hidden');
        staggeredPanel?.classList.add('hidden');

        syncGeneralToAllDocTypes();
    } else {
        modeStaggeredBtn?.classList.remove(...inactiveClasses);
        modeStaggeredBtn?.classList.add(...activeClasses);
        modeGeneralBtn?.classList.remove(...activeClasses);
        modeGeneralBtn?.classList.add(...inactiveClasses);

        generalPanel?.classList.add('hidden');
        staggeredPanel?.classList.remove('hidden');
    }
}

if (modeGeneralBtn) {
    modeGeneralBtn.addEventListener('click', () => setScheduleMode('general'));
}
if (modeStaggeredBtn) {
    modeStaggeredBtn.addEventListener('click', () => setScheduleMode('staggered'));
}
if (syncAllBtn) {
    syncAllBtn.addEventListener('click', () => {
        syncGeneralToAllDocTypes();
        if (typeof window.showToast === 'function') {
            window.showToast('General dates copied to all paper tiers.', 'success');
        }
    });
}

// General inputs event listeners
[genTargetStart, genTargetEnd, genEvalStart, genEvalEnd].forEach(input => {
    if (input) {
        input.addEventListener('input', handleGeneralDateConstraints);
        input.addEventListener('change', handleGeneralDateConstraints);
    }
});

// Individual inputs event listeners
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
        if (titleInput) titleInput.value = '';

        // Default: Target Setting = Today to Today + 14 days; Evaluation = Today + 14 days to Today + 28 days
        const now = new Date();
        now.setHours(8, 0, 0, 0);

        const targetEnd = new Date(now);
        targetEnd.setDate(targetEnd.getDate() + 14);
        targetEnd.setHours(23, 59, 0, 0);

        const evalStart = new Date(targetEnd);

        const evalEnd = new Date(evalStart);
        evalEnd.setDate(evalEnd.getDate() + 14);
        evalEnd.setHours(23, 59, 0, 0);

        if (genTargetStart) genTargetStart.value = formatForDateTimeLocal(now);
        if (genTargetEnd) genTargetEnd.value = formatForDateTimeLocal(targetEnd);
        if (genEvalStart) genEvalStart.value = formatForDateTimeLocal(evalStart);
        if (genEvalEnd) genEvalEnd.value = formatForDateTimeLocal(evalEnd);

        setScheduleMode('general');
        handleGeneralDateConstraints();
        syncGeneralToAllDocTypes();

        folderModal.open();
    });
}

const formCreateFolder = document.getElementById('form-create-folder');
if (formCreateFolder) {
    formCreateFolder.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isCreatingFolder) return;

        // Ensure docTypes fields are in sync with general dates if in general mode
        if (scheduleModeInput?.value === 'general') {
            syncGeneralToAllDocTypes();
        }

        const tokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
        const tokenHash = document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content') || '';

        const csrfInput = e.target.querySelector(`input[name="${tokenName}"]`);
        if (csrfInput && tokenHash) {
            csrfInput.value = tokenHash;
        }

        const formData = new FormData(e.target);
        if (tokenName && tokenHash && (!formData.has(tokenName) || !formData.get(tokenName))) {
            formData.set(tokenName, tokenHash);
        }

        isCreatingFolder = true;
        submitBtn.innerText = 'Creating Folder...';

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
            onError: (errorMsg) => {
                isCreatingFolder = false;
                submitBtn.innerText = 'Create Folder';
                if (typeof window.showToast === 'function') {
                    window.showToast(errorMsg || 'Failed to create folder. Please check inputs and try again.', 'danger');
                }
            }
        });
    });
}

const btnCloseCreate = document.getElementById('btn-close-create-folder');
if (btnCloseCreate) {
    btnCloseCreate.addEventListener('click', () => folderModal.close());
}

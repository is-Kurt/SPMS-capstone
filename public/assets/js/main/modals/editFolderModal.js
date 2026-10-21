import Modal from './Modal.js';

const folderModal = new Modal('edit-folder-modal');
let isSavingFolder = false;

const submitBtn = document.getElementById('btn-submit-edit-folder');
const idInput = document.getElementById('edit-folder-id');
const titleInput = document.getElementById('edit-folder-title');

const docTypes = ['ipcr', 'cdpcr', 'dpcr', 'opcr', 'iperf'];
const inputs = {};

docTypes.forEach(type => {
    inputs[type] = {
        targetStart: document.getElementById(`edit-folder-${type}-target-start`),
        targetEnd: document.getElementById(`edit-folder-${type}-target-end`),
        evalStart: document.getElementById(`edit-folder-${type}-eval-start`),
        evalEnd: document.getElementById(`edit-folder-${type}-eval-end`),
    };
});

// Mode Elements
const modeGeneralBtn = document.getElementById('edit-mode-general-btn');
const modeStaggeredBtn = document.getElementById('edit-mode-staggered-btn');
const scheduleModeInput = document.getElementById('edit-schedule-mode');
const generalPanel = document.getElementById('edit-general-schedule-panel');
const staggeredPanel = document.getElementById('edit-staggered-schedule-panel');
const syncAllBtn = document.getElementById('edit-sync-all-btn');

// General Input Elements
const genTargetStart = document.getElementById('edit-general-target-start');
const genTargetEnd = document.getElementById('edit-general-target-end');
const genEvalStart = document.getElementById('edit-general-eval-start');
const genEvalEnd = document.getElementById('edit-general-eval-end');

const genTargetDuration = document.getElementById('edit-gen-target-duration');
const genEvalDuration = document.getElementById('edit-gen-eval-duration');
const timelineTargetDates = document.getElementById('edit-timeline-target-dates');
const timelineEvalDates = document.getElementById('edit-timeline-eval-dates');
const timelineSummary = document.getElementById('edit-timeline-summary');

// Helper to format ISO date strings for timeline preview
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

    docTypes.forEach(type => {
        if (inputs[type].targetStart) inputs[type].targetStart.value = tStart;
        if (inputs[type].targetEnd) inputs[type].targetEnd.value = tEnd;
        if (inputs[type].evalStart) inputs[type].evalStart.value = eStart;
        if (inputs[type].evalEnd) inputs[type].evalEnd.value = eEnd;

        handleDateConstraints(type);
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
docTypes.forEach(type => {
    Object.values(inputs[type]).forEach(input => {
        if (input) {
            input.addEventListener('input', () => handleDateConstraints(type));
        }
    });
});

window.openEditFolderModal = function(idOrData, maybeTitle, maybeDates = {}) {
    let id = idOrData;
    let title = maybeTitle;
    let dates = maybeDates;

    if (typeof idOrData === 'object' && idOrData !== null) {
        id = idOrData.id;
        title = idOrData.title || '';
        dates = idOrData;
    }

    if (idInput) idInput.value = id || '';
    if (titleInput) titleInput.value = title || '';

    const approvingNameInput = document.getElementById('edit-folder-approving-name');
    const approvingPosInput = document.getElementById('edit-folder-approving-position');
    if (approvingNameInput) approvingNameInput.value = dates.approving_authority_name || '';
    if (approvingPosInput) approvingPosInput.value = dates.approving_authority_position || '';

    // Populate individual docType inputs
    docTypes.forEach(type => {
        ['target_start', 'target_end', 'eval_start', 'eval_end'].forEach(phase => {
            let rawDate = dates[`${type}_${phase}`];
            if (!rawDate && type === 'cdpcr' && dates[`dpcr_${phase}`]) {
                rawDate = dates[`dpcr_${phase}`];
            }
            const safeDate = rawDate ? String(rawDate).replace(' ', 'T').slice(0, 16).replace('T24:00', 'T23:59') : '';
            const propName = phase.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
            const inputField = inputs[type]?.[propName];
            if (inputField) {
                inputField.value = safeDate;
            }
        });
        handleDateConstraints(type);
    });

    // Detect if this cycle has unified or staggered dates
    const safeFormat = (val) => val ? String(val).replace(' ', 'T').slice(0, 16) : '';
    const norm = (key) => safeFormat(dates[key]);

    const targetStarts = [norm('opcr_target_start'), norm('dpcr_target_start'), norm('ipcr_target_start'), norm('iperf_target_start')].filter(Boolean);
    const targetEnds   = [norm('opcr_target_end'), norm('dpcr_target_end'), norm('ipcr_target_end'), norm('iperf_target_end')].filter(Boolean);
    const evalStarts   = [norm('opcr_eval_start'), norm('dpcr_eval_start'), norm('ipcr_eval_start'), norm('iperf_eval_start')].filter(Boolean);
    const evalEnds     = [norm('opcr_eval_end'), norm('dpcr_eval_end'), norm('ipcr_eval_end'), norm('iperf_eval_end')].filter(Boolean);

    const isAllSame = (arr) => arr.length === 0 || arr.every(item => item === arr[0]);
    const isUnified = isAllSame(targetStarts) && isAllSame(targetEnds) && isAllSame(evalStarts) && isAllSame(evalEnds);

    // Resolve general dates from IPCR or first available non-empty date
    const defTStart = norm('ipcr_target_start') || targetStarts[0] || '';
    const defTEnd   = norm('ipcr_target_end')   || targetEnds[0]   || '';
    const defEStart = norm('ipcr_eval_start')   || evalStarts[0]   || '';
    const defEEnd   = norm('ipcr_eval_end')     || evalEnds[0]     || '';

    if (genTargetStart) genTargetStart.value = defTStart;
    if (genTargetEnd)   genTargetEnd.value   = defTEnd;
    if (genEvalStart)   genEvalStart.value   = defEStart;
    if (genEvalEnd)     genEvalEnd.value     = defEEnd;

    handleGeneralDateConstraints();

    if (isUnified) {
        setScheduleMode('general');
    } else {
        setScheduleMode('staggered');
    }

    folderModal.open();
};

const formEditFolder = document.getElementById('form-edit-folder');
if (formEditFolder) {
    formEditFolder.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isSavingFolder) return;

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

        isSavingFolder = true;
        submitBtn.innerText = 'Saving...';

        apiPost('/folder/update', formData, {
            onSuccess: () => {
                isSavingFolder = false;
                folderModal.close();
                window.location.reload();
            },
            onError: (errorMsg) => {
                isSavingFolder = false;
                submitBtn.innerText = 'Save Changes';
                if (typeof window.showToast === 'function') {
                    window.showToast(errorMsg || 'Failed to update folder. Please try again.', 'danger');
                }
            }
        });
    });
}

document.getElementById('btn-close-edit-folder')?.addEventListener('click', () => folderModal.close());

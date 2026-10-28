import Modal from './Modal.js';

const folderModal = new Modal('edit-folder-modal');
let isSavingFolder = false;

const submitBtn = document.getElementById('btn-submit-edit-folder');
const idInput = document.getElementById('edit-folder-id');
const titleInput = document.getElementById('edit-folder-title');

// Rating period elements
const ratingPeriodStart = document.getElementById('edit-rating-period-start');
const ratingPeriodEnd = document.getElementById('edit-rating-period-end');
const ratingPeriodPreview = document.getElementById('edit-rating-period-preview');
const cyclePeriodInput = document.getElementById('edit-cycle-rating-period');

const docTypes = ['opcr', 'dpcr', 'ipcr', 'iperf'];

// Mode Elements
const modeGeneralBtn = document.getElementById('edit-mode-general-btn');
const modeStaggeredBtn = document.getElementById('edit-mode-staggered-btn');
const scheduleModeInput = document.getElementById('edit-schedule-mode');
const generalPanel = document.getElementById('edit-general-schedule-panel');
const staggeredPanel = document.getElementById('edit-staggered-schedule-panel');
const syncAllBtn = document.getElementById('edit-sync-all-btn');

// Unified Start Dates (On Top)
const genTargetStart = document.getElementById('edit-general-target-start');
const genEvalStart = document.getElementById('edit-general-eval-start');

// General Deadlines (Panel 1)
const genTargetEnd = document.getElementById('edit-general-target-end');
const genEvalEnd = document.getElementById('edit-general-eval-end');

const genTargetDuration = document.getElementById('edit-gen-target-duration');
const genEvalDuration = document.getElementById('edit-gen-eval-duration');

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Helper to format rating period string from YYYY-MM-DD
function formatRatingPeriodFromDates(startVal, endVal) {
    if (!startVal) return '';
    const [sY, sM] = startVal.split('-').map(Number);
    const startMonth = monthNames[sM - 1];

    if (!endVal) {
        return `${startMonth} ${sY}`;
    }

    const [eY, eM] = endVal.split('-').map(Number);
    const endMonth = monthNames[eM - 1];

    if (sY === eY) {
        if (sM === eM) {
            return `${startMonth} ${sY}`;
        }
        return `${startMonth} – ${endMonth} ${sY}`;
    }
    return `${startMonth} ${sY} – ${endMonth} ${eY}`;
}

// Parse existing rating period string into start and end dates
function parseRatingPeriodStringToDates(periodStr) {
    if (!periodStr || typeof periodStr !== 'string') return null;

    // Direct ISO date range: YYYY-MM-DD to YYYY-MM-DD
    const isoMatch = periodStr.match(/^(\d{4}-\d{2}-\d{2})\s*(?:[–\-—]|to)\s*(\d{4}-\d{2}-\d{2})$/i);
    if (isoMatch) {
        return { start: isoMatch[1], end: isoMatch[2] };
    }

    const monthLookup = {
        january: '01', jan: '01',
        february: '02', feb: '02',
        march: '03', mar: '03',
        april: '04', apr: '04',
        may: '05',
        june: '06', jun: '06',
        july: '07', jul: '07',
        august: '08', aug: '08',
        september: '09', sep: '09', sept: '09',
        october: '10', oct: '10',
        november: '11', nov: '11',
        december: '12', dec: '12',
    };

    // Month [Year] – Month Year (e.g., "July – December 2026" or "November 2026 – April 2027")
    const rangeMatch = periodStr.match(/([a-zA-Z]+)(?:\s+(\d{4}))?\s*(?:[–\-—]|to)\s*([a-zA-Z]+)\s+(\d{4})/i);
    if (rangeMatch) {
        const m1 = monthLookup[rangeMatch[1].toLowerCase()];
        const y1 = rangeMatch[2] || rangeMatch[4];
        const m2 = monthLookup[rangeMatch[3].toLowerCase()];
        const y2 = rangeMatch[4];

        if (m1 && m2 && y1 && y2) {
            const lastDay = new Date(Number(y2), Number(m2), 0).getDate();
            return {
                start: `${y1}-${m1}-01`,
                end: `${y2}-${m2}-${String(lastDay).padStart(2, '0')}`
            };
        }
    }

    // Single Month Year (e.g., "October 2026")
    const singleMatch = periodStr.match(/([a-zA-Z]+)\s+(\d{4})/i);
    if (singleMatch) {
        const m = monthLookup[singleMatch[1].toLowerCase()];
        const y = singleMatch[2];
        if (m && y) {
            const lastDay = new Date(Number(y), Number(m), 0).getDate();
            return {
                start: `${y}-${m}-01`,
                end: `${y}-${m}-${String(lastDay).padStart(2, '0')}`
            };
        }
    }

    return null;
}

// Update rating period hidden input and preview chip
function updateRatingPeriodUI() {
    if (!ratingPeriodStart || !ratingPeriodEnd) return;

    if (ratingPeriodStart.value) {
        ratingPeriodEnd.min = ratingPeriodStart.value;
        if (ratingPeriodEnd.value && ratingPeriodEnd.value < ratingPeriodStart.value) {
            ratingPeriodEnd.value = ratingPeriodStart.value;
        }
    }

    const formattedPeriod = formatRatingPeriodFromDates(ratingPeriodStart.value, ratingPeriodEnd.value);

    if (cyclePeriodInput) {
        cyclePeriodInput.value = formattedPeriod;
    }

    if (ratingPeriodPreview) {
        ratingPeriodPreview.innerText = formattedPeriod || 'Select coverage dates';
    }
}

if (ratingPeriodStart) {
    ratingPeriodStart.addEventListener('input', updateRatingPeriodUI);
    ratingPeriodStart.addEventListener('change', updateRatingPeriodUI);
}
if (ratingPeriodEnd) {
    ratingPeriodEnd.addEventListener('input', updateRatingPeriodUI);
    ratingPeriodEnd.addEventListener('change', updateRatingPeriodUI);
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

// Synchronize general deadlines across all staggered doc type tiers
function syncGeneralToAllDocTypes() {
    if (!genTargetEnd || !genEvalEnd) return;
    const tEnd = genTargetEnd.value || '';
    const eEnd = genEvalEnd.value || '';

    docTypes.forEach(docType => {
        const dtTargetEnd = document.getElementById(`edit-folder-${docType}-target-end`);
        const dtEvalEnd = document.getElementById(`edit-folder-${docType}-eval-end`);

        if (dtTargetEnd) dtTargetEnd.value = tEnd;
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
}

// Constraint validation for General Dates
function handleGeneralDateConstraints() {
    if (!genTargetStart || !genTargetEnd || !genEvalStart || !genEvalEnd) return;

    if (genTargetStart.value) {
        genTargetEnd.min = genTargetStart.value;
        if (genTargetEnd.value && genTargetEnd.value < genTargetStart.value) {
            genTargetEnd.value = genTargetStart.value;
        }

        docTypes.forEach(docType => {
            const dtTargetEnd = document.getElementById(`edit-folder-${docType}-target-end`);
            if (dtTargetEnd) {
                dtTargetEnd.min = genTargetStart.value;
                if (dtTargetEnd.value && dtTargetEnd.value < genTargetStart.value) {
                    dtTargetEnd.value = genTargetStart.value;
                }
            }
        });
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

        docTypes.forEach(docType => {
            const dtEvalEnd = document.getElementById(`edit-folder-${docType}-eval-end`);
            if (dtEvalEnd) {
                dtEvalEnd.min = genEvalStart.value;
                if (dtEvalEnd.value && dtEvalEnd.value < genEvalStart.value) {
                    dtEvalEnd.value = genEvalStart.value;
                }
            }
        });
    }

    updateGeneralTimelinePreview();

    // If currently in general mode, mirror deadlines to all docTypes automatically
    if (scheduleModeInput?.value === 'general') {
        syncGeneralToAllDocTypes();
    }
}

function handleDateConstraints(docType) {
    const targetEnd = document.getElementById(`edit-folder-${docType}-target-end`);
    const evalEnd = document.getElementById(`edit-folder-${docType}-eval-end`);

    if (genTargetStart?.value && targetEnd) {
        targetEnd.min = genTargetStart.value;
        if (targetEnd.value && targetEnd.value < genTargetStart.value) {
            targetEnd.value = genTargetStart.value;
        }
    }

    if (genEvalStart?.value && evalEnd) {
        evalEnd.min = genEvalStart.value;
        if (evalEnd.value && evalEnd.value < genEvalStart.value) {
            evalEnd.value = genEvalStart.value;
        }
    }
}

// Switch between 'general' and 'staggered' mode
function setScheduleMode(mode) {
    if (!scheduleModeInput) return;
    scheduleModeInput.value = mode;

    const allToggleClasses = [
        'bg-white', 'bg-zinc-100', 'dark:bg-zinc-800',
        'text-emerald-700', 'dark:text-emerald-400', 'shadow-xs',
        'bg-transparent', 'dark:bg-transparent', 'shadow-none',
        'text-text-muted', 'hover:text-text'
    ];
    const activeClasses = ['bg-white', 'dark:bg-zinc-800', 'text-emerald-700', 'dark:text-emerald-400', 'shadow-xs'];
    const inactiveClasses = ['text-text-muted', 'hover:text-text', 'bg-transparent', 'dark:bg-transparent', 'shadow-none'];

    modeGeneralBtn?.classList.remove(...allToggleClasses);
    modeStaggeredBtn?.classList.remove(...allToggleClasses);

    if (mode === 'general') {
        modeGeneralBtn?.classList.add(...activeClasses);
        modeStaggeredBtn?.classList.add(...inactiveClasses);

        generalPanel?.classList.remove('hidden');
        staggeredPanel?.classList.add('hidden');

        syncGeneralToAllDocTypes();
    } else {
        modeStaggeredBtn?.classList.add(...activeClasses);
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
            window.showToast('All dates synced across all form tiers.', 'success');
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

// Individual staggered inputs event listeners
docTypes.forEach(docType => {
    const targetEnd = document.getElementById(`edit-folder-${docType}-target-end`);
    const evalEnd = document.getElementById(`edit-folder-${docType}-eval-end`);

    if (targetEnd) {
        targetEnd.addEventListener('input', () => handleDateConstraints(docType));
        targetEnd.addEventListener('change', () => handleDateConstraints(docType));
    }
    if (evalEnd) {
        evalEnd.addEventListener('input', () => handleDateConstraints(docType));
        evalEnd.addEventListener('change', () => handleDateConstraints(docType));
    }
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

    // Parse Rating Period covered
    const ratingPeriodStr = dates.rating_period || '';
    const parsedRange = parseRatingPeriodStringToDates(ratingPeriodStr);
    if (parsedRange) {
        if (ratingPeriodStart) ratingPeriodStart.value = parsedRange.start;
        if (ratingPeriodEnd) ratingPeriodEnd.value = parsedRange.end;
        updateRatingPeriodUI();
    } else {
        if (cyclePeriodInput) cyclePeriodInput.value = ratingPeriodStr;
        if (ratingPeriodPreview) ratingPeriodPreview.innerText = ratingPeriodStr || 'Not Configured';
    }

    const approvingNameInput = document.getElementById('edit-folder-approving-name');
    const approvingPosInput = document.getElementById('edit-folder-approving-position');
    if (approvingNameInput) approvingNameInput.value = dates.approving_authority_name || '';
    if (approvingPosInput) approvingPosInput.value = dates.approving_authority_position || '';

    // Date formatter helper
    const safeDate = (raw) => raw ? String(raw).replace(' ', 'T').slice(0, 16).replace('T24:00', 'T23:59') : '';

    // Populate staggered end date inputs
    docTypes.forEach(type => {
        const dtTargetEnd = document.getElementById(`edit-folder-${type}-target-end`);
        const dtEvalEnd = document.getElementById(`edit-folder-${type}-eval-end`);

        if (dtTargetEnd) dtTargetEnd.value = safeDate(dates[`${type}_target_end`]);
        if (dtEvalEnd) dtEvalEnd.value = safeDate(dates[`${type}_eval_end`]);

        handleDateConstraints(type);
    });

    // Populate unified opening dates (on top)
    const defTStart = dates.general_target_start || dates.opcr_target_start || dates.ipcr_target_start || '';
    const defEStart = dates.general_eval_start || dates.opcr_eval_start || dates.ipcr_eval_start || '';
    if (genTargetStart) genTargetStart.value = safeDate(defTStart);
    if (genEvalStart) genEvalStart.value = safeDate(defEStart);

    // Populate general deadlines
    const defTEnd = dates.general_target_end || dates.opcr_target_end || dates.ipcr_target_end || '';
    const defEEnd = dates.general_eval_end || dates.opcr_eval_end || dates.ipcr_eval_end || '';
    if (genTargetEnd) genTargetEnd.value = safeDate(defTEnd);
    if (genEvalEnd) genEvalEnd.value = safeDate(defEEnd);

    // Determine if cycle was configured in unified or staggered mode
    const targetEnds = docTypes.map(t => safeDate(dates[`${t}_target_end`])).filter(Boolean);
    const evalEnds = docTypes.map(t => safeDate(dates[`${t}_eval_end`])).filter(Boolean);

    const isAllSame = (arr) => arr.length === 0 || arr.every(item => item === arr[0]);
    const isUnified = isAllSame(targetEnds) && isAllSame(evalEnds);

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

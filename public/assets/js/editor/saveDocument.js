let savePromise = null;
let submitting = false;
let saveStatusTimeout = null; 

function saveDocument(manualSave = true) {
    if (savePromise && !isClosing) {
        console.log("Save already in progress. Waiting for it to finish...");
        return savePromise;
    }

    savePromise = new Promise((resolve, reject) => {
        const editor = tinymce.get('editable-doc');
        if (!editor) return resolve(); // Fail gracefully if editor is not loaded

        if (typeof tabs !== 'undefined' && typeof activeTabId !== 'undefined') {
            const activeTab = tabs.find(t => t.id === activeTabId);
            if (activeTab) {
                activeTab.content = editor.getContent();
            }
        }

        const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled Document';

        const saveStatus = document.getElementById('save-status');
        if (saveStatus && manualSave) {
            if (saveStatusTimeout) {
                clearTimeout(saveStatusTimeout);
                saveStatusTimeout = null;
            }
            saveStatus.innerText = '● Saving...';
            saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-info-500 animate-pulse';
        }

        const formData = new FormData();
        const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
        
        formData.append('id', docId);
        if (typeof tabs !== 'undefined' && tabs.length > 0) {
            formData.append('tabs', JSON.stringify(tabs));
        } else if (editor) {
            formData.append('content', editor.getContent());
        }
        
        formData.append('title', title);
        formData.append('is_rating_mode', AppConfig.isRatingMode);
        formData.append('_method', 'PATCH');

        apiPost('/document', formData, {
            onSuccess: (data) => { 
                AppState.setDirty(false); 

                if (saveStatus && manualSave) {
                    saveStatus.innerText = '✓ Saved';
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-success-500';

                    saveStatusTimeout = setTimeout(() => {
                        saveStatus.innerText = '';
                        saveStatus.className = '';
                        saveStatusTimeout = null;
                    }, 2000);
                }
                resolve(data);
            },
            onError: (errorMessage) => {
                if (saveStatus && manualSave) {
                    saveStatus.innerText = '✗ Save Failed';
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-danger-500';
                }
                reject(new Error(errorMessage));
            },
            config: { 
                keepalive: true 
            }
        });
    }).finally(() => {
        savePromise = null;
    });

    return savePromise;
}

function autoSave() {
    setTimeout(async () => {
        if (AppState.isDirty && !savePromise) {
            console.log("Auto-saving in the background...");
            try {
                await saveDocument(false);
            } catch (error) {
                console.warn("Auto-save skipped this cycle due to an error.");
            }
        }
        autoSave();
    }, 2000);
}

// Keyboard shortcut (Ctrl+S / Cmd+S)
window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveDocument();
    }
});

// Save before leaving the page
let isClosing = false;
window.addEventListener('beforeunload', () => {
    isClosing = true;
    if (AppState.isDirty) {
        saveDocument(false);
    }
});

async function saveWith({before, after}) {
    let result = null;

    // 1. Run the `rate()` calculation & validation first (awaited: rate() may show
    // a blocking appAlert() on validation failure before resolving)
    if (before) result = await before();

    // Halt immediately if a user left a cell blank or typed an invalid number
    if (result === 'hasError') return;

    try {
        // 2. Save the document (This grabs the newly calculated totals from TinyMCE!)
        if (AppState.isDirty || savePromise === null) {
            await saveDocument();
        }
        // 3. Finally, trigger the POST request to Submit or Rate
        if (after) after();
    } catch (error) {
        console.error("Submission halted: Could not save the latest changes.", error);
    }
}



async function rate() {
    let hasError = false;

    const editor = tinymce.get('editable-doc');
    if (!editor) return 'hasError';

    const body = editor.getBody();
    const ratingCells = body.querySelectorAll('.calc-rating');

    // 1. Validate every single rating box
    ratingCells.forEach(cell => {
        const value = cell.innerText.trim();
        const num = parseFloat(value);

        const parentTable = cell.closest('table');
        const scoreRange = parentTable ? parseFloat(parentTable.getAttribute('data-score-range')) : null;

        const isInvalid = value === '' ||
                          isNaN(num) ||
                          num < 0 ||
                          (scoreRange !== null && num > scoreRange);

        if (isInvalid) {
            cell.style.backgroundColor = 'rgba(220, 38, 38, 0.35)'; // Red Error Box
            hasError = true;
        } else {
            cell.style.backgroundColor = 'rgba(16, 185, 129, 0.25)'; // Green Success Box
        }
    });

    if (hasError) {
        console.warn('Validation failed: check highlighted cells.');
        await window.appAlert('Please fill in every rating cell with a valid score before continuing.', { title: 'Missing or Invalid Scores', variant: 'danger' });
        return 'hasError';
    }

    // 2. If validation passes, run the master math function!
    if (typeof calculateAllTables === 'function') {
        calculateAllTables();
    }

    // 3. Mark the document as dirty so `saveWith` knows it needs to save the new green boxes/math!
    AppState.setDirty(true);
}
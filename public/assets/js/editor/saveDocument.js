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
        if (!editor) return resolve(); // Fail gracefully if editor isn't loaded

        const content = editor.getContent();
        const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled Document';

        const saveStatus = document.getElementById('save-status');
        if (saveStatus && manualSave) {
            if (saveStatusTimeout) {
                clearTimeout(saveStatusTimeout);
                saveStatusTimeout = null;
            }
            saveStatus.innerText = '● Saving...';
            saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-blue-500 animate-pulse';
        }

        const formData = new FormData();
        // Fallback to the ID in the URL if AppConfig is somehow missing it
        const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
        
        formData.append('id', docId);
        formData.append('content', content);
        formData.append('title', title);
        formData.append('is_rating_mode', AppConfig.isRatingMode);
        formData.append('_method', 'PATCH'); 
        
        const dateStart = document.getElementById('doc-date-start');
        const dateEnd = document.getElementById('doc-date-end');

        if (dateStart && dateEnd) {
            formData.append('doc_date_start', dateStart.value);
            formData.append('doc_date_end', dateEnd.value);
        }

        apiPost('document', formData, {
            onSuccess: (data) => { 
                AppState.setDirty(false); 

                if (saveStatus && manualSave) {
                    saveStatus.innerText = '✓ Saved';
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-500';

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
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-red-500';
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

    // 1. Run the `rate()` calculation & validation first
    if (before) result = before();
    
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

function toggleSubmit(action) {
    if (submitting) return;

    const btn = document.getElementById('btn-submit');
    const originalText = btn.innerText;
    
    // Update button to show loading state
    btn.innerText = action === 'submit' ? 'Submitting...' : 'Unsubmitting...';
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    
    const formData = new FormData();
    const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
    
    formData.append('doc_id', docId);
    formData.append('action', action); // 'submit' or 'unsubmit'

    submitting = true;
    
    apiPost(`${AppConfig.baseUrl}/submit`, formData, {
        onSuccess: () => {
            // Reload the page to reflect the new Status and swap the button UI
            window.location.reload(); 
        },
        onError: () => {
            // Revert button if something goes wrong
            submitting = false; 
            btn.innerText = originalText;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    });
}

function evaluateDocument() {
    if (submitting) return;

    // 1. Pull the final calculated score directly out of the TinyMCE body attribute
    const editorBody = tinymce.get('editable-doc').getBody();
    const finalScore = editorBody.getAttribute('data-final-score');

    if (!finalScore || isNaN(parseFloat(finalScore))) {
        alert("Could not extract a valid final score. Please ensure the tables are filled out correctly.");
        return;
    }

    const btn = document.getElementById('btn-submit');
    const originalText = btn.innerText;
    
    btn.innerText = 'Evaluating...';
    btn.classList.add('opacity-75', 'cursor-not-allowed');

    // 2. Bundle the score and send it to the backend controller
    const formData = new FormData();
    const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
    
    formData.append('doc_id', docId);
    formData.append('final_rating', finalScore);

    submitting = true;
    
    apiPost(`${AppConfig.baseUrl}/evaluate`, formData, {
        onSuccess: () => {
            // Instantly reload to show the green "Evaluated ✓" button
            window.location.reload(); 
        },
        onError: () => {
            submitting = false; 
            btn.innerText = originalText;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    });
}

function getEditorData() {
    const editor = tinymce.get('editable-doc');
    if (!editor) return {};

    const body = editor.getBody();

    return {
        // Grab the grand total from the DOM where calculateAllTables() stored it
        final_rating: body.getAttribute('data-final-score') || 0
    };
}

function rate() {
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

        const validationMsg = document.getElementById('validation-msg');

        if (isInvalid) {
            cell.style.backgroundColor = 'rgba(220, 38, 38, 0.35)'; // Red Error Box
            if(validationMsg) validationMsg.classList.remove('hidden');
            hasError = true;
        } else {
            cell.style.backgroundColor = 'rgba(16, 185, 129, 0.25)'; // Green Success Box
        }
    });

    if (hasError) {
        console.warn('Validation failed: check highlighted cells.');
        return 'hasError';
    }

    // 2. If validation passes, run the master math function!
    if (typeof calculateAllTables === 'function') {
        calculateAllTables();
    }
    
    // 3. Mark the document as dirty so `saveWith` knows it needs to save the new green boxes/math!
    AppState.setDirty(true); 
}
let savePromise = null;
let submitting = false;
let saveStatusTimeout = null; 

async function saveDocument(manualSave = true) {
    if (savePromise && !isClosing) {
        console.log("Save already in progress. Waiting for it to finish...");
        try {
            await savePromise;
        } catch (_) {}
        if (AppState.isDirty || manualSave) {
            return saveDocument(manualSave);
        }
        return;
    }

    savePromise = new Promise((resolve, reject) => {
        const isRubricsActive = (typeof activeTabId !== 'undefined' && activeTabId === 'rubrics-tab');
        const hasTabs = (typeof tabs !== 'undefined' && Array.isArray(tabs) && tabs.length > 0);
        if (!window.isSpmsFormActive && !isRubricsActive && !hasTabs) return resolve();

        if (hasTabs) {
            const isSpmsFormActiveDoc = window.isSpmsFormActive || isRubricsActive || Boolean(document.getElementById('printable-form'));
            if (isSpmsFormActiveDoc && typeof window.syncSpmsActiveTab === 'function') {
                window.syncSpmsActiveTab();
            }
            if (typeof window.syncDigitalRubricsData === 'function') {
                window.syncDigitalRubricsData();
            }
        }

        const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled Document';

        const saveStatus = document.getElementById('save-status');
        const rubricsSaveStatus = document.getElementById('rubrics-save-status');
        const rubricsSaveBtnText = document.getElementById('btn-rubrics-save-text');

        if (manualSave) {
            if (saveStatusTimeout) {
                clearTimeout(saveStatusTimeout);
                saveStatusTimeout = null;
            }
            if (saveStatus) {
                saveStatus.innerText = '● Saving...';
                saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-info-500 animate-pulse';
            }
            if (rubricsSaveStatus) {
                rubricsSaveStatus.innerText = '● Saving...';
                rubricsSaveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-600 dark:text-emerald-400 animate-pulse';
            }
            if (rubricsSaveBtnText) {
                rubricsSaveBtnText.innerText = 'Saving...';
            }
        }

        const formData = new FormData();
        const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
        
        formData.append('id', docId);
        if (typeof tabs !== 'undefined' && tabs.length > 0) {
            formData.append('tabs', JSON.stringify(tabs));
        }
        
        formData.append('title', title);
        formData.append('is_rating_mode', AppConfig.isRatingMode);
        formData.append('_method', 'PATCH');

        const saveUrl = (typeof AppConfig !== 'undefined' && AppConfig.baseUrl) ? AppConfig.baseUrl : '/document';
        apiPost(saveUrl, formData, {
            onSuccess: (data) => { 
                AppState.setDirty(false); 

                if (manualSave) {
                    if (saveStatus) {
                        saveStatus.innerText = '✓ Saved';
                        saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-success-500';
                    }
                    if (rubricsSaveStatus) {
                        rubricsSaveStatus.innerText = '✓ Saved';
                        rubricsSaveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-600 dark:text-emerald-400';
                    }
                    if (rubricsSaveBtnText) {
                        rubricsSaveBtnText.innerText = 'Saved';
                    }

                    saveStatusTimeout = setTimeout(() => {
                        if (saveStatus) {
                            saveStatus.innerText = '';
                            saveStatus.className = '';
                        }
                        if (rubricsSaveStatus) {
                            rubricsSaveStatus.innerText = '';
                            rubricsSaveStatus.className = '';
                        }
                        if (rubricsSaveBtnText) {
                            rubricsSaveBtnText.innerText = 'Save';
                        }
                        saveStatusTimeout = null;
                    }, 2000);
                } else {
                    if (rubricsSaveStatus && rubricsSaveStatus.innerText === '') {
                        rubricsSaveStatus.innerText = '✓ Saved';
                        rubricsSaveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-600/70 dark:text-emerald-400/70';
                        setTimeout(() => {
                            if (rubricsSaveStatus.innerText === '✓ Saved') {
                                rubricsSaveStatus.innerText = '';
                                rubricsSaveStatus.className = '';
                            }
                        }, 1500);
                    }
                }
                resolve(data);
            },
            onError: (errorMessage) => {
                if (manualSave) {
                    if (saveStatus) {
                        saveStatus.innerText = '✗ Save Failed';
                        saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-danger-500';
                    }
                    if (rubricsSaveStatus) {
                        rubricsSaveStatus.innerText = '✗ Save Failed';
                        rubricsSaveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-danger-500';
                    }
                    if (rubricsSaveBtnText) {
                        rubricsSaveBtnText.innerText = 'Save';
                    }
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

let debounceAutoSaveTimer = null;

function triggerDebouncedAutoSave() {
    if (debounceAutoSaveTimer) {
        clearTimeout(debounceAutoSaveTimer);
    }
    debounceAutoSaveTimer = setTimeout(async () => {
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing) {
            try {
                await saveDocument(false);
            } catch (error) {
                console.warn("Debounced auto-save skipped:", error);
            }
        }
    }, 700);
}
window.triggerDebouncedAutoSave = triggerDebouncedAutoSave;

// Instantly save whenever the user finishes editing and blurs an input/textarea
document.addEventListener('focusout', (e) => {
    if (e.target && e.target.matches && e.target.matches('input, textarea, select')) {
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing) {
            if (debounceAutoSaveTimer) clearTimeout(debounceAutoSaveTimer);
            saveDocument(false);
        }
    }
});

function autoSave() {
    setTimeout(async () => {
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing) {
            try {
                await saveDocument(false);
            } catch (error) {
                console.warn("Auto-save skipped this cycle due to an error.");
            }
        }
        autoSave();
    }, 1500);
}

// Keyboard shortcut (Ctrl+S / Cmd+S)
window.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveDocument(true);
    }
});

// ==========================================
// UNSAVED CHANGES CUSTOM EXIT MODAL & IN-APP NAVIGATION GUARD
// ==========================================
let isClosing = false;
window.pendingExitTarget = null;
window.pendingExitHistoryBack = false;

function openUnsavedChangesModal(targetUrl = null, prefersHistoryBack = false) {
    window.pendingExitTarget = targetUrl;
    window.pendingExitHistoryBack = prefersHistoryBack;
    const modal = document.getElementById('unsaved-changes-modal');
    const card = document.getElementById('unsaved-changes-card');
    if (!modal) return;

    modal.classList.remove('hidden');
    void modal.offsetWidth;
    if (card) {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    }
}
window.openUnsavedChangesModal = openUnsavedChangesModal;

function closeUnsavedChangesModal() {
    const modal = document.getElementById('unsaved-changes-modal');
    const card = document.getElementById('unsaved-changes-card');
    if (!modal) return;

    if (card) {
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        window.pendingExitTarget = null;
        window.pendingExitHistoryBack = false;
    }, 150);
}
window.closeUnsavedChangesModal = closeUnsavedChangesModal;

async function handleSaveAndExit() {
    const btn = document.getElementById('btn-save-and-exit');
    const textEl = document.getElementById('btn-save-exit-text');
    const origText = textEl ? textEl.innerText : 'Save and Exit';

    if (btn) btn.disabled = true;
    if (textEl) textEl.innerText = 'Saving...';

    try {
        await saveDocument(true);
        if (typeof AppState !== 'undefined') AppState.setDirty(false);
        isClosing = true;

        const target = window.pendingExitTarget;
        const useBack = window.pendingExitHistoryBack;
        closeUnsavedChangesModal();

        if (useBack && window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
            window.history.back();
        } else if (target) {
            window.location.href = target;
        } else {
            window.history.back();
        }
    } catch (err) {
        console.error("Save on exit failed:", err);
        if (btn) btn.disabled = false;
        if (textEl) textEl.innerText = origText;
        if (typeof window.appAlert === 'function') {
            await window.appAlert("Could not save recent changes. Please review and try again.", { title: 'Save Failed', variant: 'danger' });
        } else {
            alert("Could not save recent changes. Please review and try again.");
        }
    }
}
window.handleSaveAndExit = handleSaveAndExit;

function handleExitWithoutSaving() {
    if (typeof AppState !== 'undefined') AppState.setDirty(false);
    isClosing = true;

    const target = window.pendingExitTarget;
    const useBack = window.pendingExitHistoryBack;
    closeUnsavedChangesModal();

    if (useBack && window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
        window.history.back();
    } else if (target) {
        window.location.href = target;
    } else {
        window.history.back();
    }
}
window.handleExitWithoutSaving = handleExitWithoutSaving;

window.handleInAppExit = function(event, targetUrl, prefersHistoryBack = false) {
    const isDirty = (typeof AppState !== 'undefined' && AppState.isDirty) || Boolean(savePromise);
    if (isDirty) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        openUnsavedChangesModal(targetUrl, prefersHistoryBack);
        return false;
    }
    if (prefersHistoryBack && window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
        if (event) event.preventDefault();
        window.history.back();
        return false;
    }
    return true;
};

// Global delegated capture listener for any in-app exit links
document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([download])');
    if (!link) return;
    // Skip if clicking inside modals or buttons
    if (link.closest('#unsaved-changes-modal')) return;

    const isDirty = (typeof AppState !== 'undefined' && AppState.isDirty) || Boolean(savePromise);
    if (isDirty && !isClosing) {
        e.preventDefault();
        e.stopPropagation();
        openUnsavedChangesModal(link.href, false);
    }
}, true);

// Browser window/tab close safety net (only triggers when closing the whole browser tab directly while unsaved)
window.addEventListener('beforeunload', (e) => {
    if (isClosing) return;
    if (typeof AppState !== 'undefined' && AppState.isDirty) {
        saveDocument(false);
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

async function saveWith({before, after}) {
    if (window.weightError) {
        if (typeof window.appAlert === 'function') {
            await window.appAlert("Formula Weights must equal 100% exactly.", { title: 'Invalid Weights', variant: 'danger' });
        }
        return 'hasError';
    }

    let result = null;

    if (before) result = await before();
    if (result === 'hasError') return;

    try {
        await saveDocument(true);
        if (after) await after();
    } catch (error) {
        console.error("Submission halted: Could not save the latest changes.", error);
        if (typeof window.appAlert === 'function') {
            await window.appAlert(error.message || "Failed to save document before proceeding. Please try again.");
        }
    }
}

function toggleSubmit(action) {
    if (submitting) return;

    const btn = document.getElementById('btn-submit');
    const originalText = btn.innerText;
    
    btn.innerText = action === 'submit' ? 'Submitting...' : 'Unsubmitting...';
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    
    const formData = new FormData();
    const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');
    
    formData.append('doc_id', docId);
    formData.append('action', action);

    submitting = true;
    
    apiPost(`${AppConfig.baseUrl}/submit`, formData, {
        onSuccess: () => {
            window.location.reload(); 
        },
        onError: () => {
            submitting = false; 
            btn.innerText = originalText;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    });
}

async function rate() {
    if (typeof recalculateForm === 'function') {
        recalculateForm();
    }
    if (typeof window.syncSpmsActiveTab === 'function') {
        window.syncSpmsActiveTab();
    }
    if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') {
        AppState.setDirty(true);
    }

    const scoreEl = document.getElementById('grand-score') || document.getElementById('iperf-overall-average');
    const badgeEl = document.getElementById('adjectival-badge') || document.getElementById('iperf-adjectival-badge');
    const score = scoreEl?.innerText?.trim() || '0.000';
    const badge = badgeEl?.innerText?.trim() || '';

    if (typeof window.showToast === 'function') {
        window.showToast(`Calculations verified: Final Rating is ${score}${badge ? ' (' + badge + ')' : ''}`, 'success');
    }
    return 'success';
}
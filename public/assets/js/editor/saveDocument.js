let savePromise = null;
let submitting = false;
let saveStatusTimeout = null; 

function documentSaveUrl() {
    return (typeof AppConfig !== 'undefined' && AppConfig.baseUrl) ? AppConfig.baseUrl : '/document';
}

/**
 * Copies the editor's current state into the tabs data and builds the save request body, or returns
 * null when there is nothing to save. Shared by saveDocument() and flushSaveOnUnload() so both send
 * exactly the same fields.
 */
function buildSaveFormData() {
    const isRubricsActive = (typeof activeTabId !== 'undefined' && activeTabId === 'rubrics-tab');
    const hasTabs = (typeof tabs !== 'undefined' && Array.isArray(tabs) && tabs.length > 0);
    if (!window.isSpmsFormActive && !isRubricsActive && !hasTabs) return null;

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
    const formData = new FormData();
    const docId = AppConfig.docId || new URLSearchParams(window.location.search).get('Id');

    formData.append('id', docId);
    if (typeof tabs !== 'undefined' && tabs.length > 0) {
        formData.append('tabs', JSON.stringify(tabs));
    }

    formData.append('title', title);
    formData.append('is_rating_mode', AppConfig.isRatingMode);
    formData.append('_method', 'PATCH');
    if (AppConfig.contentFingerprint) {
        formData.append('base_fingerprint', AppConfig.contentFingerprint);
    }
    return formData;
}

/**
 * Someone else saved this document after it was opened here (the server refused the save; see
 * Document::update). Saving is stopped for this page, since every further save would be refused too,
 * and the user is offered a reload. Choosing to stay lets them copy what they typed first; after that,
 * leaving or reloading no longer tries to save or asks again.
 */
const SAVE_CONFLICT_MESSAGE = 'Someone else saved this document after you opened it, so your latest edits were not saved.';
window.saveConflict = false;
let conflictNoticeOpen = false;
async function handleSaveConflict() {
    window.saveConflict = true;
    if (conflictNoticeOpen) return;
    conflictNoticeOpen = true;
    const text = SAVE_CONFLICT_MESSAGE + ' Reload to see their changes. If you need anything you typed, copy it before reloading.';
    const reload = (typeof window.appConfirm === 'function')
        ? await window.appConfirm(text, { title: 'Document Changed by Someone Else', confirmText: 'Reload Now', cancelText: 'Stay to Copy My Edits', variant: 'warning' })
        : window.confirm(text);
    conflictNoticeOpen = false;
    if (reload) {
        isClosing = true;
        window.location.reload();
    }
}

async function saveDocument(manualSave = true) {
    if (typeof window.isDocumentReadOnly !== 'undefined' && window.isDocumentReadOnly) {
        return;
    }
    if (typeof window.isEditable !== 'undefined' && !window.isEditable) {
        return;
    }
    if (window.saveConflict) {
        if (manualSave) handleSaveConflict();
        throw new Error(SAVE_CONFLICT_MESSAGE);
    }
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

    savePromise = new Promise(async (resolve, reject) => {
        const formData = buildSaveFormData();
        if (!formData) return resolve();

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

        apiPost(documentSaveUrl(), formData, {
            onSuccess: (data) => {
                if (data && data.fingerprint) AppConfig.contentFingerprint = data.fingerprint;
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
            onError: (errorMessage, data) => {
                if (data && data.code === 'conflict') handleSaveConflict();
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
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing && !window.saveConflict) {
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
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing && !window.saveConflict) {
            if (debounceAutoSaveTimer) clearTimeout(debounceAutoSaveTimer);
            saveDocument(false);
        }
    }
});

function autoSave() {
    setTimeout(async () => {
        if (typeof AppState !== 'undefined' && AppState.isDirty && !savePromise && !isClosing && !window.saveConflict) {
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
        if (typeof window.isDocumentReadOnly !== 'undefined' && window.isDocumentReadOnly) return;
        if (typeof window.isEditable !== 'undefined' && !window.isEditable) return;
        if (!document.getElementById('btn-save-doc')) return;
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
        navigateAway(target, useBack);
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
    navigateAway(target, useBack);
}
window.handleExitWithoutSaving = handleExitWithoutSaving;

function navigateAway(target, useBack) {
    if (useBack && window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
        window.history.back();
    } else if (target) {
        window.location.href = target;
    } else {
        window.history.back();
    }
}

/**
 * Leaving the document from inside the app (SPMS icon, any link): save pending edits first, then go.
 * The unsaved-changes modal only appears if that save fails, so the user can retry or choose to leave.
 */
let leavingDocument = false;
async function saveThenLeave(target, useBack) {
    if (leavingDocument) return;
    leavingDocument = true;
    if (window.saveConflict) {   // already told that these edits cannot be saved
        isClosing = true;
        navigateAway(target, useBack);
        return;
    }
    try {
        await saveDocument(true);
        if (typeof AppState !== 'undefined') AppState.setDirty(false);
        isClosing = true;
        navigateAway(target, useBack);
    } catch (err) {
        console.error("Save before leaving failed:", err);
        leavingDocument = false;
        openUnsavedChangesModal(target, useBack);
    }
}

window.handleInAppExit = function(event, targetUrl, prefersHistoryBack = false) {
    const isDirty = (typeof AppState !== 'undefined' && AppState.isDirty) || Boolean(savePromise);
    if (isDirty) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        saveThenLeave(targetUrl, prefersHistoryBack);
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
        saveThenLeave(link.href, false);
    }
}, true);

// Reload shortcuts (F5, Ctrl+R, Cmd+R, also with Shift): save first, then reload. Works for any document size.
window.addEventListener('keydown', async (e) => {
    const isReload = e.key === 'F5' || ((e.ctrlKey || e.metaKey) && (e.key || '').toLowerCase() === 'r');
    if (!isReload || isClosing || window.saveConflict) return;
    const isDirty = (typeof AppState !== 'undefined' && AppState.isDirty) || Boolean(savePromise);
    if (!isDirty) return;
    e.preventDefault();
    if (leavingDocument) return;
    leavingDocument = true;
    try {
        await saveDocument(true);
        isClosing = true;
        window.location.reload();
    } catch (err) {
        console.error("Save before reload failed:", err);
        leavingDocument = false;
        if (typeof window.appAlert === 'function') {
            await window.appAlert("Could not save recent changes, so the page was not reloaded. Please try again.", { title: 'Save Failed', variant: 'danger' });
        }
    }
});

/**
 * Saves pending edits while the page is being left by the browser itself (reload button, closing the tab,
 * typing a new address), where the page cannot wait for an answer. axios requests are cancelled when the
 * page unloads, so this sends the same request with fetch(keepalive), which the browser completes after
 * the page is gone. Browsers cap keepalive bodies at 64 KB: returns false for a larger document, so the
 * caller keeps the browser's own "changes may not be saved" warning instead.
 */
const KEEPALIVE_BODY_LIMIT = 60 * 1024;   // under the 64 KB cap, leaving room for the multipart framing
let unloadSaveSent = false;
function flushSaveOnUnload() {
    if (unloadSaveSent) return true;
    if (typeof window.isDocumentReadOnly !== 'undefined' && window.isDocumentReadOnly) return true;
    if (typeof window.isEditable !== 'undefined' && !window.isEditable) return true;

    const formData = buildSaveFormData();
    if (!formData) return true;

    const { name, hash } = getCsrfInfo();
    formData.set(name, hash);

    let size = 0;
    for (const [key, value] of formData.entries()) {
        size += key.length + 100 + (typeof value === 'string' ? new Blob([value]).size : value.size);
    }
    if (size > KEEPALIVE_BODY_LIMIT) return false;

    try {
        fetch(documentSaveUrl(), {
            method: 'POST',
            body: formData,
            keepalive: true,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': hash },
        });
        unloadSaveSent = true;
        return true;
    } catch (_) {
        return false;
    }
}

window.addEventListener('beforeunload', (e) => {
    if (isClosing || window.saveConflict) return;
    if (typeof AppState === 'undefined' || !AppState.isDirty) return;
    if (flushSaveOnUnload()) return;   // sent in the background: leave without asking

    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    return e.returnValue;
});

// Mobile browsers often skip beforeunload when a tab is closed or discarded; pagehide still fires.
window.addEventListener('pagehide', () => {
    if (!isClosing && !window.saveConflict && typeof AppState !== 'undefined' && AppState.isDirty) flushSaveOnUnload();
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
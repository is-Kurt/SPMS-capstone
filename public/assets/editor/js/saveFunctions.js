let savePromise = null;
let submitting = false;

function saveDocument() {
    if (savePromise) {
        console.log("Save already in progress. Waiting for it to finish...");
        return savePromise;
    }

    savePromise = (async () => {
        const content = tinymce.get('editable-doc').getContent();
        const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled1 Document';

        const saveStatus = document.getElementById('save-status');
        if (saveStatus) {
            saveStatus.innerText = '● Saving...';
            saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-blue-500 animate-pulse';
        }

        const formData = new FormData();
        formData.append('id', AppConfig.docId);
        formData.append('content', content);
        formData.append('title', title);
        formData.append('_method', 'PATCH');
        formData.append(AppConfig.csrfToken, AppConfig.csrfHash);
        
        // From documents
        const dateStart = document.getElementById('doc-date-start');
        const dateEnd = document.getElementById('doc-date-end');

        if (dateStart && dateEnd) {
            formData.append('doc_date_start', dateStart.value);
            formData.append('doc_date_end', dateEnd.value);
        }

        try {            
            const response = await axios.post(AppConfig.saveUrl, formData, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (response.data.status === 'success') {
                AppState.setDirty(false);
                AppConfig.csrfHash = response.data.csrfHash;

                if (saveStatus) {
                    saveStatus.innerText = '✓ Saved';
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-500';
                }
            } else {
                console.warn(data.message, data.errors);
            }


        } catch (error) {
            console.error('Failed at:', error);
            console.error('Response:', error.response?.status, error.response?.data);
            throw error;
        }
    })();

    savePromise.finally(() => {
        savePromise = null;
    });

    return savePromise;
}

function saveOnExit() {
    if (!AppState.isDirty) return;

    const content = tinymce.get('editable-doc').getContent();
    const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled1 Document';

    const formData = new FormData();
    formData.append('id', AppConfig.docId);
    formData.append('content', content);
    formData.append('title', title);
    formData.append('_method', 'PATCH');
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);
    
    const dateStart = document.getElementById('doc-date-start');
    const dateEnd = document.getElementById('doc-date-end');
    if (dateStart && dateEnd) {
        formData.append('doc_date_start', dateStart.value);
        formData.append('doc_date_end', dateEnd.value);
    }

    fetch(AppConfig.saveUrl, {
        method: 'POST',
        body: formData,
        keepalive: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    AppState.setDirty(false);
}

async function prepareAndSubmit() {
    try {
        if (AppState.isDirty || savePromise === null) {
            console.log("Ensuring document is saved before submitting...");
            await saveDocument();
            console.log("Document is fully saved. Submitting form...");
        }
        await submit();
    } catch (error) {
        console.warn("Submission halted: Could not save the latest changes.");
    }
}

async function submit() {
     if (submitting) return;

    const formData = new FormData();
    formData.append('doc_id', AppConfig.docId);
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try  {
        submitting = true;
        const response = await axios.post(AppConfig.submitUrl, formData, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (response.data.status === 'success') {
            AppConfig.csrfHash = response.data.csrfHash;
            console.log(response.data.message);
            if (AppConfig.redirectUrl) window.location.href = AppConfig.redirectUrl;

            submitting = false;
        } else {
            console.log(response.data.message);
        }
    } catch (error) {
        console.error('Submission failed:', error);
    }
}

function autoSave() {
    const autoSaveInterval = 2000; 

    setInterval(async () => {
        if (AppState.isDirty && !savePromise) {
            console.log("Auto-saving in the background...");
            
            try {
                await saveDocument(); 
            } catch (error) {
                console.warn("Auto-save skipped this cycle due to an error.");
            }
        }
    }, autoSaveInterval);
}
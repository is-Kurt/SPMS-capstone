let savePromise = null;

function saveDocument() {
    if (savePromise) {
        console.log("Save already in progress. Waiting for it to finish...");
        return savePromise;
    }

    savePromise = (async () => {
        const content = tinymce.get('editable-doc').getContent();
        const title = document.getElementById('doc-title')?.value?.trim() || 'Untitled IPCR';

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
        
        // From ducuments
        const dateStart = document.getElementById('doc-date-start');
        const dateEnd = document.getElementById('doc-date-end');

        if (dateStart && dateEnd) {
            console.log('afafdfdas');
            formData.append('doc_date_start', dateStart.value);
            formData.append('doc_date_end', dateEnd.value);
        }

        try {
            const response = await axios.post(AppConfig.saveUrl, formData, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = response.data;

            if (data.status === 'success') {
                AppState.setDirty(false);
                AppConfig.csrfHash = response.data.csrfHash;

                if (saveStatus) {
                    saveStatus.innerText = '✓ Saved';
                    saveStatus.className = 'ml-3 text-[10px] uppercase tracking-widest font-bold transition-all text-emerald-500';
                }
                console.log('Saved successfully');
            }
            return data; 

        } catch (error) {
            console.error('Save failed:', error.response?.data || error.message);
            throw error; 
        }
    })();

    savePromise.finally(() => {
        savePromise = null;
    });

    return savePromise;
}

async function prepareAndSubmit() {
    const form = document.getElementById('submit-form');

    try {
        if (AppState.isDirty || savePromise !== null) {
            console.log("Ensuring document is saved before submitting...");
            await saveDocument();
        }

        console.log("Document is fully saved. Submitting form...");
        form.submit();
    } catch (error) {
        console.warn("Submission halted: Could not save the latest changes.");
    }
}

function autoSave() {
    const autoSaveInterval = 10000; 

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
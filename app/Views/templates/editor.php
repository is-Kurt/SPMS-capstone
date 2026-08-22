<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>    

<!-- Added w-full to strictly enforce screen bounds -->
<div class="h-full flex flex-col bg-bg w-full">
    
    <?= form_open('templates/store', ['id' => 'template-form', 'class' => 'flex flex-col h-full m-0 w-full', 'onsubmit' => 'event.preventDefault(); saveDocument();']) ?>
        <input type="hidden" name="template_id" value="<?= $template ? $template['id'] : '' ?>">

        <!-- Adjusted padding, gaps, and added min-w-0 logic so the flexbox can shrink -->
        <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg gap-2 sm:gap-4 w-full">
            
            <div class="flex items-center gap-1 sm:gap-3 flex-1 min-w-0">
                <!-- Back-to-templates link. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <a href="<?= site_url('templates') ?>" class="shrink-0">
                    <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-4 text-text hover:text-accent transition-colors">
                        <!-- Left-arrow icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <!-- Hidden on mobile, shown on larger screens -->
                        <span class="hidden sm:block font-black tracking-tighter text-sm uppercase">Back</span>
                    </div>
                </a>

                <!-- Replaced fixed w-64 with flex-1 min-w-0 and w-full so it shrinks gracefully -->
                <input type="text" name="title" placeholder="Enter Template Title..."
                    value="<?= $template ? esc($template['title']) : '' ?>"
                    class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-2 py-1 flex-1 min-w-0 w-full md:w-96 placeholder:text-text-muted/50 truncate">
            </div>

            <div class="flex items-center shrink-0">
                <span id="save-status" class=""></span>
                <!-- Abbreviated text on mobile (Save), expanded on desktop (Save Template) -->
                <button type="button" onclick="saveDocument()" class="ml-2 bg-accent hover:bg-accent-hover text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-4 sm:px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer whitespace-nowrap">
                    Save<span class="hidden sm:inline"> Template</span>
                </button>
            </div>
        </div>
        
        <div class="flex-none flex bg-bg border-b border-surface-border px-3 sm:px-6 gap-2 text-sm font-bold pt-2 overflow-x-auto whitespace-nowrap scrollbar-hide" id="tab-bar">
            <!-- Tabs injected here via JS -->
        </div>
        
        <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto" id="editor-container">
            <textarea id="editable-doc" name="content"></textarea>
        </div>
    <?= form_close() ?>

</div>

<script>
    <?php
    $tabsJson = '[]';
    if ($template && !empty($template['tabs'])) {
        $tabsJson = is_string($template['tabs']) ? $template['tabs'] : json_encode($template['tabs']);
    }
    ?>
    let tabs = <?= $tabsJson ?>;

    if (!tabs || tabs.length === 0) {
        tabs = [
            { id: 'tab-' + Date.now(), title: 'Target Form', content: '' }
        ];
    }

    let activeTabId = tabs[0].id;
    
    // Set initial content for TinyMCE initialization
    document.getElementById('editable-doc').value = tabs[0].content;

    function getUniqueTitle(baseTitle, excludeTabId = null) {
        let title = baseTitle;
        let counter = 1;
        const existingTitles = tabs.filter(t => t.id !== excludeTabId).map(t => t.title.toLowerCase());
        while (existingTitles.includes(title.toLowerCase())) {
            title = `${baseTitle} (${counter})`;
            counter++;
        }
        return title;
    }

    function renderTabs() {
        const tabBar = document.getElementById('tab-bar');
        tabBar.innerHTML = '';
        
        tabs.forEach(tab => {
            const isActive = tab.id === activeTabId;
            
            const btn = document.createElement('div');
            btn.className = `group flex items-center gap-1 pb-2 border-b-2 transition-colors select-none ${isActive ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text cursor-pointer'}`;
            btn.onclick = () => switchEditorTab(tab.id);
            
            const span = document.createElement('span');
            span.textContent = tab.title;
            span.contentEditable = "true";
            span.title = 'Click to edit tab name';
            span.className = 'outline-none cursor-text px-1 min-w-[20px] inline-block rounded focus:bg-surface-border/30';
            
            span.onblur = (e) => {
                const newName = e.target.textContent.trim();
                if (newName !== '' && newName !== tab.title) {
                    tab.title = getUniqueTitle(newName, tab.id);
                    AppState.setDirty(true);
                }
                e.target.textContent = tab.title;
            };
            
            span.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.target.blur();
                }
            };
            btn.appendChild(span);
            
            if (tabs.length > 1) {
                const delBtn = document.createElement('span');
                delBtn.innerHTML = '&times;';
                delBtn.className = `text-text-muted/40 hover:text-danger-600 transition-colors font-black ml-1 px-1 rounded hover:bg-danger-500/10`;
                delBtn.title = 'Delete tab';
                delBtn.onclick = (e) => { e.stopPropagation(); deleteTab(tab.id); };
                btn.appendChild(delBtn);
            }

            tabBar.appendChild(btn);
        });
        
        const addBtn = document.createElement('button');
        addBtn.innerHTML = '＋';
        addBtn.title = 'Add Tab';
        addBtn.className = 'pb-2 border-b-2 border-transparent text-text-muted hover:text-text transition-colors text-lg font-black px-2';
        addBtn.onclick = () => addTab();
        tabBar.appendChild(addBtn);
    }

    function switchEditorTab(tabId) {
        if (tabId === activeTabId) return;

        const editor = tinymce.get('editable-doc');
        if (editor) {
            const activeTab = tabs.find(t => t.id === activeTabId);
            if (activeTab) activeTab.content = editor.getContent();
        }
        
        activeTabId = tabId;
        renderTabs();

        const newTab = tabs.find(t => t.id === activeTabId);
        if (editor && newTab) {
            editor.setContent(newTab.content);
        }
    }

    function addTab() {
        const title = getUniqueTitle('New Section');
        const newTab = {
            id: 'tab-' + Date.now(),
            title: title,
            content: ''
        };
        
        const editor = tinymce.get('editable-doc');
        if (editor) {
            const activeTab = tabs.find(t => t.id === activeTabId);
            if (activeTab) activeTab.content = editor.getContent();
        }

        tabs.push(newTab);
        activeTabId = newTab.id;
        
        renderTabs();
        if (editor) {
            editor.setContent('');
            editor.focus();
        }
        AppState.setDirty(true);
    }

    function renameTab(tabId) {
        const tab = tabs.find(t => t.id === tabId);
        if (!tab) return;
        
        const newName = prompt('Enter new name for tab:', tab.title);
        if (newName && newName.trim() !== '') {
            tab.title = getUniqueTitle(newName.trim(), tabId);
            renderTabs();
            AppState.setDirty(true);
        }
    }

    async function deleteTab(tabId) {
        if (tabs.length <= 1) return;
        
        const tabToDelete = tabs.find(t => t.id === tabId);
        if (!tabToDelete) return;

        const confirmed = await window.appConfirm(`Are you sure you want to delete '${tabToDelete.title}'? This cannot be undone.`, {
            title: 'Delete Tab',
            confirmText: 'Delete',
            isDanger: true
        });
        
        if (!confirmed) return;
        
        const index = tabs.findIndex(t => t.id === tabId);
        if (index === -1) return;
        
        tabs.splice(index, 1);
        
        if (activeTabId === tabId) {
            activeTabId = tabs[Math.min(index, tabs.length - 1)].id;
            const newActive = tabs.find(t => t.id === activeTabId);
            const editor = tinymce.get('editable-doc');
            if (editor && newActive) {
                editor.setContent(newActive.content);
            }
        }
        
        renderTabs();
        AppState.setDirty(true);
    }

    // Initialize tabs UI
    renderTabs();
</script>

<script src="<?= base_url('assets/js/editor/functions.js?v=' . time()) ?>"></script>
<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    const AppConfig = {
        editorCss: '<?= base_url('assets/css/editor/style.css') ?>',
        ciDebug: <?= (ENVIRONMENT === 'development') ? 'true' : 'false' ?>
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
        }
    };

    let savePromise = null;
    let saveStatusTimeout = null;
    let isClosing = false;

    function saveDocument(manualSave = true) {
        if (savePromise && !isClosing) return savePromise;

        savePromise = new Promise((resolve, reject) => {
            const editor = tinymce.get('editable-doc');
            if (editor) {
                const activeTab = tabs.find(t => t.id === activeTabId);
                if (activeTab) activeTab.content = editor.getContent();
            }

            const title = document.querySelector('input[name="title"]').value.trim();
            const templateIdInput = document.querySelector('input[name="template_id"]');
            
            const saveStatus = document.getElementById('save-status');
            if (saveStatus && manualSave) {
                if (saveStatusTimeout) {
                    clearTimeout(saveStatusTimeout);
                    saveStatusTimeout = null;
                }
                saveStatus.innerText = '● Saving...';
                saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-info-500 animate-pulse';
            }

            const formData = new FormData();
            formData.append('template_id', templateIdInput.value);
            formData.append('tabs', JSON.stringify(tabs));
            formData.append('title', title);

            apiPost('<?= site_url('templates/store') ?>', formData, {
                onSuccess: (data) => { 
                    AppState.setDirty(false); 

                    // If it was a new template, save the ID so subsequent autosaves update it instead of creating a new one
                    if (data.template_id) {
                        templateIdInput.value = data.template_id;
                    }

                    if (saveStatus && manualSave) {
                        saveStatus.innerText = '✓ Saved';
                        saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-success-500';

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
                        saveStatus.className = 'text-[10px] uppercase tracking-widest font-bold transition-all text-danger-500';
                    }
                    reject(new Error(errorMessage));
                },
                config: { keepalive: true }
            });
        }).finally(() => {
            savePromise = null;
        });

        return savePromise;
    }

    function autoSave() {
        setTimeout(async () => {
            if (AppState.isDirty && !savePromise) {
                try {
                    await saveDocument(false);
                } catch (error) {}
            }
            autoSave();
        }, 2000);
    }

    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveDocument();
        }
    });

    window.addEventListener('beforeunload', () => {
        isClosing = true;
        if (AppState.isDirty) {
            saveDocument(false);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        initEditor();
        autoSave();
    });
</script>

<?= $this->endSection() ?>
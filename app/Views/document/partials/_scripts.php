<script>
    <?php
    $tabsJson = '[]';
    if ($doc && !empty($doc['tabs'])) {
        $tabsJson = is_string($doc['tabs']) ? $doc['tabs'] : json_encode($doc['tabs']);
        // Normalize any legacy PTWG strings in stored tab HTML to standard TWG/PMT
        $tabsJson = str_replace(
            ['PTWG COMMENTS', 'PTWG Comments', '+ [PTWG]', 'Enter PTWG calibration comment...', 'No PTWG comment', 'Performance Technical Working Group (PTWG)', 'PTWG tag', 'PTWG'],
            ['TWG/PMT COMMENTS', 'TWG/PMT Comments', '+ [' . ($sysRole === 'PMT' ? 'PMT' : ($sysRole === 'TWG' ? 'TWG' : 'TWG/PMT')) . ']', 'Enter TWG/PMT calibration comment...', 'No TWG/PMT comment', 'Technical Working Group / Performance Management Team (TWG/PMT)', ($sysRole === 'PMT' ? 'PMT' : 'TWG') . ' tag', 'TWG/PMT'],
            $tabsJson
        );
        // Permanently strip legacy Add Deliverable footers and buttons from stored tab HTML
        $tabsJson = preg_replace('/<tbody[^>]*id=[\'"]tfoot-add-[^\'"]*[\'"][^>]*>.*?<\/tbody>/si', '', $tabsJson);
        $tabsJson = preg_replace('/<button[^>]*class=[\'"][^\'"]*btn-add-dashed[^\'"]*[\'"][^>]*>.*?<\/button>/si', '', $tabsJson);
        $tabsJson = preg_replace('/<button[^>]*onclick=[\'"]addTableRow\([^\)]*\)[\'"][^>]*>.*?<\/button>/si', '', $tabsJson);
    }
    ?>
    let tabs = <?= $tabsJson ?>;

    if (!tabs || tabs.length === 0) {
        tabs = [
            { id: 'tab-' + Date.now(), title: 'Target Form', content: '' }
        ];
    }
    tabs.forEach((t, i) => {
        if (!t.title || !t.title.trim()) {
            t.title = (i === 0) ? 'Target Form' : `Tab ${i + 1}`;
        }
    });

    let activeTabId = tabs[0].id;
    
    const allBasisGuides = <?= json_encode($allBasisGuidesJson ?? []) ?>;
    const basisFormData = <?= json_encode($basisFormData ?? null) ?>;
    const basisDocContent = <?= json_encode($basisDocContent ?? '') ?>;
    const superiorUserInfo = <?= json_encode($superiorUser ?? null) ?>;
    const ownerAccountInfo = <?= json_encode($ownerInfo ?? []) ?>;
    const defaultSupervisors = <?= json_encode($supervisors ?? []) ?>;
    const defaultCascadeDate = <?= json_encode($cascadeDate ?? date('Y-m-d')) ?>;
    const cascadedTargets = <?= json_encode($cascadedTargets ?? null) ?>;

    const canEditTargets = <?= json_encode($canEditTargets) ?>;
    const canEditApprover = <?= json_encode($canEditApprover) ?>;
    const canEditEvaluation = <?= json_encode($canEditEvaluation) ?>;
    const isTargetPhase = <?= json_encode($isTargetPhase) ?>;
    const isEvaluationPhase = <?= json_encode($isEvaluationPhase) ?>;

    window.canEditTargets = canEditTargets;
    window.canEditApprover = canEditApprover;
    window.canEditEvaluation = canEditEvaluation;

    window.digitalRubricsData = (tabs[0]?.formData?.rubrics && !Array.isArray(tabs[0]?.formData?.rubrics)) ? tabs[0].formData.rubrics : {};
    


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
            btn.className = `group flex items-center gap-1 pb-2 border-b-2 transition-colors select-none ${isActive ? 'border-emerald-600 text-emerald-800 dark:border-amber-400 dark:text-amber-300 font-extrabold' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-text-muted dark:hover:text-text cursor-pointer'}`;
            btn.onclick = () => switchEditorTab(tab.id);
            
            const span = document.createElement('span');
            span.textContent = (tab.title && tab.title.trim()) ? tab.title : 'Target Form';
            span.className = 'px-1 inline-block select-none cursor-pointer';
            
            btn.appendChild(span);

            <?php if ($isEditable): ?>
            if (tabs.length > 1) {
                const delBtn = document.createElement('span');
                delBtn.innerHTML = '&times;';
                delBtn.className = `text-text-muted/40 hover:text-danger-600 transition-colors font-black ml-1 px-1 rounded hover:bg-danger-500/10`;
                delBtn.title = 'Delete tab';
                delBtn.onclick = (e) => { e.stopPropagation(); deleteTab(tab.id); };
                btn.appendChild(delBtn);
            }
            <?php endif; ?>

            tabBar.appendChild(btn);
        });

        // Digital Rubrics Matrix Tab
        const isRubricsActive = (activeTabId === 'rubrics-tab');
        const rubricsBtn = document.createElement('div');
        rubricsBtn.className = `group flex items-center gap-1.5 pb-2 border-b-2 transition-colors select-none cursor-pointer ${isRubricsActive ? 'border-amber-500 text-amber-700 dark:text-amber-400 font-extrabold' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-text-muted dark:hover:text-text'}`;
        rubricsBtn.onclick = () => switchEditorTab('rubrics-tab');
        rubricsBtn.title = 'View and Edit Rubrics Matrix';
        rubricsBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ${isRubricsActive ? 'text-amber-500' : 'text-amber-500/70'} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Rubrics Matrix</span>
        `;
        tabBar.appendChild(rubricsBtn);

        <?php if (!empty($allBasisGuides) && !$isGuide): ?>
            <?php foreach ($allBasisGuides as $gIdx => $g): ?>
                <?php 
                    $isBasisOpcr = !empty($isParentDocOpcr) || str_contains(strtoupper($g['title'] ?? ''), 'OPCR') || (strtoupper($g['doc_type'] ?? '') === 'OPCR');
                    $basisBadgeLabel = $g['is_approved'] ? ($isBasisOpcr ? 'Institutional' : 'Approved') : 'Pending';
                    $tabKey = 'basis-tab-' . $gIdx;
                    $supShortName = esc($g['superior_name'] ?? '');
                    $guideTitle = esc($g['title'] ?? 'Basis');
                    $tabLabel = (count($allBasisGuides) > 1) 
                        ? "Basis: {$guideTitle} ({$supShortName})" 
                        : "Basis: {$guideTitle}";
                ?>
                const isBasisActive_<?= $gIdx ?> = (activeTabId === '<?= $tabKey ?>');
                const basisBtn_<?= $gIdx ?> = document.createElement('div');
                basisBtn_<?= $gIdx ?>.className = `group flex items-center gap-1.5 pb-2 border-b-2 transition-colors select-none cursor-pointer ${isBasisActive_<?= $gIdx ?> ? 'border-sky-600 text-sky-800 dark:border-sky-400 dark:text-sky-300 font-extrabold' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-text-muted dark:hover:text-text'}`;
                basisBtn_<?= $gIdx ?>.onclick = () => switchEditorTab('<?= $tabKey ?>');
                basisBtn_<?= $gIdx ?>.title = 'View Superior Basis Document: <?= esc($g['title']) ?> (<?= esc($g['superior_name']) ?>)';
                basisBtn_<?= $gIdx ?>.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ${isBasisActive_<?= $gIdx ?> ? 'text-sky-500' : 'text-sky-500/70'} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span><?= $tabLabel ?></span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold ${isBasisActive_<?= $gIdx ?> ? 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-300 dark:border-sky-700' : '<?= $g['is_approved'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' ?>'}">
                        <?= esc($basisBadgeLabel) ?>
                    </span>
                `;
                tabBar.appendChild(basisBtn_<?= $gIdx ?>);
            <?php endforeach; ?>
        <?php elseif (!empty($basisDoc) && !$isGuide): ?>
            <?php 
                $isBasisOpcr = !empty($isParentDocOpcr) || str_contains(strtoupper($basisDoc['title'] ?? ''), 'OPCR') || (strtoupper($basisDoc['doc_type'] ?? '') === 'OPCR');
                $basisBadgeLabel = $isParentTargetApproved ? ($isBasisOpcr ? 'Institutional' : 'Approved') : 'Pending';
            ?>
            const isBasisActive = (activeTabId === 'basis-tab' || activeTabId === 'basis-tab-0');
            const basisBtn = document.createElement('div');
            basisBtn.className = `group flex items-center gap-1.5 pb-2 border-b-2 transition-colors select-none cursor-pointer ${isBasisActive ? 'border-sky-600 text-sky-800 dark:border-sky-400 dark:text-sky-300 font-extrabold' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-text-muted dark:hover:text-text'}`;
            basisBtn.onclick = () => switchEditorTab('basis-tab-0');
            basisBtn.title = 'View Superior Basis Document';
            basisBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ${isBasisActive ? 'text-sky-500' : 'text-sky-500/70'} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Basis: <?= esc($basisDoc['title']) ?></span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold ${isBasisActive ? 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border border-sky-300 dark:border-sky-700' : '<?= $isParentTargetApproved ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400' ?>'}">
                    <?= esc($basisBadgeLabel) ?>
                </span>
            `;
            tabBar.appendChild(basisBtn);
        <?php endif; ?>
        

    }

    function switchEditorTab(tabId) {
        if (tabId === activeTabId) return;

        if (activeTabId === 'rubrics-tab') {
            if (typeof window.syncDigitalRubricsData === 'function') {
                window.syncDigitalRubricsData();
            }
        } else if (typeof activeTabId !== 'string' || !activeTabId.startsWith('basis-tab')) {
            if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
                window.syncSpmsActiveTab();
            }
        }
        
        activeTabId = tabId;
        renderTabs();
        initActiveTabView();
        if (typeof updateRubricsTabBadge === 'function') {
            updateRubricsTabBadge();
        }
        if (typeof window.updateDynamicDocActionButton === 'function') {
            window.updateDynamicDocActionButton();
        }
    }

    function addTab() {
        const title = getUniqueTitle('New Section');
        const newTab = {
            id: 'tab-' + Date.now(),
            title: title,
            content: ''
        };
        
        if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }

        tabs.push(newTab);
        activeTabId = newTab.id;
        
        renderTabs();
        initActiveTabView();
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
        }
        
        renderTabs();
        AppState.setDirty(true);
    }

    // Initialize tabs UI
    renderTabs();
</script>
<script src="<?= base_url('assets/js/editor/saveDocument.js') ?>?v=<?= file_exists(FCPATH . 'assets/js/editor/saveDocument.js') ? filemtime(FCPATH . 'assets/js/editor/saveDocument.js') : time() ?>"></script>

<script>
    const AppConfig = {
        editorCss: '<?= base_url('assets/css/editor/style.css') ?>',
        ciDebug: <?= (ENVIRONMENT === 'development') ? 'true' : 'false' ?>,
        baseUrl: '<?= site_url('document') ?>',
        docId: '<?= $doc['id'] ?>'
    };

    const AppState = {
        isDirty: false,
        setDirty(val) {
            this.isDirty = val;
            if (val && typeof window.triggerDebouncedAutoSave === 'function') {
                window.triggerDebouncedAutoSave();
            }
        }
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        autoSave();
        if (typeof window.updateDynamicDocActionButton === 'function') {
            window.updateDynamicDocActionButton();
        }
        if (typeof window.updateWeights === 'function') {
            setTimeout(() => window.updateWeights(true), 500);
        }
    });

    window.updateWeights = function(isInitialLoad = false) {
        const coreInput = document.querySelector('input[data-cat-weight="core"]');
        const stratInput = document.querySelector('input[data-cat-weight="strategic"]');
        const suppInput = document.querySelector('input[data-cat-weight="support"]');
        
        if (coreInput && stratInput && suppInput) {
            const cVal = coreInput.value.trim();
            const sVal = stratInput.value.trim();
            const pVal = suppInput.value.trim();
            const formulaText = document.getElementById('formula-desc-text');

            const isAnyEmpty = (cVal === '' || sVal === '' || pVal === '');
            const c = cVal !== '' ? parseInt(cVal, 10) : 0;
            const s = sVal !== '' ? parseInt(sVal, 10) : 0;
            const p = pVal !== '' ? parseInt(pVal, 10) : 0;

            CATEGORY_WEIGHTS.core = cVal !== '' ? c / 100 : 0;
            CATEGORY_WEIGHTS.strategic = sVal !== '' ? s / 100 : 0;
            CATEGORY_WEIGHTS.support = pVal !== '' ? p / 100 : 0;

            const cDisp = cVal !== '' ? `${c}%` : '____ %';
            const sDisp = sVal !== '' ? `${s}%` : '____ %';
            const pDisp = pVal !== '' ? `${p}%` : '____ %';

            if (isAnyEmpty) {
                window.weightError = true;
                window.weightErrorMessage = 'Percentage weights for Core, Strategic, and Support Functions are required and must total 100%.';
                if (formulaText) {
                    formulaText.innerHTML = `Core Functions (${cDisp}) + Strategic Functions (${sDisp}) + Support Functions (${pDisp}). <span style="color: #ef4444; font-weight: bold; font-size: 11px;">(Required: All category weights must be entered and total 100%)</span>`;
                }
            } else {
                const total = c + s + p;
                if (total !== 100) {
                    window.weightError = true;
                    window.weightErrorMessage = `Total category weight is currently ${total}%. It must equal 100% exactly.`;
                    if (formulaText) {
                        formulaText.innerHTML = `Core Functions (${c}%) + Strategic Functions (${s}%) + Support Functions (${p}%). <span style="color: #ef4444; font-weight: bold;">Error: Total weight is ${total}% (Must equal 100% exactly)</span>`;
                    }
                } else {
                    window.weightError = false;
                    window.weightErrorMessage = null;
                    if (formulaText) {
                        formulaText.innerHTML = `Core Functions (${c}%) + Strategic Functions (${s}%) + Support Functions (${p}%). <span style="color: #10b981; font-weight: bold;">(Total: 100%)</span>`;
                    }
                }
            }

            // Visual feedback on the inputs themselves
            [
                { el: coreInput, val: cVal },
                { el: stratInput, val: sVal },
                { el: suppInput, val: pVal }
            ].forEach(({ el, val }) => {
                if (val === '') {
                    el.style.borderBottom = '2px dashed #ef4444';
                    el.style.backgroundColor = 'rgba(239, 68, 68, 0.08)';
                } else if (window.weightError) {
                    el.style.borderBottom = '2px dashed #f59e0b';
                    el.style.backgroundColor = 'transparent';
                } else {
                    el.style.borderBottom = '2px solid #10b981';
                    el.style.backgroundColor = 'transparent';
                }
            });
            
            if (!isInitialLoad) {
                if (tabs[0] && !tabs[0].formData) tabs[0].formData = {};
                if (tabs[0]) {
                    tabs[0].formData.weights = isAnyEmpty ? null : CATEGORY_WEIGHTS;
                    
                    const currentHtml = document.getElementById('printable-form')?.innerHTML;
                    if (currentHtml) tabs[0].content = currentHtml;
                }
                
                if (typeof calculateAllTables === 'function') calculateAllTables();
                if (typeof renderDigitalRubricsMatrix === 'function') renderDigitalRubricsMatrix(); 
                if (typeof AppState !== 'undefined' && AppState.setDirty) AppState.setDirty(true);
            }
        }
    };

    // Helper: sanitize budget input and cap at 12 whole digits (up to 999 Billion) + 2 decimal places
    function sanitizeBudgetInput(raw) {
        let clean = String(raw || '').replace(/[^0-9.]/g, '');
        const parts = clean.split('.');
        let whole = parts[0] || '';
        let decimal = parts.length > 1 ? parts.slice(1).join('') : null;
        if (whole.length > 12) {
            whole = whole.substring(0, 12);
        }
        if (decimal !== null && decimal.length > 2) {
            decimal = decimal.substring(0, 2);
        }
        return decimal !== null ? whole + '.' + decimal : whole;
    }

    // Helper: full exact comma-formatted string (e.g. 10,000,000,000.00)
    function formatBudgetFull(num) {
        if (isNaN(num) || num === null || num === undefined) return '';
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatBudgetDisplay(num) {
        return formatBudgetFull(num);
    }

    // Auto-shrink font size for large budget values + dynamic hover tooltip
    function adjustBudgetFontSize(input) {
        if (!input) return;
        const len = (input.value || '').length;
        if (len >= 18) {
            input.style.fontSize = '7.5px';
            input.style.letterSpacing = '-0.5px';
        } else if (len >= 15) {
            input.style.fontSize = '8px';
            input.style.letterSpacing = '-0.3px';
        } else if (len >= 12) {
            input.style.fontSize = '9px';
            input.style.letterSpacing = '-0.2px';
        } else if (len >= 9) {
            input.style.fontSize = '10px';
            input.style.letterSpacing = 'normal';
        } else {
            input.style.fontSize = '11px';
            input.style.letterSpacing = 'normal';
        }
        const wrapper = input.closest('.budget-input-wrapper');
        const cur = wrapper ? (wrapper.querySelector('.field-budget-currency')?.value || '₱') : '₱';
        input.title = input.value ? `${cur} ${input.value}` : 'Allotted Budget (Numbers only)';
    }

    function applyBudgetBlur(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        const clean = sanitizeBudgetInput(raw);
        if (clean !== '') {
            const num = parseFloat(clean);
            if (!isNaN(num) && num >= 0) {
                el.dataset.rawValue = clean;
                const full = formatBudgetFull(num);
                el.dataset.fullValue = full;
                el.value = full;
            } else {
                el.value = '';
                el.dataset.rawValue = '';
                el.dataset.fullValue = '';
            }
        } else {
            el.value = '';
            el.dataset.rawValue = '';
            el.dataset.fullValue = '';
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetFocus(el) {
        if (!el) return;
        const raw = el.dataset.rawValue || el.value.replace(/,/g, '').trim();
        if (raw !== '') {
            el.value = raw;
            setTimeout(() => { try { el.select(); } catch(e) {} }, 0);
        }
        adjustBudgetFontSize(el);
    }

    function applyBudgetInput(el) {
        if (!el) return;
        const oldVal = el.value;
        const clean = sanitizeBudgetInput(oldVal);
        if (clean !== oldVal) {
            el.value = clean;
        }
        el.dataset.rawValue = clean;
        const num = parseFloat(clean);
        if (!isNaN(num)) {
            el.dataset.fullValue = formatBudgetFull(num);
        } else {
            el.dataset.fullValue = clean;
        }
        adjustBudgetFontSize(el);
    }

    // Global delegation for .field-budget to strictly enforce numbers-only input and capping at billions
    document.addEventListener('keydown', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
            (e.ctrlKey || e.metaKey)) {
            return;
        }
        if (/^[0-9]$/.test(e.key)) {
            const val = el.value || '';
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const newVal = val.substring(0, selStart) + e.key + val.substring(selEnd);
            const parts = newVal.split('.');
            if (parts[0].length > 12) {
                e.preventDefault();
                return;
            }
            if (parts.length > 1 && parts[1].length > 2) {
                e.preventDefault();
                return;
            }
            return;
        }
        if (e.key === '.') {
            const selStart = el.selectionStart;
            const selEnd = el.selectionEnd;
            const selectedText = el.value.substring(selStart, selEnd);
            if (!el.value.includes('.') || selectedText.includes('.')) {
                return;
            }
        }
        e.preventDefault();
    });

    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetInput(el);
    });

    document.addEventListener('paste', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const clean = sanitizeBudgetInput(text);
        document.execCommand('insertText', false, clean);
        applyBudgetInput(el);
    });

    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetFocus(el);
    });

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (!el || !el.classList || !el.classList.contains('field-budget')) return;
        applyBudgetBlur(el);
        if (typeof window.syncSpmsActiveTab === 'function') window.syncSpmsActiveTab();
        if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') AppState.setDirty(true);
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
    });

    async function lockFolderEvaluation() {
        let finalScore = document.getElementById('grand-score')?.innerText?.trim() || '';
        if (finalScore === '0.000' || finalScore === '—') finalScore = '';

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_rating', finalScore);

        const btn = document.getElementById('btn-submit');
        const origContent = btn ? btn.innerHTML : 'Submit';
        if (btn) {
            btn.innerText = 'Locking...';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        const restoreBtn = () => {
            if (btn) {
                btn.innerHTML = origContent;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        };

        apiPost('<?= site_url('folder/evaluate') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                restoreBtn();
                await window.appAlert(errMsg || "An error occurred while locking evaluation.");
            }
        });
    }

    async function approveFolderEvaluation() {
        const isTwgOrPmt = (window.sysRole === 'TWG' || window.sysRole === 'PMT');
        const roleLabel = (window.sysRole === 'PMT') ? 'PMT' : 'TWG';
        let finalScore = document.getElementById('grand-score')?.innerText?.trim() || '';
        if (finalScore === '0.000' || finalScore === '—') finalScore = '';

        const btnApprove = document.getElementById('btn-approve');
        if (btnApprove && btnApprove.disabled) {
            await window.appAlert(
                isTwgOrPmt
                    ? `Cannot approve evaluation directly while ${roleLabel} calibration adjustments exist.\n\nPlease click 'Return' to return the document with feedback, or revert your score calibrations to approve.`
                    : "Cannot approve evaluation directly while score adjustments exist.\n\nPlease click 'Return' to return the document to the employee with feedback, or revert your score adjustments to their original ratings to approve.",
                {
                    title: isTwgOrPmt ? 'Calibrations Present' : 'Score Adjustments Present',
                    variant: 'warning'
                }
            );
            return;
        }

        const ok = await window.appConfirm(
            isTwgOrPmt
                ? `Are you sure you want to finalize and approve this rating as the ${roleLabel}?`
                : "Complete and approve this evaluation?",
            {
                title: isTwgOrPmt ? 'Verify Rating' : 'Approve Evaluation',
                confirmText: isTwgOrPmt ? 'Verify & Approve' : 'Approve',
                cancelText: 'Cancel',
                variant: 'success'
            }
        );
        if (!ok) return;

        // Ensure all reviewer remarks (PMT remarks and row remarks) are saved before approving
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_score', finalScore);

        if (btnApprove) btnApprove.innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btnApprove) {
                    btnApprove.innerText = 'Approve';
                }
                await window.appAlert(errMsg || "An error occurred while approving the evaluation.", {
                    title: 'Action Conflict',
                    variant: 'warning'
                });
                window.location.reload();
            }
        });
    }

    async function unapproveFolderEvaluation() {
        const isTwgOrPmt = (window.sysRole === 'TWG' || window.sysRole === 'PMT');
        const roleLabel = (window.sysRole === 'PMT') ? 'PMT' : 'TWG';
        const ok = await window.appConfirm(
            isTwgOrPmt
                ? `Are you sure you want to revoke your previous ${roleLabel} verification?`
                : "Remove your approval and revert to pending review?",
            {
                title: isTwgOrPmt ? `Revoke ${roleLabel} Verification` : 'Remove Approval',
                confirmText: isTwgOrPmt ? 'Revoke Verification' : 'Remove Approval',
                cancelText: 'Cancel',
                variant: isTwgOrPmt ? 'undo' : 'warning'
            }
        );
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        const btnUnapp = document.getElementById('btn-unapprove-evaluation');
        if (btnUnapp) btnUnapp.innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btnUnapp) btnUnapp.innerText = 'Unapprove';
                await window.appAlert(errMsg || "An error occurred while removing approval.", {
                    title: 'Action Conflict',
                    variant: 'warning'
                });
                window.location.reload();
            }
        });
    }

    async function returnFolderRevision() {
        const isTargetPhase = <?= $isTargetPhase ? 'true' : 'false' ?>;
        const promptTitle = isTargetPhase ? 'Return Targets for Revision' : 'Return Evaluation for Revision';
        const promptMsg = isTargetPhase
            ? "Return targets to the employee for revision?\n\nAll notes and feedback entered in the Remarks column or comments will be saved and returned to the employee."
            : "Return this evaluation to the employee for revision?\n\nAll notes and feedback entered in the Remarks column or comments will be saved and returned to the employee.";

        const ok = await window.appConfirm(promptMsg, {
            title: promptTitle,
            confirmText: 'Return for Revision',
            cancelText: 'Cancel',
            variant: 'warning'
        });
        if (!ok) return;
        
        // Save evaluator row notes and PMT remarks before returning
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('reason', '');

        const btn = document.getElementById('btn-return') || document.getElementById('btn-return-target');
        if (btn) btn.innerText = 'Returning...';
        apiPost('<?= site_url('folder/return') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) btn.innerText = 'Return';
                await window.appAlert(errMsg || "An error occurred while returning for revision.", {
                    title: 'Action Conflict',
                    variant: 'warning'
                });
                window.location.reload();
            }
        });
    }

    async function lockFolderTarget() {
        if (!window.isCurrentDocIperf) {
            const coreInp = document.querySelector('input[data-cat-weight="core"]');
            const stratInp = document.querySelector('input[data-cat-weight="strategic"]');
            const suppInp = document.querySelector('input[data-cat-weight="support"]');
            if (coreInp && stratInp && suppInp) {
                const cVal = coreInp.value.trim();
                const sVal = stratInp.value.trim();
                const pVal = suppInp.value.trim();
                if (cVal === '' || sVal === '' || pVal === '') {
                    await window.appAlert('Category percentage weights (Core, Strategic, Support) are required before submitting targets. Please enter all percentage weights totaling 100%.', { title: 'Missing Percentage Weights' });
                    if (cVal === '') coreInp.focus();
                    else if (sVal === '') stratInp.focus();
                    else suppInp.focus();
                    return;
                }
                const total = parseInt(cVal, 10) + parseInt(sVal, 10) + parseInt(pVal, 10);
                if (total !== 100) {
                    await window.appAlert(`Category percentage weights must total exactly 100% (currently ${total}%). Please adjust them before submitting targets.`, { title: 'Invalid Percentage Weights' });
                    coreInp.focus();
                    return;
                }
            }
        }

        const rubricsData = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};
        let criteriaCount = 0;
        Object.values(rubricsData).forEach(scores => {
            if (typeof scores === 'object') {
                Object.values(scores).forEach(s => {
                    if (s && (s.q || s.t || s.e)) criteriaCount++;
                });
            }
        });

        let rubricNotice = '';
        if (criteriaCount > 0) {
            rubricNotice = `Rubrics Matrix configured (${criteriaCount} criteria set).`;
        } else {
            rubricNotice = 'Caution: No Rubrics standards configured yet.';
        }

        const confirmMsg = `Submit targets and rubrics for supervisor approval?\n\n` +
            `• Rubrics Status: ${rubricNotice}\n\n` +
            `Note: The targets in your rubrics should match the targets in this form.`;

        const ok = await window.appConfirm(confirmMsg, { 
            title: 'Submit Targets & Rubrics',
            confirmText: 'Submit Now',
            cancelText: 'Keep Editing',
            variant: (criteriaCount > 0) ? 'primary' : 'warning'
        });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        const btn = document.getElementById('btn-submit-target');
        const origContent = btn ? btn.innerHTML : 'Submit Targets';
        if (btn) {
            btn.innerHTML = 'Submitting...';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        const restoreBtn = () => {
            if (btn) {
                btn.innerHTML = origContent;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        };

        apiPost('<?= site_url('folder/submit_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                restoreBtn();
                await window.appAlert(errMsg || "An error occurred while submitting targets.");
            }
        });
    }

    async function unsubmitEvaluationDocument() {
        const ok = await window.appConfirm("Are you sure you want to unsubmit your evaluation? This will return your evaluation to drafting status so you can edit your ratings and accomplishments.", { 
            title: 'Unsubmit Evaluation',
            variant: 'undo', 
            confirmText: 'Unsubmit',
            cancelText: 'Keep Submitted'
        });
        if (!ok) return;

        const btn = document.getElementById('btn-unsubmit-eval');
        if (btn) {
            btn.innerText = 'Unsubmitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) {
                    btn.innerText = 'Unsubmit';
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                await window.appAlert(errMsg || "An error occurred.");
            }
        });
    }

    async function unsubmitTargetDocument() {
        const ok = await window.appConfirm("Are you sure you want to unsubmit your targets? This will return your folder to Draft status so you can make edits and fixes to your commitments.", { 
            title: 'Unsubmit Targets',
            variant: 'undo', 
            confirmText: 'Unsubmit',
            cancelText: 'Keep Submitted'
        });
        if (!ok) return;

        const btn = document.getElementById('btn-unsubmit-target');
        if (btn) {
            btn.innerText = 'Unsubmitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        apiPost('<?= site_url('folder/unsubmit_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) {
                    btn.innerText = 'Unsubmit';
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                await window.appAlert(errMsg || "An error occurred.");
            }
        });
    }



    async function approveFolderTarget(releaseScope = null) {
        let msg = "Approve these targets?";
        let confirmBtnText = 'Approve';

        if (releaseScope === 'deans') {
            msg = "Approve this OPCR and immediately distribute it to all College Deans as their target basis?";
            confirmBtnText = 'Approve & Release to Deans';
        } else if (releaseScope === 'chairs') {
            msg = "Distribute this approved collegiate DPCR to all Department Chairs as their target basis?";
            confirmBtnText = 'Release to Chairs';
        } else if (releaseScope === 'faculty' || releaseScope === 'subordinates') {
            msg = "Approve this DPCR Department Chair and immediately distribute it to all faculty members as their target basis?";
            confirmBtnText = 'Approve & Release to Faculty';
        }

        const ok = await window.appConfirm(msg, { 
            title: 'Approve Targets',
            confirmText: confirmBtnText,
            cancelText: 'Cancel',
            variant: 'success'
        });
        if (!ok) return;

        // Save any reviewer remarks before approving targets
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        if (releaseScope) {
            formData.append('release_scope', releaseScope);
            if (releaseScope === 'deans') {
                formData.append('release_to_deans', '1');
            }
        }

        const btn = document.getElementById(releaseScope ? 'btn-approve-release-target' : 'btn-approve-target')
                 || document.getElementById('btn-release-subordinates');
        if (btn) btn.innerText = releaseScope ? 'Releasing...' : 'Approving...';

        apiPost('<?= site_url('folder/approve_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) btn.innerText = releaseScope ? 'Release Targets' : 'Approve Targets';
                await window.appAlert(errMsg || "An error occurred while approving targets.", {
                    title: 'Action Conflict',
                    variant: 'warning'
                });
                window.location.reload();
            }
        });
    }

    async function unapproveFolderTarget() {
        const ok = await window.appConfirm("Remove approval and revert to pending?", { 
            title: 'Remove Approval',
            confirmText: 'Remove Approval',
            cancelText: 'Cancel',
            variant: 'warning'
        });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        const btn = document.getElementById('btn-unapprove-target');
        if (btn) btn.innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove_target') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                if (btn) btn.innerText = 'Remove Approval';
                await window.appAlert(errMsg || "An error occurred while removing approval.", {
                    title: 'Action Conflict',
                    variant: 'warning'
                });
                window.location.reload();
            }
        });
    }

    function stampRoleTag(btn) {
        const td = btn.closest('td');
        if (!td) return;
        const ta = td.querySelector('.field-remarks');
        if (!ta) return;
        const roleTag = `[${window.currentReviewerRole || 'Reviewer'}]: `;
        if (!ta.value.includes(roleTag)) {
            ta.value = ta.value ? `${ta.value.trim()}\n${roleTag}` : roleTag;
        }
        ta.focus();
        if (typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }
        AppState.setDirty(true);
    }

    function stampTwgTag(btn) {
        const td = btn.closest('td');
        if (!td) return;
        const ta = td.querySelector('.field-twg-comment');
        if (!ta || ta.disabled) return;
        const role = (window.sysRole === 'PMT') ? 'PMT' : (window.sysRole === 'TWG' ? 'TWG' : 'TWG/PMT');
        const tag = `[${role}]: `;
        if (!ta.value.includes(tag)) {
            ta.value = ta.value ? `${ta.value.trim()}\n${tag}` : tag;
        }
        ta.focus();
        if (typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }
        AppState.setDirty(true);
    }
</script>



<script>
    window.isGuide = <?= json_encode($isGuide) ?>;
    window.status = <?= json_encode($doc['folder_status']) ?>;
    window.isOwner = <?= json_encode($doc['owner_id'] == session()->get('user_id')) ?>;
    window.currentReviewerRole = <?= json_encode($currentReviewerRole ?? 'Reviewer') ?>;
    window.currentReviewerName = <?= json_encode($currentReviewerName ?? '') ?>;
    window.sysRole = <?= json_encode($sysRole ?? '') ?>;
    window.isTwg = <?= $isTwg ? 'true' : 'false' ?>;
    window.isTwgUser = <?= ($isTwg || $isAdmin) ? 'true' : 'false' ?>;
    window.canEditTwg = <?= $canEditTwg ? 'true' : 'false' ?>;
    window.currentDocVersion = <?= (int)($currentVersion ?? 1) ?>;
    window.viewingDocVersion = <?= (int)($viewingVersion ?? 1) ?>;
    window.isHistoricalVersion = <?= !empty($isHistoricalVersion) ? 'true' : 'false' ?>;

    // Enum values exported for JS use
    window.FolderStatus = <?= json_encode([
        'DRAFT_TARGET'     => \App\Enums\FolderStatus::DRAFT_TARGET->value,
        'TARGET_SUBMITTED' => \App\Enums\FolderStatus::TARGET_SUBMITTED->value,
        'TARGET_APPROVED'  => \App\Enums\FolderStatus::TARGET_APPROVED->value,
        'TARGET_MISSED'    => \App\Enums\FolderStatus::TARGET_MISSED->value,
        'DRAFT_EVALUATION' => \App\Enums\FolderStatus::DRAFT_EVALUATION->value,
        'EVAL_SUBMITTED'   => \App\Enums\FolderStatus::EVAL_SUBMITTED->value,
        'EVAL_APPROVED'    => \App\Enums\FolderStatus::EVAL_APPROVED->value,
        'UNEVALUATED'      => \App\Enums\FolderStatus::UNEVALUATED->value,
    ]) ?>;



    // =========================================================================
    // SPMS Form Builder logic (Matches templates/editor.php)
    // =========================================================================
    window.isSpmsFormActive = false;
    const IS_DOC_DPCR = <?= $isDocDpcr ? 'true' : 'false' ?>;
    const IS_DOC_OPCR = <?= $isDocOpcr ? 'true' : 'false' ?>;
    const IS_DOC_IPERF = <?= $isDocIperf ? 'true' : 'false' ?>;

    // Default Seed Blueprint (Clean empty rows upon creation)
    const DEFAULT_BLUEPRINT = IS_DOC_DPCR ? {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" },
            { row_id: "row_core_2", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "", std_5: "", std_4: "", std_3: "", std_2: "", std_1: "" }
        ]
    } : (IS_DOC_OPCR ? {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" },
            { row_id: "row_core_2", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", budget: "", accountable: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    } : (IS_DOC_IPERF ? {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "", twg_comment: "" }
        ],
        strategic: [],
        support: []
    } : {
        core: [
            { row_id: "row_core_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        strategic: [
            { row_id: "row_strat_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ],
        support: [
            { row_id: "row_supp_1", mfo: "", indicators: "", accomplishments: "", q: "", t: "", e: "", remarks: "" }
        ]
    }));

    const CATEGORY_WEIGHTS = IS_DOC_IPERF ? {
        core: 1.00,
        strategic: 0.00,
        support: 0.00
    } : {
        core: 0,
        strategic: 0,
        support: 0
    };

    function initActiveTabView() {
        const basisWorkspace = document.getElementById('spms-basis-workspace');
        const rubricsWorkspace = document.getElementById('spms-rubrics-workspace');

        if (activeTabId === 'basis-tab' || (typeof activeTabId === 'string' && activeTabId.startsWith('basis-tab'))) {
            window.isSpmsFormActive = false;
            document.getElementById('spms-form-workspace')?.classList.add('hidden');
            if (rubricsWorkspace) rubricsWorkspace.classList.add('hidden');
            if (basisWorkspace) {
                basisWorkspace.classList.remove('hidden');
                const gIdx = parseInt(activeTabId.replace('basis-tab-', '').replace('basis-tab', '')) || 0;
                renderBasisStaticSheet(gIdx);
            }
            if (typeof window.updateDynamicDocActionButton === 'function') window.updateDynamicDocActionButton();
            return;
        }

        if (activeTabId === 'rubrics-tab') {
            window.isSpmsFormActive = false;
            document.getElementById('spms-form-workspace')?.classList.add('hidden');
            if (basisWorkspace) basisWorkspace.classList.add('hidden');
            if (rubricsWorkspace) {
                rubricsWorkspace.classList.remove('hidden');
                renderDigitalRubricsMatrix();
            }
            if (typeof window.updateDynamicDocActionButton === 'function') window.updateDynamicDocActionButton();
            return;
        }
        if (basisWorkspace) {
            basisWorkspace.classList.add('hidden');
        }
        if (rubricsWorkspace) {
            rubricsWorkspace.classList.add('hidden');
        }

        const activeTab = tabs.find(t => t.id === activeTabId);
        if (!activeTab) return;

        window.isSpmsFormActive = true;
        document.getElementById('spms-form-workspace')?.classList.remove('hidden');
        populateSpmsForm(activeTab.formData);
        if (typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }
        if (typeof window.updateDynamicDocActionButton === 'function') window.updateDynamicDocActionButton();
    }

    function addApproverBlock(name = '', position = '', date = '', isFirst = null, evalDate = '') {
        const container = document.getElementById('approvers-container');
        const footerContainer = document.getElementById('footer-approvers-container');
        if (!container) return;
        const items = container.querySelectorAll('.approver-item');
        const index = items.length;
        const isActuallyFirst = (isFirst !== null) ? isFirst : (index === 0);

        const isDpcr = (typeof window.isCurrentDocDpcr !== 'undefined') ? window.isCurrentDocDpcr : <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isOpcr = (typeof window.isCurrentDocOpcr !== 'undefined') ? window.isCurrentDocOpcr : <?= $isDocOpcr ? 'true' : 'false' ?>;
        const isDpcrOrOpcr = isDpcr || isOpcr;

        const namePlaceholder = isOpcr ? '(name of head of office / approving authority)' : (isDpcr ? '(name of office head)' : 'Name of Approving Authority');
        const posPlaceholder = isOpcr ? '(position / designation)' : (isDpcr ? '(position of office head)' : 'Official Designation');
        const disabledAttr = canEditTargets ? '' : 'disabled';

        // 1. Top Approver Item
        const div = document.createElement('div');
        div.className = 'approver-item';
        div.dataset.index = index;
        div.style.cssText = (!isActuallyFirst ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : '') + ' position: relative;';

        div.innerHTML = `
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                <tr>
                    <td style="width: ${isDpcrOrOpcr ? '65px' : '60px'}; border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#0f172a' : '#64748b'};">Name:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-name spms-meta-input" ${isActuallyFirst ? 'id="approver-name"' : ''} value="${escapeHtml(name)}" placeholder="${namePlaceholder}" style="font-weight: bold; width: ${isDpcrOrOpcr ? '60%' : '100%'}; padding: 2px 4px;" ${disabledAttr}>
                        ${!isActuallyFirst ? (canEditTargets ? `
                        <button type="button" onclick="removeApproverBlock(this)" class="print-hide" style="margin-left: 6px; color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                        ` : '') : (isDpcrOrOpcr ? `
                        <span style="color: #64748b; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                        ` : '')}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#0f172a' : '#64748b'};">Position:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="text" class="field-approver-pos spms-meta-input" ${isActuallyFirst ? 'id="approver-pos"' : ''} value="${escapeHtml(position)}" placeholder="${posPlaceholder}" style="width: ${isDpcrOrOpcr ? '80%' : '100%'}; padding: 2px 4px;" ${disabledAttr}>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 3px 0; font-weight: bold; color: ${isDpcrOrOpcr ? '#0f172a' : '#64748b'};">Date:</td>
                    <td style="border: none; padding: 3px 0;">
                        <input type="date" class="field-approver-date spms-meta-input dark:[color-scheme:dark]" ${isActuallyFirst ? 'id="approver-date"' : ''} value="${escapeHtml(date)}" onclick="this.showPicker && this.showPicker()" style="width: 130px; padding: 2px 4px; cursor: pointer;" ${disabledAttr}>
                    </td>
                </tr>
            </table>
        `;
        container.appendChild(div);

        // 2. Matching Footer Approver Item (in Final Rating by)
        let footerDiv = null;
        if (footerContainer) {
            footerDiv = document.createElement('div');
            footerDiv.className = 'footer-approver-item';
            footerDiv.dataset.index = index;
            footerDiv.style.cssText = (!isActuallyFirst ? 'border-top: 1px dashed #cbd5e1; margin-top: 8px; padding-top: 8px;' : '') + ' position: relative;';

            const footerNamePlaceholder = isOpcr ? '(name of head of office / approving authority)' : (isDpcr ? '(name of office head)' : '(name of office head)');
            const footerPosPlaceholder = isOpcr ? '(position / designation)' : (isDpcr ? '(position of office head)' : '(position of office head)');
            const evalDateDisabled = canEditEvaluation ? '' : 'disabled';
            const namePosDisabled = canEditTargets ? '' : 'disabled';

            footerDiv.innerHTML = `
                <div style="margin-bottom: 6px; color: #0f172a; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: bold;">Final Rating by${!isActuallyFirst ? ` (Signatory ${index + 1})` : ''}:</span>
                        ${isActuallyFirst ? `
                        <button type="button" id="btn-add-footer-approver" onclick="addApproverBlock()" class="print-hide" style="display: ${canEditTargets ? 'inline-flex' : 'none'}; align-items: center; gap: 4px; padding: 2px 8px; font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;" title="Add another approving signatory">
                            + Add Signatory
                        </button>
                        ` : ''}
                    </div>
                    ${!isActuallyFirst && canEditTargets ? `
                    <button type="button" onclick="removeFooterApproverBlock(this)" class="print-hide" style="color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; font-size: 9px; padding: 1px 5px; border-radius: 3px; cursor: pointer; font-weight: bold;" title="Remove this signatory">✕ Remove</button>
                    ` : ''}
                </div>
                <div style="margin-bottom: 6px; color: #0f172a;">
                    <input type="text" class="field-footer-approver-name spms-meta-input" ${isActuallyFirst ? 'id="sig-dean-name"' : ''} value="${escapeHtml(name)}" placeholder="${footerNamePlaceholder}" style="font-weight: bold; width: 65%; padding: 2px 4px;" ${namePosDisabled}>
                </div>
                <div style="margin-bottom: 6px; color: #0f172a;">Position: 
                    <input type="text" class="field-footer-approver-pos spms-meta-input" ${isActuallyFirst ? 'id="sig-dean-pos"' : ''} value="${escapeHtml(position)}" placeholder="${footerPosPlaceholder}" style="width: 70%; padding: 2px 4px;" ${namePosDisabled}>
                </div>
                <div style="margin-bottom: 6px; color: #0f172a;">
                    Date: <input type="date" class="field-footer-approver-date spms-meta-input dark:[color-scheme:dark]" ${isActuallyFirst ? 'id="sig-dean-date"' : ''} value="${escapeHtml(evalDate)}" onclick="this.showPicker && this.showPicker()" style="width: 130px; cursor: pointer; padding: 1px 4px;" ${evalDateDisabled}>
                </div>
                ${isActuallyFirst ? `
                <div style="color: #64748b; font-size: 10px; font-style: italic;">(may add signatories depending on position/designation)</div>
                ` : ''}
            `;
            footerContainer.appendChild(footerDiv);
        }

        // 3. Attach Two-Way Live Name and Position Sync
        const topName = div.querySelector('.field-approver-name');
        const topPos = div.querySelector('.field-approver-pos');
        const footName = footerDiv ? footerDiv.querySelector('.field-footer-approver-name') : null;
        const footPos = footerDiv ? footerDiv.querySelector('.field-footer-approver-pos') : null;

        const syncField = (src, dest) => {
            if (!src || !dest) return;
            dest.value = src.value;
            dest.style.color = '#0f172a';
            dest.style.borderColor = dest.value.trim() !== '' ? '#94a3b8' : '#cbd5e1';
        };

        if (topName && footName) {
            topName.addEventListener('input', () => syncField(topName, footName));
            footName.addEventListener('input', () => syncField(footName, topName));
        }
        if (topPos && footPos) {
            topPos.addEventListener('input', () => syncField(topPos, footPos));
            footPos.addEventListener('input', () => syncField(footPos, topPos));
        }

        // 4. Attach input listeners for dirty state and styling
        [div, footerDiv].filter(Boolean).forEach(containerEl => {
            containerEl.querySelectorAll('input').forEach(inp => {
                const updateStyle = () => {
                    inp.style.color = '#0f172a';
                    inp.style.borderColor = inp.value.trim() !== '' ? '#94a3b8' : '#cbd5e1';
                };
                updateStyle();
                const handleUpdate = () => {
                    updateStyle();
                    window.syncSpmsActiveTab();
                    if (typeof AppState !== 'undefined') AppState.setDirty(true);
                };
                inp.addEventListener('input', handleUpdate);
                inp.addEventListener('change', handleUpdate);
            });
        });

        if (isFirst === null) {
            window.syncSpmsActiveTab();
            if (typeof AppState !== 'undefined') AppState.setDirty(true);
        }
    }

    function removeApproverBlock(btn) {
        const item = btn.closest('.approver-item');
        if (!item) return;
        const index = Array.from(item.parentElement.children).indexOf(item);
        removeApproverBlockByIndex(index);
    }

    function removeFooterApproverBlock(btn) {
        const item = btn.closest('.footer-approver-item');
        if (!item) return;
        const index = Array.from(item.parentElement.children).indexOf(item);
        removeApproverBlockByIndex(index);
    }

    function removeApproverBlockByIndex(index) {
        const headerItems = document.querySelectorAll('#approvers-container .approver-item');
        const footerItems = document.querySelectorAll('#footer-approvers-container .footer-approver-item');
        if (headerItems[index]) headerItems[index].remove();
        if (footerItems[index]) footerItems[index].remove();
        window.syncSpmsActiveTab();
        if (typeof AppState !== 'undefined') AppState.setDirty(true);
    }

    function extractApprovers() {
        const items = document.querySelectorAll('#approvers-container .approver-item');
        const footerItems = document.querySelectorAll('#footer-approvers-container .footer-approver-item');
        const approvers = [];
        items.forEach((item, idx) => {
            const name = item.querySelector('.field-approver-name')?.value || '';
            const position = item.querySelector('.field-approver-pos')?.value || '';
            const date = item.querySelector('.field-approver-date')?.value || '';
            const footerItem = footerItems[idx];
            const evalDate = footerItem?.querySelector('.field-footer-approver-date')?.value || '';
            if (name || position || date || evalDate || items.length === 1) {
                approvers.push({ name, position, date, evalDate });
            }
        });
        if (approvers.length === 0) {
            approvers.push({
                name: document.getElementById('approver-name')?.value || '',
                position: document.getElementById('approver-pos')?.value || '',
                date: document.getElementById('approver-date')?.value || '',
                evalDate: document.getElementById('sig-dean-date')?.value || ''
            });
        }
        return approvers;
    }

    window.addApproverBlock = addApproverBlock;
    window.removeApproverBlock = removeApproverBlock;
    window.removeFooterApproverBlock = removeFooterApproverBlock;
    window.removeApproverBlockByIndex = removeApproverBlockByIndex;

    window.updateDynamicDocActionButton = function() {
        const btn = document.getElementById('btn-dynamic-doc-action');
        if (!btn) return;
        const label = document.getElementById('dynamic-doc-action-label');
        const icon = document.getElementById('dynamic-doc-action-icon');

        const isBasisTab = (typeof activeTabId === 'string' && activeTabId.startsWith('basis-tab'));

        if (isBasisTab) {
            btn.className = "inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-lg text-[10px] sm:text-xs font-extrabold text-white bg-sky-600 hover:bg-sky-700 border border-sky-500 shadow-md shadow-sky-600/20 transition-all active:scale-[0.98] cursor-pointer print-hide";
            btn.title = "Copy all targets, success indicators, and rubrics from this superior basis into your target form";
            if (label) label.innerText = "Append to My Form";
            if (icon) {
                icon.outerHTML = `<svg id="dynamic-doc-action-icon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>`;
            }
        } else {
            btn.className = "inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-lg text-[10px] sm:text-xs font-bold border transition-all shadow-sm active:scale-[0.98] cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 border-slate-300 dark:border-zinc-700 print-hide";
            btn.title = "Reset document deliverables and custom rubrics back to blank template";
            if (label) label.innerText = "Reset Doc";
            if (icon) {
                icon.outerHTML = `<svg id="dynamic-doc-action-icon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>`;
            }
        }
    };

    window.handleDynamicDocAction = async function() {
        const isBasisTab = (typeof activeTabId === 'string' && activeTabId.startsWith('basis-tab'));
        if (isBasisTab) {
            const gIdx = parseInt(activeTabId.replace('basis-tab-', '').replace('basis-tab', '')) || 0;
            window.appendBasisGuideToForm(gIdx);
        } else {
            await window.resetDocumentForm();
        }
    };

    window.appendBasisGuideToForm = function(guideIdx = 0) {
        const guide = (typeof allBasisGuides !== 'undefined' && allBasisGuides && allBasisGuides[guideIdx]) 
            ? allBasisGuides[guideIdx] 
            : ((typeof allBasisGuides !== 'undefined' && allBasisGuides && allBasisGuides[0]) ? allBasisGuides[0] : null);

        if (!guide || !guide.formData) {
            if (typeof window.showToast === 'function') {
                window.showToast('No structured targets or rubrics available in this basis document.', 'info');
            }
            return;
        }

        const cats = guide.formData.categories || {};
        let addedCount = 0;
        ['core', 'strategic', 'support'].forEach(cat => {
            if (window.isCurrentDocIperf && cat !== 'core') return;
            if (Array.isArray(cats[cat])) {
                cats[cat].forEach(row => {
                    const rowMfo = (row.mfo || '').trim().toLowerCase();
                    const rowInd = (row.indicators || '').trim().toLowerCase();
                    if (!rowMfo && !rowInd) return;

                    // Deduplicate against rows currently present in table tbody
                    const tbody = document.getElementById('tbody-' + cat);
                    let alreadyPresent = false;
                    if (tbody) {
                        const trList = tbody.querySelectorAll('.table-row-' + cat);
                        trList.forEach(tr => {
                            const curMfo = (tr.querySelector('.field-mfo')?.value || '').trim().toLowerCase();
                            const curInd = (tr.querySelector('.field-indicators')?.value || '').trim().toLowerCase();
                            if (curMfo === rowMfo && curInd === rowInd) {
                                alreadyPresent = true;
                            }
                        });
                    }

                    if (!alreadyPresent) {
                        const clone = Object.assign({}, row, {
                            row_id: 'row_' + cat + '_basis_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6)
                        });
                        addTableRow(cat, clone);
                        if (guide.formData.rubrics && guide.formData.rubrics[row.row_id]) {
                            if (!window.digitalRubricsData) window.digitalRubricsData = {};
                            window.digitalRubricsData[clone.row_id] = JSON.parse(JSON.stringify(guide.formData.rubrics[row.row_id]));
                        }
                        addedCount++;
                    }
                });
            }
        });

        if (addedCount > 0) {
            if (tabs[0]?.formData) tabs[0].formData.rubrics = window.digitalRubricsData;
            if (typeof window.syncSpmsActiveTab === 'function') window.syncSpmsActiveTab();
            if (typeof AppState !== 'undefined') AppState.setDirty(true);
            if (typeof window.renderDigitalRubricsMatrix === 'function') window.renderDigitalRubricsMatrix();

            // Switch back to user's form tab
            if (tabs && tabs[0]) {
                switchEditorTab(tabs[0].id);
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Successfully appended ${addedCount} target(s) and rubrics from ${guide.title} (${guide.superior_name}).`, 'success');
            }
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast('All targets and rubrics from this superior basis are already present in your form.', 'info');
            }
        }
    };

    window.resetDocumentForm = async function() {
        if (!canEditTargets) {
            if (typeof window.showToast === 'function') {
                window.showToast('Document cannot be reset in current status.', 'warning');
            }
            return;
        }

        const confirmed = (typeof window.appConfirm === 'function') 
            ? await window.appConfirm('Are you sure you want to reset this document? All current deliverables and rubrics will be cleared back to a clean template.', {
                title: 'Reset Document',
                confirmText: 'Reset Document',
                isDanger: true
            })
            : confirm('Are you sure you want to reset this document? All current deliverables and rubrics will be cleared back to a clean template.');

        if (!confirmed) return;

        ['core', 'strategic', 'support'].forEach(cat => {
            const tbody = document.getElementById('tbody-' + cat);
            if (tbody) {
                tbody.innerHTML = '';
                addTableRow(cat, {
                    mfo: '',
                    indicators: '',
                    budget: '',
                    accountable: '',
                    actual: '',
                    q: '',
                    t: '',
                    e: '',
                    remarks: '',
                    row_id: 'row_' + cat + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6)
                });
            }
        });

        window.digitalRubricsData = {};
        if (tabs[0]?.formData) {
            tabs[0].formData.rubrics = {};
            tabs[0].formData.categories = { core: [], strategic: [], support: [] };
        }

        if (typeof window.syncSpmsActiveTab === 'function') window.syncSpmsActiveTab();
        if (typeof AppState !== 'undefined') AppState.setDirty(true);
        if (typeof window.renderDigitalRubricsMatrix === 'function') window.renderDigitalRubricsMatrix();
        if (typeof window.recalcAllCategoriesGrandTotal === 'function') window.recalcAllCategoriesGrandTotal();

        if (typeof window.showToast === 'function') {
            window.showToast('Document targets and rubrics reset to clean template.', 'info');
        }
    };

    // Alias legacy appendCascadedTargets to appendBasisGuideToForm
    window.appendCascadedTargets = function() {
        window.appendBasisGuideToForm(0);
    };

    function populateSpmsForm(formData) {
        const data = formData || {};

        const explicitDocTitle = '<?= strtoupper(trim($doc['title'] ?? '')) ?>';
        const titleText = (data.title || explicitDocTitle).toUpperCase();
        const docType = '<?= strtolower($doc['doc_type'] ?? 'ipcr') ?>';
        
        // Prioritize explicit document title: A DPCR is always a DPCR regardless of user default doc_type
        const isDocExplicitDpcr = <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isDpcr = isDocExplicitDpcr || explicitDocTitle === 'DPCR' || titleText.includes('DPCR') || titleText.includes('DIVISION') || (docType === 'dpcr' && !titleText.includes('OPCR'));
        window.isCurrentDocDpcr = isDpcr;
        const isDocExplicitOpcr = <?= $isDocOpcr ? 'true' : 'false' ?>;
        const isOpcr = !isDpcr && (isDocExplicitOpcr || explicitDocTitle === 'OPCR' || titleText.includes('OPCR') || titleText.includes('OFFICE') || docType === 'opcr');
        window.isCurrentDocOpcr = isOpcr;
        const isDocExplicitIperf = <?= $isDocIperf ? 'true' : 'false' ?>;
        const isIperf = !isDpcr && !isOpcr && (isDocExplicitIperf || explicitDocTitle.includes('IPERF') || titleText.startsWith('IPERF') || titleText.includes('INDIVIDUAL PERFORMANCE EVALUATION RATING FORM') || docType === 'iperf');
        window.isCurrentDocIperf = isIperf;

        let coreW = data.weights?.core ?? null;
        let stratW = data.weights?.strategic ?? null;
        let suppW = data.weights?.support ?? null;
        let defaultDocTitle = 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW';
        let coreTitle = coreW ? `CORE FUNCTIONS (${Math.round(coreW * 100)}%)` : "CORE FUNCTIONS ( ____ %)";
        let stratTitle = stratW ? `STRATEGIC FUNCTIONS (${Math.round(stratW * 100)}%)` : "STRATEGIC FUNCTIONS ( ____ %)";
        let suppTitle = suppW ? `SUPPORT FUNCTIONS (${Math.round(suppW * 100)}%)` : "SUPPORT FUNCTIONS ( ____ %)";
        let rateeRole = "Faculty Member / Professor";
        let deanRole = "College Dean / Unit Head";
        let vpRole = "Vice President for Academic Affairs";

        if (isOpcr) {
            coreW = data.weights?.core ?? null;
            stratW = data.weights?.strategic ?? null;
            suppW = data.weights?.support ?? null;
            defaultDocTitle = "OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)";
            coreTitle = coreW ? `CORE FUNCTIONS (${Math.round(coreW * 100)}%)` : 'CORE FUNCTIONS ( ____ %)';
            stratTitle = stratW ? `STRATEGIC FUNCTIONS (${Math.round(stratW * 100)}%)` : 'STRATEGIC FUNCTIONS ( ____ %)';
            suppTitle = suppW ? `SUPPORT FUNCTIONS (${Math.round(suppW * 100)}%)` : 'SUPPORT FUNCTIONS ( ____ %)';
            const posLower = (ownerAccountInfo.position || '').toLowerCase();
            rateeRole = posLower.includes('vice president') ? "Vice President / Sector Head" : "Vice President / College Dean";
            deanRole = "University President / PMT Chair";
            vpRole = "University President";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('DPCR') || data.title.includes('Faculty / Professors')) {
                data.title = defaultDocTitle;
            }
        } else if (isDpcr) {
            coreW = data.weights?.core ?? null;
            stratW = data.weights?.strategic ?? null;
            suppW = data.weights?.support ?? null;
            defaultDocTitle = "DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)";
            coreTitle = coreW ? `CORE FUNCTIONS (${Math.round(coreW * 100)}%)` : 'CORE FUNCTIONS ( ____ %)';
            stratTitle = stratW ? `STRATEGIC FUNCTIONS (${Math.round(stratW * 100)}%)` : 'STRATEGIC FUNCTIONS ( ____ %)';
            suppTitle = suppW ? `SUPPORT FUNCTIONS (${Math.round(suppW * 100)}%)` : 'SUPPORT FUNCTIONS ( ____ %)';
            const posLower = (ownerAccountInfo.position || '').toLowerCase();
            rateeRole = posLower.includes('dean') ? "College Dean / Supervisor" : "Department Chairperson / Unit Head";
            deanRole = posLower.includes('dean') ? "Vice President for Academic Affairs" : "College Dean / Supervisor";
            vpRole = "Vice President for Academic Affairs";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('OPCR') || data.title.includes('Division Performance')) {
                data.title = defaultDocTitle;
            }
        } else if (isIperf) {
            coreW = 1.00;
            stratW = 0.00;
            suppW = 0.00;
            defaultDocTitle = "INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL";
            coreTitle = "OFFICE PPA / EXPECTED OUTPUTS";
            stratTitle = "";
            suppTitle = "";
            rateeRole = "Contract of Service / Job Order Personnel";
            deanRole = "Immediate Supervisor";
            vpRole = "Office Head";
            if (!data.title || data.title.includes('IPCR') || data.title.includes('DPCR') || data.title.includes('OPCR')) {
                data.title = defaultDocTitle;
            }
        }

        CATEGORY_WEIGHTS.core = coreW || 0;
        CATEGORY_WEIGHTS.strategic = stratW || 0;
        CATEGORY_WEIGHTS.support = suppW || 0;

        // Header info
        const titleEl = document.getElementById('spms-doc-title');
        if (titleEl) {
            let titleToSet = data.title || defaultDocTitle;
            if (titleToSet.includes('Faculty / Professors') || titleToSet.includes('IPCR Form')) {
                titleToSet = defaultDocTitle;
            }
            titleEl.innerText = titleToSet;
        }

        function makeEditableWeight(cat, titlePrefix, currentWeight) {
            const hasWeight = currentWeight !== undefined && currentWeight !== null && currentWeight !== '' && !isNaN(currentWeight) && Number(currentWeight) > 0;
            const pct = hasWeight ? Math.round(currentWeight * 100) : '';
            if (!canEditTargets || isIperf) {
                return pct !== '' ? `${escapeHtml(titlePrefix)} (${pct}%)` : `${escapeHtml(titlePrefix)} ( ____ %)`;
            }
            return `${escapeHtml(titlePrefix)} (<input type="number" min="1" max="100" required data-cat-weight="${cat}" value="${pct}" placeholder="—" title="Required: Enter percentage weight" oninput="window.updateWeights()" class="spms-meta-input spms-cat-weight-input print-borderless" style="width:36px; text-align:center; font-weight:900; font-size:inherit; color:inherit; background:transparent; border:none; border-bottom:2px dashed #f59e0b; outline:none; padding:0 2px; -moz-appearance:textfield; appearance:textfield;">%) <span style="color:#ef4444; font-weight:bold;" title="Required">*</span>`;
        }

        const elCore = document.getElementById('label-cat-core');
        if (elCore) {
            const hint = ' <span style="font-weight: 500; font-size: 9px; color: #64748b; font-style: italic;">(depending on position/designation)</span>';
            elCore.innerHTML = makeEditableWeight('core', 'CORE FUNCTIONS', coreW) + hint;
        }
        const elStrat = document.getElementById('label-cat-strategic');
        if (elStrat) {
            const hint = ' <span style="font-weight: 500; font-size: 9px; color: #64748b; font-style: italic;">(depending on position/designation)</span>';
            elStrat.innerHTML = makeEditableWeight('strategic', 'STRATEGIC FUNCTIONS', stratW) + hint;
        }
        const elSupp = document.getElementById('label-cat-support');
        if (elSupp) {
            const hint = ' <span style="font-weight: 500; font-size: 9px; color: #64748b; font-style: italic;">(depending on position/designation)</span>';
            elSupp.innerHTML = makeEditableWeight('support', 'SUPPORT FUNCTIONS', suppW) + hint;
        }

        const elRoleRatee = document.getElementById('sig-role-ratee');
        if (elRoleRatee) elRoleRatee.innerText = rateeRole;
        const elRoleDean = document.getElementById('sig-role-dean');
        if (elRoleDean) elRoleDean.innerText = deanRole;
        const elRoleVp = document.getElementById('sig-role-vp');
        if (elRoleVp) elRoleVp.innerText = vpRole;

        const elFDesc = document.getElementById('formula-desc-text');
        if (elFDesc) {
            if (coreW && stratW && suppW) {
                const totalW = Math.round((Number(coreW) + Number(stratW) + Number(suppW)) * 100);
                if (totalW === 100) {
                    elFDesc.innerHTML = `Core Functions (${(coreW * 100).toFixed(0)}%) + Strategic Functions (${(stratW * 100).toFixed(0)}%) + Support Functions (${(suppW * 100).toFixed(0)}%). <span style="color: #10b981; font-weight: bold;">(Total: 100%)</span>`;
                } else {
                    elFDesc.innerHTML = `Core Functions (${(coreW * 100).toFixed(0)}%) + Strategic Functions (${(stratW * 100).toFixed(0)}%) + Support Functions (${(suppW * 100).toFixed(0)}%). <span style="color: #ef4444; font-weight: bold;">Error: Total weight is ${totalW}% (Must equal 100% exactly)</span>`;
                }
            } else if (!isIperf) {
                elFDesc.innerHTML = 'Core Functions ( ____ %) + Strategic Functions ( ____ %) + Support Functions ( ____ %). <span style="color: #ef4444; font-weight: bold; font-size: 11px;">(Required: All category weights must be entered and total 100%)</span>';
            }
        }

        const elMC = document.getElementById('mult-core-val');
        if (elMC) elMC.innerText = (coreW !== null && coreW !== undefined && coreW !== '') ? Number(coreW).toFixed(2) : '—';
        const elMS = document.getElementById('mult-strategic-val');
        if (elMS) elMS.innerText = (stratW !== null && stratW !== undefined && stratW !== '') ? Number(stratW).toFixed(2) : '—';
        const elMP = document.getElementById('mult-support-val');
        if (elMP) elMP.innerText = (suppW !== null && suppW !== undefined && suppW !== '') ? Number(suppW).toFixed(2) : '—';

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) {
                el.value = (val !== undefined && val !== null && val !== '') ? val : '';
                el.style.color = '#0f172a';
                el.style.borderColor = el.value.trim() !== '' ? '#94a3b8' : '#cbd5e1';
            }
        };

        const defaultRateeName = ownerAccountInfo.name || '';
        const defaultRateePos  = ownerAccountInfo.position || '';
        const defaultRateeDept = ownerAccountInfo.dept || '';
        const defaultPeriod    = ownerAccountInfo.period || '';
        const cascadeDateVal   = ownerAccountInfo.cascadeDate || defaultCascadeDate;

        let savedPeriod = data.ratee?.period || '';
        if (savedPeriod.toLowerCase().startsWith('untitled evaluation') || (ownerAccountInfo.folderTitle && savedPeriod === ownerAccountInfo.folderTitle)) {
            savedPeriod = '';
        }

        setVal('ratee-name', (data.ratee?.name && data.ratee.name.trim() !== '') ? data.ratee.name : defaultRateeName);
        setVal('ratee-position', (data.ratee?.position && data.ratee.position.trim() !== '') ? data.ratee.position : defaultRateePos);
        setVal('ratee-dept', (data.ratee?.dept && data.ratee.dept.trim() !== '') ? data.ratee.dept : defaultRateeDept);
        setVal('ratee-period', savedPeriod || defaultPeriod);

        // Auto-sync masthead college / department subtitle
        const lhDept = document.getElementById('spms-letterhead-dept');
        if (lhDept) {
            const currentDept = (data.ratee?.dept || '').trim() || (defaultRateeDept || '') || '<?= addslashes(esc($ownerInfo['dept'] ?? '')) ?>' || 'Office of the University President';
            lhDept.textContent = currentDept;
        }

        // Populate Approver(s) - Supports multiple cascaded supervisors
        const container = document.getElementById('approvers-container');
        const footerContainer = document.getElementById('footer-approvers-container');
        let approversList = [];

        if (container) {
            container.innerHTML = '';
            if (footerContainer) footerContainer.innerHTML = '';

            let hasValidSavedApprovers = false;
            if (data.approvers && Array.isArray(data.approvers) && data.approvers.length > 0) {
                if (data.approvers.some(a => a && a.name && a.name.trim() !== '')) {
                    hasValidSavedApprovers = true;
                    approversList = data.approvers;
                }
            } else if (data.approver && data.approver.name && data.approver.name.trim() !== '') {
                hasValidSavedApprovers = true;
                approversList = [data.approver];
            } else if (data.signatories?.dean && data.signatories.dean.trim() !== '') {
                hasValidSavedApprovers = true;
                approversList = [{
                    name: data.signatories.dean,
                    position: data.signatories.deanPos || '',
                    date: data.signatories.deanDate || cascadeDateVal,
                    evalDate: data.signatories.deanDate || cascadeDateVal
                }];
            }

            if (!hasValidSavedApprovers) {
                // Populate default from cascaded supervisor(s)
                if (defaultSupervisors && Array.isArray(defaultSupervisors) && defaultSupervisors.length > 0) {
                    approversList = defaultSupervisors.map(sup => ({
                        name: sup.name || '',
                        position: sup.position || '',
                        date: sup.date || cascadeDateVal,
                        evalDate: sup.evalDate || cascadeDateVal
                    }));
                } else {
                    approversList = [{ name: '', position: '', date: cascadeDateVal, evalDate: cascadeDateVal }];
                }
            }

            approversList.forEach((appr, idx) => {
                if (!appr.date) appr.date = cascadeDateVal;
                if (!appr.evalDate) appr.evalDate = cascadeDateVal;
                addApproverBlock(appr.name || '', appr.position || '', appr.date, idx === 0, appr.evalDate);
            });
        } else {
            const firstSup = (defaultSupervisors && defaultSupervisors[0]) ? defaultSupervisors[0] : {};
            setVal('approver-name', (data.approver?.name && data.approver.name.trim() !== '') ? data.approver.name : (firstSup.name || ''));
            setVal('approver-pos', (data.approver?.position && data.approver.position.trim() !== '') ? data.approver.position : (firstSup.position || ''));
            setVal('approver-date', (data.approver?.date && data.approver.date.trim() !== '') ? data.approver.date : (firstSup.date || cascadeDateVal));
        }

        const addApprBtn = document.getElementById('btn-add-approver') || document.getElementById('btn-add-approver-ipcr');
        if (addApprBtn) {
            addApprBtn.style.display = canEditTargets ? 'inline-flex' : 'none';
        }
        const addFooterApprBtn = document.getElementById('btn-add-footer-approver');
        if (addFooterApprBtn) {
            addFooterApprBtn.style.display = canEditTargets ? 'inline-flex' : 'none';
        }

        setVal('ratee-sign-name', (data.rateeSign?.name && data.rateeSign.name.trim() !== '') ? data.rateeSign.name : defaultRateeName);
        setVal('ratee-sign-date', (data.rateeSign?.date && data.rateeSign.date.trim() !== '') ? data.rateeSign.date : cascadeDateVal);

        window.currentDocCurrency = data.budget_currency || data.currency || '₱';
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            el.value = window.currentDocCurrency;
            el.style.width = Math.max(22, (el.value.length + 1) * 7.5) + 'px';
            el.disabled = !canEditTargets;
        });

        setVal('pmt-remarks', data.pmtRemarks || '');

        setVal('sig-ratee-name', (data.signatories?.ratee && data.signatories.ratee.trim() !== '') ? data.signatories.ratee : defaultRateeName);
        setVal('sig-ratee-pos', (data.signatories?.rateePos && data.signatories.rateePos.trim() !== '') ? data.signatories.rateePos : defaultRateePos);
        setVal('sig-ratee-date', (data.signatories?.rateeDate && data.signatories.rateeDate.trim() !== '') ? data.signatories.rateeDate : cascadeDateVal);
        setVal('sig-dean-name', (data.signatories?.dean && data.signatories.dean.trim() !== '') ? data.signatories.dean : (approversList[0]?.name || defaultSupervisors[0]?.name || ''));
        setVal('sig-dean-pos', (data.signatories?.deanPos && data.signatories.deanPos.trim() !== '') ? data.signatories.deanPos : (approversList[0]?.position || defaultSupervisors[0]?.position || ''));
        setVal('sig-dean-date', (data.signatories?.deanDate && data.signatories.deanDate.trim() !== '') ? data.signatories.deanDate : (approversList[0]?.evalDate || cascadeDateVal));
        setVal('sig-vp-name', data.signatories?.vp || '');
        setVal('sig-vp-date', data.signatories?.vpDate || '');

        if (isIperf) {
            setVal('ratee-classification', data.classification || 'Contract of Service (COS)');
            setVal('sig-targets-prepared-name', (data.signatories?.targetsPreparedName && data.signatories.targetsPreparedName.trim() !== '') ? data.signatories.targetsPreparedName : defaultRateeName);
            setVal('sig-targets-prepared-date', (data.signatories?.targetsPreparedDate && data.signatories.targetsPreparedDate.trim() !== '') ? data.signatories.targetsPreparedDate : cascadeDateVal);
            setVal('sig-targets-approved-name', (data.signatories?.targetsApprovedName && data.signatories.targetsApprovedName.trim() !== '') ? data.signatories.targetsApprovedName : (approversList[0]?.name || defaultSupervisors[0]?.name || ''));
            setVal('sig-targets-approved-date', (data.signatories?.targetsApprovedDate && data.signatories.targetsApprovedDate.trim() !== '') ? data.signatories.targetsApprovedDate : cascadeDateVal);
            setVal('sig-eval-rated-name', (data.signatories?.evalRatedName && data.signatories.evalRatedName.trim() !== '') ? data.signatories.evalRatedName : (approversList[0]?.name || defaultSupervisors[0]?.name || ''));
            setVal('sig-eval-rated-date', (data.signatories?.evalRatedDate && data.signatories.evalRatedDate.trim() !== '') ? data.signatories.evalRatedDate : cascadeDateVal);
            setVal('sig-eval-conforme-name', (data.signatories?.evalConformeName && data.signatories.evalConformeName.trim() !== '') ? data.signatories.evalConformeName : defaultRateeName);
            setVal('sig-eval-conforme-date', (data.signatories?.evalConformeDate && data.signatories.evalConformeDate.trim() !== '') ? data.signatories.evalConformeDate : cascadeDateVal);

            const elClass = document.getElementById('ratee-classification');
            if (elClass) elClass.disabled = !canEditTargets;
            ['sig-targets-prepared-name', 'sig-targets-prepared-date',
             'sig-targets-approved-name', 'sig-targets-approved-date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !canEditTargets;
            });
            ['sig-eval-rated-name', 'sig-eval-rated-date',
             'sig-eval-conforme-name', 'sig-eval-conforme-date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !canEditEvaluation;
            });
        }

        // Clear existing rows
        document.querySelectorAll('.table-row-core, .table-row-strategic, .table-row-support').forEach(tr => tr.remove());

        // Populate category rows
        const hasRealCustomTargets = Boolean(data && data.categories && typeof data.categories === 'object' && (
            (Array.isArray(data.categories.core) && data.categories.core.some(r => r && ((r.mfo && r.mfo.trim() !== '') || (r.indicators && r.indicators.trim() !== '')))) ||
            (Array.isArray(data.categories.strategic) && data.categories.strategic.some(r => r && ((r.mfo && r.mfo.trim() !== '') || (r.indicators && r.indicators.trim() !== '')))) ||
            (Array.isArray(data.categories.support) && data.categories.support.some(r => r && ((r.mfo && r.mfo.trim() !== '') || (r.indicators && r.indicators.trim() !== '')))) ||
            window._userExplicitlyDeletedRow
        ));

        let cats = data.categories;
        if (!hasRealCustomTargets && cascadedTargets && cascadedTargets.hasTargets) {
            cats = cascadedTargets.categories;
            if (cascadedTargets.rubrics && Object.keys(cascadedTargets.rubrics).length > 0) {
                window.digitalRubricsData = Object.assign({}, cascadedTargets.rubrics, window.digitalRubricsData || {});
                if (tabs[0]?.formData) tabs[0].formData.rubrics = window.digitalRubricsData;
            }
            if (tabs[0]?.formData) tabs[0].formData.categories = cats;
            if (cascadedTargets.currency) window.currentDocCurrency = cascadedTargets.currency;
        } else if (hasRealCustomTargets && canEditTargets && cascadedTargets && cascadedTargets.hasTargets && !window._userExplicitlyDeletedRow) {
            // When multiple supervisors cascade to the same subordinate, automatically merge any new cascaded targets
            // that do not already exist in the user's current categories
            let merged = false;
            ['core', 'strategic', 'support'].forEach(cat => {
                if (window.isCurrentDocIperf && cat !== 'core') return;
                const incomingRows = (cascadedTargets.categories && cascadedTargets.categories[cat]) || [];
                if (!Array.isArray(cats[cat])) cats[cat] = [];
                
                incomingRows.forEach(inRow => {
                    const inMfo = (inRow.mfo || '').trim().toLowerCase();
                    const inInd = (inRow.indicators || '').trim().toLowerCase();
                    if (!inMfo && !inInd) return;

                    const alreadyExists = cats[cat].some(existingRow => {
                        const exMfo = (existingRow.mfo || '').trim().toLowerCase();
                        const exInd = (existingRow.indicators || '').trim().toLowerCase();
                        return (exMfo === inMfo && exInd === inInd);
                    });

                    if (!alreadyExists) {
                        const newRow = Object.assign({}, inRow, {
                            row_id: 'row_' + cat + '_cascaded_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6)
                        });
                        cats[cat].push(newRow);
                        if (cascadedTargets.rubrics && cascadedTargets.rubrics[inRow.row_id]) {
                            if (!window.digitalRubricsData) window.digitalRubricsData = {};
                            window.digitalRubricsData[newRow.row_id] = JSON.parse(JSON.stringify(cascadedTargets.rubrics[inRow.row_id]));
                        }
                        merged = true;
                    }
                });
            });
            if (merged) {
                if (tabs[0]?.formData) {
                    tabs[0].formData.categories = cats;
                    tabs[0].formData.rubrics = window.digitalRubricsData;
                }
            }
        } else if (!cats) {
            cats = DEFAULT_BLUEPRINT;
        }

        const coreRows = Array.isArray(cats.core) 
            ? (cats.core.length > 0 ? cats.core : (DEFAULT_BLUEPRINT.core || []))
            : (DEFAULT_BLUEPRINT.core || []);
        coreRows.forEach(row => addTableRow('core', row));

        if (!isIperf) {
            const stratRows = Array.isArray(cats.strategic)
                ? (cats.strategic.length > 0 ? cats.strategic : (DEFAULT_BLUEPRINT.strategic || []))
                : (DEFAULT_BLUEPRINT.strategic || []);
            stratRows.forEach(row => addTableRow('strategic', row));

            const suppRows = Array.isArray(cats.support)
                ? (cats.support.length > 0 ? cats.support : (DEFAULT_BLUEPRINT.support || []))
                : (DEFAULT_BLUEPRINT.support || []);
            suppRows.forEach(row => addTableRow('support', row));
        }

        if (tabs[0] && (!tabs[0].formData || !tabs[0].formData.categories) && typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }

        // Permanently remove all "+ Add Deliverable" buttons across all forms and categories (rows managed via right-click)
        document.querySelectorAll('.btn-add-dashed, .btn-add-row, [id^="tfoot-add-"]').forEach(el => {
            el.remove();
        });

        // Update header ratee inputs based on phase permissions and attach live styling listeners
        ['ratee-name', 'ratee-position', 'ratee-dept', 'ratee-period', 'ratee-sign-name', 'ratee-sign-date', 'approver-name', 'approver-pos', 'approver-date', 'sig-ratee-name', 'sig-ratee-pos', 'sig-ratee-date', 'sig-dean-name', 'sig-dean-pos', 'sig-dean-date', 'sig-targets-prepared-date', 'sig-targets-approved-date', 'sig-eval-rated-date', 'sig-eval-conforme-date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = !canEditTargets;
                const updateStyle = function() {
                    el.style.color = '#0f172a';
                    el.style.borderColor = el.value.trim() !== '' ? '#94a3b8' : '#cbd5e1';
                };
                if (!el.dataset.hasStyleListener) {
                    el.dataset.hasStyleListener = '1';
                    el.addEventListener('input', () => {
                        updateStyle();
                        if (id === 'ratee-dept') {
                            const lh = document.getElementById('spms-letterhead-dept');
                            if (lh) {
                                lh.textContent = el.value.trim() || '<?= addslashes(esc($ownerInfo['dept'] ?? '')) ?>' || 'Office of the University President';
                            }
                        }
                        AppState.setDirty(true);
                        if (typeof window.syncSpmsActiveTab === 'function') {
                            window.syncSpmsActiveTab();
                        }
                    });
                    el.addEventListener('change', () => {
                        updateStyle();
                        if (id === 'ratee-dept') {
                            const lh = document.getElementById('spms-letterhead-dept');
                            if (lh) {
                                lh.textContent = el.value.trim() || '<?= addslashes(esc($ownerInfo['dept'] ?? '')) ?>' || 'Office of the University President';
                            }
                        }
                        AppState.setDirty(true);
                        if (typeof window.syncSpmsActiveTab === 'function') {
                            window.syncSpmsActiveTab();
                        }
                    });
                }
                updateStyle();
            }
        });

        // Setup live sync for header currency inputs
        document.querySelectorAll('.header-budget-currency').forEach(el => {
            if (!el.dataset.hasCurrencyListener) {
                el.dataset.hasCurrencyListener = '1';
                el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
                el.addEventListener('input', function() {
                    const val = this.value || '₱';
                    this.style.width = Math.max(18, (this.value.length + 1) * 6.5) + 'px';
                    window.currentDocCurrency = val;
                    document.querySelectorAll('.header-budget-currency').forEach(other => {
                        if (other !== this) other.value = this.value;
                    });
                    document.querySelectorAll('.field-budget-currency').forEach(rowCur => {
                        rowCur.value = val;
                        rowCur.style.width = Math.max(18, (val.length + 1) * 6.5) + 'px';
                        const bInput = rowCur.closest('.budget-input-wrapper')?.querySelector('.field-budget');
                        if (bInput) adjustBudgetFontSize(bInput);
                    });
                    AppState.setDirty(true);
                    if (typeof window.syncSpmsActiveTab === 'function') {
                        window.syncSpmsActiveTab();
                    }
                });
                el.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.value = '₱';
                        this.dispatchEvent(new Event('input'));
                    }
                });
            }
        });

        if (typeof applyAllCellCommentBadges === 'function') {
            applyAllCellCommentBadges();
        }

        if (typeof autoExpandAllRowTextareas === 'function') {
            autoExpandAllRowTextareas();
        }
    }

    // ==========================================
    // SCORE ATTRIBUTION & DISTINCTION HELPERS
    // ==========================================
    function getCurrentScoreActor() {
        if (window.isTwgUser) return 'twg';
        if (!window.isOwner) return 'head';
        return 'self';
    }

    function getScoreAttribution(fieldKey, data) {
        const val = (data[fieldKey] !== undefined && data[fieldKey] !== null && data[fieldKey] !== '') ? data[fieldKey] : '';
        let selfVal = data[fieldKey + '_self'];
        let headVal = data[fieldKey + '_head'];
        let twgVal = data[fieldKey + '_twg'];
        let lastActor = data['last_rated_by_' + fieldKey] || '';

        // Backward compatibility: If selfVal is not set, initialize based on current data
        if (selfVal === undefined || selfVal === null) {
            if (val !== '' && val !== 'X') {
                if (headVal || twgVal) {
                    selfVal = '';
                } else {
                    selfVal = val;
                    if (!lastActor) lastActor = 'self';
                }
            } else {
                selfVal = '';
            }
        }
        headVal = (headVal !== undefined && headVal !== null) ? headVal : '';
        twgVal = (twgVal !== undefined && twgVal !== null) ? twgVal : '';

        if (!lastActor && val !== '' && val !== 'X') {
            if (twgVal !== '') lastActor = 'twg';
            else if (headVal !== '') lastActor = 'head';
            else if (selfVal !== '') lastActor = 'self';
        }

        return {
            val: val,
            self: selfVal,
            head: headVal,
            twg: twgVal,
            lastActor: lastActor
        };
    }

    function checkAdjustmentsAndToggleApproveButton() {
        const actor = getCurrentScoreActor();
        if (actor === 'self') return; // Ratee submits, doesn't approve

        const scoreInputs = document.querySelectorAll('.spms-score-input');
        let adjustedCount = 0;

        scoreInputs.forEach(input => {
            const val = String(input.value || '').trim();
            if (!val || val === 'X' || val === 'N/A' || val === '—') return;

            const selfScore = String(input.dataset.selfScore || '').trim();
            const headScore = String(input.dataset.headScore || '').trim();

            if (actor === 'twg') {
                const baseline = headScore || selfScore;
                if (baseline && baseline !== 'X' && baseline !== 'N/A' && baseline !== val) {
                    adjustedCount++;
                }
            } else if (actor === 'head') {
                if (selfScore && selfScore !== 'X' && selfScore !== 'N/A' && selfScore !== val) {
                    adjustedCount++;
                }
            }
        });

        // Supervisor / HoO / TWG / PMT Approve button
        const btnApprove = document.getElementById('btn-approve');
        if (btnApprove) {
            if (adjustedCount > 0) {
                btnApprove.disabled = true;
                btnApprove.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                btnApprove.classList.remove('hover:bg-success-600', 'active:scale-[0.98]', 'cursor-pointer');
                btnApprove.title = `Cannot approve directly: ${adjustedCount} adjusted score(s) present. Return for revision to notify the ratee, or revert adjustments to approve.`;
            } else {
                btnApprove.disabled = false;
                btnApprove.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                btnApprove.classList.add('hover:bg-success-600', 'active:scale-[0.98]', 'cursor-pointer');
                btnApprove.title = 'Approve';
            }
        }
    }

    function renderScoreBadgeHtml(attr, currentScore) {
        return '';
    }

    function renderScoreCellTdHtml(fieldKey, data, evalDisabled, canEditTargets, canEditEvaluation) {
        const attr = getScoreAttribution(fieldKey, data);
        const isX = attr.val === 'X' || attr.val === 'N/A';
        const fieldClass = 'field-' + fieldKey;

        // In Target Drafting mode: the entire cell toggles the dimension's N/A status
        if (canEditTargets) {
            return `
                <td style="padding: 0; text-align: center; vertical-align: middle; border: 1px solid #000; position: relative;"
                    class="spms-score-cell qte-toggleable ${isX ? 'qte-na' : ''}" data-field-name="${fieldKey}"
                    tabindex="0" role="switch" aria-checked="${isX ? 'false' : 'true'}"
                    onclick="toggleQte(this, '${fieldClass}')"
                    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleQte(this, '${fieldClass}'); }"
                    title="${isX ? 'Not Applicable — click to enable this dimension' : 'Click to mark this dimension as Not Applicable (N/A)'}">
                    <input type="text" 
                        value="${isX ? 'N/A' : (attr.val || '')}" 
                        placeholder="—" 
                        data-field="${fieldKey}"
                        data-self-score="${escapeHtml(attr.self)}"
                        data-head-score="${escapeHtml(attr.head)}"
                        data-twg-score="${escapeHtml(attr.twg)}"
                        data-last-actor="${escapeHtml(attr.lastActor)}"
                        data-eval-disabled="${evalDisabled}"
                        disabled
                        tabindex="-1"
                        class="spms-score-input ${fieldClass}">
                    
                    ${renderScoreBadgeHtml(attr, attr.val)}
                </td>
            `;
        }

        // In Evaluation Phase / Review / Viewing / Printing (when NOT editing targets):
        // NO toggle button is rendered at all!
        if (isX) {
            return `
                <td style="padding: 0; text-align: center; vertical-align: middle; border: 1px solid #000; position: relative;" class="spms-score-cell qte-na" data-field-name="${fieldKey}">
                    <input type="text" 
                        value="N/A" 
                        data-field="${fieldKey}"
                        data-self-score="X"
                        data-head-score="X"
                        data-twg-score="X"
                        data-last-actor=""
                        data-eval-disabled="true"
                        disabled 
                        title="Not Applicable for this deliverable" 
                        class="spms-score-input ${fieldClass}">
                </td>
            `;
        }

        return `
            <td style="padding: 0; text-align: center; vertical-align: middle; border: 1px solid #000; position: relative;" class="spms-score-cell" data-field-name="${fieldKey}">
                <input type="number" min="1" max="5" step="1" 
                    value="${attr.val}" 
                    placeholder="—" 
                    data-field="${fieldKey}"
                    data-self-score="${escapeHtml(attr.self)}"
                    data-head-score="${escapeHtml(attr.head)}"
                    data-twg-score="${escapeHtml(attr.twg)}"
                    data-last-actor="${escapeHtml(attr.lastActor)}"
                    data-eval-disabled="${evalDisabled}"
                    ${evalDisabled ? 'disabled title="Locked"' : 'title="Enter 1 to 5" oninput="handleScoreInput(this)" onkeydown="handleScoreKeydown(event, this)"'} 
                    class="spms-score-input ${fieldClass}">
                
                ${renderScoreBadgeHtml(attr, attr.val)}
            </td>
        `;
    }

    function updateScoreCellBadge(td, input) {
        if (!td) return;
        const existingMarker = td.querySelector('.score-notif-marker');
        if (existingMarker) existingMarker.remove();

        if (typeof checkAdjustmentsAndToggleApproveButton === 'function') {
            checkAdjustmentsAndToggleApproveButton();
        }
    }

    function openScoreAuditModal(el, event) {
        if (event) event.stopPropagation();
    }

    function closeScoreAuditModal(event) {
        if (event) event.stopPropagation();
    }

    // ==========================================
    // ROW-SYNCHRONIZED AUTO-EXPANSION (Uniform Height across all Columns)
    // ==========================================
    function autoExpandRow(tr) {
        if (!tr || tr.nodeType !== Node.ELEMENT_NODE) return;
        const minH = 68;
        const textareas = Array.from(tr.querySelectorAll('.spms-textarea'));
        if (textareas.length === 0) return;

        // Reset height on all textareas and column wrappers in this row so scrollHeight can accurately shrink when lines are deleted
        textareas.forEach(ta => {
            ta.style.height = 'auto';
            ta.style.minHeight = minH + 'px';
        });
        tr.querySelectorAll('.budget-input-wrapper').forEach(bw => {
            bw.style.height = 'auto';
            bw.style.minHeight = minH + 'px';
        });

        let maxContentH = minH;
        textareas.forEach(ta => {
            const sh = ta.scrollHeight;
            if (sh > maxContentH) {
                maxContentH = sh;
            }
        });

        const targetHStr = Math.max(minH, Math.ceil(maxContentH)) + 'px';

        // Apply uniform height to all textareas in this row
        textareas.forEach(ta => {
            ta.style.height = targetHStr;
            ta.style.minHeight = targetHStr;
        });

        // Accomplishments column container
        tr.querySelectorAll('.spms-cell-accomplishments > div').forEach(div => {
            div.style.minHeight = targetHStr;
            div.style.height = targetHStr;
        });

        // Budget column wrapper
        tr.querySelectorAll('.budget-input-wrapper').forEach(bw => {
            bw.style.minHeight = targetHStr;
            bw.style.height = targetHStr;
        });

        // Score inputs & score cells
        tr.querySelectorAll('.spms-score-input').forEach(si => {
            si.style.minHeight = targetHStr;
            si.style.height = targetHStr;
        });

        // Row average wrapper
        tr.querySelectorAll('.field-row-avg-wrapper').forEach(rw => {
            rw.style.minHeight = targetHStr;
            rw.style.height = targetHStr;
        });
    }

    function autoExpandRowTextarea(ta) {
        if (!ta) return;
        const tr = ta.closest('tr');
        if (tr) autoExpandRow(tr);
    }

    function autoExpandAllRowTextareas() {
        const rows = new Set();
        document.querySelectorAll('.spms-table .spms-textarea').forEach(ta => {
            const tr = ta.closest('tr');
            if (tr) rows.add(tr);
        });
        rows.forEach(tr => autoExpandRow(tr, true));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoExpandAllRowTextareas);
    } else {
        autoExpandAllRowTextareas();
    }

    window.addEventListener('resize', autoExpandAllRowTextareas);

    function addTableRow(category, rowData = null, referenceRow = null, insertPosition = 'append') {
        if (!canEditTargets && !rowData) return;
        const tbody = document.getElementById(`tbody-${category}`);
        if (!tbody && !referenceRow) return;

        const data = rowData || (window.isCurrentDocIperf ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: "",
            twg_comment: ""
        } : (window.isCurrentDocDpcr ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            budget: "",
            budget_currency: "₱",
            accountable: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: "",
            twg_comment: "",
            std_5: "", std_4: "", std_3: "", std_2: "", std_1: ""
        } : (window.isCurrentDocOpcr ? {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            budget: "",
            budget_currency: "₱",
            accountable: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: "",
            twg_comment: ""
        } : {
            row_id: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7),
            mfo: "",
            indicators: "",
            accomplishments: "",
            q: "", t: "", e: "",
            remarks: "",
            twg_comment: ""
        })));

        const rowId = data.row_id || ('row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7));
        const rawBudget = (data.budget !== undefined && data.budget !== null && String(data.budget).trim() !== '')
            ? sanitizeBudgetInput(String(data.budget))
            : '';
        const numBudget = parseFloat(rawBudget);
        const fullBudget = !isNaN(numBudget) ? formatBudgetFull(numBudget) : rawBudget;
        const displayBudget = fullBudget;
        const rowCurrency = data.budget_currency || (typeof window !== 'undefined' && window.currentDocCurrency) || '₱';
        const rowFiles = (window.documentAttachments && window.documentAttachments[rowId]) || [];
        const fileCount = rowFiles.length;
        const hasFiles = fileCount > 0;

        const mfoDisabled = !canEditTargets;
        const evalDisabled = !canEditEvaluation;
        const canEditTwg = window.canEditTwg;
        const isTwgUser = window.isTwgUser;

        const tr = document.createElement('tr');
        tr.className = `table-row-${category}`;
        tr.dataset.rowId = rowId;
        tr.dataset.remarks = data.remarks || '';
        tr.dataset.twgComment = data.twg_comment || '';
        tr.id = rowId;
        tr.style.borderBottom = '1px solid #000000';

        if (window.isCurrentDocIperf) {
            tr.innerHTML = `
                <!-- 1. Office PPA (Target Phase) -->
                <td class="spms-cell-input spms-cell-mfo" data-field-name="mfo" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter office/project/programs/activities aligned with office deliverables..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Expected Outputs (Target Phase) -->
                <td class="spms-cell-input spms-cell-indicators" data-field-name="indicators" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter expected outputs based on contract or duties..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Actual Accomplishments (Evaluation Phase) -->
                <td class="spms-cell-input spms-cell-accomplishments" data-field-name="accomplishments" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <div style="display: flex; flex-direction: column; width: 100%; height: 100%;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''} style="flex: 1 1 auto;">${escapeHtml(data.accomplishments)}</textarea>
                        
                        <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                        ${isEvaluationPhase ? `
                        <div class="mov-toolbar flex items-center justify-between text-[10px] print-hide" style="padding: 3px 6px; background: #f8fafc; border-top: 1px dashed #cbd5e1; flex-shrink: 0;">
                            ${hasFiles ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                    title="View Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">MOV (${fileCount})</span>
                            </button>
                            ` : (canEditEvaluation ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-zinc-200 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700" 
                                    title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">Attach MOV</span>
                            </button>
                            ` : `
                            <span class="text-[9px] text-zinc-400 italic">No MOV</span>
                            `)}
                            <span class="text-[9px] text-zinc-400 font-medium italic">Evidence</span>
                        </div>
                        ` : ''}
                    </div>
                </td>

                <!-- 4. Rating Q (Evaluation Phase) -->
                ${renderScoreCellTdHtml('q', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- 5. Rating T (Evaluation Phase) -->
                ${renderScoreCellTdHtml('t', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- 6. Rating E (Evaluation Phase) -->
                ${renderScoreCellTdHtml('e', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- 7. Row Average -->
                <td style="padding: 0; text-align: center; vertical-align: middle; background: #f8fafc; border: 1px solid #000;">
                    <div class="field-row-avg-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: var(--spms-row-min-height, 68px);">
                        <span class="field-row-avg" style="font-weight: 900; color: #0f172a; font-size: 11px;">—</span>
                    </div>
                </td>
            `;
        } else if (window.isCurrentDocDpcr) {
            tr.innerHTML = `
                <!-- 1. Programs, Projects, Activities (Target Phase) -->
                <td class="spms-cell-input spms-cell-mfo" data-field-name="mfo" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter programs, projects, activities..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Success Indicators (Target Phase) -->
                <td class="spms-cell-input spms-cell-indicators" data-field-name="indicators" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget (Target Phase) - Decimal with Editable Currency -->
                <td class="spms-cell-input spms-cell-budget" data-field-name="budget" style="padding: 0; vertical-align: middle; text-align: center; border: 1px solid #000; position: relative; height: 100%;">
                    <div class="budget-input-wrapper" onclick="const inp=this.querySelector('.field-budget'); if(inp && !inp.disabled && document.activeElement !== inp && document.activeElement !== this.querySelector('.field-budget-currency')) inp.focus();">
                        <div class="budget-input-inner flex items-center justify-center gap-1" style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; max-width: 150px; width: 100%; height: 100%;">
                            <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; cursor: pointer; flex-shrink: 0; border: none; background: transparent; outline: none;" ${mfoDisabled ? 'disabled' : ''}>
                            <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; font-size: 11px; text-align: center; font-family: inherit; font-weight: 500; color: #0f172a; border: none; background: transparent; outline: none; transition: font-size 0.1s ease;" ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>
                        </div>
                    </div>
                </td>

                <!-- 4. Individuals / Offices Accountable (Target Phase) -->
                <td class="spms-cell-input spms-cell-accountable" data-field-name="accountable" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Individuals / offices accountable..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- 5. Actual Accomplishments (Evaluation Phase) -->
                <td class="spms-cell-input spms-cell-accomplishments" data-field-name="accomplishments" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <div style="display: flex; flex-direction: column; width: 100%; height: 100%;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''} style="flex: 1 1 auto;">${escapeHtml(data.accomplishments)}</textarea>
                        
                        <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                        ${isEvaluationPhase ? `
                        <div class="mov-toolbar flex items-center justify-between text-[10px] print-hide" style="padding: 3px 6px; background: #f8fafc; border-top: 1px dashed #cbd5e1; flex-shrink: 0;">
                            ${hasFiles ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                    title="View Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">MOV (${fileCount})</span>
                            </button>
                            ` : (canEditEvaluation ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-zinc-200 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700" 
                                    title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">Attach MOV</span>
                            </button>
                            ` : `
                            <span class="text-[9px] text-zinc-400 italic">No MOV</span>
                            `)}
                            <span class="text-[9px] text-zinc-400 font-medium italic">Evidence</span>
                        </div>
                        ` : ''}
                    </div>
                </td>

                <!-- Rating Q, T, E Inputs (Evaluation Phase) -->
                ${renderScoreCellTdHtml('q', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('t', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('e', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- Row Average -->
                <td style="padding: 0; text-align: center; vertical-align: middle; background: #f8fafc; border: 1px solid #000;">
                    <div class="field-row-avg-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: var(--spms-row-min-height, 68px);">
                        <span class="field-row-avg" style="font-weight: 900; color: #0f172a; font-size: 11px;">—</span>
                    </div>
                </td>
            `;
        } else if (window.isCurrentDocOpcr) {
            tr.innerHTML = `
                <!-- 1. Programs, Projects, Activities (Target Phase) -->
                <td class="spms-cell-input spms-cell-mfo" data-field-name="mfo" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter project, program, activities..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- 2. Success Indicators (Targets + Measures) Performance (Target Phase) -->
                <td class="spms-cell-input spms-cell-indicators" data-field-name="indicators" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- 3. Allotted Budget (Target Phase) - Decimal with Editable Currency -->
                <td class="spms-cell-input spms-cell-budget" data-field-name="budget" style="padding: 0; vertical-align: middle; text-align: center; border: 1px solid #000; position: relative; height: 100%;">
                    <div class="budget-input-wrapper" onclick="const inp=this.querySelector('.field-budget'); if(inp && !inp.disabled && document.activeElement !== inp && document.activeElement !== this.querySelector('.field-budget-currency')) inp.focus();">
                        <div class="budget-input-inner flex items-center justify-center gap-1" style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; max-width: 150px; width: 100%; height: 100%;">
                            <input type="text" class="field-budget-currency" value="${escapeHtml(rowCurrency)}" placeholder="₱" title="Currency (editable, defaults to ₱)" style="width: 18px; text-align: center; font-weight: 700; color: #0284c7; cursor: pointer; flex-shrink: 0; border: none; background: transparent; outline: none;" ${mfoDisabled ? 'disabled' : ''}>
                            <input type="text" inputmode="decimal" spellcheck="false" autocomplete="off" class="field-budget" maxlength="18" data-raw-value="${escapeHtml(rawBudget)}" data-full-value="${escapeHtml(fullBudget)}" value="${escapeHtml(fullBudget)}" placeholder="0.00" style="flex: 1; min-width: 0; width: 100%; font-size: 11px; text-align: center; font-family: inherit; font-weight: 500; color: #0f172a; border: none; background: transparent; outline: none; transition: font-size 0.1s ease;" ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>
                        </div>
                    </div>
                </td>

                <!-- 4. Divisions Accountable (Target Phase) -->
                <td class="spms-cell-input spms-cell-accountable" data-field-name="accountable" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-accountable" rows="3" placeholder="Divisions accountable..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.accountable || '')}</textarea>
                </td>

                <!-- 5. Actual Accomplishment (Evaluation Phase) -->
                <td class="spms-cell-input spms-cell-accomplishments" data-field-name="accomplishments" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <div style="display: flex; flex-direction: column; width: 100%; height: 100%;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishment..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''} style="flex: 1 1 auto;">${escapeHtml(data.accomplishments)}</textarea>
                        
                        <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                        ${isEvaluationPhase ? `
                        <div class="mov-toolbar flex items-center justify-between text-[10px] print-hide" style="padding: 3px 6px; background: #f8fafc; border-top: 1px dashed #cbd5e1; flex-shrink: 0;">
                            ${hasFiles ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                    title="View Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">MOV (${fileCount})</span>
                            </button>
                            ` : (canEditEvaluation ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-zinc-200 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700" 
                                    title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">Attach MOV</span>
                            </button>
                            ` : `
                            <span class="text-[9px] text-zinc-400 italic">No MOV</span>
                            `)}
                            <span class="text-[9px] text-zinc-400 font-medium italic">Evidence</span>
                        </div>
                        ` : ''}
                    </div>
                </td>

                <!-- Rating Q, T, E Inputs (Evaluation Phase) -->
                ${renderScoreCellTdHtml('q', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('t', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('e', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- Row Average -->
                <td style="padding: 0; text-align: center; vertical-align: middle; background: #f8fafc; border: 1px solid #000;">
                    <div class="field-row-avg-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: var(--spms-row-min-height, 68px);">
                        <span class="field-row-avg" style="font-weight: 900; color: #0f172a; font-size: 11px;">—</span>
                    </div>
                </td>
            `;
        } else {
            tr.innerHTML = `
                <!-- Major Final Output (Only modifiable during Target Phase) -->
                <td class="spms-cell-input spms-cell-mfo" data-field-name="mfo" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-mfo" rows="3" placeholder="Enter academic function / major final output..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.mfo)}</textarea>
                </td>

                <!-- Success Indicators (Only modifiable during Target Phase) -->
                <td class="spms-cell-input spms-cell-indicators" data-field-name="indicators" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <textarea class="spms-textarea field-indicators" rows="3" placeholder="Enter success indicators (targets + measures)..." ${mfoDisabled ? 'disabled title="Locked (Only modifiable during Target Phase)"' : ''}>${escapeHtml(data.indicators)}</textarea>
                </td>

                <!-- Actual Accomplishments (Unlocked during evaluation phase) -->
                <td class="spms-cell-input spms-cell-accomplishments" data-field-name="accomplishments" style="padding: 0; vertical-align: top; border: 1px solid #000; position: relative;">
                    <div style="display: flex; flex-direction: column; width: 100%; height: 100%;">
                        <textarea class="spms-textarea field-accomplishments" rows="3" placeholder="Enter actual accomplishments..." ${evalDisabled ? 'disabled title="Locked during Target Phase (Unlocked during Evaluation Phase)"' : ''} style="flex: 1 1 auto;">${escapeHtml(data.accomplishments)}</textarea>
                        
                        <!-- MOV Evidence Toolbar (Only visible during Evaluation Phase) -->
                        ${isEvaluationPhase ? `
                        <div class="mov-toolbar flex items-center justify-between text-[10px] print-hide" style="padding: 3px 6px; background: #f8fafc; border-top: 1px dashed #cbd5e1; flex-shrink: 0;">
                            ${hasFiles ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25" 
                                    title="View Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">MOV (${fileCount})</span>
                            </button>
                            ` : (canEditEvaluation ? `
                            <button type="button" onclick="openMovModal('${rowId}', this)" id="btn-mov-${rowId}" 
                                    class="btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-zinc-200 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700" 
                                    title="Upload Means of Verification (MOV) evidence proof for this accomplishment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="mov-btn-text">Attach MOV</span>
                            </button>
                            ` : `
                            <span class="text-[9px] text-zinc-400 italic">No MOV</span>
                            `)}
                            <span class="text-[9px] text-zinc-400 font-medium italic">Evidence</span>
                        </div>
                        ` : ''}
                    </div>
                </td>

                <!-- Rating Q, T, E Inputs (Unlocked during evaluation phase) -->
                ${renderScoreCellTdHtml('q', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('t', data, evalDisabled, canEditTargets, canEditEvaluation)}
                ${renderScoreCellTdHtml('e', data, evalDisabled, canEditTargets, canEditEvaluation)}

                <!-- Row Average -->
                <td style="padding: 0; text-align: center; vertical-align: middle; background: #f8fafc; border: 1px solid #000;">
                    <div class="field-row-avg-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: var(--spms-row-min-height, 68px);">
                        <span class="field-row-avg" style="font-weight: 900; color: #0f172a; font-size: 11px;">—</span>
                    </div>
                </td>
            `;
        }

        if (referenceRow && referenceRow.parentNode) {
            if (insertPosition === 'above') {
                referenceRow.parentNode.insertBefore(tr, referenceRow);
            } else {
                referenceRow.parentNode.insertBefore(tr, referenceRow.nextSibling);
            }
        } else if (tbody) {
            tbody.appendChild(tr);
        }

        if (typeof bindCellContextMenu === 'function') {
            bindCellContextMenu(tr);
        }
        if (typeof applyCellCommentBadgesToRow === 'function') {
            applyCellCommentBadgesToRow(tr);
        }

        // Track changes for autoSave and bind row-synchronized auto-expanding
        autoExpandRow(tr);
        tr.querySelectorAll('.spms-textarea').forEach(ta => {
            ta.addEventListener('input', function() {
                autoExpandRow(tr);
            });
            ta.addEventListener('cut', function() {
                setTimeout(() => autoExpandRow(tr), 0);
            });
            ta.addEventListener('paste', function() {
                setTimeout(() => autoExpandRow(tr), 0);
            });
            ta.addEventListener('change', function() {
                autoExpandRow(tr);
            });
        });

        tr.querySelectorAll('textarea, input').forEach(el => {
            const sync = () => {
                window.syncSpmsActiveTab();
                if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') AppState.setDirty(true);
            };
            el.addEventListener('input', sync);
            el.addEventListener('change', sync);
        });

        // Decimal formatting and currency sync for Budget inputs
        const budgetInput = tr.querySelector('.field-budget');
        const curInput = tr.querySelector('.field-budget-currency');
        if (budgetInput) {
            budgetInput.setAttribute('spellcheck', 'false');
            budgetInput.setAttribute('autocomplete', 'off');
            budgetInput.setAttribute('maxlength', '16');
            adjustBudgetFontSize(budgetInput);
        }
        if (curInput) {
            const syncW = (el) => {
                el.style.width = Math.max(18, (el.value.length + 1) * 6.5) + 'px';
            };
            syncW(curInput);
            curInput.addEventListener('input', function() {
                syncW(this);
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
            curInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '₱';
                    syncW(this);
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
                if (budgetInput) adjustBudgetFontSize(budgetInput);
            });
        }

        recalculateForm();
    }

    function deleteTableRow(target) {
        if (!target) return;
        const tr = target.tagName === 'TR' ? target : target.closest('tr');
        if (!tr) return;
        window._userExplicitlyDeletedRow = true;
        tr.remove();
        recalculateForm();
        window.syncSpmsActiveTab();
        if (typeof AppState !== 'undefined' && typeof AppState.setDirty === 'function') {
            AppState.setDirty(true);
        }
    }

    function clearScore(input) {
        input.value = '';
        const actor = getCurrentScoreActor();
        if (actor === 'self') input.dataset.selfScore = '';
        else if (actor === 'head') input.dataset.headScore = '';
        else if (actor === 'twg') input.dataset.twgScore = '';
        input.dataset.lastActor = actor;

        updateScoreCellBadge(input.closest('td'), input);
        recalculateForm();
        window.syncSpmsActiveTab();
        AppState.setDirty(true);
    }

    function handleScoreKeydown(e, input) {
        if (e.key === 'Escape') {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // Disallow non-numeric keys: 'e', 'E', '+', '-', '.', ','
        if (['e', 'E', '+', '-', '.', ','].includes(e.key)) {
            e.preventDefault();
            return;
        }
        // Disallow '0' directly since SPMS ratings are 1 to 5
        if (e.key === '0') {
            e.preventDefault();
            return;
        }
        // ArrowDown at 1 or blank clears back to blank
        if (e.key === 'ArrowDown' && (input.value === '1' || input.value === '0' || input.value === '')) {
            e.preventDefault();
            clearScore(input);
            return;
        }
        // ArrowUp when blank starts at 1
        if (e.key === 'ArrowUp' && (input.value === '' || input.value === null)) {
            e.preventDefault();
            input.value = '1';
            handleScoreInput(input);
            return;
        }
        // ArrowUp at 5 stays at 5
        if (e.key === 'ArrowUp' && input.value === '5') {
            e.preventDefault();
            return;
        }
    }

    function handleScoreInput(input) {
        let raw = input.value.trim();
        if (raw === '') {
            input.value = '';
            const actor = getCurrentScoreActor();
            if (actor === 'self') input.dataset.selfScore = '';
            else if (actor === 'head') input.dataset.headScore = '';
            else if (actor === 'twg') input.dataset.twgScore = '';
            input.dataset.lastActor = actor;

            updateScoreCellBadge(input.closest('td'), input);
            recalculateForm();
            window.syncSpmsActiveTab();
            AppState.setDirty(true);
            return;
        }
        // If user typed a new digit while one already existed (e.g. was 5, typed 3 -> "53"),
        // take the newly typed digit if it's 1-5 so replacing digits feels effortless
        if (raw.length > 1) {
            const lastChar = raw.slice(-1);
            if (['1', '2', '3', '4', '5'].includes(lastChar)) {
                raw = lastChar;
            }
        }
        const num = parseInt(raw, 10);
        // Only allow 1 to 5. If 0, > 5, or invalid, clear to blank
        if (isNaN(num) || num < 1 || num > 5) {
            input.value = '';
        } else {
            input.value = num;
        }

        const actor = getCurrentScoreActor();
        if (actor === 'self') input.dataset.selfScore = input.value;
        else if (actor === 'head') input.dataset.headScore = input.value;
        else if (actor === 'twg') input.dataset.twgScore = input.value;
        input.dataset.lastActor = actor;

        updateScoreCellBadge(input.closest('td'), input);

        recalculateForm();
        window.syncSpmsActiveTab();
        AppState.setDirty(true);
    }

    function parseWholeScore(val) {
        if (val === null || val === undefined) return null;
        const str = String(val).trim();
        if (str === '') return null;
        const num = parseInt(str, 10);
        if (isNaN(num) || num < 1 || num > 5) return null;
        return num;
    }

    function toggleQte(el, fieldClass) {
        if (!canEditTargets) return;

        const td = el.closest('td');
        if (!td) return;
        const input = td.querySelector('.' + fieldClass);
        if (!input) return;
        const isX = (input.value === 'X' || input.value === 'N/A');
        const actor = getCurrentScoreActor();
        const dim = fieldClass.replace('field-', '');
        
        if (isX) {
            td.classList.remove('qte-na', 'bg-zinc-100', 'dark:bg-zinc-800/40');
            td.setAttribute('aria-checked', 'true');
            td.title = 'Click to mark this dimension as Not Applicable (N/A)';
            input.value = '';
            input.type = 'text';
            input.placeholder = '—';
            input.className = `spms-score-input ${fieldClass}`;
            input.disabled = true;
            
            if (actor === 'self') input.dataset.selfScore = '';
            else if (actor === 'head') input.dataset.headScore = '';
            else if (actor === 'twg') input.dataset.twgScore = '';
            input.dataset.lastActor = actor;

            // Purge any 'N/A' placeholder text that leaked into this row's rubric criteria
            const tr = td.closest('tr');
            const rowId = tr ? (tr.dataset.rowId || tr.id) : null;
            const rubrics = window.digitalRubricsData;
            if (rowId && rubrics && rubrics[rowId]) {
                [5, 4, 3, 2, 1].forEach(sc => {
                    const cell = rubrics[rowId][sc];
                    if (cell && typeof cell[dim] === 'string' && cell[dim].trim().toUpperCase() === 'N/A') {
                        cell[dim] = '';
                    }
                });
                if (tabs[0]) {
                    if (!tabs[0].formData) tabs[0].formData = {};
                    tabs[0].formData.rubrics = rubrics;
                }
            }
        } else {
            td.classList.add('qte-na');
            td.classList.remove('spms-cell-active');
            if (window.activeSelectedCell === td) {
                window.activeSelectedCell = null;
            }
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            td.setAttribute('aria-checked', 'false');
            td.title = 'Not Applicable — click to enable this dimension';
            input.type = 'text';
            input.value = 'N/A';
            input.className = `spms-score-input ${fieldClass}`;
            input.disabled = true;

            if (actor === 'self') input.dataset.selfScore = 'X';
            else if (actor === 'head') input.dataset.headScore = 'X';
            else if (actor === 'twg') input.dataset.twgScore = 'X';
            input.dataset.lastActor = actor;
        }

        td.classList.remove('qte-just-toggled');
        void td.offsetWidth;
        td.classList.add('qte-just-toggled');
        
        updateScoreCellBadge(td, input);
        recalculateForm();
        if (typeof renderDigitalRubricsMatrix === 'function') {
            renderDigitalRubricsMatrix();
        }
        if (typeof saveDocument === 'function') {
            saveDocument(true);
        }
    }

    function recalculateForm() {
        if (window.isCurrentDocIperf) {
            const rows = document.querySelectorAll('.table-row-core');
            let sumOfRowAvgs = 0;
            let count = 0;

            rows.forEach(row => {
                const q = parseWholeScore(row.querySelector('.field-q')?.value);
                const t = parseWholeScore(row.querySelector('.field-t')?.value);
                const e = parseWholeScore(row.querySelector('.field-e')?.value);

                let rowSum = 0;
                let rowInputs = 0;

                if (q !== null) { rowSum += q; rowInputs++; }
                if (t !== null) { rowSum += t; rowInputs++; }
                if (e !== null) { rowSum += e; rowInputs++; }

                let rowAvg = rowInputs > 0 ? (rowSum / rowInputs) : 0;
                const avgEl = row.querySelector('.field-row-avg');
                if (avgEl) avgEl.innerText = rowInputs > 0 ? rowAvg.toFixed(2) : '—';

                if (rowInputs > 0) {
                    sumOfRowAvgs += rowAvg;
                    count++;
                }
            });

            let overallAvg = count > 0 ? (sumOfRowAvgs / count) : 0;
            const iperfAvgEl = document.getElementById('iperf-overall-average');
            if (iperfAvgEl) iperfAvgEl.innerText = count > 0 ? overallAvg.toFixed(3) : '0.000';

            let adjectival = getAdjectivalRating(overallAvg, count);
            const iperfBadgeEl = document.getElementById('iperf-adjectival-badge');
            if (iperfBadgeEl) {
                iperfBadgeEl.innerText = adjectival.text;
                iperfBadgeEl.style.background = adjectival.color;
            }

            // Sync with grand-score and adjectival-badge
            const gScoreEl = document.getElementById('grand-score');
            if (gScoreEl) gScoreEl.innerText = count > 0 ? overallAvg.toFixed(3) : '0.000';
            const badgeEl = document.getElementById('adjectival-badge');
            if (badgeEl) {
                badgeEl.innerText = adjectival.text;
                badgeEl.style.background = adjectival.color;
            }
            if (typeof checkAdjustmentsAndToggleApproveButton === 'function') {
                checkAdjustmentsAndToggleApproveButton();
            }
            return;
        }

        let categoryResults = {
            core: calculateCategory('core'),
            strategic: calculateCategory('strategic'),
            support: calculateCategory('support')
        };

        let totalRated = categoryResults.core.count + categoryResults.strategic.count + categoryResults.support.count;

        // Update Subtotal Badges
        const bCore = document.getElementById('badge-core-subtotal');
        const bStrat = document.getElementById('badge-strategic-subtotal');
        const bSupp = document.getElementById('badge-support-subtotal');
        if (bCore) bCore.innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        if (bStrat) bStrat.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        if (bSupp) bSupp.innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';

        // Update Summary Section Breakdown
        const sCoreScore = document.getElementById('sum-core-score');
        const sCoreAvg = document.getElementById('sum-core-avg');
        if (sCoreScore) sCoreScore.innerText = categoryResults.core.count > 0 ? categoryResults.core.subtotal.toFixed(3) : '0.000';
        if (sCoreAvg) sCoreAvg.innerText = categoryResults.core.count > 0 ? categoryResults.core.avg.toFixed(3) : '0.000';

        const sStratScore = document.getElementById('sum-strategic-score');
        const sStratAvg = document.getElementById('sum-strategic-avg');
        if (sStratScore) sStratScore.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.subtotal.toFixed(3) : '0.000';
        if (sStratAvg) sStratAvg.innerText = categoryResults.strategic.count > 0 ? categoryResults.strategic.avg.toFixed(3) : '0.000';

        const sSuppScore = document.getElementById('sum-support-score');
        const sSuppAvg = document.getElementById('sum-support-avg');
        if (sSuppScore) sSuppScore.innerText = categoryResults.support.count > 0 ? categoryResults.support.subtotal.toFixed(3) : '0.000';
        if (sSuppAvg) sSuppAvg.innerText = categoryResults.support.count > 0 ? categoryResults.support.avg.toFixed(3) : '0.000';

        // Calculate Grand Final Rating
        let grandScore = categoryResults.core.subtotal + categoryResults.strategic.subtotal + categoryResults.support.subtotal;
        const gScoreEl = document.getElementById('grand-score');
        if (gScoreEl) gScoreEl.innerText = totalRated > 0 ? grandScore.toFixed(3) : '0.000';

        // Calculate Adjectival Rating
        let adjectival = getAdjectivalRating(grandScore, totalRated);
        const badgeEl = document.getElementById('adjectival-badge');
        if (badgeEl) {
            badgeEl.innerText = adjectival.text;
            badgeEl.style.background = adjectival.color;
        }

        // Formula text
        const gFormulaEl = document.getElementById('grand-formula');
        if (gFormulaEl) {
            gFormulaEl.innerText = 
                `(Core ${categoryResults.core.subtotal.toFixed(3)} + Strategic ${categoryResults.strategic.subtotal.toFixed(3)} + Support ${categoryResults.support.subtotal.toFixed(3)})`;
        }

        if (typeof checkAdjustmentsAndToggleApproveButton === 'function') {
            checkAdjustmentsAndToggleApproveButton();
        }
    }

    function calculateCategory(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        if (rows.length === 0) {
            return { avg: 0, subtotal: 0, count: 0 };
        }

        let sumOfRowAvgs = 0;
        let count = 0;

        rows.forEach(row => {
            const q = parseWholeScore(row.querySelector('.field-q')?.value);
            const t = parseWholeScore(row.querySelector('.field-t')?.value);
            const e = parseWholeScore(row.querySelector('.field-e')?.value);

            let rowSum = 0;
            let rowInputs = 0;

            if (q !== null) { rowSum += q; rowInputs++; }
            if (t !== null) { rowSum += t; rowInputs++; }
            if (e !== null) { rowSum += e; rowInputs++; }

            let rowAvg = rowInputs > 0 ? (rowSum / rowInputs) : 0;
            const avgEl = row.querySelector('.field-row-avg');
            if (avgEl) avgEl.innerText = rowInputs > 0 ? rowAvg.toFixed(2) : '—';

            if (rowInputs > 0) {
                sumOfRowAvgs += rowAvg;
                count++;
            }
        });

        let categoryAvg = count > 0 ? (sumOfRowAvgs / count) : 0;
        let weight = CATEGORY_WEIGHTS[category] || 0;
        let subtotal = categoryAvg * weight;

        return {
            avg: categoryAvg,
            subtotal: subtotal,
            count: count
        };
    }

    function getAdjectivalRating(score, totalRated = 1) {
        let res;
        if (totalRated === 0) res = { text: 'PENDING EVALUATION', color: '#64748b' };
        else if (score >= 4.500) res = { text: 'OUTSTANDING', color: '#059669' };
        else if (score >= 3.500) res = { text: 'VERY SATISFACTORY', color: '#2563eb' };
        else if (score >= 2.500) res = { text: 'SATISFACTORY', color: '#d97706' };
        else if (score >= 1.500) res = { text: 'UNSATISFACTORY', color: '#ea580c' };
        else res = { text: 'POOR', color: '#dc2626' };
        res.toString = function() { return this.text; };
        return res;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function extractRowsData(category) {
        const rows = document.querySelectorAll(`.table-row-${category}`);
        const result = [];

        rows.forEach(row => {
            const qInput = row.querySelector('.field-q');
            const tInput = row.querySelector('.field-t');
            const eInput = row.querySelector('.field-e');

            const rawQ = qInput?.value;
            const rawT = tInput?.value;
            const rawE = eInput?.value;

            const q = (rawQ === 'X' || rawQ === 'N/A') ? 'X' : parseWholeScore(rawQ);
            const t = (rawT === 'X' || rawT === 'N/A') ? 'X' : parseWholeScore(rawT);
            const e = (rawE === 'X' || rawE === 'N/A') ? 'X' : parseWholeScore(rawE);

            const rowData = {
                row_id: row.dataset.rowId || ('row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7)),
                mfo: row.querySelector('.field-mfo')?.value || '',
                indicators: row.querySelector('.field-indicators')?.value || '',
                accomplishments: row.querySelector('.field-accomplishments')?.value || '',
                q: q !== null ? q : '',
                t: t !== null ? t : '',
                e: e !== null ? e : '',
                q_self: qInput?.dataset?.selfScore || '',
                q_head: qInput?.dataset?.headScore || '',
                q_twg: qInput?.dataset?.twgScore || '',
                last_rated_by_q: qInput?.dataset?.lastActor || '',
                t_self: tInput?.dataset?.selfScore || '',
                t_head: tInput?.dataset?.headScore || '',
                t_twg: tInput?.dataset?.twgScore || '',
                last_rated_by_t: tInput?.dataset?.lastActor || '',
                e_self: eInput?.dataset?.selfScore || '',
                e_head: eInput?.dataset?.headScore || '',
                e_twg: eInput?.dataset?.twgScore || '',
                last_rated_by_e: eInput?.dataset?.lastActor || '',
                remarks: row.querySelector('.field-remarks')?.value || '',
                twg_comment: row.querySelector('.field-twg-comment')?.value || ''
            };

            if (window.isCurrentDocDpcr || window.isCurrentDocOpcr) {
                const bInput = row.querySelector('.field-budget');
                rowData.budget = bInput?.dataset?.rawValue || bInput?.dataset?.fullValue || bInput?.value.trim() || '';
                rowData.budget_currency = row.querySelector('.field-budget-currency')?.value.trim() || '₱';
                rowData.accountable = row.querySelector('.field-accountable')?.value || '';
            }
            if (window.isCurrentDocDpcr) {
                rowData.std_5 = row.querySelector('.field-std-5')?.value || '';
                rowData.std_4 = row.querySelector('.field-std-4')?.value || '';
                rowData.std_3 = row.querySelector('.field-std-3')?.value || '';
                rowData.std_2 = row.querySelector('.field-std-2')?.value || '';
                rowData.std_1 = row.querySelector('.field-std-1')?.value || '';
            }

            result.push(rowData);
        });

        return result;
    }

    window.syncSpmsActiveTab = function() {
        const pf = document.getElementById('printable-form');
        if (!pf) return;
        const activeTab = tabs.find(t => t.id === activeTabId && t.id !== 'rubrics-tab' && t.id !== 'basis-tab') 
                       || tabs.find(t => t.id !== 'rubrics-tab' && t.id !== 'basis-tab') 
                       || tabs[0];
        if (!activeTab) return;

        const getVal = (id) => document.getElementById(id)?.value || '';

        const approversList = extractApprovers();

        const defaultTitle = window.isCurrentDocDpcr 
            ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' 
            : (window.isCurrentDocOpcr 
                ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' 
                : (window.isCurrentDocIperf 
                    ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
                    : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW'));

        const existingCats = activeTab.formData?.categories || {};
        const isFormWorkspaceVisible = window.isSpmsFormActive && !document.getElementById('spms-form-workspace')?.classList.contains('hidden');

        let extractedCore = extractRowsData('core');
        let extractedStrat = window.isCurrentDocIperf ? [] : extractRowsData('strategic');
        let extractedSupp = window.isCurrentDocIperf ? [] : extractRowsData('support');

        // Safeguard against wiping out existing categories:
        // 1. If form workspace is hidden or inactive, or
        // 2. If extracted rows are empty while existing rows exist (and the user hasn't explicitly clicked delete)
        if (!isFormWorkspaceVisible || (extractedCore.length === 0 && !window._userExplicitlyDeletedRow)) {
            extractedCore = (extractedCore.length > 0) ? extractedCore : (existingCats.core?.length ? existingCats.core : (DEFAULT_BLUEPRINT.core || []));
            if (!window.isCurrentDocIperf) {
                extractedStrat = (extractedStrat.length > 0) ? extractedStrat : (existingCats.strategic?.length ? existingCats.strategic : (DEFAULT_BLUEPRINT.strategic || []));
                extractedSupp = (extractedSupp.length > 0) ? extractedSupp : (existingCats.support?.length ? existingCats.support : (DEFAULT_BLUEPRINT.support || []));
            }
        } else if (!canEditTargets && (existingCats.core?.length || 0) > 0) {
            // When user cannot edit targets (e.g. reviewer/evaluator), preserve original target fields
            // while updating reviewer fields (q, t, e, accomplishments, remarks)
            const mergeWithOriginal = (extractedRows, origRows) => {
                return extractedRows.map((row, idx) => {
                    const orig = origRows.find(o => o.row_id === row.row_id) || origRows[idx] || {};
                    return {
                        ...row,
                        row_id: row.row_id || orig.row_id,
                        mfo: orig.mfo || row.mfo,
                        indicators: orig.indicators || row.indicators,
                        budget: (orig.budget !== undefined && orig.budget !== '') ? orig.budget : row.budget,
                        budget_currency: orig.budget_currency || row.budget_currency,
                        accountable: orig.accountable || row.accountable,
                        std_5: orig.std_5 !== undefined ? orig.std_5 : row.std_5,
                        std_4: orig.std_4 !== undefined ? orig.std_4 : row.std_4,
                        std_3: orig.std_3 !== undefined ? orig.std_3 : row.std_3,
                        std_2: orig.std_2 !== undefined ? orig.std_2 : row.std_2,
                        std_1: orig.std_1 !== undefined ? orig.std_1 : row.std_1,
                        q_self: row.q_self !== undefined ? row.q_self : (orig.q_self || ''),
                        q_head: row.q_head !== undefined ? row.q_head : (orig.q_head || ''),
                        q_twg: row.q_twg !== undefined ? row.q_twg : (orig.q_twg || ''),
                        last_rated_by_q: row.last_rated_by_q !== undefined ? row.last_rated_by_q : (orig.last_rated_by_q || ''),
                        t_self: row.t_self !== undefined ? row.t_self : (orig.t_self || ''),
                        t_head: row.t_head !== undefined ? row.t_head : (orig.t_head || ''),
                        t_twg: row.t_twg !== undefined ? row.t_twg : (orig.t_twg || ''),
                        last_rated_by_t: row.last_rated_by_t !== undefined ? row.last_rated_by_t : (orig.last_rated_by_t || ''),
                        e_self: row.e_self !== undefined ? row.e_self : (orig.e_self || ''),
                        e_head: row.e_head !== undefined ? row.e_head : (orig.e_head || ''),
                        e_twg: row.e_twg !== undefined ? row.e_twg : (orig.e_twg || ''),
                        last_rated_by_e: row.last_rated_by_e !== undefined ? row.last_rated_by_e : (orig.last_rated_by_e || ''),
                        twg_comment: row.twg_comment !== undefined ? row.twg_comment : (orig.twg_comment || '')
                    };
                });
            };
            extractedCore = mergeWithOriginal(extractedCore, existingCats.core || []);
            extractedStrat = mergeWithOriginal(extractedStrat, existingCats.strategic || []);
            extractedSupp = mergeWithOriginal(extractedSupp, existingCats.support || []);
        }

        const formData = {
            revisionHistory: activeTab.formData?.revisionHistory || [],
            title: document.getElementById('spms-doc-title')?.innerText || document.getElementById('doc-title')?.value || defaultTitle,
            doc_type: window.isCurrentDocDpcr ? 'dpcr' : (window.isCurrentDocOpcr ? 'opcr' : (window.isCurrentDocIperf ? 'iperf' : (activeTab.formData?.doc_type || 'ipcr'))),
            currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            budget_currency: document.querySelector('.header-budget-currency')?.value.trim() || '₱',
            classification: window.isCurrentDocIperf ? getVal('ratee-classification') : undefined,
            weights: window.isCurrentDocIperf 
                ? { core: 1.00, strategic: 0.00, support: 0.00 }
                : ((CATEGORY_WEIGHTS && (CATEGORY_WEIGHTS.core > 0 || CATEGORY_WEIGHTS.strategic > 0 || CATEGORY_WEIGHTS.support > 0)) ? CATEGORY_WEIGHTS : null),
            ratee: {
                name: getVal('ratee-name'),
                position: getVal('ratee-position'),
                dept: getVal('ratee-dept'),
                period: getVal('ratee-period')
            },
            approver: approversList[0] || {
                name: getVal('approver-name'),
                position: getVal('approver-pos'),
                date: getVal('approver-date')
            },
            approvers: approversList,
            rateeSign: {
                name: getVal('ratee-sign-name'),
                date: getVal('ratee-sign-date')
            },
            categories: {
                core: extractedCore,
                strategic: extractedStrat,
                support: extractedSupp
            },
            pmtRemarks: getVal('pmt-remarks'),
            signatories: window.isCurrentDocIperf ? {
                targetsPreparedName: getVal('sig-targets-prepared-name'),
                targetsPreparedDate: getVal('sig-targets-prepared-date'),
                targetsApprovedName: getVal('sig-targets-approved-name'),
                targetsApprovedDate: getVal('sig-targets-approved-date'),
                evalRatedName: getVal('sig-eval-rated-name'),
                evalRatedDate: getVal('sig-eval-rated-date'),
                evalConformeName: getVal('sig-eval-conforme-name'),
                evalConformeDate: getVal('sig-eval-conforme-date')
            } : {
                ratee: getVal('sig-ratee-name'),
                rateePos: getVal('sig-ratee-pos'),
                rateeDate: getVal('sig-ratee-date'),
                dean: approversList[0]?.name || getVal('sig-dean-name'),
                deanPos: approversList[0]?.position || getVal('sig-dean-pos'),
                deanDate: approversList[0]?.evalDate || getVal('sig-dean-date'),
                vp: getVal('sig-vp-name'),
                vpDate: getVal('sig-vp-date')
            },
            rubrics: (window.digitalRubricsData || activeTab.formData?.rubrics || {})
        };

        activeTab.formData = formData;
        if (isFormWorkspaceVisible && (extractedCore.length > 0 || (existingCats.core?.length || 0) === 0)) {
            activeTab.content = document.getElementById('printable-form')?.innerHTML || activeTab.content || '';
        }
    };

    // -------------------------------------------------------------
    // Digital Rubrics matrix & attachments handling
    // -------------------------------------------------------------
    function getFormDeliverables() {
        const deliverables = [];
        const seenRows = new Set();

        const collectFromTbody = (tbodyId, catName) => {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            tbody.querySelectorAll(`tr.table-row-${catName}, tr[data-row-id], tr[id^="row-"]`).forEach((tr, idx) => {
                const rowId = tr.dataset.rowId || tr.id || `row-${catName}-${idx}`;
                if (seenRows.has(rowId)) return;
                seenRows.add(rowId);

                const indEl = tr.querySelector('.field-indicators') || tr.querySelector('.spms-input-indicator') || tr.querySelector('.spms-input-success-indicator');
                const expEl = tr.querySelector('.spms-input-expected-outputs');
                
                const qEl = tr.querySelector('.field-q');
                const tEl = tr.querySelector('.field-t');
                const eEl = tr.querySelector('.field-e');

                const ind = indEl ? indEl.value.trim() : '';
                const exp = expEl ? expEl.value.trim() : '';
                
                let text = ind || exp;
                
                if (!text) {
                    const mfoEl = tr.querySelector('.field-mfo') || tr.querySelector('.spms-input-mfo') || tr.querySelector('.spms-input-paps');
                    if (mfoEl && mfoEl.value.trim()) text = mfoEl.value.trim();
                }
                
                deliverables.push({
                    rowId: rowId,
                    cat: catName,
                    title: text || `Deliverable #${deliverables.length + 1}`,
                    isQ_na: qEl ? (qEl.value === 'X' || qEl.value === 'N/A') : false,
                    isT_na: tEl ? (tEl.value === 'X' || tEl.value === 'N/A') : false,
                    isE_na: eEl ? (eEl.value === 'X' || eEl.value === 'N/A') : false
                });
            });
        };

        collectFromTbody('tbody-core', 'core');
        if (!window.isCurrentDocIperf) {
            collectFromTbody('tbody-strategic', 'strategic');
            collectFromTbody('tbody-support', 'support');
        }
        
        // Fallback to activeTab.formData if table DOM is not yet populated
        if (deliverables.length === 0) {
            const activeTab = tabs[0];
            const cats = activeTab?.formData?.categories || DEFAULT_BLUEPRINT;
            if (cats) {
                const catKeys = window.isCurrentDocIperf ? ['core'] : ['core', 'strategic', 'support'];
                catKeys.forEach(cat => {
                    if (Array.isArray(cats[cat])) {
                        cats[cat].forEach((r, idx) => {
                            const ppa = r.office_ppa || '';
                            const exp = r.expected_outputs || r.indicators || r.success_indicators || '';
                            const mfo = r.mfo || r.mfo_title || r.paps || '';
                            const text = [mfo, ppa, exp].filter(Boolean).join(' — ');
                            deliverables.push({
                                rowId: r.row_id || `row-${cat}-${idx}`,
                                cat: cat,
                                title: text || `Deliverable #${deliverables.length + 1}`,
                                isQ_na: r.q === 'X' || r.q === 'N/A',
                                isT_na: r.t === 'X' || r.t === 'N/A',
                                isE_na: r.e === 'X' || r.e === 'N/A'
                            });
                        });
                    }
                });
            }
        }
        return deliverables;
    }

    function autoExpandRubricRow(tr) {
        if (!tr || tr.nodeType !== Node.ELEMENT_NODE) return;
        const minH = 56;
        const textareas = Array.from(tr.querySelectorAll('.rubric-cell-textarea'));
        if (textareas.length === 0) return;

        textareas.forEach(ta => {
            ta.style.height = 'auto';
            ta.style.minHeight = minH + 'px';
        });

        let maxH = minH;
        textareas.forEach(ta => {
            const sh = ta.scrollHeight;
            if (sh > maxH) maxH = sh;
        });

        const finalHStr = Math.max(minH, Math.ceil(maxH)) + 'px';
        textareas.forEach(ta => {
            ta.style.height = finalHStr;
            ta.style.minHeight = finalHStr;
        });
    }

    function autoResizeTextarea(el) {
        if (!el) return;
        const tr = el.closest('tr');
        if (tr) autoExpandRubricRow(tr);
    }

    function autoExpandAllRubricRows() {
        document.querySelectorAll('#digital-rubrics-tbody tr[data-deliverable-id]').forEach(tr => {
            autoExpandRubricRow(tr);
        });
    }

    function openRubricCellComment(rowId, fieldName, targetEl, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        openCommentOverlay(rowId, fieldName, targetEl);
    }

    function applyRubricCommentBadges() {
        const tds = document.querySelectorAll('#digital-rubrics-table td.rubric-score-td');
        tds.forEach(td => {
            const rowId = td.dataset.rowId;
            const fieldName = td.dataset.fieldName;
            if (!rowId || !fieldName) return;
            const thread = (window.docComments || []).find(t => t.row_id === rowId && t.field_name === fieldName);
            if (thread && thread.status !== 'deleted') {
                td.classList.add('has-cell-comment');
                if (thread.status === 'resolved') {
                    td.classList.add('comment-resolved');
                    td.title = `Resolved Comment by ${thread.author_name}: "${thread.comment_text.substring(0, 50)}..."`;
                } else {
                    td.classList.remove('comment-resolved');
                    td.title = `Comment by ${thread.author_name} (${thread.author_role}): "${thread.comment_text.substring(0, 50)}..."`;
                }
            } else {
                td.classList.remove('has-cell-comment', 'comment-resolved');
            }
        });
    }

    // Single Full Sheet Table View (Cards view removed)
    window.setRubricsViewMode = function() {};
    window.toggleRubricCard = function() {};
    window.toggleAllRubricCards = function() {};

    function filterRubricDeliverable(filterKey) {
        document.querySelectorAll('.rubric-jump-pill').forEach(p => {
            const isSelected = (p.getAttribute('data-target-filter') === filterKey);
            if (isSelected) {
                p.className = 'rubric-jump-pill px-3 py-1.5 rounded-xl font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 transition-all cursor-pointer shrink-0 flex items-center gap-1.5 shadow-xs';
            } else {
                p.className = 'rubric-jump-pill px-3 py-1.5 rounded-xl font-bold bg-surface-border/30 hover:bg-surface-border/50 text-text-muted hover:text-text transition-all cursor-pointer shrink-0 flex items-center gap-1.5';
            }
        });

        if (filterKey === 'all') {
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => tr.classList.remove('hidden'));
        } else if (['core', 'strategic', 'support'].includes(filterKey)) {
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => {
                const cat = tr.getAttribute('data-category');
                const isHeader = tr.id === `sheet-category-row-${filterKey}`;
                if (cat === filterKey || isHeader) {
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            const headerRow = document.getElementById(`sheet-category-row-${filterKey}`);
            if (headerRow) {
                headerRow.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } else {
            // Specific rowId / deliverable
            document.querySelectorAll('#digital-rubrics-tbody tr').forEach(tr => {
                if (tr.hasAttribute('data-deliverable-separator')) {
                    tr.classList.add('hidden');
                    return;
                }
                const delId = tr.getAttribute('data-deliverable-id');
                if (!delId || delId === filterKey) {
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            const delRow = document.querySelector(`#digital-rubrics-tbody tr[data-deliverable-id="${filterKey}"]`);
            if (delRow) {
                delRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }


    function updateSingleDeliverableBadge(rowId) {
        const badge = document.getElementById(`rubric-count-badge-${rowId}`);
        if (!badge) return;
        const rowData = (window.digitalRubricsData && window.digitalRubricsData[rowId]) || {};
        let filled = 0;
        [5, 4, 3, 2, 1].forEach(sc => {
            const d = rowData[sc];
            if (d) {
                if (d.q && String(d.q).trim().toUpperCase() !== 'N/A') filled++;
                if (d.t && String(d.t).trim().toUpperCase() !== 'N/A') filled++;
                if (d.e && String(d.e).trim().toUpperCase() !== 'N/A') filled++;
            }
        });
        badge.innerText = `${filled}/15 Defined`;
        if (filled === 15) {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else if (filled > 0) {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30';
        } else {
            badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-medium text-text-muted bg-surface-border/40';
        }
    }
    window.updateSingleCardCriteriaBadge = updateSingleDeliverableBadge;

    function renderDigitalRubricsMatrix() {
        const tbody = document.getElementById('digital-rubrics-tbody');
        const quickPills = document.getElementById('rubric-quick-jump-pills');
        const overallProg = document.getElementById('rubric-overall-progress');
        if (!tbody) return;
        
        const deliverables = getFormDeliverables();
        if (deliverables.length === 0) {
            const emptyHtml = `
                <div class="py-14 text-center rounded-2xl bg-surface border border-surface-border p-8 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-black text-base text-text">No deliverables found on the Commitment Form yet</h3>
                    <p class="text-xs text-text-muted max-w-md mx-auto leading-relaxed">
                        Add your committed Major Final Outputs and Expected Outputs under the "Target Form" tab first. They will automatically link here for standard criteria setting.
                    </p>
                    <button type="button" onclick="switchEditorTab(tabs[0].id)" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-accent text-accent-text hover:bg-accent-hover shadow-sm transition-all cursor-pointer">
                        <span>Open Target Form</span>
                    </button>
                </div>
            `;
            tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-text-muted">${emptyHtml}</td></tr>`;
            if (quickPills) quickPills.innerHTML = '';
            if (overallProg) overallProg.innerText = '';
            return;
        }

        const savedRubrics = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};
        // Clean legacy data: the literal "N/A" display text was previously persisted as a criterion
        Object.values(savedRubrics || {}).forEach(rowRub => {
            if (!rowRub || typeof rowRub !== 'object') return;
            Object.values(rowRub).forEach(cell => {
                if (!cell || typeof cell !== 'object') return;
                ['q', 't', 'e'].forEach(d => {
                    if (typeof cell[d] === 'string' && cell[d].trim().toUpperCase() === 'N/A') cell[d] = '';
                });
            });
        });

        const isIperf = (typeof window.isCurrentDocIperf !== 'undefined') ? window.isCurrentDocIperf : <?= $isDocIperf ? 'true' : 'false' ?>;
        const isDpcr = (typeof window.isCurrentDocDpcr !== 'undefined') ? window.isCurrentDocDpcr : <?= $isDocDpcr ? 'true' : 'false' ?>;
        const isOpcr = (typeof window.isCurrentDocOpcr !== 'undefined') ? window.isCurrentDocOpcr : <?= $isDocOpcr ? 'true' : 'false' ?>;

        const catWeights = {
            core: (CATEGORY_WEIGHTS.core && CATEGORY_WEIGHTS.core > 0) ? `${Math.round(CATEGORY_WEIGHTS.core * 100)}%` : '—',
            strategic: (CATEGORY_WEIGHTS.strategic && CATEGORY_WEIGHTS.strategic > 0) ? `${Math.round(CATEGORY_WEIGHTS.strategic * 100)}%` : '—',
            support: (CATEGORY_WEIGHTS.support && CATEGORY_WEIGHTS.support > 0) ? `${Math.round(CATEGORY_WEIGHTS.support * 100)}%` : '—'
        };

        const catConfigs = {
            core: {
                title: isIperf ? 'Committed Outputs' : 'Core Functions',
                weight: catWeights.core,
                badge: 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30',
                dot: 'bg-emerald-500',
                borderL: 'border-l-emerald-500'
            },
            strategic: {
                title: 'Strategic Functions',
                weight: catWeights.strategic,
                badge: 'bg-sky-500/15 text-sky-800 dark:text-sky-300 border-sky-500/30',
                dot: 'bg-sky-500',
                borderL: 'border-l-sky-500'
            },
            support: {
                title: 'Support Functions',
                weight: catWeights.support,
                badge: 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30',
                dot: 'bg-amber-500',
                borderL: 'border-l-amber-500'
            }
        };

        const activeCatKeys = isIperf ? ['core'] : ['core', 'strategic', 'support'];

        const scoreMeta = {
            5: { label: '5 • Outstanding', hint: 'Exceptional / Top Quality', pill: 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30' },
            4: { label: '4 • Very Satisfactory', hint: 'Exceeds Target', pill: 'bg-sky-500/15 text-sky-800 dark:text-sky-300 border-sky-500/30' },
            3: { label: '3 • Satisfactory', hint: '100% Target Standard', pill: 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30' },
            2: { label: '2 • Unsatisfactory', hint: 'Below Target Standard', pill: 'bg-orange-500/15 text-orange-800 dark:text-orange-300 border-orange-500/30' },
            1: { label: '1 • Poor', hint: 'Fails Standard / Deficient', pill: 'bg-rose-500/15 text-rose-800 dark:text-rose-300 border-rose-500/30' }
        };

        const readonlyAttr = canEditTargets ? '' : 'readonly disabled';
        const inputBg = canEditTargets 
            ? 'bg-input border border-surface-border text-text placeholder:text-text-muted/50 focus:bg-surface focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-xs' 
            : 'bg-transparent border border-transparent text-text cursor-default select-text';

        let sheetHtml = '';
        let totalConfiguredCriteria = 0;
        const maxPossibleCriteria = deliverables.length * 15;

        // Quick jump category pills
        if (quickPills) {
            let pillsHtml = `
                <button type="button" onclick="filterRubricDeliverable('all')" data-target-filter="all"
                        class="rubric-jump-pill px-3 py-1.5 rounded-xl font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 transition-all cursor-pointer shrink-0 shadow-xs">
                    All Outputs (${deliverables.length})
                </button>
            `;

            activeCatKeys.forEach(catKey => {
                const cfg = catConfigs[catKey];
                const catDelivs = deliverables.filter(d => (d.cat || 'core') === catKey);
                pillsHtml += `
                    <button type="button" onclick="filterRubricDeliverable('${catKey}')" data-target-filter="${catKey}"
                            class="rubric-jump-pill px-3 py-1.5 rounded-xl font-bold bg-surface-border/30 hover:bg-surface-border/50 text-text-muted hover:text-text transition-all cursor-pointer shrink-0 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full ${cfg.dot}"></span>
                        <span>${cfg.title} (${catDelivs.length})</span>
                    </button>
                `;
            });

            quickPills.innerHTML = pillsHtml;
        }

        // Build Table Rows per Category
        activeCatKeys.forEach(catKey => {
            const cfg = catConfigs[catKey];
            const catDelivs = deliverables.filter(d => (d.cat || 'core') === catKey);

            let catFilledCriteria = 0;
            const catMaxCriteria = catDelivs.length * 15;

            catDelivs.forEach(del => {
                const rowId = del.rowId;
                const rubricData = savedRubrics[rowId] || {};
                [5, 4, 3, 2, 1].forEach(sc => {
                    const s = rubricData[sc];
                    if (s?.q && String(s.q).trim().toUpperCase() !== 'N/A') catFilledCriteria++;
                    if (s?.t && String(s.t).trim().toUpperCase() !== 'N/A') catFilledCriteria++;
                    if (s?.e && String(s.e).trim().toUpperCase() !== 'N/A') catFilledCriteria++;
                });
            });
            totalConfiguredCriteria += catFilledCriteria;

            // Category Header Row
            sheetHtml += `
                <tr class="border-t-2 border-b-2 border-surface-border bg-surface-border/25 font-bold" id="sheet-category-row-${catKey}">
                    <td colspan="5" class="p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full ${cfg.dot}"></span>
                                <span class="text-xs font-black uppercase tracking-wider text-text">${cfg.title} (${cfg.weight})</span>
                                <span class="text-[10px] font-medium text-text-muted">(${catDelivs.length} ${catDelivs.length === 1 ? 'output' : 'outputs'})</span>
                            </div>
                            <span class="text-[10px] font-bold text-text-muted">${catFilledCriteria}/${catMaxCriteria} Criteria Defined</span>
                        </div>
                    </td>
                </tr>
            `;

            if (catDelivs.length === 0) {
                sheetHtml += `
                    <tr class="border-b border-surface-border" data-category="${catKey}">
                        <td colspan="5" class="p-4 text-center text-xs text-text-muted italic bg-surface/20">
                            No deliverables under ${cfg.title} yet.
                        </td>
                    </tr>
                `;
            } else {
                catDelivs.forEach((del, dIdx) => {
                    const rowId = del.rowId;
                    const rubricData = savedRubrics[rowId] || {};
                    const catClass = cfg.badge;

                    let rowFilledCount = 0;
                    [5, 4, 3, 2, 1].forEach(sc => {
                        const s = rubricData[sc];
                        if (s?.q && String(s.q).trim().toUpperCase() !== 'N/A') rowFilledCount++;
                        if (s?.t && String(s.t).trim().toUpperCase() !== 'N/A') rowFilledCount++;
                        if (s?.e && String(s.e).trim().toUpperCase() !== 'N/A') rowFilledCount++;
                    });

                    const badgeCompClass = rowFilledCount === 15 
                        ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30 font-extrabold'
                        : (rowFilledCount > 0 
                            ? 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30 font-extrabold'
                            : 'bg-surface-border/40 text-text-muted font-medium');

                    // Clean, visible gap between deliverables
                    if (dIdx > 0) {
                        sheetHtml += `
                            <tr data-category="${catKey}" data-deliverable-separator="true" class="select-none print:hidden">
                                <td colspan="5" class="p-0 border-y border-surface-border/60 bg-surface-border/20">
                                    <div class="h-3.5"></div>
                                </td>
                            </tr>
                        `;
                    }

                    [5, 4, 3, 2, 1].forEach((score, sIdx) => {
                        const scData = rubricData[score] || { q: '', t: '', e: '' };
                        const meta = scoreMeta[score];
                        const isFirst = (sIdx === 0);
                        const cleanQ = (scData.q && String(scData.q).trim().toUpperCase() === 'N/A') ? '' : (scData.q || '');
                        const cleanT = (scData.t && String(scData.t).trim().toUpperCase() === 'N/A') ? '' : (scData.t || '');
                        const cleanE = (scData.e && String(scData.e).trim().toUpperCase() === 'N/A') ? '' : (scData.e || '');

                        sheetHtml += `<tr class="border-b border-surface-border hover:bg-surface-border/5 transition-colors" data-category="${catKey}" data-deliverable-id="${escapeHtml(rowId)}">`;

                        if (isFirst) {
                            sheetHtml += `
                                <td rowspan="5" class="p-3.5 align-top bg-surface-border/10 border-r border-surface-border text-text" style="width: 32%; max-width: 38%; overflow: hidden;">
                                    <div class="space-y-2.5" style="max-width: 100%; overflow-wrap: anywhere; word-break: break-word;">
                                        <div class="flex items-center justify-between gap-1 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border ${catClass}">
                                                Output #${dIdx + 1} &bull; ${escapeHtml(cfg.title.toUpperCase())}
                                            </span>
                                            <span id="rubric-count-badge-${escapeHtml(rowId)}" class="px-2 py-0.5 rounded-full text-[9px] border ${badgeCompClass}">
                                                ${rowFilledCount}/15 Defined
                                            </span>
                                        </div>

                                        <div class="font-bold text-xs text-text leading-snug break-words" style="overflow-wrap: anywhere; word-break: break-word;">
                                            ${escapeHtml(del.title)}
                                        </div>
                                    </div>
                                </td>
                            `;
                        }

                        sheetHtml += `
                            <td class="p-2.5 text-center align-middle border-r border-surface-border" style="width: 11%;">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-black border ${meta.pill}">
                                        ${score}
                                    </span>
                                    <span class="text-[9px] text-text-muted font-medium leading-tight">
                                        ${meta.hint}
                                    </span>
                                </div>
                            </td>
                            <td class="rubric-score-td border-r border-surface-border ${del.isQ_na ? 'rubric-dim-na' : ''}" style="width: 19%;" data-row-id="${escapeHtml(rowId)}" data-field-name="rubric_${score}_q">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="q" ${del.isQ_na ? 'disabled' : readonlyAttr}
                                          placeholder="${del.isQ_na ? '' : (canEditTargets ? 'e.g. No revisions / zero defect...' : '—')}"
                                          rows="2"
                                          oninput="autoExpandRubricRow(this.closest('tr')); onDigitalRubricChange(this)"
                                          class="rubric-cell-textarea ${del.isQ_na ? 'is-na' : ''}">${del.isQ_na ? 'N/A' : escapeHtml(cleanQ)}</textarea>
                            </td>
                            <td class="rubric-score-td border-r border-surface-border ${del.isT_na ? 'rubric-dim-na' : ''}" style="width: 19%;" data-row-id="${escapeHtml(rowId)}" data-field-name="rubric_${score}_t">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="t" ${del.isT_na ? 'disabled' : readonlyAttr}
                                          placeholder="${del.isT_na ? '' : (canEditTargets ? 'e.g. within 1–2 working days ahead...' : '—')}"
                                          rows="2"
                                          oninput="autoExpandRubricRow(this.closest('tr')); onDigitalRubricChange(this)"
                                          class="rubric-cell-textarea ${del.isT_na ? 'is-na' : ''}">${del.isT_na ? 'N/A' : escapeHtml(cleanT)}</textarea>
                            </td>
                            <td class="rubric-score-td ${del.isE_na ? 'rubric-dim-na' : ''}" style="width: 19%;" data-row-id="${escapeHtml(rowId)}" data-field-name="rubric_${score}_e">
                                <textarea data-row-id="${escapeHtml(rowId)}" data-score="${score}" data-dim="e" ${del.isE_na ? 'disabled' : readonlyAttr}
                                          placeholder="${del.isE_na ? '' : (canEditTargets ? 'e.g. 100% of target accomplished...' : '—')}"
                                          rows="2"
                                          oninput="autoExpandRubricRow(this.closest('tr')); onDigitalRubricChange(this)"
                                          class="rubric-cell-textarea ${del.isE_na ? 'is-na' : ''}">${del.isE_na ? 'N/A' : escapeHtml(cleanE)}</textarea>
                            </td>
                        </tr>`;
                    });
                });
            }


        });

        tbody.innerHTML = sheetHtml;
        autoExpandAllRubricRows();
        applyRubricCommentBadges();

        if (overallProg) {
            const pct = Math.round((totalConfiguredCriteria / maxPossibleCriteria) * 100);
            overallProg.innerText = `${totalConfiguredCriteria} / ${maxPossibleCriteria} Criteria Configured (${pct}%)`;
        }

        setTimeout(() => {
            document.querySelectorAll('#spms-rubrics-workspace textarea').forEach(ta => autoResizeTextarea(ta));
        }, 50);

        updateRubricsTabBadge();
    }

    function onDigitalRubricChange(el) {
        const rowId = el.getAttribute('data-row-id');
        const score = el.getAttribute('data-score');
        const dim = el.getAttribute('data-dim');
        const val = el.value;

        if (!window.digitalRubricsData) {
            window.digitalRubricsData = tabs[0]?.formData?.rubrics || {};
        }
        if (!window.digitalRubricsData[rowId]) {
            window.digitalRubricsData[rowId] = {};
        }
        if (!window.digitalRubricsData[rowId][score]) {
            window.digitalRubricsData[rowId][score] = { q: '', t: '', e: '' };
        }
        window.digitalRubricsData[rowId][score][dim] = val;

        // Keep sibling textarea in sync if both card view and sheet view exist in DOM
        document.querySelectorAll(`textarea[data-row-id="${rowId}"][data-score="${score}"][data-dim="${dim}"]`).forEach(sibling => {
            if (sibling !== el && sibling.value !== val) {
                sibling.value = val;
                autoResizeTextarea(sibling);
            }
        });

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            tabs[0].formData.rubrics = window.digitalRubricsData;
        }

        updateSingleCardCriteriaBadge(rowId);

        AppState.setDirty(true);
        updateRubricsTabBadge();
    }

    window.syncDigitalRubricsData = function() {
        const container = document.getElementById('spms-rubrics-workspace');
        if (!container) return;

        if (!window.digitalRubricsData || Array.isArray(window.digitalRubricsData)) {
            window.digitalRubricsData = (tabs[0]?.formData?.rubrics && !Array.isArray(tabs[0]?.formData?.rubrics)) ? tabs[0].formData.rubrics : {};
        }

        container.querySelectorAll('textarea[data-row-id]').forEach(ta => {
            // Disabled N/A cells only display "N/A" — never persist that placeholder as a criterion
            if (ta.disabled) return;
            const rowId = ta.getAttribute('data-row-id');
            const score = ta.getAttribute('data-score');
            const dim = ta.getAttribute('data-dim');
            const val = ta.value;

            if (!window.digitalRubricsData[rowId]) {
                window.digitalRubricsData[rowId] = {};
            }
            if (!window.digitalRubricsData[rowId][score]) {
                window.digitalRubricsData[rowId][score] = { q: '', t: '', e: '' };
            }
            window.digitalRubricsData[rowId][score][dim] = val;
        });

        if (tabs[0]) {
            if (!tabs[0].formData) tabs[0].formData = {};
            if (!tabs[0].formData.categories && typeof window.syncSpmsActiveTab === 'function') {
                window.syncSpmsActiveTab();
            }
            tabs[0].formData.rubrics = window.digitalRubricsData;
        }

        updateRubricsTabBadge();
    };

    function updateRubricsTabBadge() {
        const badge = document.getElementById('tab-rubrics-badge');
        if (!badge) return;

        const rubricsData = window.digitalRubricsData || tabs[0]?.formData?.rubrics || {};
        let criteriaCount = 0;
        Object.values(rubricsData).forEach(scores => {
            if (typeof scores === 'object') {
                Object.values(scores).forEach(s => {
                    if (s && (s.q || s.t || s.e)) criteriaCount++;
                });
            }
        });

        if (criteriaCount > 0) {
            badge.innerHTML = `${criteriaCount} Criteria Set`;
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30';
        } else {
            badge.innerHTML = 'Needs Rubrics';
            badge.className = 'px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30';
        }
    }

    // Attach event delegation listeners on printable-form for reliable autosave across all dynamic rows
    document.addEventListener('DOMContentLoaded', () => {
        const pf = document.getElementById('printable-form');
        if (pf) {
            pf.addEventListener('input', (e) => {
                if (e.target && e.target.matches('input, textarea, select')) {
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
            });
            pf.addEventListener('change', (e) => {
                if (e.target && e.target.matches('input, textarea, select')) {
                    window.syncSpmsActiveTab();
                    AppState.setDirty(true);
                }
            });
        }
    });

    window.rate = async function() {
        if (window.isSpmsFormActive) {
            recalculateForm();
            window.syncSpmsActiveTab();
            AppState.setDirty(true);
            return 'calculated';
        }
    };

    function exportToExcel() {
        const exportUrl = '<?= site_url("document/" . $doc["id"] . "/export-excel") ?>';
        if (window.isSpmsFormActive && AppState.dirty) {
            saveWith({
                after: () => {
                    window.location.href = exportUrl;
                }
            });
        } else {
            window.location.href = exportUrl;
        }
    }

    function exportToPdf() {
        if (activeTabId === 'basis-tab') {
            window.print();
            return;
        }
        // Auto-expand all textareas so multi-line text is never clipped during printing
        document.querySelectorAll('#printable-form textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        window.print();
    }

    // Auto-expand textareas before printing
    window.addEventListener('beforeprint', () => {
        document.querySelectorAll('#printable-form textarea, #basis-printable-sheet textarea').forEach(ta => {
            ta.style.height = 'auto';
            ta.style.height = Math.max(ta.scrollHeight, 38) + 'px';
        });
        document.querySelectorAll('.field-budget').forEach(input => {
            if (input.dataset && input.dataset.fullValue) {
                input.value = input.dataset.fullValue;
            }
        });
    });

    window.addEventListener('afterprint', () => {
        document.querySelectorAll('.field-budget').forEach(adjustBudgetFontSize);
    });

    function renderBasisStaticSheet(guideIdx = 0) {
        const sheet = document.getElementById('basis-printable-sheet');
        if (!sheet) return;

        const guide = (typeof allBasisGuides !== 'undefined' && allBasisGuides && allBasisGuides[guideIdx]) 
            ? allBasisGuides[guideIdx] 
            : ((typeof allBasisGuides !== 'undefined' && allBasisGuides && allBasisGuides[0]) ? allBasisGuides[0] : null);

        const data = (guide && guide.formData) ? guide.formData : (basisFormData || {});
        const docContent = (guide && guide.content) ? guide.content : (basisDocContent || '');
        const fallbackEl = document.getElementById('basis-html-fallback');

        // Header info bar
        const headerDocTitleEl = document.getElementById('basis-header-doc-title');
        if (headerDocTitleEl && guide) {
            headerDocTitleEl.innerText = `${guide.title} — ${guide.superior_name} (${guide.superior_role})`;
        }
        const openLinkEl = document.getElementById('basis-header-open-link');
        if (openLinkEl && guide && guide.id) {
            openLinkEl.href = '<?= site_url('document/') ?>' + guide.id;
        }
        const statusBadgeEl = document.getElementById('basis-header-status-badge');
        if (statusBadgeEl && guide) {
            const isOpcr = (guide.title || '').toUpperCase().includes('OPCR') || (guide.doc_type || '').toUpperCase() === 'OPCR';
            statusBadgeEl.innerText = guide.is_approved ? (isOpcr ? 'Institutional Basis' : 'Approved Targets') : 'Pending Approval';
            statusBadgeEl.className = `px-2 py-0.5 rounded-full text-[10px] font-extrabold ${guide.is_approved ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300'}`;
        }

        // If no structured categories, but has HTML content:
        if ((!data.categories || Object.keys(data.categories).length === 0) && docContent && docContent.trim() !== '') {
            if (fallbackEl) {
                fallbackEl.innerHTML = docContent;
                fallbackEl.classList.remove('hidden');
            }
            return;
        }

        if (fallbackEl) fallbackEl.classList.add('hidden');

        // Header info
        const titleText = data.title || (guide ? guide.title : '<?= esc($basisDoc['title'] ?? '') ?>');
        const sheetTitleEl = document.getElementById('basis-sheet-title');
        if (sheetTitleEl) sheetTitleEl.innerText = titleText;

        const escapeHtml = (str) => {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const setVal = (id, val, fallback = '') => {
            const el = document.getElementById(id);
            if (!el) return;
            const finalVal = (val !== undefined && val !== null && String(val).trim() !== '' && String(val).trim() !== '—') ? String(val).trim() : fallback;
            if ('value' in el) {
                el.value = finalVal;
                el.removeAttribute('placeholder');
            } else {
                el.innerText = finalVal;
            }
        };

        // In the cascaded basis, if original owner kept fields empty, do not fallback-fill; keep them empty!
        setVal('basis-val-ratee-name', data.ratee?.name || '');
        setVal('basis-val-ratee-pos', data.ratee?.position || '');
        setVal('basis-val-ratee-dept', data.ratee?.dept || '');
        setVal('basis-val-ratee-period', data.ratee?.period || '');

        const approversContainer = document.getElementById('basis-approvers-container');
        if (data.approvers && data.approvers.length > 0 && approversContainer) {
            approversContainer.innerHTML = data.approvers.map((a, idx) => `
                <div class="approver-item" style="${idx > 0 ? 'border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 6px;' : ''} position: relative;">
                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Name:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.name ? escapeHtml(a.name) : ''}" style="font-weight: bold; color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 60%; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Position:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.position ? escapeHtml(a.position) : ''}" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 80%; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #000000;">Date:</td>
                            <td style="border: none; padding: 3px 0;">
                                <input type="text" readonly value="${a.date ? escapeHtml(a.date) : ''}" style="color: #000000; border: none; border-bottom: 1px solid #94a3b8; outline: none; font-size: 11px; width: 130px; background: transparent; font-family: inherit;">
                            </td>
                        </tr>
                    </table>
                </div>
            `).join('');
        } else {
            setVal('basis-val-approver-name', data.approver?.name || '');
            setVal('basis-val-approver-pos', data.approver?.position || '');
            setVal('basis-val-approver-date', data.approver?.date || '');
        }

        setVal('basis-val-ratee-sign', data.rateeSign?.name || '');
        const posEl = document.getElementById('basis-val-ratee-sign-pos');
        if (posEl) posEl.innerText = 'Name of Employee';
        setVal('basis-val-ratee-sign-date', data.rateeSign?.date || '');

        const isOpcr = titleText.toUpperCase().includes('OPCR') || titleText.toUpperCase().includes('OFFICE') || (data.doc_type && data.doc_type.toUpperCase() === 'OPCR');
        const isDpcr = titleText.toUpperCase().includes('DPCR') || titleText.toUpperCase().includes('DIVISION') || titleText.toUpperCase().includes('DEPARTMENT') || (data.doc_type && data.doc_type.toUpperCase() === 'DPCR');

        const colCountBadge = document.getElementById('basis-col-count-badge');
        if (colCountBadge) colCountBadge.innerText = (isOpcr || isDpcr) ? '10 Columns' : '8 Columns';

        // Dynamically update thead and colgroup of basis-printable-sheet
        const colgroupEl = document.getElementById('basis-table-colgroup');
        const theadEl = document.getElementById('basis-table-thead');
        if (colgroupEl && theadEl) {
            if (isOpcr) {
                colgroupEl.innerHTML = `
                    <col style="width: 15%;">
                    <col style="width: 17%;">
                    <col style="width: 11%;">
                    <col style="width: 13%;">
                    <col style="width: 18%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 4.5%;">
                    <col style="width: 11%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROJECT / PROGRAM / ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">DIVISIONS ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                    </tr>
                    <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                `;
            } else if (isDpcr) {
                colgroupEl.innerHTML = `
                    <col style="width: 15%;">
                    <col style="width: 17%;">
                    <col style="width: 11%;">
                    <col style="width: 13%;">
                    <col style="width: 18%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 3.5%;">
                    <col style="width: 4.5%;">
                    <col style="width: 11%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 1px solid #000; font-size: 10px;">
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">PROGRAMS, PROJECTS, ACTIVITIES</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">SUCCESS INDICATORS<br><span style="font-weight: normal; font-size: 9px;">(TARGETS + MEASURES)</span></th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ALLOTTED BUDGET</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">INDIVIDUALS / OFFICES ACCOUNTABLE</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="border: 1px solid #000; padding: 4px;">RATING</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px 4px;">REMARKS</th>
                    </tr>
                    <tr style="background-color: #f1f5f9; color: #0f172a; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="border: 1px solid #000; padding: 4px 2px;">Q</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">T</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">E</th>
                        <th style="border: 1px solid #000; padding: 4px 2px;">Ave.</th>
                    </tr>
                `;
            } else {
                colgroupEl.innerHTML = `
                    <col style="width: 25%;">
                    <col style="width: 25%;">
                    <col style="width: 24%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 4%;">
                    <col style="width: 5%;">
                    <col style="width: 9%;">
                `;
                theadEl.innerHTML = `
                    <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACADEMIC FUNCTION /<br>MAJOR FINAL OUTPUT</th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">SUCCESS INDICATORS<br><span style="font-size: 9px; font-weight: normal;">(Targets + Measures)</span></th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">ACTUAL ACCOMPLISHMENTS</th>
                        <th colspan="4" style="padding: 4px; border: 1px solid #000;">RATING</th>
                        <th rowspan="2" style="padding: 8px; border: 1px solid #000;">REMARKS</th>
                    </tr>
                    <tr style="background: #f1f5f9; text-align: center; font-weight: bold; border-bottom: 2px solid #000; font-size: 10px;">
                        <th style="padding: 4px; border: 1px solid #000;">Q</th>
                        <th style="padding: 4px; border: 1px solid #000;">T</th>
                        <th style="padding: 4px; border: 1px solid #000;">E</th>
                        <th style="padding: 4px; border: 1px solid #000;">Ave.</th>
                    </tr>
                `;
            }
        }

        const catLabels = {
            core: "Core Functions",
            strategic: "Strategic Functions",
            support: "Support Functions"
        };

        const colCount = (isOpcr || isDpcr) ? 10 : 8;

        ['core', 'strategic', 'support'].forEach(cat => {
            const tbody = document.getElementById(`basis-tbody-${cat}`);
            if (!tbody) return;
            tbody.innerHTML = '';

            // Header row
            const trHeader = document.createElement('tr');
            trHeader.style.cssText = 'background: #f8fafc; border-top: 2px solid #000; border-bottom: 1px solid #000;';
            trHeader.innerHTML = `
                <td colspan="${colCount}" style="padding: 8px 12px; font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                    ${catLabels[cat]}
                </td>
            `;
            tbody.appendChild(trHeader);

            const rows = data.categories?.[cat] || [];
            if (rows.length === 0 || rows.every(r => !r.mfo && !r.indicators)) {
                const trEmpty = document.createElement('tr');
                trEmpty.style.cssText = 'border-bottom: 1px solid #000;';
                trEmpty.innerHTML = `
                    <td colspan="${colCount}" style="padding: 12px; text-align: center; color: #94a3b8; font-style: italic; font-size: 11px;">
                        No deliverables entered for this category.
                    </td>
                `;
                tbody.appendChild(trEmpty);
            } else {
                rows.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.style.cssText = 'border-bottom: 1px solid #000;';
                    if (isOpcr || isDpcr) {
                        const budgetRaw = (r.budget !== undefined && r.budget !== null && String(r.budget).trim() !== '')
                            ? parseFloat(String(r.budget).replace(/,/g, ''))
                            : null;
                        const budgetFormatted = (budgetRaw !== null && !isNaN(budgetRaw))
                            ? `${escapeHtml(r.budget_currency || '₱')} ${formatBudgetFull(budgetRaw)}`
                            : '—';
                        tr.innerHTML = `
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; line-height: 1.5;">
                                ${escapeHtml(r.mfo)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${escapeHtml(r.indicators)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; text-align: right; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${budgetFormatted}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accountable || '')}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accomplishments)}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.q || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.t || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.e || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 900; color: #0369a1; background: #f0f9ff; font-size: 11px;">
                                ${r.ave || '—'}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 10px; color: #475569;">
                                ${escapeHtml(r.remarks)}
                            </td>
                        `;
                    } else {
                        tr.innerHTML = `
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; line-height: 1.5;">
                                ${escapeHtml(r.mfo)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #0f172a; font-weight: 600; line-height: 1.5;">
                                ${escapeHtml(r.indicators)}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 11px; white-space: pre-wrap; color: #334155; line-height: 1.5;">
                                ${escapeHtml(r.accomplishments)}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.q || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.t || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; font-size: 11px;">
                                ${r.e || '—'}
                            </td>
                            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: 900; color: #0369a1; background: #f0f9ff; font-size: 11px;">
                                ${r.ave || '—'}
                            </td>
                            <td style="padding: 8px; vertical-align: top; border: 1px solid #000; font-size: 10px; color: #475569;">
                                ${escapeHtml(r.remarks)}
                            </td>
                        `;
                    }
                    tbody.appendChild(tr);
                });
            }
        });
    }

    // Launch active view on page load
    document.addEventListener('DOMContentLoaded', () => {
        initActiveTabView();
        if (window.isSpmsFormActive && typeof window.syncSpmsActiveTab === 'function') {
            window.syncSpmsActiveTab();
        }
        updateRubricsTabBadge();
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            setTimeout(() => exportToPdf(), 700);
        }
        if (typeof fetchDocumentComments === 'function') {
            fetchDocumentComments();
        }
    });

    // ==========================================
    // SPREADSHEET CONTEXT MENU & THREADED COMMENTS
    // ==========================================
    window.docComments = [];
    window.activeContextTarget = null;
    window.activeCommentThread = null;
    window.currentUserId = <?= (int)(session()->get('user_id') ?? 0) ?>;
    window.currentSysRole = '<?= esc(session()->get('role') ?? 'Employee') ?>';

    function bindCellContextMenu(rowOrEl) {
        if (!rowOrEl) return;
        const tds = rowOrEl.tagName === 'TD' ? [rowOrEl] : rowOrEl.querySelectorAll('td.spms-cell-input, td[data-field-name]');
        tds.forEach(td => {
            td.removeEventListener('contextmenu', handleCellContextMenu);
            td.addEventListener('contextmenu', handleCellContextMenu);
        });
    }

    window.activeSelectedCell = null;

    function selectSpreadsheetCell(td, fromContextMenu = false) {
        if (!td) return;
        if (td.id?.startsWith('label-cat-') || td.closest('tr')?.querySelector('[id^="label-cat-"]') || td.classList.contains('spms-category-header-cell') || td.querySelector('#badge-core-subtotal, #badge-strategic-subtotal, #badge-support-subtotal')) {
            if (window.activeSelectedCell) {
                window.activeSelectedCell.classList.remove('spms-cell-active');
                window.activeSelectedCell = null;
            }
            return;
        }
        const cellInput = td.querySelector('textarea, input') || (['TEXTAREA', 'INPUT'].includes(td.tagName) ? td : null);
        const isDisabledOrReadonlyRubricCell = Boolean(
            td.closest('#digital-rubrics-table') && (
                !td.classList.contains('rubric-score-td') ||
                td.classList.contains('rubric-dim-na') ||
                !cellInput ||
                cellInput.disabled ||
                cellInput.readOnly ||
                cellInput.classList.contains('is-na')
            )
        );
        if (td.classList.contains('qte-na') || td.classList.contains('rubric-dim-na') || (!fromContextMenu && isDisabledOrReadonlyRubricCell)) {
            if (window.activeSelectedCell) {
                window.activeSelectedCell.classList.remove('spms-cell-active');
                window.activeSelectedCell = null;
            }
            if (document.activeElement && (document.activeElement.tagName === 'TEXTAREA' || document.activeElement.tagName === 'INPUT')) {
                document.activeElement.blur();
            }
            return;
        }
        if (window.activeSelectedCell && window.activeSelectedCell !== td) {
            window.activeSelectedCell.classList.remove('spms-cell-active');
        }
        window.activeSelectedCell = td;
        td.classList.add('spms-cell-active');

        const tr = td.closest('tr');
        const input = td.querySelector('textarea, input') || (['TEXTAREA', 'INPUT'].includes(td.tagName) ? td : null);
        const inRubric = Boolean(td.closest('#digital-rubrics-table') || (input && input.classList.contains('rubric-cell-textarea')));
        const fieldName = td.dataset.fieldName || (input && (input.className.match(/field-([a-z_]+)/) || [])[1]) || (inRubric ? 'rubric' : 'row');
        const rowId = td.dataset.rowId || (tr ? (tr.dataset.rowId || tr.dataset.deliverableId) : null);
        const categoryMatch = tr && tr.className ? tr.className.match(/table-row-([a-z_]+)/) : null;
        const category = categoryMatch ? categoryMatch[1] : (tr ? tr.dataset.category : 'core') || 'core';

        window.activeContextTarget = { tr, td, input, fieldName, rowId, category };
    }

    function handleCellContextMenu(e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }

        let td = null;
        if (e && e.currentTarget && e.currentTarget !== document && e.currentTarget !== window && typeof e.currentTarget.closest === 'function') {
            td = e.currentTarget.tagName === 'TD' ? e.currentTarget : e.currentTarget.closest('td');
        }
        if (!td && e && e.target && typeof e.target.closest === 'function') {
            td = e.target.closest('td');
        }
        if (!td) return;

        const tr = td.closest('tr');
        const rowId = td.dataset.rowId || (tr ? (tr.dataset.rowId || tr.dataset.deliverableId) : null);
        const isHeaderOrNa = !tr || !rowId ||
            td.classList.contains('qte-na') ||
            td.classList.contains('rubric-dim-na') ||
            td.id?.startsWith('label-cat-') ||
            tr.querySelector('[id^="label-cat-"]') ||
            td.classList.contains('spms-category-header-cell') ||
            (td.closest('#digital-rubrics-table') && !td.classList.contains('rubric-score-td'));

        if (isHeaderOrNa) {
            closeContextMenu();
            if (window.activeSelectedCell) {
                window.activeSelectedCell.classList.remove('spms-cell-active');
                window.activeSelectedCell = null;
            }
            return;
        }

        selectSpreadsheetCell(td, true);

        const input = td.querySelector('textarea, input') || (e && e.target && ['TEXTAREA', 'INPUT'].includes(e.target.tagName) ? e.target : null);
        const inRubric = Boolean(td.closest('#digital-rubrics-table') || (input && input.classList.contains('rubric-cell-textarea')));
        const fieldName = td.dataset.fieldName || (input && (input.className.match(/field-([a-z_]+)/) || [])[1]) || (inRubric ? 'rubric' : 'row');
        const categoryMatch = tr.className ? tr.className.match(/table-row-([a-z_]+)/) : null;
        const category = categoryMatch ? categoryMatch[1] : (tr.dataset.category || 'core');

        window.activeContextTarget = { tr, td, input, fieldName, rowId, category };

        const posX = (e && e.clientX && e.clientX > 0) ? e.clientX : (cellTouchStartPos.x || window.innerWidth / 2);
        const posY = (e && e.clientY && e.clientY > 0) ? e.clientY : (cellTouchStartPos.y || window.innerHeight / 2);
        showContextMenu(posX, posY);
    }

    // Delegated contextmenu listener on table workspace (Desktop RMB & Mobile contextmenu)
    document.addEventListener('contextmenu', (e) => {
        // Intercept right-clicks on Digital Rubrics Matrix table cells and textareas
        const rubricTd = e.target.closest('#digital-rubrics-table td.rubric-score-td');
        if (rubricTd && !rubricTd.classList.contains('rubric-dim-na')) {
            const tr = rubricTd.closest('tr');
            const rowId = rubricTd.dataset.rowId || (tr ? (tr.dataset.rowId || tr.dataset.deliverableId) : null);
            if (rowId) {
                e.preventDefault();
                e.stopPropagation();
                handleCellContextMenu(e);
                return;
            }
        }

        const td = e.target.closest('td.spms-cell-input, td[data-field-name], td.spms-score-cell, #printable-form table tbody tr td');
        if (td && td.closest('#printable-form') && !td.classList.contains('qte-na')) {
            const tr = td.closest('tr');
            if (tr && (tr.dataset.rowId || tr.dataset.deliverableId)) {
                e.preventDefault();
                e.stopPropagation();
                handleCellContextMenu(e);
                return;
            }
        }

        // Outside actionable cells: suppress native browser RMB menu and dismiss any open custom menu / cell selection
        e.preventDefault();
        closeContextMenu();
        if (window.activeSelectedCell) {
            window.activeSelectedCell.classList.remove('spms-cell-active');
            window.activeSelectedCell = null;
        }
    });

    // Mobile Touch Long-Press support for spreadsheet context menu
    let cellTouchTimer = null;
    let cellTouchStartPos = { x: 0, y: 0 };
    let isCellLongPress = false;

    document.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) {
            if (cellTouchTimer) { clearTimeout(cellTouchTimer); cellTouchTimer = null; }
            return;
        }

        const touch = e.touches[0];
        cellTouchStartPos = { x: touch.clientX, y: touch.clientY };
        isCellLongPress = false;

        const td = e.target.closest('td.rubric-score-td, #digital-rubrics-table td, td.spms-cell-input, td[data-field-name], td.spms-score-cell, #printable-form table tbody tr td');
        const inRubric = td && td.closest('#digital-rubrics-table');
        const inPrintable = td && td.closest('#printable-form');
        if (!td || (!inPrintable && !inRubric)) return;

        const tr = td.closest('tr');
        const rowId = td.dataset.rowId || (tr ? (tr.dataset.rowId || tr.dataset.deliverableId) : null);
        if (!tr || !rowId) return;

        // Visually highlight cell immediately on touch
        selectSpreadsheetCell(td, true);

        cellTouchTimer = setTimeout(() => {
            isCellLongPress = true;

            // Blur active element so virtual keyboard closes
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }

            const input = td.querySelector('textarea, input') || (['TEXTAREA', 'INPUT'].includes(e.target.tagName) ? e.target : null);
            const fieldName = td.dataset.fieldName || (input && (input.className.match(/field-([a-z_]+)/) || [])[1]) || (inRubric ? 'rubric' : 'row');
            const categoryMatch = tr.className ? tr.className.match(/table-row-([a-z_]+)/) : null;
            const category = categoryMatch ? categoryMatch[1] : (tr.dataset.category || 'core');

            window.activeContextTarget = { tr, td, input, fieldName, rowId, category };

            if (navigator.vibrate) {
                try { navigator.vibrate(40); } catch(err) {}
            }
            showContextMenu(cellTouchStartPos.x, cellTouchStartPos.y);
        }, 380);
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!cellTouchTimer) return;
        const touch = e.touches[0];
        if (Math.abs(touch.clientX - cellTouchStartPos.x) > 20 || Math.abs(touch.clientY - cellTouchStartPos.y) > 20) {
            clearTimeout(cellTouchTimer);
            cellTouchTimer = null;
        }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        if (cellTouchTimer) {
            clearTimeout(cellTouchTimer);
            cellTouchTimer = null;
        }
        if (isCellLongPress) {
            if (e.cancelable) {
                e.preventDefault();
            }
            setTimeout(() => { isCellLongPress = false; }, 400);
        }
    });

    document.addEventListener('touchcancel', () => {
        if (cellTouchTimer) {
            clearTimeout(cellTouchTimer);
            cellTouchTimer = null;
        }
        isCellLongPress = false;
    });

    // Synchronize active spreadsheet cell on focusin
    document.addEventListener('focusin', (e) => {
        if (e.target.classList.contains('spms-cat-weight-input') || e.target.closest('[id^="label-cat-"]')) {
            return;
        }
        const td = e.target.closest('td.spms-cell-input, td[data-field-name], td.spms-score-cell, td.rubric-score-td, #digital-rubrics-table td, #printable-form table tbody tr td');
        if (td && (td.closest('#printable-form') || td.closest('#digital-rubrics-table'))) {
            selectSpreadsheetCell(td, false);
        }
    });

    function showContextMenu(x, y) {
        window._lastContextMenuOpenTime = Date.now();
        const menu = document.getElementById('spms-context-menu');
        const backdrop = document.getElementById('spms-context-backdrop');
        if (!menu) return;

        const target = window.activeContextTarget;
        const isRubric = Boolean(target && (target.td?.closest('#digital-rubrics-table') || (target.fieldName && target.fieldName.startsWith('rubric_'))));
        // Determine if target cell / document is currently editable
        const isEditable = target && target.input && !target.input.disabled && !target.input.readOnly && (window.canEditTargets || (isRubric && window.canEditTargets) || window.canEditEvaluation);

        // Edit mutating actions (Cut, Paste, Clear Cell) - only show when cell is editable
        const editActions = menu.querySelectorAll('[data-action="cut"], [data-action="paste"], [data-action="clear"]');
        editActions.forEach(el => {
            el.style.display = isEditable ? 'flex' : 'none';
        });

        // Row structural actions (Insert Above, Insert Below, Delete Row) - only during target drafting on printable form rows, never on rubrics matrix
        const canModifyRows = isEditable && window.canEditTargets && !isRubric;
        const targetActions = menu.querySelectorAll('[data-action="insert-above"], [data-action="insert-below"], [data-action="delete-row"]');
        targetActions.forEach(el => {
            el.style.display = canModifyRows ? 'flex' : 'none';
        });

        // Context Menu Dividers
        const div1 = document.getElementById('spms-ctx-div-1');
        const div2 = document.getElementById('spms-ctx-div-2');
        if (div1) {
            // Keep divider between Comment and Copy
            div1.style.display = 'block';
        }
        if (div2) {
            // Only show divider 2 if row actions are visible
            div2.style.display = canModifyRows ? 'block' : 'none';
        }

        menu.style.display = 'block';
        menu.classList.remove('hidden');

        if (backdrop && window.innerWidth <= 768) {
            backdrop.style.display = 'block';
            backdrop.classList.remove('hidden');
        }

        const menuWidth = 190;
        const menuHeight = isEditable ? 260 : 80;
        let left = x;
        let top = y;

        if (window.innerWidth <= 640) {
            left = Math.max(16, Math.min(x - 20, window.innerWidth - menuWidth - 16));
            top = Math.max(16, Math.min(y - 20, window.innerHeight - menuHeight - 16));
        } else {
            if (left + menuWidth > window.innerWidth) {
                left = Math.max(10, window.innerWidth - menuWidth - 15);
            }
            if (top + menuHeight > window.innerHeight) {
                top = Math.max(10, window.innerHeight - menuHeight - 15);
            }
        }

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    }

    function closeContextMenu() {
        const menu = document.getElementById('spms-context-menu');
        const backdrop = document.getElementById('spms-context-backdrop');
        if (menu) {
            menu.classList.add('hidden');
            menu.style.display = 'none';
        }
        if (backdrop) {
            backdrop.classList.add('hidden');
            backdrop.style.display = 'none';
        }
    }

    // Dismiss context menu on click outside, or open comment directly on badge tap
    document.addEventListener('click', (e) => {
        // Suppress synthetic click right after long-press open
        if (Date.now() - (window._lastContextMenuOpenTime || 0) < 550) {
            return;
        }

        // Direct tap on comment indicator badge (top-right corner of cell) opens comment thread immediately
        const tdComment = e.target.closest('td.has-cell-comment');
        if (tdComment) {
            const rect = tdComment.getBoundingClientRect();
            // If click/tap occurred within 32px of top-right corner
            if (e.clientX >= rect.right - 32 && e.clientY <= rect.top + 32) {
                e.preventDefault();
                e.stopPropagation();
                closeContextMenu();
                const tr = tdComment.closest('tr');
                const rowId = tdComment.dataset.rowId || (tr ? (tr.dataset.rowId || tr.dataset.deliverableId) : null);
                if (rowId) {
                    const input = tdComment.querySelector('textarea, input');
                    const fieldName = tdComment.dataset.fieldName || (input && (input.className.match(/field-([a-z_]+)/) || [])[1]) || 'row';
                    openCommentOverlay(rowId, fieldName, tdComment);
                    return;
                }
            }
        }

        // Cell selection on click
        if (e.target.classList.contains('spms-cat-weight-input') || e.target.closest('[id^="label-cat-"]')) {
            return;
        }
        const ctxMenu = document.getElementById('spms-context-menu');
        const wasContextMenuOpen = Boolean(ctxMenu && !ctxMenu.classList.contains('hidden') && ctxMenu.style.display !== 'none');

        if (wasContextMenuOpen && !e.target.closest('#spms-context-menu')) {
            closeContextMenu();
            if (window.activeSelectedCell) {
                window.activeSelectedCell.classList.remove('spms-cell-active');
                window.activeSelectedCell = null;
            }
            const clickedInput = e.target.closest('textarea, input');
            if (!clickedInput || clickedInput.disabled || clickedInput.readOnly) {
                if (document.activeElement && (document.activeElement.tagName === 'TEXTAREA' || document.activeElement.tagName === 'INPUT')) {
                    document.activeElement.blur();
                }
                return;
            }
        }

        const td = e.target.closest('td.spms-cell-input, td[data-field-name], td.spms-score-cell, td.rubric-score-td, #digital-rubrics-table td, #printable-form table tbody tr td');
        if (td && (td.closest('#printable-form') || td.closest('#digital-rubrics-table'))) {
            selectSpreadsheetCell(td, false);
        } else if (!e.target.closest('#spms-context-menu') && !e.target.closest('#spms-comment-overlay') && !e.target.closest('.modal') && !e.target.closest('button')) {
            if (window.activeSelectedCell) {
                window.activeSelectedCell.classList.remove('spms-cell-active');
                window.activeSelectedCell = null;
            }
        }

        if (!e.target.closest('#spms-context-menu')) {
            closeContextMenu();
        }
    });

    // Helper to copy text to clipboard with HTTP fallback
    async function copyTextToClipboard(text) {
        if (!text) return false;
        let copied = false;
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
                copied = true;
            }
        } catch(err) {}
        if (!copied) {
            try {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                copied = document.execCommand('copy');
                document.body.removeChild(ta);
            } catch(err) {}
        }
        if (copied && typeof showToast === 'function') {
            showToast('Copied cell text to clipboard', 'info');
        }
        return copied;
    }

    document.addEventListener('keydown', async (e) => {
        if (e.key === 'Escape') {
            closeContextMenu();
            closeCommentOverlay();
        }

        // Alt+M or Ctrl+Alt+M to open comment on active cell (works whether document is locked or unlocked)
        if ((e.altKey && (e.key === 'm' || e.key === 'M')) || (e.ctrlKey && e.altKey && (e.key === 'm' || e.key === 'M'))) {
            const activeEl = document.activeElement;
            const td = (activeEl && (activeEl.tagName === 'TD' ? activeEl : activeEl.closest('td'))) || window.activeSelectedCell;
            if (td) {
                const tr = td.closest('tr');
                if (tr && tr.dataset.rowId) {
                    e.preventDefault();
                    e.stopPropagation();
                    const input = td.querySelector('textarea, input');
                    const fieldName = td.dataset.fieldName || (input && (input.className.match(/field-([a-z_]+)/) || [])[1]) || 'row';
                    openCommentOverlay(tr.dataset.rowId, fieldName, td);
                }
            }
        }

        // Ctrl+C / Cmd+C when cell is selected on locked documents
        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
            const activeEl = document.activeElement;
            const isEditing = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && !activeEl.disabled;
            if (!isEditing && window.activeSelectedCell) {
                const td = window.activeSelectedCell;
                const input = td.querySelector('textarea, input');
                const text = input ? input.value : td.innerText.trim();
                if (text) {
                    await copyTextToClipboard(text);
                }
            }
        }

        // Keyboard arrow navigation on locked documents or table grid
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            const activeEl = document.activeElement;
            const isEditing = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && !activeEl.disabled;
            if (!isEditing && window.activeSelectedCell) {
                const td = window.activeSelectedCell;
                const tr = td.closest('tr');
                if (tr) {
                    let nextTd = null;
                    if (e.key === 'ArrowRight') {
                        nextTd = td.nextElementSibling;
                    } else if (e.key === 'ArrowLeft') {
                        nextTd = td.previousElementSibling;
                    } else if (e.key === 'ArrowDown') {
                        const nextTr = tr.nextElementSibling;
                        if (nextTr && nextTr.children) {
                            nextTd = nextTr.children[td.cellIndex] || nextTr.children[0];
                        }
                    } else if (e.key === 'ArrowUp') {
                        const prevTr = tr.previousElementSibling;
                        if (prevTr && prevTr.children) {
                            nextTd = prevTr.children[td.cellIndex] || prevTr.children[0];
                        }
                    }
                    if (nextTd && nextTd.tagName === 'TD') {
                        e.preventDefault();
                        selectSpreadsheetCell(nextTd);
                        nextTd.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                    }
                }
            }
        }
    });

    // Handle Context Menu Actions
    document.addEventListener('DOMContentLoaded', () => {
        const menu = document.getElementById('spms-context-menu');
        if (!menu) return;

        menu.querySelectorAll('.spms-ctx-item').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const action = btn.dataset.action;
                const target = window.activeContextTarget;
                closeContextMenu();
                if (!target) return;

                const { tr, td, input, fieldName, rowId, category } = target;

                if (action === 'comment') {
                    openCommentOverlay(rowId, fieldName, td);
                } else if (action === 'cut') {
                    if (input && !input.disabled) {
                        input.focus();
                        let text = '';
                        if (input.selectionStart !== input.selectionEnd) {
                            text = input.value.substring(input.selectionStart, input.selectionEnd);
                            input.setRangeText('', input.selectionStart, input.selectionEnd, 'end');
                        } else {
                            text = input.value;
                            input.value = '';
                        }
                        await copyTextToClipboard(text);
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else if (action === 'copy') {
                    let text = '';
                    if (input) {
                        if (input.selectionStart !== undefined && input.selectionStart !== input.selectionEnd) {
                            text = input.value.substring(input.selectionStart, input.selectionEnd);
                        } else {
                            text = input.value;
                        }
                    } else if (td) {
                        text = td.innerText.trim();
                    }
                    if (text) {
                        await copyTextToClipboard(text);
                    }
                } else if (action === 'paste') {
                    if (input && !input.disabled) {
                        input.focus();
                        try {
                            const text = await navigator.clipboard.readText();
                            if (text) {
                                if (input.selectionStart !== undefined) {
                                    input.setRangeText(text, input.selectionStart, input.selectionEnd, 'end');
                                } else {
                                    input.value = text;
                                }
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        } catch(err) {}
                    }
                } else if (action === 'clear') {
                    if (input && !input.disabled) {
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else if (action === 'insert-above') {
                    addTableRow(category, null, tr, 'above');
                } else if (action === 'insert-below') {
                    addTableRow(category, null, tr, 'below');
                } else if (action === 'delete-row') {
                    deleteTableRow(tr);
                }
            });
        });
    });

    // ==========================================
    // THREADED COMMENTS ENGINE
    // ==========================================
    async function fetchDocumentComments() {
        const docId = '<?= esc($doc['id'] ?? '') ?>';
        if (!docId) return;

        try {
            const verParam = window.viewingDocVersion ? `&version=${encodeURIComponent(window.viewingDocVersion)}` : '';
            const resp = await fetch(`<?= site_url('document/comments') ?>?doc_id=${encodeURIComponent(docId)}${verParam}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            if (data.status === 'success') {
                window.docComments = data.threads || [];
                applyAllCellCommentBadges();
            }
        } catch(err) {
            console.error('Failed to fetch document comments', err);
        }
    }

    function applyAllCellCommentBadges() {
        document.querySelectorAll('tr[data-row-id]').forEach(tr => {
            applyCellCommentBadgesToRow(tr);
        });
        if (typeof applyRubricCommentBadges === 'function') {
            applyRubricCommentBadges();
        }
    }

    function applyCellCommentBadgesToRow(tr) {
        if (!tr) return;
        const rowId = tr.dataset.rowId;
        if (!rowId) return;

        const tds = tr.querySelectorAll('td.spms-cell-input, td[data-field-name]');
        tds.forEach(td => {
            const fieldName = td.dataset.fieldName || 'row';
            const thread = (window.docComments || []).find(t => t.row_id === rowId && (t.field_name === fieldName || (!fieldName && t.field_name === 'row')));
            
            if (thread && thread.status !== 'deleted') {
                td.classList.add('has-cell-comment');
                if (thread.status === 'resolved') {
                    td.classList.add('comment-resolved');
                    td.title = `Resolved Comment by ${thread.author_name}: "${thread.comment_text.substring(0, 50)}..." (Alt+M or Right-click to view)`;
                } else {
                    td.classList.remove('comment-resolved');
                    td.title = `Comment by ${thread.author_name} (${thread.author_role}): "${thread.comment_text.substring(0, 50)}..." (Alt+M or Right-click to view)`;
                }
            } else {
                td.classList.remove('has-cell-comment', 'comment-resolved');
            }
        });
    }

    function openCommentOverlay(rowId, fieldName, targetEl) {
        if (!rowId) return;
        const overlay = document.getElementById('spms-comment-overlay');
        if (!overlay) return;

        fieldName = fieldName || 'row';
        const docId = '<?= esc($doc['id'] ?? '') ?>';

        // Find existing thread
        let thread = (window.docComments || []).find(t => t.row_id === rowId && t.field_name === fieldName);
        window.activeCommentThread = thread ? JSON.parse(JSON.stringify(thread)) : {
            id: null,
            document_id: docId,
            row_id: rowId,
            field_name: fieldName,
            status: 'open',
            replies: []
        };

        renderCommentThreadBody();

        overlay.style.display = 'flex';
        overlay.classList.remove('hidden');

        // Position overlay near target cell (or centered cleanly on mobile)
        if (window.innerWidth <= 640) {
            overlay.style.left = '50%';
            overlay.style.top = '50%';
            overlay.style.transform = 'translate(-50%, -50%)';
            overlay.style.width = 'calc(100vw - 24px)';
            overlay.style.maxWidth = '360px';
        } else if (targetEl) {
            overlay.style.transform = '';
            overlay.style.width = '340px';
            overlay.style.maxWidth = '340px';
            const rect = targetEl.getBoundingClientRect();
            let left = rect.right + 12;
            let top = rect.top;

            if (left + 350 > window.innerWidth) {
                left = Math.max(10, rect.left - 355);
            }
            if (top + 420 > window.innerHeight) {
                top = Math.max(10, window.innerHeight - 430);
            }

            overlay.style.left = `${left}px`;
            overlay.style.top = `${top}px`;
        } else {
            overlay.style.left = '50%';
            overlay.style.top = '50%';
            overlay.style.transform = 'translate(-50%, -50%)';
            overlay.style.width = '340px';
            overlay.style.maxWidth = '340px';
        }

        setTimeout(() => {
            const input = document.getElementById('comment-reply-input');
            if (input) input.focus();
        }, 80);
    }

    function closeCommentOverlay() {
        const overlay = document.getElementById('spms-comment-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.style.display = 'none';
        }
        const input = document.getElementById('comment-reply-input');
        if (input) input.value = '';
        window.activeCommentThread = null;
    }

    function renderCommentThreadBody() {
        const thread = window.activeCommentThread;
        const bodyEl = document.getElementById('spms-comment-thread-body');
        const titleEl = document.getElementById('comment-overlay-title');
        const dotEl = document.getElementById('comment-status-dot');
        const resolveBtn = document.getElementById('btn-toggle-resolve-thread');

        if (!bodyEl || !thread) return;

        let fieldTitle = 'DELIVERABLE';
        if (thread.field_name && thread.field_name !== 'row') {
            const rMatch = thread.field_name.match(/^rubric_(\d)_([qte])$/);
            if (rMatch) {
                const dimMap = { q: 'Quality', t: 'Timeliness', e: 'Efficiency' };
                fieldTitle = `${dimMap[rMatch[2]] || 'Rubric'} (Score ${rMatch[1]})`;
            } else {
                fieldTitle = thread.field_name.toUpperCase();
            }
        }
        titleEl.textContent = `${fieldTitle} Comments`;

        if (thread.id && !window.isHistoricalVersion) {
            resolveBtn.style.display = 'inline-block';
            if (thread.status === 'resolved') {
                dotEl.className = 'w-2 h-2 rounded-full bg-emerald-500';
                resolveBtn.textContent = 'Reopen';
                resolveBtn.className = 'text-[10px] font-bold px-2 py-0.5 rounded border transition-colors cursor-pointer text-amber-700 bg-amber-50 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800 hover:bg-amber-100';
            } else {
                dotEl.className = 'w-2 h-2 rounded-full bg-amber-500';
                resolveBtn.textContent = 'Resolve';
                resolveBtn.className = 'text-[10px] font-bold px-2 py-0.5 rounded border transition-colors cursor-pointer text-emerald-700 bg-emerald-50 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-100';
            }
        } else {
            dotEl.className = thread.id ? (thread.status === 'resolved' ? 'w-2 h-2 rounded-full bg-emerald-500' : 'w-2 h-2 rounded-full bg-amber-500') : 'w-2 h-2 rounded-full bg-zinc-400';
            resolveBtn.style.display = 'none';
        }

        const inputContainer = document.getElementById('comment-reply-input')?.closest('div.p-3, div.border-t');
        if (inputContainer) {
            inputContainer.style.display = window.isHistoricalVersion ? 'none' : '';
        }

        if (!thread.id) {
            bodyEl.innerHTML = `
                <div class="py-6 text-center text-zinc-400">
                    <svg class="w-8 h-8 mx-auto text-zinc-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="font-medium text-xs">No comments on this cell yet.</p>
                    <p class="text-[10px] text-zinc-500 mt-0.5">Start a conversation with reviewers or TWG/PMT below.</p>
                </div>
            `;
            return;
        }

        // Aggregate root comment and all replies chronologically into a messaging chat stream
        let allItems = [thread];
        if (Array.isArray(thread.replies) && thread.replies.length > 0) {
            allItems = allItems.concat(thread.replies);
        }

        let html = '<div class="flex flex-col space-y-2.5 w-full pb-1">';
        allItems.forEach((c, idx) => {
            html += renderSingleCommentCard(c, idx === 0);
        });
        html += '</div>';

        bodyEl.innerHTML = html;
        bodyEl.scrollTop = bodyEl.scrollHeight;
    }

    function renderSingleCommentCard(c, isRoot = false) {
        const isMe = Boolean(window.currentUserId && (Number(c.user_id) === Number(window.currentUserId)));
        const canDelete = !window.isHistoricalVersion && Boolean(window.currentUserId && (Number(c.user_id) === Number(window.currentUserId) || window.currentSysRole === 'Admin'));
        const initials = (c.author_name || 'U').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const role = c.author_role || 'Reviewer';
        const roleBadgeClass = getRoleBadgeClass(role);
        const timeStr = formatCommentTime(c.created_at);

        if (isMe) {
            // OUTGOING / "MY" COMMENT: Positioned on the RIGHT like messaging apps
            return `
                <div class="flex flex-col items-end self-end ml-auto max-w-[88%] text-left" style="text-align: left !important;">
                    <div class="flex items-center gap-1.5 mb-1 pr-1 text-[10px] text-zinc-400 text-left">
                        ${canDelete ? `
                            <button type="button" onclick="deleteCommentItem(${c.id}, ${isRoot ? 'true' : 'false'})" class="text-zinc-400 hover:text-red-500 p-0.5 rounded transition-colors cursor-pointer" title="Delete comment">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        ` : ''}
                        <span title="${escapeHtml(c.created_at || '')}">${timeStr}</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-300 text-[10.5px]">You</span>
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold uppercase tracking-tight ${roleBadgeClass}">
                            ${escapeHtml(role)}
                        </span>
                        <div class="w-4 h-4 rounded-full flex items-center justify-center font-black text-[8px] text-white flex-shrink-0" style="background-color: ${c.author_avatar_color || '#059669'};">
                            ${escapeHtml(initials)}
                        </div>
                    </div>
                    <div class="bg-emerald-600 dark:bg-emerald-600 text-white rounded-2xl rounded-tr-xs px-3.5 py-2 shadow-xs text-xs text-left" style="text-align: left !important;"><div class="text-[12px] leading-relaxed break-words whitespace-pre-wrap text-left text-white" style="text-align: left !important;">${escapeHtml(c.comment_text)}</div></div>
                </div>
            `;
        } else {
            // INCOMING / OTHER USER'S COMMENT: Positioned on the LEFT
            return `
                <div class="flex flex-col items-start self-start mr-auto max-w-[88%] text-left" style="text-align: left !important;">
                    <div class="flex items-center gap-1.5 mb-1 pl-1 text-[10px] text-zinc-400 text-left">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center font-black text-[8px] text-white flex-shrink-0" style="background-color: ${c.author_avatar_color || '#059669'};">
                            ${escapeHtml(initials)}
                        </div>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate text-[10.5px]">${escapeHtml(c.author_name)}</span>
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold uppercase tracking-tight ${roleBadgeClass}">
                            ${escapeHtml(role)}
                        </span>
                        <span title="${escapeHtml(c.created_at || '')}">${timeStr}</span>
                        ${canDelete ? `
                            <button type="button" onclick="deleteCommentItem(${c.id}, ${isRoot ? 'true' : 'false'})" class="text-zinc-400 hover:text-red-500 p-0.5 rounded transition-colors cursor-pointer" title="Delete comment">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        ` : ''}
                    </div>
                    <div class="bg-zinc-200/90 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-xs text-xs text-left" style="text-align: left !important;"><div class="text-[12px] leading-relaxed break-words whitespace-pre-wrap text-left text-zinc-900 dark:text-zinc-100" style="text-align: left !important;">${escapeHtml(c.comment_text)}</div></div>
                </div>
            `;
        }
    }

    function getRoleBadgeClass(role) {
        const r = (role || '').toUpperCase();
        if (r.includes('RATEE')) return 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300';
        if (r.includes('SUPERVISOR')) return 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300';
        if (r.includes('DEAN') || r.includes('VP')) return 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300';
        if (r.includes('TWG') || r.includes('PMT')) return 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300';
        if (r.includes('ADMIN')) return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300';
        return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300';
    }

    function formatCommentTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return dateStr;

            const now = new Date();
            const isToday = (d.toDateString() === now.toDateString());
            
            const yesterday = new Date();
            yesterday.setDate(now.getDate() - 1);
            const isYesterday = (d.toDateString() === yesterday.toDateString());

            const isSameYear = (d.getFullYear() === now.getFullYear());

            const timeFormatted = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

            if (isToday) {
                return `Today, ${timeFormatted}`;
            } else if (isYesterday) {
                return `Yesterday, ${timeFormatted}`;
            } else if (isSameYear) {
                const monthDay = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                return `${monthDay}, ${timeFormatted}`;
            } else {
                const fullDate = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                return `${fullDate}, ${timeFormatted}`;
            }
        } catch(e) {
            return dateStr;
        }
    }

    function getCsrfData() {
        const metaName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
        const metaHash = document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content') || '';
        return { name: metaName, hash: metaHash };
    }

    function updateCsrfFromResponse(resp) {
        if (!resp || !resp.headers) return;
        const newHash = resp.headers.get('x-csrf-token') || resp.headers.get('X-CSRF-TOKEN');
        if (newHash) {
            document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', newHash);
        }
    }

    async function postCurrentCommentOrReply() {
        const input = document.getElementById('comment-reply-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;

        const thread = window.activeCommentThread;
        if (!thread) return;

        const docId = '<?= esc($doc['id'] ?? '') ?>';
        const sendBtn = document.getElementById('btn-post-comment-reply');
        if (sendBtn) sendBtn.disabled = true;

        try {
            const { name: csrfName, hash: csrfHash } = getCsrfData();
            const formData = new FormData();
            formData.append('doc_id', docId);
            if (csrfHash) {
                formData.append(csrfName, csrfHash);
            }

            const reqHeaders = {
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfHash) {
                reqHeaders['X-CSRF-TOKEN'] = csrfHash;
            }

            if (thread.id) {
                // Reply to existing thread
                formData.append('parent_id', thread.id);
                formData.append('reply_text', text);

                const resp = await fetch('<?= site_url('document/comment/reply') ?>', {
                    method: 'POST',
                    headers: reqHeaders,
                    body: formData
                });
                updateCsrfFromResponse(resp);
                let res;
                try {
                    res = await resp.json();
                } catch(e) {
                    res = { status: 'error', message: resp.status === 403 ? 'CSRF security token expired. Please refresh the page.' : 'Failed to post reply.' };
                }
                if (res.status === 'success' && res.reply) {
                    if (!thread.replies) thread.replies = [];
                    thread.replies.push(res.reply);
                    input.value = '';
                    renderCommentThreadBody();

                    // Update local docComments cache
                    const idx = (window.docComments || []).findIndex(t => t.id === thread.id);
                    if (idx !== -1) window.docComments[idx] = thread;
                    applyAllCellCommentBadges();
                } else {
                    alert(res.message || 'Failed to post reply.');
                }
            } else {
                // Create new root comment
                formData.append('row_id', thread.row_id);
                formData.append('field_name', thread.field_name);
                formData.append('comment_text', text);

                const resp = await fetch('<?= site_url('document/comment/create') ?>', {
                    method: 'POST',
                    headers: reqHeaders,
                    body: formData
                });
                updateCsrfFromResponse(resp);
                let res;
                try {
                    res = await resp.json();
                } catch(e) {
                    res = { status: 'error', message: resp.status === 403 ? 'CSRF security token expired. Please refresh the page.' : 'Failed to create comment.' };
                }
                if (res.status === 'success' && res.comment) {
                    window.activeCommentThread = res.comment;
                    input.value = '';
                    renderCommentThreadBody();

                    if (!window.docComments) window.docComments = [];
                    window.docComments.push(res.comment);
                    applyAllCellCommentBadges();
                } else {
                    alert(res.message || 'Failed to create comment.');
                }
            }
        } catch(err) {
            console.error('Comment submission error', err);
            alert('An error occurred while posting your comment.');
        } finally {
            if (sendBtn) sendBtn.disabled = false;
        }
    }

    async function toggleResolveCurrentThread() {
        const thread = window.activeCommentThread;
        if (!thread || !thread.id) return;

        const nextStatus = thread.status === 'resolved' ? 'open' : 'resolved';
        const { name: csrfName, hash: csrfHash } = getCsrfData();
        const formData = new FormData();
        formData.append('comment_id', thread.id);
        formData.append('status', nextStatus);
        if (csrfHash) {
            formData.append(csrfName, csrfHash);
        }

        const reqHeaders = {
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfHash) {
            reqHeaders['X-CSRF-TOKEN'] = csrfHash;
        }

        try {
            const resp = await fetch('<?= site_url('document/comment/resolve') ?>', {
                method: 'POST',
                headers: reqHeaders,
                body: formData
            });
            updateCsrfFromResponse(resp);
            let res;
            try {
                res = await resp.json();
            } catch(e) {
                res = { status: 'error' };
            }
            if (res.status === 'success') {
                thread.status = nextStatus;
                renderCommentThreadBody();

                const idx = (window.docComments || []).findIndex(t => t.id === thread.id);
                if (idx !== -1) window.docComments[idx].status = nextStatus;
                applyAllCellCommentBadges();
            }
        } catch(err) {
            console.error('Failed to resolve comment', err);
        }
    }

    async function deleteCommentItem(commentId, isRoot = false) {
        if (!confirm('Are you sure you want to delete this comment?')) return;

        const { name: csrfName, hash: csrfHash } = getCsrfData();
        const formData = new FormData();
        formData.append('comment_id', commentId);
        if (csrfHash) {
            formData.append(csrfName, csrfHash);
        }

        const reqHeaders = {
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfHash) {
            reqHeaders['X-CSRF-TOKEN'] = csrfHash;
        }

        try {
            const resp = await fetch('<?= site_url('document/comment/delete') ?>', {
                method: 'POST',
                headers: reqHeaders,
                body: formData
            });
            updateCsrfFromResponse(resp);
            let res;
            try {
                res = await resp.json();
            } catch(e) {
                res = { status: 'error' };
            }
            if (res.status === 'success') {
                if (isRoot) {
                    if (window.activeCommentThread && window.activeCommentThread.replies && window.activeCommentThread.replies.length > 0) {
                        const nextRoot = window.activeCommentThread.replies.shift();
                        nextRoot.replies = window.activeCommentThread.replies;
                        window.activeCommentThread = nextRoot;
                        const idx = (window.docComments || []).findIndex(t => t.id === commentId);
                        if (idx !== -1) {
                            window.docComments[idx] = nextRoot;
                        }
                        renderCommentThreadBody();
                        applyAllCellCommentBadges();
                    } else {
                        window.docComments = (window.docComments || []).filter(t => t.id !== commentId);
                        closeCommentOverlay();
                        applyAllCellCommentBadges();
                    }
                } else {
                    if (window.activeCommentThread && window.activeCommentThread.replies) {
                        window.activeCommentThread.replies = window.activeCommentThread.replies.filter(r => r.id !== commentId);
                        renderCommentThreadBody();
                    }
                }
            } else {
                alert(res.message || 'Could not delete comment.');
            }
        } catch(err) {
            console.error('Delete comment error', err);
        }
    }

    // Ctrl+Enter on reply input
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'Enter') {
            const input = document.getElementById('comment-reply-input');
            if (document.activeElement === input) {
                e.preventDefault();
                postCurrentCommentOrReply();
            }
        }
    });

    // Auto-exit comment overlay when another button or action control is clicked
    ['pointerdown', 'click'].forEach(eventType => {
        document.addEventListener(eventType, (e) => {
            const overlay = document.getElementById('spms-comment-overlay');
            if (!overlay || overlay.classList.contains('hidden') || overlay.style.display === 'none') {
                return;
            }

            // Do not close if clicking inside the comment overlay itself
            if (e.target.closest('#spms-comment-overlay')) {
                return;
            }

            // Auto-exit if any button, link, modal trigger/element, or action control outside the comments div is clicked
            const isOtherButtonOrAction = e.target.closest('button, a, [role="button"], input[type="button"], input[type="submit"], select, summary, [data-action], .modal, dialog, [id*="modal"]');

            if (isOtherButtonOrAction) {
                closeCommentOverlay();
            }
        }, true);
    });

    // Also guarantee comment overlay auto-exits if any confirm or alert dialog opens
    if (typeof window.appConfirm === 'function') {
        const _origAppConfirm = window.appConfirm;
        window.appConfirm = function (...args) {
            closeCommentOverlay();
            return _origAppConfirm.apply(this, args);
        };
    }
    if (typeof window.appAlert === 'function') {
        const _origAppAlert = window.appAlert;
        window.appAlert = function (...args) {
            closeCommentOverlay();
            return _origAppAlert.apply(this, args);
        };
    }

    // Ensure comments are fetched immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchDocumentComments);
    } else {
        fetchDocumentComments();
    }
</script>

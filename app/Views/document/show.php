<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php 
    use App\Enums\FolderStatus;

    $status = $doc['folder_status']; 
    $isOwner = ($doc['owner_id'] == session()->get('user_id'));
    
    $editableStatuses = [
        FolderStatus::DRAFT_TARGET->value,
        FolderStatus::TARGET_RETURNED->value
    ];
    $isEditable = ($isOwner && in_array($status, $editableStatuses) && !$isGuide);
?>

<div class="h-full flex flex-col bg-bg">
    
    <div class="flex-none flex items-center justify-between py-2 px-3 sm:px-6 bg-bg gap-2 sm:gap-4">
        
        <div class="flex items-center gap-1 sm:gap-3 min-w-0 flex-1">
            <a href="javascript:history.back()" class="cursor-pointer shrink-0">
                <!-- Back-to-folders brand mark. text-text (not text-white) so it stays visible on the
                     theme-aware bg-bg header in both light and dark mode. -->
                <div class="flex-shrink-0 flex items-center gap-1 mr-2 sm:mr-6 text-text hover:text-accent transition-colors">
                    <!-- Folder/document icon -->
                    <img src="<?= base_url('assets/images/spms-logo.svg') ?>" alt="SPMS Logo" class="h-6 w-auto sm:h-7 object-contain" />
                    <span class="hidden sm:block font-black tracking-tighter text-xl uppercase">SPMS</span>
                </div>
            </a>

            <input type="text" maxlength="100" id="doc-title" value="<?= esc($doc['title']) ?>"
                class="bg-transparent border-none font-bold text-sm text-text focus:ring-0 px-1 sm:px-2 py-1 min-w-[50px]"
                oninput="AppState.setDirty(true); autoResize(this);"
                onblur="restoreTitle(this, '<?= esc($doc['title']) ?>'); autoResize(this);"
                onload="autoResize(this);"
                <?= (!$isEditable) ? 'disabled' : '' ?>>

            <span id="save-status" class="ml-1 sm:ml-3 shrink-0 text-[10px] uppercase tracking-widest font-bold transition-all"></span>
        </div>
        
        <!-- Call autoResize immediately after the element is in DOM -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const titleInput = document.getElementById('doc-title');
                if (titleInput) autoResize(titleInput);
            });
        </script>
        
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <?php if (!$isGuide): ?>
                <?php if ($doc['is_target'] == 0 && !in_array($status, [FolderStatus::DRAFT->value, FolderStatus::REEVALUATE->value])): ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Supporting<span class="hidden sm:inline"> Evidence</span>
                    </button>

                <?php elseif ($status === FolderStatus::APPROVED->value || $status === FolderStatus::TWG_APPROVED->value || $status === FolderStatus::TWG_DISAPPROVED->value): ?>
                    <?php if (session()->get('role') === 'TWG'): ?>
                        <div class="flex gap-1.5 sm:gap-2">
                            <button id="btn-twg-disapprove" type="button" 
                                    onclick="setTwgStatus('twg_disapproved')" 
                                    class="<?= $status === FolderStatus::TWG_DISAPPROVED->value ? 'bg-danger-600 ring-2 ring-danger-400' : 'bg-danger-500 hover:bg-danger-600' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-danger-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                Disapprove
                            </button>
                            <button id="btn-twg-approve" type="button" 
                                    onclick="setTwgStatus('twg_approved')" 
                                    class="<?= $status === FolderStatus::TWG_APPROVED->value ? 'bg-success-600 ring-2 ring-success-400' : 'bg-success-500 hover:bg-success-600' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                Approve
                            </button>
                        </div>
                    <?php else: ?>
                        <?php 
                            $canRemoveEvalApproval = false;
                            if (session()->get('role') === 'Admin' && $status === FolderStatus::APPROVED->value) $canRemoveEvalApproval = true;
                            if (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value && $status === FolderStatus::APPROVED->value) $canRemoveEvalApproval = true;
                        ?>
                        <?php if ($canRemoveEvalApproval): ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $evalEndCol = $ownerDocType . '_eval_end';
                                $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                            ?>
                            <button id="btn-unapprove-evaluation" type="button" 
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderEvaluation() })"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        <?php else: ?>
                            <button type="button" disabled class="bg-success-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                <span class="hidden sm:inline">Folder </span><?= $status === FolderStatus::TWG_APPROVED->value ? 'TWG Approved' : ($status === FolderStatus::TWG_DISAPPROVED->value ? 'TWG Disapproved' : 'Approved ✓') ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                <?php elseif ($status === FolderStatus::EVALUATED->value): ?>
                    <?php if ($isOwner): ?>
                        <button type="button" disabled class="bg-warning-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            <span class="hidden sm:inline">Awaiting </span>Approval
                        </button>
                    <?php else: ?>
                        
                        <?php if (session()->get('role') === 'Admin'): ?>
                            <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Monitoring<span class="hidden sm:inline"> View</span>
                            </button>
                        
                        <?php elseif (session()->get('role') === 'TWG'): ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-twg-disapprove" type="button" 
                                        onclick="setTwgStatus('twg_disapproved')" 
                                        class="bg-danger-500 hover:bg-danger-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-danger-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Disapprove
                                </button>
                                <button id="btn-twg-approve" type="button" 
                                        onclick="setTwgStatus('twg_approved')" 
                                        class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve
                                </button>
                            </div>
                        <?php elseif (isset($routingStatus) && $routingStatus === FolderStatus::APPROVED->value): ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $evalEndCol = $ownerDocType . '_eval_end';
                                $isEvalPeriodEnded = !empty($doc[$evalEndCol]) && date('Y-m-d H:i:s') > $doc[$evalEndCol]; 
                            ?>
                            <button id="btn-unapprove-evaluation" type="button" 
                                    <?= $isEvalPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderEvaluation() })"' ?>
                                    class="<?= $isEvalPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                Remove<span class="hidden sm:inline"> Approval</span>
                            </button>
                        <?php else: ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button type="button" 
                                        onclick="rate()" 
                                        class="flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Calculate
                                </button>
                                <button id="btn-return" type="button" 
                                        onclick="saveWith({ after: () => returnFolderRevision() })" 
                                        class="bg-revision-500 hover:bg-revision-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-revision-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Return<span class="hidden sm:inline"> for Revision</span>
                                </button>
                                <button id="btn-approve" type="button" 
                                        onclick="saveWith({ after: () => approveFolderEvaluation() })" 
                                        class="bg-success-500 hover:bg-success-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-success-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                    Approve<span class="hidden sm:inline"> Rating</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TO_EVALUATE->value || $status === FolderStatus::REEVALUATE->value): ?>
                    <?php if ($isOwner): ?>
                        <div class="flex gap-1.5 sm:gap-2">
                            <button type="button" 
                                    onclick="rate()" 
                                    class="flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-sm transition-all active:scale-[0.98] cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Calculate
                            </button>
                            <button id="btn-submit" type="button" 
                                    onclick="saveWith({ after: () => lockFolderEvaluation() })" 
                                    class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                                <?= $status === FolderStatus::REEVALUATE->value ? 'Submit Revision' : '<span class="sm:hidden">Self-Rate</span><span class="hidden sm:inline">Complete Self-Rating</span>' ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::UNEVALUATED->value): ?>
                    <button type="button" disabled class="bg-danger-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Missed Deadline
                    </button>
                    
                <?php elseif ($status === FolderStatus::DRAFT_TARGET->value || $status === FolderStatus::TARGET_RETURNED->value): ?>
                    <?php if ($isOwner): ?>
                        <button id="btn-submit-target" type="button" 
                                onclick="saveWith({ after: () => lockFolderTarget() })" 
                                class="bg-info-500 hover:bg-info-600 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg shadow-info-500/20 transition-all active:scale-[0.98] cursor-pointer">
                            <span class="sm:hidden">Submit Target</span><span class="hidden sm:inline">Submit Targets</span>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Wait<span class="hidden sm:inline">ing for Employee Targets</span>
                        </button>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::PENDING_TARGET_APPROVAL->value): ?>
                    <?php if ($isOwner): ?>
                        <button type="button" disabled class="bg-warning-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            <span class="hidden sm:inline">Awaiting </span>Target Approval
                        </button>
                    <?php else: ?>
                        <?php if (session()->get('role') === 'Admin'): ?>
                            <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                                Monitoring<span class="hidden sm:inline"> View</span>
                            </button>
                        <?php else: ?>
                            <?php 
                                $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                                $targetEndCol = $ownerDocType . '_target_end';
                                $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                            ?>
                            <div class="flex gap-1.5 sm:gap-2">
                                <button id="btn-return-target" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => returnTargetRevision() })"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-revision-500/50 cursor-not-allowed opacity-80' : 'bg-revision-500 hover:bg-revision-600 shadow-revision-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                    Return<span class="hidden sm:inline"> Target for Revision</span>
                                </button>
                                <button id="btn-approve-target" type="button" 
                                        <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => approveFolderTarget() })"' ?>
                                        class="<?= $isTargetPeriodEnded ? 'bg-success-500/50 cursor-not-allowed opacity-80' : 'bg-success-500 hover:bg-success-600 shadow-success-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                                    Approve<span class="hidden sm:inline"> Target</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php elseif ($status === FolderStatus::TARGET_APPROVED->value || $status === FolderStatus::SUBMITTED->value): ?>
                    <?php if (!$isOwner && $status === FolderStatus::TARGET_APPROVED->value): ?>
                        <?php 
                            $ownerDocType = strtolower($doc['doc_type'] ?? 'ipcr');
                            $targetEndCol = $ownerDocType . '_target_end';
                            $isTargetPeriodEnded = !empty($doc[$targetEndCol]) && date('Y-m-d H:i:s') > $doc[$targetEndCol]; 
                        ?>
                        <button id="btn-unapprove-target" type="button" 
                                <?= $isTargetPeriodEnded ? 'disabled' : 'onclick="saveWith({ after: () => unapproveFolderTarget() })"' ?>
                                class="<?= $isTargetPeriodEnded ? 'bg-warning-500/50 cursor-not-allowed opacity-80' : 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20 active:scale-[0.98] cursor-pointer' ?> text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg transition-all">
                            Remove<span class="hidden sm:inline"> Approval</span>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-highlight-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                            Awaiting Eval<span class="hidden sm:inline"> Window</span>
                        </button>
                    <?php endif; ?>
                <?php elseif ($status === \App\Enums\FolderStatus::TARGET_UNAPPROVED->value): ?>
                    <button type="button" disabled class="bg-revision-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Target<span class="hidden sm:inline"> Unapproved</span>
                    </button>

                <?php else: ?>
                    <button type="button" disabled class="bg-zinc-500 text-white text-[10px] sm:text-xs font-bold py-2 sm:py-2.5 px-3 sm:px-6 rounded-lg shadow-lg opacity-80 cursor-not-allowed">
                        Drafting
                    </button>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="bg-info-50 dark:bg-info-500/10 border border-info-200 dark:border-info-500/20 text-info-600 dark:text-info-400 text-[10px] uppercase tracking-widest font-black py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg shadow-sm cursor-default flex items-center gap-1.5">
                    Guide<span class="hidden sm:inline"> Template</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-none flex bg-bg border-b border-surface-border px-3 sm:px-6 <?= $isEditable ? 'gap-2' : 'gap-4' ?> text-sm font-bold pt-2 overflow-x-auto whitespace-nowrap scrollbar-hide" id="tab-bar">
        <!-- Tabs injected here via JS -->
    </div>

    <div class="flex-1 min-h-0 w-full relative bg-white dark:bg-zinc-950 overflow-x-auto" id="editor-container">
        <textarea id="editable-doc" name="content"></textarea>
    </div>
</div>

<script>
    <?php
    $tabsJson = '[]';
    if ($doc && !empty($doc['tabs'])) {
        $tabsJson = is_string($doc['tabs']) ? $doc['tabs'] : json_encode($doc['tabs']);
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
            
            <?php if ($isEditable): ?>
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
            <?php endif; ?>
            
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
        
        <?php if ($isEditable): ?>
        const addBtn = document.createElement('button');
        addBtn.innerHTML = '＋';
        addBtn.title = 'Add Tab';
        addBtn.className = 'pb-2 border-b-2 border-transparent text-text-muted hover:text-text transition-colors text-lg font-black px-2';
        addBtn.onclick = () => addTab();
        tabBar.appendChild(addBtn);
        <?php endif; ?>
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
</script> <script src="<?= base_url('assets/js/editor/functions.js?v=' . time()) ?>"></script>
<script src="<?= base_url('assets/js/editor/saveDocument.js') ?>"></script>

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
        }
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        autoSave();
    });

    async function lockFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        let finalScore = editorBody.getAttribute('data-final-score') || '';
        
        // Prioritize manually edited cell text if available
        const finalCell = editorBody.querySelector('.calc-final-total');
        if (finalCell && finalCell.innerText.trim() !== '') {
            finalScore = finalCell.innerText.trim();
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_rating', finalScore);

        document.getElementById('btn-submit').innerText = 'Locking...';
        apiPost('<?= site_url('folder/evaluate') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function approveFolderEvaluation() {
        const editorBody = tinymce.get('editable-doc').getBody();
        let finalScore = editorBody.getAttribute('data-final-score') || '';
        
        // Prioritize manually edited cell text if available
        const finalCell = editorBody.querySelector('.calc-final-total');
        if (finalCell && finalCell.innerText.trim() !== '') {
            finalScore = finalCell.innerText.trim();
        }

        const ok = await window.appConfirm("Complete and approve this evaluation?", { confirmText: 'Approve' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('final_score', finalScore);

        document.getElementById('btn-approve').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function unapproveFolderEvaluation() {
        const ok = await window.appConfirm("Remove your approval and revert to To Evaluate?", { confirmText: 'Remove Approval' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-unapprove-evaluation').innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function setTwgStatus(status) {
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');
        formData.append('status', status);

        apiPost('<?= site_url('folder/twg_approve') ?>', formData, {
            onSuccess: () => window.location.reload(),
            onError: async (errMsg) => {
                await window.appAlert(errMsg || "An error occurred.");
                window.location.reload();
            }
        });
    }

    async function returnFolderRevision() {
        const ok = await window.appConfirm("Return this to the employee for revision?", { variant: 'warning', confirmText: 'Return' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-return').innerText = 'Returning...';
        apiPost('<?= site_url('folder/return') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function lockFolderTarget() {
        const ok = await window.appConfirm("Submit these targets for approval? You won't be able to edit them until returned.", { confirmText: 'Submit Targets' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-submit-target').innerText = 'Submitting...';
        apiPost('<?= site_url('folder/submit_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function approveFolderTarget() {
        const ok = await window.appConfirm("Approve these targets?", { confirmText: 'Approve' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-approve-target').innerText = 'Approving...';
        apiPost('<?= site_url('folder/approve_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function unapproveFolderTarget() {
        const ok = await window.appConfirm("Remove approval and revert to pending?", { confirmText: 'Remove Approval' });
        if (!ok) return;

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-unapprove-target').innerText = 'Removing...';
        apiPost('<?= site_url('folder/unapprove_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }

    async function returnTargetRevision() {
        const ok = await window.appConfirm("Return targets to the employee for revision?", { variant: 'warning', confirmText: 'Return' });
        if (!ok) return;
        
        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?>');

        document.getElementById('btn-return-target').innerText = 'Returning...';
        apiPost('<?= site_url('folder/return_target') ?>', formData, {
            onSuccess: () => window.location.reload()
        });
    }
</script>

<script src="<?= base_url('assets/js/editor/plugins.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/TableTools.js') ?>"></script>
<script src="<?= base_url('assets/js/editor/config.js') ?>"></script>

<script>
    window.isGuide = <?= json_encode($isGuide) ?>;
    window.status = <?= json_encode($doc['folder_status']) ?>;
    window.isTarget = <?= json_encode($doc['is_target'] == 1) ?>;
    window.isOwner = <?= json_encode($doc['owner_id'] == session()->get('user_id')) ?>;

    // Enum values exported for JS use
    window.FolderStatus = <?= json_encode([
        'DRAFT_TARGET'            => \App\Enums\FolderStatus::DRAFT_TARGET->value,
        'PENDING_TARGET_APPROVAL' => \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
        'TARGET_APPROVED'         => \App\Enums\FolderStatus::TARGET_APPROVED->value,
        'TARGET_RETURNED'         => \App\Enums\FolderStatus::TARGET_RETURNED->value,
        'DRAFT'                   => \App\Enums\FolderStatus::DRAFT->value,
        'SUBMITTED'               => \App\Enums\FolderStatus::SUBMITTED->value,
        'TO_EVALUATE'             => \App\Enums\FolderStatus::TO_EVALUATE->value,
        'EVALUATED'               => \App\Enums\FolderStatus::EVALUATED->value,
        'APPROVED'                => \App\Enums\FolderStatus::APPROVED->value,
        'REEVALUATE'              => \App\Enums\FolderStatus::REEVALUATE->value,
        'UNEVALUATED'             => \App\Enums\FolderStatus::UNEVALUATED->value,
    ]) ?>;

    let isFullyLocked = true;
    let useFullEditor = false;
    let useRemarksOnlyEditor = false;

    if (isGuide) {
        // Guide documents can only be edited by their owner (the Admin)
        isFullyLocked = !isOwner;
        useFullEditor = isOwner;
    } else {
        if (status === FolderStatus.DRAFT_TARGET || status === FolderStatus.TARGET_RETURNED) {
            isFullyLocked = !isOwner;
            useFullEditor = isOwner; // Target drafting gets the full editor to build tables
        } else if (status === FolderStatus.PENDING_TARGET_APPROVAL && !isOwner) {
            // Supervisor reviewing targets can only edit the remarks column
            isFullyLocked = !isTarget;
            useFullEditor = false;
            useRemarksOnlyEditor = isTarget; // Only allow editing remarks if it's the target document
        } else if (status === FolderStatus.DRAFT) {
            isFullyLocked = !isOwner;
            useFullEditor = false; // Eval drafting gets plain editor (structure locked)
        } else if ((status === FolderStatus.TO_EVALUATE || status === FolderStatus.REEVALUATE) && isOwner) {
            // Owner can only self-rate the TARGET document.
            isFullyLocked = !isTarget && status === FolderStatus.TO_EVALUATE;
            // REEVALUATE implies revision of eval, or revision of targets?
            // For now, if REEVALUATE, we give them the plain editor so they can fix their ratings/eval.
            // If they need to fix targets, they would need to be back in DRAFT_TARGET.
            useFullEditor = false; 
        } else if (status === FolderStatus.EVALUATED && !isOwner) {
            // Evaluator can only evaluate/rate the TARGET document.
            isFullyLocked = !isTarget;
            useFullEditor = false;
        }
    }

    if (useFullEditor) {
        initEditor();
    } else if (useRemarksOnlyEditor) {
        initRemarksOnlyEditor();
    } else {
        initPlainEditor(isFullyLocked);
    }
</script>

<?= $this->endSection() ?>
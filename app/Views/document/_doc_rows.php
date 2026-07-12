<?php 
    use App\Enums\FolderStatus;
    
    $groupedGuides = $groupedGuides ?? [];
    $hasGuides = !empty($groupedGuides);

    $folderModel = new \App\Models\DocumentFolderModel();
    $isLocked = $activeFolder ? $folderModel->isFolderLocked($activeFolder) : true;
?>

<?php if (!$activeFolder): ?>
    <div class="flex-1 flex flex-col items-center justify-center min-h-[400px] w-full">
        <?php if (session()->get('role') === 'Admin'): ?>
            <button onclick="document.getElementById('btn-create-folder-modal').click()" class="w-full max-w-2xl border-2 border-dashed border-surface-border hover:border-accent rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-accent/5 transition-all group cursor-pointer h-full min-h-[300px]">
                <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 group-hover:bg-accent/10 text-zinc-400 group-hover:text-accent dark:text-zinc-500 mb-4 shadow-sm transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-text group-hover:text-accent transition-colors mb-1">Create New Folder</h3>
                <p class="text-sm text-text-muted max-w-sm">Click here to start a new evaluation period and create the initial folder.</p>
            </button>
        <?php else: ?>
            <div class="w-full max-w-2xl border-2 border-dashed border-surface-border rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 h-full min-h-[300px]">
                <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-text mb-1">Select a Folder</h3>
                <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    
    <div class="flex flex-col lg:flex-row gap-8 h-full relative">
        
        <div class="flex flex-col flex-1 min-w-0 h-full relative">
            
            <div class="relative mb-6 pr-4 shrink-0" id="folder-dropdown-container">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                    <span class="text-zinc-400">/</span>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md 
                        <?= $activeFolder['status'] === 'draft' ? 'bg-zinc-100 text-zinc-500' : 
                           ($activeFolder['status'] === 'evaluated' ? 'bg-success-100 text-success-600' : 'bg-warning-100 text-warning-600') ?>">
                        STATUS: <?= esc($activeFolder['status']) ?>
                    </span>
                </div>
                
                <button onclick="toggleFolderDropdown()" class="flex items-center justify-between w-full text-left group cursor-pointer lg:cursor-default">
                    <h1 class="text-3xl font-black tracking-tight text-text truncate group-hover:text-accent lg:group-hover:text-text transition-colors">
                        <?= esc($activeFolder['title']) ?>
                    </h1>
                    <!-- Chevron-down: rotates via toggleFolderDropdown() to indicate the mobile folder-switcher is open -->
                    <svg id="folder-dropdown-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0 text-text-muted transition-transform lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <p class="text-xs font-bold text-text-muted mt-2">
                    Evaluation Window: 
                    <span class="text-text"><?= !empty($activeFolder['eval_date_start']) ? date('M d, Y h:ia', strtotime($activeFolder['eval_date_start'])) : 'Not Set' ?></span> 
                    — 
                    <span class="text-text"><?= !empty($activeFolder['eval_date_end']) ? date('M d, Y h:ia', strtotime($activeFolder['eval_date_end'])) : 'Not Set' ?></span>
                </p>

                <div id="folder-dropdown-menu" class="hidden absolute top-full left-0 mt-2 w-full max-w-sm bg-surface border border-surface-border rounded-xl shadow-xl z-[40] max-h-64 overflow-y-auto lg:hidden custom-scrollbar">
                    <?php if (session()->get('role') === 'Admin'): ?>
                        <div class="p-2 border-b border-surface-border">
                            <button onclick="toggleFolderDropdown(); event.stopPropagation(); setTimeout(() => document.getElementById('btn-create-folder-modal').click(), 50);" 
                                class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-bold text-accent bg-accent/10 hover:bg-accent/20 transition-colors flex justify-between items-center cursor-pointer">
                                + Create New Folder
                            </button>
                        </div>
                    <?php endif; ?>
                    <?= view('document/_folder_rows', ['folders' => $sidebarFolders ?? [], 'selectedFolderId' => $activeFolder['id']]) ?>
                </div>
            </div>

            <div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 overflow-x-auto custom-scrollbar">
                <button id="tab-btn-mine" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-all cursor-pointer" onclick="switchDocTab('mine')">
                    My Submissions
                    <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors"><?= count($myDocs) ?></span>
                </button>

                <?php $isFirst = false; ?>
                <?php foreach ($groupedGuides as $index => $group): ?>
                    <button id="tab-btn-guide-<?= $index ?>" 
                            class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 <?= $isFirst ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> transition-all cursor-pointer" 
                            onclick="switchDocTab('guide-<?= $index ?>')">
                        Guide: <?= esc($group['superior']['name']) ?> <span class="text-text-muted font-normal text-[9px]">(<?= esc($group['superior']['role']) ?>)</span>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full <?= $isFirst ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors"><?= count($group['docs']) ?></span>
                    </button>
                    <?php $isFirst = false; ?>
                <?php endforeach; ?>
            </div>

            <!-- Added h-[calc(100dvh-240px)] for mobile, resets to h-auto on lg screens -->
            <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-0 relative h-[calc(100dvh-240px)] lg:h-auto pb-32 lg:pb-0">
                
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0 z-10 hidden md:grid">
                    <div class="col-span-6">Document Name</div>
                    <div class="col-span-4">Status / Activity</div>
                    <div class="col-span-2 text-right">Actions</div>
                </div>

                <?php $isFirst = false; ?>
                <?php foreach ($groupedGuides as $index => $group): ?>
                    <!-- Added flex-1 to tab content -->
                    <div id="tab-content-guide-<?= $index ?>" class="tab-content-doc hidden flex-1 bg-info-50/20 dark:bg-info-900/10">
                        <div class="overflow-y-auto custom-scrollbar flex-1">
                            <div class="divide-y divide-surface-border mx-2">
                                <?php foreach ($group['docs'] as $doc): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-4 items-start md:items-center hover:bg-zinc-100/50 dark:bg-zinc-800/40 transition-all group">
                                        <div class="col-span-1 md:col-span-6 flex items-center gap-4">
                                            <div class="bg-info-100 dark:bg-info-900/40 p-1.5 rounded-xl text-info-600 dark:text-info-400 border border-info-200 dark:border-info-800 group-hover:scale-110 transition-transform">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <a href="<?= site_url('document/' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-info-500 transition-colors truncate">
                                                    <?= esc($doc['title']) ?>
                                                </a>
                                                <span class="text-[10px] text-info-500 font-bold tracking-tight uppercase">Basis / Guide</span>
                                            </div>
                                        </div>
                                        <div class="col-span-1 md:col-span-4 flex flex-row md:flex-col gap-2 md:gap-0.5 items-center md:items-start pl-12 md:pl-0">
                                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest"><?= esc($group['superior']['role']) ?> Template</span>
                                            <span class="text-[10px] font-semibold text-text-muted hidden md:inline"><?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?></span>
                                        </div>
                                        <div class="col-span-1 md:col-span-2 flex justify-start md:justify-end gap-1 pl-12 md:pl-0">
                                            <a href="<?= site_url('document/' . $doc['id']) ?>" class="text-[10px] font-black uppercase tracking-widest text-info-600 dark:text-info-400 hover:text-info-800 px-3 py-1.5 rounded-lg bg-info-50 dark:bg-info-500/10 hover:bg-info-100 dark:bg-info-500/20 transition-colors cursor-pointer">
                                                View Target
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php $isFirst = false; ?>
                <?php endforeach; ?>

                <!-- Added flex-1 to tab content -->
                <div id="tab-content-mine" class="tab-content-doc flex-1 flex flex-col lg:absolute lg:inset-0 lg:top-[45px] bg-surface">
                    <div id="doc-scroll-container" class="overflow-y-auto custom-scrollbar flex-1">
                        <div class="divide-y divide-surface-border mx-2">
                            <?php if (empty($myDocs)): ?>
                                <div id="empty-doc" class="p-24 text-center">
                                    <p class="text-sm text-text-muted font-medium italic">You haven't created any documents yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($myDocs as $doc): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-4 items-start md:items-center hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors group">
                                        <div class="col-span-1 md:col-span-6 flex items-center gap-4">
                                            <div class="bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-xl text-text-muted border border-surface-border group-hover:scale-110 transition-transform">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <a href="<?= site_url('document/' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-accent hover:underline transition-colors truncate">
                                                    <?= esc($doc['title']) ?>
                                                </a>
                                                <span class="text-[10px] text-text-muted font-bold tracking-tight">
                                                    Last edited <?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-span-1 md:col-span-4 flex items-center gap-2 pl-12 md:pl-0">
                                            <?php if (isset($doc['is_target']) && $doc['is_target'] == 1): ?>
                                                <span class="text-[10px] font-black text-warning-600 bg-warning-100 px-3 py-1 rounded-lg uppercase tracking-widest border border-warning-200">
                                                    ★ Basis Target
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-3 py-1 rounded-lg uppercase tracking-widest">
                                                    Evidence
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-span-1 md:col-span-2 flex justify-start md:justify-end gap-1 pl-12 md:pl-0 lg:opacity-0 lg:group-hover:opacity-100 transition-all lg:translate-x-2 lg:group-hover:translate-x-0">
                                            <?php if (!$isReadOnly && !$isLocked): ?>
                                                <!-- Star icon (icon-only button, see title= for tooltip): marks this doc as the ★ Basis Target used for the final rating -->
                                                <button class="p-2 rounded-lg text-warning-500 hover:bg-warning-50 dark:hover:bg-warning-500/10 transition-all cursor-pointer border border-surface-border md:border-transparent"
                                                        onclick="setTargetDocument('<?= $doc['id'] ?>', '<?= $activeFolder['id'] ?>')" title="Set as Basis for Evaluation">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                </button>

                                                <!-- Trash icon (icon-only button): opens the shared confirm/delete modal (components/delete_modal.php) via .btn-delete-modal, posting to data-url with the doc's data-id -->
                                                <button class="btn-delete-modal p-2 rounded-lg text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-500/10 hover:text-danger-600 transition-all cursor-pointer border border-surface-border md:border-transparent"
                                                        data-id="<?= $doc['id'] ?>" data-desc="<?= esc($doc['title']) ?>" data-url="<?= site_url('document') ?>" data-title="Delete Document">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="bottom-sheet" class="lg:overflow-y-auto custom-scrollbar lg:gap-6 fixed inset-x-0 bottom-0 z-50 bg-surface border-t border-surface-border shadow-[0_-10px_40px_rgba(0,0,0,0.1)] rounded-t-3xl transition-transform duration-300 transform translate-y-[calc(100%-95px)] lg:static lg:translate-y-0 lg:w-80 lg:shrink-0 lg:shadow-none lg:border-none lg:bg-transparent lg:rounded-none flex flex-col">
            
            <div class="lg:hidden flex justify-center py-3 cursor-pointer touch-none" onclick="toggleBottomSheet()">
                <div class="w-12 h-1.5 bg-zinc-300 dark:bg-zinc-700 rounded-full"></div>
            </div>

            <div class="px-5 pb-5 lg:p-0">
                <div class="bg-surface lg:border lg:border-surface-border rounded-2xl lg:p-5 lg:shadow-sm flex flex-col gap-4">
                    <h4 class="hidden lg:block text-[10px] font-black uppercase text-text-muted tracking-widest border-b border-surface-border pb-2">Primary Actions</h4>
                    
                    <?php 
                        $status = $activeFolder['status'] ?? 'draft';
                        $hasBeenSubmitted = !empty($activeFolder['submitted_at']);
                    ?>

                    <?php if (!$isReadOnly): ?>
                        <div class="flex gap-3 lg:mt-1 lg:flex-col">
                            <?php if (session()->get('role') != 'Admin'): ?>
                                <?php if (!$hasBeenSubmitted || $status === \App\Enums\FolderStatus::REEVALUATE->value): ?>
                                    <button onclick="submitFolder('<?= $activeFolder['id'] ?>', this)" 
                                        <?= $isLocked ? 'disabled' : '' ?>
                                        class="w-full flex items-center justify-center gap-2 px-5 py-3.5 bg-info-500 hover:bg-info-600 disabled:bg-zinc-400 disabled:cursor-not-allowed text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-md active:scale-95 cursor-pointer">
                                        Submit For Evaluation
                                    </button>
                                <?php else: ?>
                                    <button onclick="unsubmitFolder('<?= $activeFolder['id'] ?>', this)" 
                                        <?= $isLocked ? 'disabled' : '' ?>
                                        class="w-full flex items-center justify-center gap-2 px-5 py-3.5 bg-warning-500 hover:bg-warning-600 disabled:bg-zinc-400 disabled:cursor-not-allowed text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-md active:scale-95 cursor-pointer">
                                        Unsubmit Folder
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($status !== FolderStatus::SUBMITTED->value): ?>
                                <button id="btn-open-create-file" data-folder-id="<?= esc($activeFolder['id']) ?>"
                                    <?= $isLocked ? 'disabled' : '' ?>
                                    class="w-full flex items-center justify-center gap-2 px-5 py-3.5 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:opacity-90 disabled:bg-zinc-400 disabled:dark:bg-zinc-700 disabled:opacity-60 disabled:hover:opacity-60 disabled:cursor-not-allowed rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-md active:scale-95 cursor-pointer">
                                    + Add Document
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-text-muted italic py-2 text-center">You are viewing this folder in read-only mode.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="overflow-y-auto max-h-[60vh] px-5 pb-8 flex flex-col gap-6 lg:max-h-none lg:p-0 lg:overflow-visible custom-scrollbar">
                
                <?php if (in_array(session()->get('role'), ['Admin', 'Supervisor']) && $activeFolder['user_id'] == session()->get('user_id')): ?>
                    <div class="bg-surface lg:border lg:border-surface-border rounded-2xl lg:p-5 lg:shadow-sm flex flex-col gap-4">
                        <h4 class="text-[10px] font-black uppercase text-text-muted tracking-widest border-b border-surface-border pb-2">Cascade Management</h4>

                        <?php $cascadedTeamId = $activeFolder['routing_preset_id']; ?>
                        
                        <div class="flex flex-col gap-3 mt-1">
                            <?php if (!empty($presets)): ?>
                                <div class="relative w-full">
                                    <select id="team-cascade-select" <?= ($cascadedTeamId || $isLocked) ? 'disabled' : '' ?> class="w-full bg-zinc-50 dark:bg-zinc-800/50 text-xs font-bold text-text outline-none px-4 py-3 rounded-xl appearance-none border border-surface-border <?= ($cascadedTeamId || $isLocked) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer focus:border-accent' ?>">
                                        <?php foreach($presets as $preset): ?>
                                            <option value="<?= $preset['id'] ?>" <?= ($cascadedTeamId == $preset['id'] || (!$cascadedTeamId && $preset === reset($presets))) ? 'selected' : '' ?> class="bg-white dark:bg-zinc-900">
                                                <?= esc($preset['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>

                                <?php if ($isLocked): ?>
                                    <!-- Padlock icon: folder is outside its evaluation window / already past drafting, so cascading is disabled -->
                                    <div class="w-full py-3 text-zinc-400 bg-zinc-100 dark:bg-zinc-800/80 border border-transparent rounded-xl flex justify-center items-center gap-2 cursor-not-allowed">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Locked</span>
                                    </div>
                                <?php elseif ($cascadedTeamId): ?>
                                    <!-- X-in-circle icon: undoes the cascade (calls Folder::uncascadeTeam) -->
                                    <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 text-danger-600 hover:text-white border border-danger-200 bg-danger-50 hover:bg-danger-500 dark:bg-danger-500/10 dark:hover:bg-danger-600 dark:border-danger-500/20 rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-2 font-bold shadow-sm active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Revoke Cascade</span>
                                    </button>
                                <?php else: ?>
                                    <!-- Arrow/paper-plane icon: assigns the selected team as this folder's evaluators (calls Folder::cascadeTeam) -->
                                    <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 text-accent hover:text-white border border-accent/30 bg-accent/10 hover:bg-accent rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-2 font-bold shadow-sm active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Cascade to Team</span>
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= site_url('teams') ?>" class="text-[10px] text-center font-black uppercase tracking-widest text-text-muted hover:text-accent border-2 border-dashed border-surface-border hover:border-accent w-full py-4 rounded-xl transition-colors block bg-zinc-50 dark:bg-zinc-800/20">
                                    + Create Distribution Team
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (in_array(session()->get('role'), ['Admin']) && $activeFolder['user_id'] == session()->get('user_id')): ?>
                            <h4 class="text-[10px] font-black uppercase text-text-muted tracking-widest border-b border-surface-border pb-2">Folder Management</h4>
                            
                            <div class="grid grid-cols-2 gap-3 mt-1">
                                <!-- Pencil icon: opens components/edit_folder_modal.php prefilled with this folder's title/dates -->
                                <button onclick="openEditFolderModal('<?= esc($activeFolder['id']) ?>', '<?= esc(addslashes($activeFolder['title'])) ?>', '<?= esc($activeFolder['eval_date_start'] ?? '') ?>', '<?= esc($activeFolder['eval_date_end'] ?? '') ?>')"
                                        class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-warning-600 border border-warning-200 bg-warning-50 hover:bg-warning-500 hover:text-white dark:bg-warning-500/10 dark:hover:bg-warning-600 dark:border-warning-500/20 transition-all cursor-pointer font-bold shadow-sm active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Edit</span>
                                </button>

                                <!-- Trash icon: opens the shared confirm/delete modal (components/delete_modal.php) via .btn-delete-modal -->
                                <button class="btn-delete-modal flex items-center justify-center gap-2 py-2.5 rounded-xl text-danger-600 border border-danger-200 bg-danger-50 hover:bg-danger-500 hover:text-white dark:bg-danger-500/10 dark:hover:bg-danger-600 dark:border-danger-500/20 transition-all cursor-pointer font-bold shadow-sm active:scale-95"
                                    data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Delete Folder">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Delete</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-surface lg:border lg:border-surface-border rounded-2xl lg:p-5 lg:shadow-sm flex flex-col gap-4">
                    <h4 class="text-[10px] font-black uppercase text-text-muted tracking-widest border-b border-surface-border pb-2">Evaluator Progress</h4>
                    
                    <?php if(empty($groupedGuides)): ?>
                        <p class="text-xs text-text-muted italic text-center py-4 bg-zinc-50 dark:bg-zinc-800/30 rounded-xl border border-dashed border-surface-border">No evaluators assigned yet.</p>
                    <?php else: ?>
                        <div class="flex flex-col gap-3 mt-1">
                            <?php foreach ($groupedGuides as $group): 
                                $db = \Config\Database::connect();
                                $routing = $db->table('evaluation_routings')
                                            ->where('folder_id', $activeFolder['id'])
                                            ->where('evaluator_folder_id', $group['docs'][0]['document_folder_id'])
                                            ->get()->getRowArray();
                                
                                $rStatus = $routing['status'] ?? 'pending';
                                $statusColor = $rStatus === 'approved' ? 'bg-success-500' : ($rStatus === 're_evaluate' ? 'bg-danger-500' : 'bg-warning-400');
                                $statusBg = $rStatus === 'approved' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 border-success-200 dark:border-success-500/20' : 
                                           ($rStatus === 're_evaluate' ? 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 border-danger-200 dark:border-danger-500/20' : 
                                           'bg-warning-50 text-warning-700 dark:bg-warning-500/10 border-warning-200 dark:border-warning-500/20');
                            ?>
                                <div class="flex flex-col p-4 border rounded-xl <?= $statusBg ?>">
                                    <div class="flex justify-between items-center mb-1.5">
                                        <span class="text-xs font-bold truncate pr-2"><?= esc($group['superior']['name']) ?></span>
                                        <div class="flex items-center gap-1.5 shrink-0 bg-white/50 dark:bg-black/20 px-2 py-1 rounded-md">
                                            <div class="w-1.5 h-1.5 rounded-full <?= $statusColor ?> <?= $rStatus !== 'approved' ? 'animate-pulse' : '' ?>"></div>
                                            <span class="text-[9px] font-black uppercase tracking-widest opacity-80"><?= ucfirst(str_replace('_', '-', $rStatus)) ?></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold opacity-60 uppercase tracking-widest truncate"><?= esc($group['superior']['role']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        
        <div id="bottom-sheet-overlay" onclick="toggleBottomSheet()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        // Dropdown Logic
        function toggleFolderDropdown() {
            const menu = document.getElementById('folder-dropdown-menu');
            const icon = document.getElementById('folder-dropdown-icon');
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Close dropdown if clicking outside
        document.addEventListener('click', function(event) {
            const container = document.getElementById('folder-dropdown-container');
            const menu = document.getElementById('folder-dropdown-menu');
            if (container && !container.contains(event.target) && !menu.classList.contains('hidden')) {
                toggleFolderDropdown();
            }
        });

        // Bottom Sheet Logic
        let isSheetOpen = false;
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottom-sheet');
            const overlay = document.getElementById('bottom-sheet-overlay');
            isSheetOpen = !isSheetOpen;
            
            if (isSheetOpen) {
                // Expand
                sheet.classList.remove('translate-y-[calc(100%-95px)]');
                sheet.classList.add('translate-y-0');
                overlay.classList.remove('hidden');
            } else {
                // Collapse
                sheet.classList.add('translate-y-[calc(100%-95px)]');
                sheet.classList.remove('translate-y-0');
                overlay.classList.add('hidden');
            }
        }

        function submitFolder(folderId, btn) {
            btn.innerText = 'Submitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/submit') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    window.location.reload();
                }
            });
        }

        async function unsubmitFolder(folderId, btn) {
            const ok = await window.appConfirm("Are you sure you want to revoke your submission?", { variant: 'warning', confirmText: 'Revoke' });
            if (!ok) return;

            btn.innerText = 'Unsubmitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    window.location.reload();
                }
            });
        }

        function setTargetDocument(docId, folderId) {
            const formData = new FormData();
            formData.append('doc_id', docId);
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('document/target') ?>', formData, {
                onSuccess: () => window.location.reload()
            });
        }

        let sending = false;

        function triggerCascade(folderId) {
            if (sending) return;
            sending = true;

            const teamId = document.getElementById('team-cascade-select').value;
            if (!teamId) {
                sending = false;
                return;
            }
            
            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);

            apiPost('<?= site_url('folder/cascade-team') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        function triggerUncascade(folderId) {
            if (sending) return;
            sending = true;

            const teamId = document.getElementById('team-cascade-select').value;
            if (!teamId) {
                sending = false;
                return;
            }

            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);
            
            apiPost('<?= site_url('folder/uncascade-team') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        function switchDocTab(tabId) {
            // Hide all contents
            document.querySelectorAll('.tab-content-doc').forEach(el => {
                el.classList.add('hidden');
                // Added flex-1 to removal list
                el.classList.remove('flex', 'flex-col', 'flex-1', 'lg:absolute', 'lg:inset-0', 'lg:top-[45px]');
            });
            
            // Reset all buttons
            document.querySelectorAll('.tab-btn-doc').forEach(btn => {
                btn.classList.remove('border-accent', 'text-accent');
                btn.classList.add('border-transparent', 'text-text-muted');
                const badge = btn.querySelector('.tab-badge');
                if(badge) {
                    badge.classList.remove('bg-accent/10', 'text-accent');
                    badge.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                }
            });

            // Activate Target Content
            const target = document.getElementById('tab-content-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                // Added flex-1 to ensure tab content expands
                target.classList.add('flex', 'flex-col', 'flex-1', 'lg:absolute', 'lg:inset-0', 'lg:top-[45px]');
            }

            // Activate Button
            const btnElement = document.getElementById('tab-btn-' + tabId);
            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'text-text-muted');
                btnElement.classList.add('border-accent', 'text-accent');
                const activeBadge = btnElement.querySelector('.tab-badge');
                if(activeBadge) {
                    activeBadge.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                    activeBadge.classList.add('bg-accent/10', 'text-accent');
                }
            }
        }
    </script>
<?php endif; ?>
<?php 
    use App\Enums\FolderStatus;
    
    $groupedGuides = $groupedGuides ?? [];
    $hasGuides = !empty($groupedGuides);
    $isArchived = !empty($isArchivedView) || !empty($activeFolder['deleted_at']);

    $folderModel = new \App\Models\DocumentFolderModel();
    $isLocked = $activeFolder ? $folderModel->isFolderLocked($activeFolder) : true;

    $displayName = session()->get('username');
    if (empty($displayName) || $displayName === 'Null username') {
        $fName = session()->get('first_name');
        $lName = session()->get('last_name');
        if ($fName || $lName) {
            $displayName = trim($fName . ' ' . ($lName ? substr($lName, 0, 1) . '.' : ''));
        } else {
            $displayName = 'User';
        }
    }
?>

<?php if (!$activeFolder): ?>
    <?php if (session()->get('role') === 'Admin'): ?>
        <button onclick="document.getElementById('btn-create-folder-modal').click()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-emerald-500 rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-emerald-500/5 transition-all group cursor-pointer min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-slate-800 text-text-muted group-hover:text-emerald-500 mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-emerald-500 transition-colors mb-1">Create New Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Click here to start a new evaluation period and create the initial folder.</p>
        </button>
    <?php else: ?>
        <button onclick="toggleAppSidebar()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-emerald-500 rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-emerald-500/5 transition-all group cursor-pointer lg:cursor-default min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-slate-800 text-text-muted group-hover:text-emerald-500 mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-emerald-500 transition-colors mb-1">Select a Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
        </button>
    <?php endif; ?>

<?php else: ?>
    
    <div class="flex flex-col lg:flex-row flex-1 lg:absolute lg:inset-0 lg:min-h-[650px] bg-transparent lg:gap-6 lg:pb-6">
        
        <!-- CENTER / MAIN CONTENT CONTAINER -->
        <div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-hidden">
            
            <!-- FOLDER HEADER -->
            <div class="px-6 lg:px-8 pt-6 pb-5 border-b border-surface-border shrink-0" id="folder-dropdown-container">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="min-w-0">
                        <button onclick="toggleAppSidebar()" class="text-left group cursor-pointer lg:cursor-default w-full">
                            <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-text truncate group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                <?= esc($activeFolder['title']) ?>
                            </h1>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="flex items-center gap-6 px-6 lg:px-8 border-b border-surface-border shrink-0 overflow-x-auto custom-scrollbar pt-2">
                <button id="tab-btn-mine" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 border-emerald-500 text-slate-900 dark:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('mine')">
                    My Submissions
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 text-[10px] font-extrabold tab-badge transition-colors"><?= count($myDocs) ?></span>
                </button>

                <?php if (session()->get('role') !== 'Admin' && !empty($groupedGuides)): ?>
                    <button id="tab-btn-team" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-700 dark:hover:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('team')">
                        Team Submissions
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-[#032316] text-slate-500 dark:text-[#94A3B8] text-[10px] font-extrabold tab-badge transition-colors"><?= array_sum(array_map(fn($g) => count($g['docs']), $groupedGuides)) ?></span>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($isArchived): ?>
                <!-- SEARCH AND FILTER BAR (Archived Folders Only) -->
                <div class="px-6 lg:px-8 py-3.5 flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="doc-search-input" oninput="filterDocuments()" placeholder="Search archived documents by name or status..."
                               class="w-full bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-[#94A3B8] rounded-xl pl-9 pr-4 py-2.5 focus:border-[#FFB800]/70 focus:outline-none transition-colors" />
                    </div>

                    <!-- Filters -->
                    <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0">
                        <div class="relative flex-1 sm:flex-initial">
                            <select id="doc-filter-status" onchange="filterDocuments()" class="w-full sm:w-auto appearance-none bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-xs font-medium text-slate-800 dark:text-white rounded-xl pl-3.5 pr-8 py-2.5 outline-none cursor-pointer focus:border-[#FFB800]/70 transition-colors">
                                <option value="all">Status: All</option>
                                <option value="approved">Approved</option>
                                <option value="in_review">In Review</option>
                                <option value="draft">Draft</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB CONTENTS AREA -->
            <div class="overflow-hidden flex flex-col flex-1 min-h-0 relative h-[calc(100dvh-320px)] lg:h-auto pb-32 lg:pb-0">
                
                <!-- 1. MY SUBMISSIONS TAB -->
                <div id="tab-content-mine" class="tab-content-doc flex-1 flex flex-col min-h-0 bg-surface overflow-y-auto custom-scrollbar px-6 lg:px-8 py-4">
                    
                    <?php if ($isArchived): ?>
                        <div id="search-empty-doc" class="hidden p-12 text-center">
                            <p class="text-sm text-text-muted font-medium italic">No documents match your search or filter criteria.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($myDocs)): ?>
                        <div class="border-2 border-dashed border-[#c2d4c4] dark:border-[#0c4a33] bg-[#f4f8f4]/50 dark:bg-[#032316]/30 rounded-2xl w-full flex-1 flex flex-col items-center justify-center p-8 sm:p-12 my-2 min-h-[380px] text-center">
                            <div class="w-12 h-12 rounded-xl bg-[#d5e4d7] dark:bg-[#0c4a33] text-[#064e3b] dark:text-emerald-400 flex items-center justify-center mb-3.5 shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No Document Available</h3>
                            <p class="text-xs text-[#527961] dark:text-[#94A3B8] max-w-sm mb-2 leading-relaxed">
                                No official performance paper is currently assigned to this folder.
                            </p>
                        </div>
                    <?php else: ?>
                        
                        <?php foreach ($myDocs as $doc): ?>
                            <?php 
                                $isTarget = (isset($doc['is_target']) && $doc['is_target'] == 1);
                                $folderStatus = $activeFolder['status'] ?? '';

                                // Determine Status Pill
                                if (in_array($folderStatus, [FolderStatus::APPROVED->value, FolderStatus::TWG_APPROVED->value])) {
                                    $statusLabel = 'APPROVED';
                                    $statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400';
                                    $dotClass = 'bg-emerald-500 dark:bg-emerald-400';
                                    $normStatus = 'approved';
                                } elseif (in_array($folderStatus, [FolderStatus::SUBMITTED->value, FolderStatus::PENDING_TARGET_APPROVAL->value, FolderStatus::EVALUATED->value])) {
                                    $statusLabel = 'IN REVIEW';
                                    $statusClass = 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:border-amber-800/80 dark:text-amber-400';
                                    $dotClass = 'bg-amber-500 dark:bg-amber-400';
                                    $normStatus = 'in_review';
                                } else {
                                    $statusLabel = 'DRAFT';
                                    $statusClass = 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-[#121d17] dark:border-[#1e3126] dark:text-[#8ea396]';
                                    $dotClass = 'bg-zinc-400 dark:bg-emerald-500';
                                    $normStatus = 'draft';
                                }

                                // Icon Box Color
                                $iconBg = $isTarget ? 'bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-950/60 dark:border-blue-800/40 dark:text-blue-400' : 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-[#12241b] dark:border-[#1a3829] dark:text-emerald-400';
                            ?>
                            <div class="doc-row-item grid grid-cols-1 md:grid-cols-12 gap-3 px-5 py-3.5 items-start md:items-center bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] hover:border-slate-300 dark:hover:border-[#233a2e] rounded-xl mb-3 shadow-xs transition-all group"
                                 data-title="<?= esc(strtolower($doc['title'])) ?>"
                                 data-status="<?= esc($normStatus) ?>">
                                
                                <!-- Document Name -->
                                <div class="col-span-1 md:col-span-6 flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl <?= $iconBg ?> flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="<?= site_url('document/' . $doc['id']) ?>" class="font-bold text-xs text-text group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors truncate">
                                            <?= esc($doc['title']) ?>
                                        </a>
                                        <span class="text-[10px] text-slate-400 dark:text-[#8ea396] font-medium">
                                            DOC • <?= date('M d, Y', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-span-1 md:col-span-2 flex items-center">
                                    <span class="text-[10px] font-bold tracking-wide px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5 <?= $statusClass ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span>
                                        • <?= esc($statusLabel) ?>
                                    </span>
                                </div>

                                <!-- Last Activity -->
                                <div class="col-span-1 md:col-span-3 flex flex-col">
                                    <span class="text-xs font-semibold text-slate-800 dark:text-[#f8fafc]"><?= date('M d, g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?></span>
                                    <span class="text-[10px] text-slate-400 dark:text-[#8ea396]">by <?= esc($displayName) ?></span>
                                </div>

                                <!-- Actions -->
                                <div class="col-span-1 md:col-span-1 flex justify-end">
                                    <a href="<?= site_url('document/' . $doc['id']) ?>" class="px-3.5 py-1.5 bg-[#f59e0b] hover:bg-[#d97706] text-black rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs active:scale-95">
                                        Open
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- 2. TEAM SUBMISSIONS TAB -->
                <div id="tab-content-team" class="tab-content-doc hidden flex-1 flex-col min-h-0 bg-surface overflow-y-auto custom-scrollbar px-6 lg:px-8 py-4">
                    <?php if (empty($groupedGuides)): ?>
                        <div class="p-12 text-center">
                            <p class="text-sm text-text-muted font-medium italic">No team submissions or guide documents found for this period.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groupedGuides as $gIdx => $group): ?>
                            <div class="mb-4">
                                <h4 class="text-xs font-black uppercase tracking-wider text-text-muted mb-2">Guide: <?= esc($group['superior']['name']) ?> (<?= esc($group['superior']['role']) ?>)</h4>
                                <?php foreach ($group['docs'] as $doc): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 px-5 py-3.5 items-center bg-surface border border-surface-border rounded-xl mb-2.5">
                                        <div class="col-span-1 md:col-span-6 flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-950/60 dark:border-blue-800/40 dark:text-blue-400 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <a href="<?= site_url('document/' . $doc['id']) ?>" class="font-bold text-xs text-text hover:text-emerald-500 truncate">
                                                    <?= esc($doc['title']) ?>
                                                </a>
                                                <span class="text-[10px] text-text-muted">Superior Basis / Guide</span>
                                            </div>
                                        </div>
                                        <div class="col-span-1 md:col-span-4 text-xs text-text-muted">
                                            <?= esc($group['superior']['role']) ?>
                                        </div>
                                        <div class="col-span-1 md:col-span-2 flex justify-end">
                                            <a href="<?= site_url('document/' . $doc['id']) ?>" class="text-[10px] font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 hover:underline px-3 py-1 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/40 rounded-lg">View</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php 
            $canCascade = in_array(session()->get('role'), ['Admin', 'Supervisor']) && ($activeFolder['user_id'] == session()->get('user_id')); 
            $docType = strtolower($ownerDocType ?? 'ipcr');
            $tStartDate = !empty($activeFolder[$docType . '_target_start']) ? date('M d, Y', strtotime($activeFolder[$docType . '_target_start'])) : null;
            $tEndDate   = !empty($activeFolder[$docType . '_target_end'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_target_end']))   : null;
            $eStartDate = !empty($activeFolder[$docType . '_eval_start'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_start']))   : null;
            $eEndDate   = !empty($activeFolder[$docType . '_eval_end'])     ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_end']))     : null;

            $targetDateStr = ($tStartDate && $tEndDate) ? ($tStartDate . ' – ' . $tEndDate) : ($tStartDate ?? $tEndDate ?? 'Start – End');
            $evalDateStr   = ($eStartDate && $eEndDate) ? ($eStartDate . ' – ' . $eEndDate) : ($eStartDate ?? $eEndDate ?? 'Start – End');
        ?>

        <!-- RIGHT SIDEBAR (CASCADE DISTRIBUTION FOR ADMINS / FOLDER DETAILS FOR USERS) -->
        <div id="bottom-sheet" class="lg:overflow-y-auto custom-scrollbar fixed inset-x-0 bottom-0 z-50 bg-surface border-t lg:border border-surface-border shadow-2xl lg:shadow-sm rounded-t-3xl lg:rounded-2xl transition-transform duration-300 transform translate-y-[calc(100%-95px)] lg:static lg:translate-y-0 lg:w-80 lg:shrink-0 flex flex-col p-5 gap-5">
            
            <div class="lg:hidden flex justify-center py-2 cursor-pointer touch-none" onclick="toggleBottomSheet()">
                <div class="w-12 h-1 bg-zinc-300 dark:bg-slate-600 rounded-full"></div>
            </div>

            <?php if ($canCascade): ?>
                <!-- CASCADE MANAGEMENT SECTION (For Admins & Supervisors) -->
                <div class="flex flex-col gap-3">
                    <?php $cascadedTeamId = $activeFolder['routing_preset_id'] ?? null; ?>
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider">Cascade Management</h4>
                        <?php if ($cascadedTeamId): ?>
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-[#34d399] bg-emerald-50 dark:bg-[#102a1e] px-2 py-0.5 rounded-md border border-emerald-200 dark:border-[#1b4330]">Cascaded</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($presets)): ?>
                        <div class="relative w-full">
                            <select id="team-cascade-select" <?= ($cascadedTeamId || $isLocked) ? 'disabled' : '' ?> class="w-full bg-zinc-50 dark:bg-[#0c1510] text-xs font-bold text-text outline-none pl-3.5 pr-8 py-2.5 rounded-xl appearance-none border border-surface-border <?= ($cascadedTeamId || $isLocked) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer focus:border-emerald-500/50' ?>">
                                <?php foreach($presets as $preset): ?>
                                    <option value="<?= $preset['id'] ?>" <?= ($cascadedTeamId == $preset['id']) ? 'selected' : '' ?> class="bg-surface text-text">
                                        <?= esc($preset['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                        <?php if ($cascadedTeamId): ?>
                            <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="w-full py-2.5 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-300 dark:border-[#361a1f] bg-rose-50 dark:bg-[#1c1214] hover:bg-rose-600 dark:hover:bg-[#261619] rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-1.5 font-bold text-xs uppercase tracking-wider">
                                Revoke Cascade
                            </button>
                        <?php elseif (!$isLocked): ?>
                            <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 bg-[#064e3b] hover:bg-[#085a3a] text-white dark:bg-[#f59e0b] dark:hover:bg-[#d97706] dark:text-black rounded-xl shadow-md transition-all cursor-pointer flex justify-center items-center gap-1.5 font-black text-xs uppercase tracking-wider active:scale-98">
                                Cascade to Team
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-3 text-center rounded-xl border border-dashed border-slate-200 dark:border-[#1a2b22] bg-slate-50/50 dark:bg-[#0c1510]/50">
                            <p class="text-[11px] text-slate-400 dark:text-[#8ea396] italic mb-1.5">No distribution teams available.</p>
                            <a href="<?= site_url('teams') ?>" class="text-xs font-bold text-amber-500 dark:text-[#f59e0b] hover:underline">
                                + Create Team in My Teams
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FOLDER MANAGEMENT BUTTONS -->
                <div class="flex flex-col gap-3 border-t border-surface-border pt-4 mt-auto">
                    <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider">Folder Management</h4>
                    
                    <?php if (!empty($activeFolder['deleted_at'])): ?>
                        <button onclick='unarchiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Restore Folder
                        </button>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-2.5">
                            <button onclick='openEditFolderModal("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>", {
                                ipcr_target_start: "<?= esc($activeFolder["ipcr_target_start"] ?? "") ?>",
                                ipcr_target_end: "<?= esc($activeFolder["ipcr_target_end"] ?? "") ?>",
                                ipcr_eval_start: "<?= esc($activeFolder["ipcr_eval_start"] ?? "") ?>",
                                ipcr_eval_end: "<?= esc($activeFolder["ipcr_eval_end"] ?? "") ?>",
                                dpcr_target_start: "<?= esc($activeFolder["dpcr_target_start"] ?? "") ?>",
                                dpcr_target_end: "<?= esc($activeFolder["dpcr_target_end"] ?? "") ?>",
                                dpcr_eval_start: "<?= esc($activeFolder["dpcr_eval_start"] ?? "") ?>",
                                dpcr_eval_end: "<?= esc($activeFolder["dpcr_eval_end"] ?? "") ?>",
                                opcr_target_start: "<?= esc($activeFolder["opcr_target_start"] ?? "") ?>",
                                opcr_target_end: "<?= esc($activeFolder["opcr_target_end"] ?? "") ?>",
                                opcr_eval_start: "<?= esc($activeFolder["opcr_eval_start"] ?? "") ?>",
                                opcr_eval_end: "<?= esc($activeFolder["opcr_eval_end"] ?? "") ?>",
                                iperf_target_start: "<?= esc($activeFolder["iperf_target_start"] ?? "") ?>",
                                iperf_target_end: "<?= esc($activeFolder["iperf_target_end"] ?? "") ?>",
                                iperf_eval_start: "<?= esc($activeFolder["iperf_eval_start"] ?? "") ?>",
                                iperf_eval_end: "<?= esc($activeFolder["iperf_eval_end"] ?? "") ?>"
                            })'
                                    class="w-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 dark:bg-[#13271b] dark:hover:bg-[#1b3b29] dark:text-[#34d399] dark:border-[#1e422f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs">
                                Edit
                            </button>

                            <button onclick='archiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                    class="w-full bg-white hover:bg-slate-100 text-amber-700 dark:text-[#b45309] border border-slate-200 dark:border-slate-300 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs">
                                Archive
                            </button>

                            <button class="w-full btn-delete-modal bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 dark:bg-[#1c1214] dark:hover:bg-[#261619] dark:text-rose-400 dark:border-[#361a1f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                                data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Delete Folder">
                                Delete
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- 2. FOLDER OVERVIEW & DETAILS SECTION (For Normal Users in exchange for Cascade Distribution) -->
                <?php
                    $now = date('Y-m-d H:i:s');
                    $tEnd = $activeFolder[$docType . '_target_end'] ?? null;
                    $isPastTarget = ($tEnd && $now > $tEnd);
                    $currentPhaseLabel = $isPastTarget ? 'Eval Phase' : 'Target Phase';
                    $phaseDotColor = $isPastTarget ? 'bg-sky-500' : 'bg-emerald-500';

                    $rawStatus = $activeFolder['status'] ?? 'draft';
                    $statusBadgeText = match($rawStatus) {
                        'approved', 'twg_approved' => 'Approved',
                        'to_evaluate', 'submitted' => 'Submitted',
                        'draft_target' => 'Target Phase',
                        'reevaluate' => 'Revision',
                        default => 'Draft'
                    };

                    $formTypeName = strtoupper($ownerDocType ?: 'IPCR');
                    $formTypeDesc = match($formTypeName) {
                        'OPCR' => 'Office Performance',
                        'DPCR' => 'Department Performance',
                        default => 'Individual Performance'
                    };
                ?>

                <!-- Submission Summary Card -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-black uppercase text-slate-400 dark:text-[#8ea396] tracking-wider">Submission Summary</h4>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-[#0c1510] dark:border-[#1a2b22] dark:text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full <?= $phaseDotColor ?> animate-pulse"></span>
                            <?= esc($currentPhaseLabel) ?>
                        </span>
                    </div>

                    <!-- 2 Compact Metric Tiles -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Total Files</span>
                            <span class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?= count($myDocs) ?></span>
                        </div>
                        <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Folder Status</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-white mt-1.5 truncate"><?= esc($statusBadgeText) ?></span>
                        </div>
                    </div>

                    <!-- Form Commitment Tile -->
                    <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl px-3 py-2.5 flex items-center justify-between">
                        <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Commitment</span>
                        <span class="text-[11px] font-bold text-slate-800 dark:text-white truncate pl-2"><?= esc($formTypeName) ?> • <?= esc($formTypeDesc) ?></span>
                    </div>
                </div>

                <!-- Folder Details Block (Matching user's reference mockup) -->
                <div class="flex flex-col gap-3 pt-5 border-t border-slate-200 dark:border-[#1a2b22] mt-auto">
                    <h4 class="text-[10px] font-black uppercase text-slate-400 dark:text-[#8ea396] tracking-wider">Folder Details</h4>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#8ea396] font-medium">Target Date</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($targetDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Evaluation Date</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($evalDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Period</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2">Q3 <?= date('Y', strtotime($activeFolder['created_at'] ?? 'now')) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Owner</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2 max-w-[150px]"><?= esc($displayName) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <div id="bottom-sheet-overlay" onclick="toggleBottomSheet()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        // Bottom Sheet Logic
        let isSheetOpen = false;
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottom-sheet');
            const overlay = document.getElementById('bottom-sheet-overlay');
            if (!sheet || !overlay) return;
            isSheetOpen = !isSheetOpen;
            
            if (isSheetOpen) {
                sheet.classList.remove('translate-y-[calc(100%-95px)]');
                sheet.classList.add('translate-y-0');
                overlay.classList.remove('hidden');
            } else {
                sheet.classList.add('translate-y-[calc(100%-95px)]');
                sheet.classList.remove('translate-y-0');
                overlay.classList.add('hidden');
            }
        }

        // Real-time Search and Filtering
        function filterDocuments() {
            const searchInput = document.getElementById('doc-search-input');
            const statusSelect = document.getElementById('doc-filter-status');
            if (!searchInput && !statusSelect) return;

            const query = (searchInput?.value || '').toLowerCase().trim();
            const statusFilter = statusSelect?.value || 'all';

            const rows = document.querySelectorAll('#tab-content-mine .doc-row-item');
            let visibleCount = 0;
            rows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = !query || title.includes(query) || status.includes(query);
                const matchesStatus = statusFilter === 'all' || status.toLowerCase().includes(statusFilter.toLowerCase());

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const emptyMsg = document.getElementById('search-empty-doc');
            if (emptyMsg) {
                emptyMsg.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
            }
        }

        // Action Menu toggle
        let activeMenuId = null;
        function toggleActionMenu(menuId) {
            const menu = document.getElementById(menuId);
            if (!menu) return;
            if (activeMenuId && activeMenuId !== menuId) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
            }
            menu.classList.toggle('hidden');
            activeMenuId = menu.classList.contains('hidden') ? null : menuId;
        }

        document.addEventListener('click', function(e) {
            if (activeMenuId && !e.target.closest('.group\\/actions')) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
                activeMenuId = null;
            }
        });

        function submitTargetFolder(folderId, btn) {
            btn.innerText = 'Submitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/submit_target') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    window.location.reload();
                }
            });
        }

        async function unsubmitTargetFolder(folderId, btn) {
            const ok = await window.appConfirm("Are you sure you want to revoke your submission?", { variant: 'warning', confirmText: 'Revoke' });
            if (!ok) return;

            btn.innerText = 'Unsubmitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/unsubmit_target') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    window.location.reload();
                }
            });
        }

        function setTargetDocument(docId, folderId, targetState) {
            const formData = new FormData();
            formData.append('doc_id', docId);
            formData.append('folder_id', folderId);
            formData.append('is_target', targetState);
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

            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/uncascade-team') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        function archiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Archive Folder',
                message: `Are you sure you want to archive "${folderTitle}"? You can view and restore it anytime from Archived Folders.`,
                confirmText: 'Archive Folder',
                variant: 'warning'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/archive') ?>', formData, {
                    onSuccess: () => {
                        window.location.href = '<?= site_url('folders') ?>';
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to archive folder.");
                        sending = false;
                    }
                });
            });
        }

        function unarchiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Restore Folder',
                message: `Restore "${folderTitle}" to your active folders?`,
                confirmText: 'Restore Folder',
                variant: 'info'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/unarchive') ?>', formData, {
                    onSuccess: () => {
                        window.location.href = '<?= site_url('folders') ?>/' + folderId;
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to restore folder.");
                        sending = false;
                    }
                });
            });
        }

        function switchDocTab(tabId) {
            document.querySelectorAll('.tab-content-doc').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
            
            document.querySelectorAll('.tab-btn-doc').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                btn.classList.add('border-transparent', 'text-text-muted');
                const badge = btn.querySelector('.tab-badge');
                if(badge) {
                    badge.classList.remove('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                    badge.classList.add('bg-zinc-100', 'dark:bg-slate-800', 'text-text-muted');
                }
            });

            const target = document.getElementById('tab-content-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('flex');
            }

            const btnElement = document.getElementById('tab-btn-' + tabId);
            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'text-text-muted');
                btnElement.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                const activeBadge = btnElement.querySelector('.tab-badge');
                if(activeBadge) {
                    activeBadge.classList.remove('bg-zinc-100', 'dark:bg-slate-800', 'text-text-muted');
                    activeBadge.classList.add('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                }
            }
        }
    </script>
<?php endif; ?>
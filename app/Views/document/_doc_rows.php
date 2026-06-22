<?php 
    use App\Enums\FolderStatus;
    
    $groupedGuides = $groupedGuides ?? [];
    $hasGuides = !empty($groupedGuides);

    $folderModel = new \App\Models\DocumentFolderModel();
    $isLocked = $folderModel->isFolderLocked($activeFolder);
?>

<?php if (!$activeFolder): ?>
    <div class="flex-1 border-2 border-dashed border-surface-border rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text mb-1">Select a Folder</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
    </div>

<?php else: ?>
    
    <div class="flex flex-col gap-4 mb-6 shrink-0 px-1">
        
        <div class="flex justify-between items-start">
            <div class="min-w-0 pr-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                    <span class="text-zinc-400">/</span>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md 
                        <?= $activeFolder['status'] === 'draft' ? 'bg-zinc-100 text-zinc-500' : 
                           ($activeFolder['status'] === 'evaluated' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600') ?>">
                        STATUS: <?= esc($activeFolder['status']) ?>
                    </span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-text truncate">
                    <?= esc($activeFolder['title']) ?>
                </h1>
                <p class="text-xs font-bold text-text-muted mt-2">
                    Evaluation Window: 
                    <span class="text-text"><?= !empty($activeFolder['eval_date_start']) ? date('M d, Y h:ia', strtotime($activeFolder['eval_date_start'])) : 'Not Set' ?></span> 
                    — 
                    <span class="text-text"><?= !empty($activeFolder['eval_date_end']) ? date('M d, Y h:ia', strtotime($activeFolder['eval_date_end'])) : 'Not Set' ?></span>
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2">

            <?php if (in_array(session()->get('role'), ['Admin', 'Supervisor']) && $activeFolder['user_id'] == session()->get('user_id')): ?>
                <?php $cascadedTeamId = $activeFolder['routing_preset_id']; ?>
                <div class="flex items-center gap-2">
                    <?php if (!empty($presets)): ?>
                        <div class="flex items-center bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-surface-border p-1 shrink-0">
                            <div class="relative">
                                <select id="team-cascade-select" <?= ($cascadedTeamId || $isLocked) ? 'disabled' : '' ?> class="bg-transparent text-[11px] font-black uppercase tracking-widest text-text outline-none pl-3 pr-8 py-1.5 appearance-none border-r border-surface-border <?= ($cascadedTeamId || $isLocked) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' ?>">
                                    <option value="" disabled <?= !$cascadedTeamId ? 'selected' : '' ?>>Cascade to...</option>
                                    <?php foreach($presets as $preset): ?>
                                        <option value="<?= $preset['id'] ?>" <?= ($cascadedTeamId == $preset['id']) ? 'selected' : '' ?>><?= esc($preset['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>

                            <?php if ($isLocked): ?>
                                <div class="px-3 py-1.5 text-zinc-400 rounded-md flex items-center gap-1.5 cursor-not-allowed" title="Folder is locked">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Locked</span>
                                </div>
                            <?php elseif ($cascadedTeamId): ?>
                                <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="px-3 py-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors cursor-pointer flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Revoke</span>
                                </button>
                            <?php else: ?>
                                <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="px-3 py-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-md transition-colors cursor-pointer flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Send</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <a href="<?= site_url('teams') ?>" class="text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-accent border border-dashed border-surface-border px-4 py-2 rounded-xl transition-colors">
                            + Create Team to Cascade
                        </a>
                    <?php endif; ?>

                    <?php if (in_array(session()->get('role'), ['Admin']) && $activeFolder['user_id'] == session()->get('user_id')): ?>
                        <div class="w-px h-6 bg-surface-border mx-1"></div>

                        <button onclick="openEditFolderModal('<?= esc($activeFolder['id']) ?>', '<?= esc(addslashes($activeFolder['title'])) ?>', '<?= esc($activeFolder['eval_date_start'] ?? '') ?>', '<?= esc($activeFolder['eval_date_end'] ?? '') ?>')"
                                class="flex items-center justify-center p-2 rounded-lg text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 hover:text-amber-600 transition-all cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>

                        <button class="btn-delete-modal flex items-center justify-center p-2 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 transition-all cursor-pointer"
                            data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Delete Folder">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <div class="flex gap-2 items-center">
                <?php 
                    $folderModel = new \App\Models\DocumentFolderModel();
                    $isLocked = $folderModel->isFolderLocked($activeFolder);
                    $status = $activeFolder['status'] ?? 'draft';

                    $hasBeenSubmitted = !empty($activeFolder['submitted_at']);
                ?>

                <?php if (!$isReadOnly): ?>
                    <?php if (!$hasBeenSubmitted || $status === \App\Enums\FolderStatus::REEVALUATE->value): ?>
                        <button onclick="submitFolder('<?= $activeFolder['id'] ?>', this)" 
                            <?= $isLocked ? 'disabled' : '' ?>
                            class="flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-zinc-400 disabled:cursor-not-allowed text-white rounded-xl font-bold text-sm transition-all shadow-md active:scale-95 cursor-pointer">
                            Submit Folder
                        </button>
                    <?php else: ?>
                        <button onclick="unsubmitFolder('<?= $activeFolder['id'] ?>', this)" 
                            <?= $isLocked ? 'disabled' : '' ?>
                            class="flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:bg-zinc-400 disabled:cursor-not-allowed text-white rounded-xl font-bold text-sm transition-all shadow-md active:scale-95 cursor-pointer">
                            Unsubmit Folder
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$isReadOnly && !$isLocked && $status !== FolderStatus::SUBMITTED->value): ?>
                    <button id="btn-open-create-file" data-folder-id="<?= esc($activeFolder['id']) ?>"
                        class="flex items-center gap-2 px-5 py-2.5 bg-accent hover:bg-accent-hover text-white rounded-xl font-bold text-sm transition-all shadow-md shadow-accent/20 active:scale-95 cursor-pointer">
                        Add Document
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <div class="px-4 py-2 mb-4 border border-surface-border rounded-xl">
        <h4 class="text-[9px] font-black uppercase text-text-muted mb-2">Evaluator Progress</h4>
        <div class="flex gap-4">
            <?php foreach ($groupedGuides as $group): 
            $db = \Config\Database::connect();
                // Fetch individual routing status for this guide
                $routing = $db->table('evaluation_routings')
                            ->where('folder_id', $activeFolder['id'])
                            ->where('evaluator_folder_id', $group['docs'][0]['document_folder_id'])
                            ->get()->getRowArray();
                $rStatus = $routing['status'] ?? 'pending';
            ?>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full <?= $rStatus === 'approved' ? 'bg-emerald-500' : ($rStatus === 're_evaluate' ? 'bg-red-500' : 'bg-amber-400') ?>"></div>
                    <span class="text-[10px] font-bold text-text"><?= esc($group['superior']['name']) ?>: <?= ucfirst($rStatus) ?></span>
                </div>
            <?php endforeach; ?>
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

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-0 relative">
        
        <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0 z-10">
            <div class="col-span-6">Document Name</div>
            <div class="col-span-4">Status / Activity</div>
            <div class="col-span-2 text-right">Actions</div>
        </div>

        <?php $isFirst = false; ?>
        <?php foreach ($groupedGuides as $index => $group): ?>
            <div id="tab-content-guide-<?= $index ?>" class="tab-content-doc hidden bg-blue-50/20 dark:bg-blue-900/10">
                <div class="overflow-y-auto custom-scrollbar flex-1">
                    <div class="divide-y divide-surface-border mx-2">
                        <?php foreach ($group['docs'] as $doc): ?>
                            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-zinc-100/50 dark:bg-zinc-800/40 transition-all group">
                                <div class="col-span-6 flex items-center gap-4">
                                    <div class="bg-blue-100 dark:bg-blue-900/40 p-1.5 rounded-xl text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-blue-500 transition-colors truncate">
                                            <?= esc($doc['title']) ?>
                                        </a>
                                        <span class="text-[10px] text-blue-500 font-bold tracking-tight uppercase">Basis / Guide</span>
                                    </div>
                                </div>
                                <div class="col-span-4 flex flex-col gap-0.5">
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest"><?= esc($group['superior']['role']) ?> Template</span>
                                    <span class="text-[10px] font-semibold text-text-muted"><?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?></span>
                                </div>
                                <div class="col-span-2 flex justify-end gap-1">
                                    <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 hover:text-blue-800 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:bg-blue-500/20 transition-colors cursor-pointer">
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

        <div id="tab-content-mine" class="tab-content-doc flex flex-col absolute inset-0 top-[45px] bg-surface">
            <div id="doc-scroll-container" class="overflow-y-auto custom-scrollbar flex-1">
                <div class="divide-y divide-surface-border mx-2">
                    <?php if (empty($myDocs)): ?>
                        <div id="empty-doc" class="p-24 text-center">
                            <p class="text-sm text-text-muted font-medium italic">You haven't created any documents yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myDocs as $doc): ?>
                            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors group">
                                <div class="col-span-6 flex items-center gap-4">
                                    <div class="bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-xl text-text-muted border border-surface-border group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-accent hover:underline transition-colors truncate">
                                            <?= esc($doc['title']) ?>
                                        </a>
                                        <span class="text-[10px] text-text-muted font-bold tracking-tight">
                                            Last edited <?= date('M d, Y g:ia', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-span-4 flex items-center gap-2">
                                    <?php if (isset($doc['is_target']) && $doc['is_target'] == 1): ?>
                                        <span class="text-[10px] font-black text-amber-600 bg-amber-100 px-3 py-1 rounded-lg uppercase tracking-widest border border-amber-200">
                                            ★ Basis Target
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-3 py-1 rounded-lg uppercase tracking-widest">
                                            Evidence
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-span-2 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                    <?php if (!$isReadOnly): ?>
                                        <button class="p-2 rounded-lg text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all cursor-pointer"
                                                onclick="setTargetDocument('<?= $doc['id'] ?>', '<?= $activeFolder['id'] ?>')" title="Set as Basis for Evaluation">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        </button>

                                        <button class="btn-delete-modal p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer"
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

            <div id="pagination-ui" class="hidden px-8 py-3 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 flex justify-between items-center shrink-0 z-10">
                <span class="text-xs text-text-muted font-bold" id="page-info">Showing...</span>
                <div class="flex gap-2">
                    <button id="prev-page" disabled class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        Prev
                    </button>
                    <button id="next-page" disabled class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        function submitFolder(folderId, btn) {
            btn.innerText = 'Submitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/submit') ?>', formData, {
                onSuccess: () => window.location.reload()
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

        function triggerCascade(folderId) {
            const teamId = document.getElementById('team-cascade-select').value;
            if (!teamId) return alert("Please select a team from the dropdown first.");
            
            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);
            
            apiPost('<?= site_url('folder/cascade-team') ?>', formData, {
                onSuccess: () => {
                    alert("Successfully cascaded to team!");
                    window.location.reload();
                }
            });
        }

        function triggerUncascade(folderId) {
            const teamId = document.getElementById('team-cascade-select').value;
            if (!teamId) return alert("Please select a team from the dropdown first.");
            
            if(!confirm("Are you sure you want to revoke this cascade?")) return;

            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);
            
            apiPost('<?= site_url('folder/uncascade-team') ?>', formData, {
                onSuccess: () => {
                    alert("Cascade revoked successfully.");
                    window.location.reload();
                }
            });
        }

        function switchDocTab(tabId) {
            // Hide all contents
            document.querySelectorAll('.tab-content-doc').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex', 'flex-col', 'absolute', 'inset-0', 'top-[45px]');
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
                target.classList.add('flex', 'flex-col', 'absolute', 'inset-0', 'top-[45px]');
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

        function unsubmitFolder(folderId, btn) {
            if(!confirm("Are you sure you want to revoke your submission?")) return;
            
            btn.innerText = 'Unsubmitting...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
                onSuccess: () => window.location.reload()
            });
        }
    </script>
<?php endif; ?>
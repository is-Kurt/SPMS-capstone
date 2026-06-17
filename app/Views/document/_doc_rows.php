<?php 
    $groupedGuides = $groupedGuides ?? [];
    $hasGuides = !empty($groupedGuides);
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
    <div class="flex justify-between items-end mb-6 shrink-0 px-1">
        <div class="min-w-0 pr-4">
            <div class="flex items-center gap-2 mb-1">
                <?php if (isset($backUrl)): ?>
                    <a href="<?= $backUrl ?>" class="flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-accent hover:underline hover:text-accent-hover transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to User List
                    </a>
                    <span class="text-zinc-400">/</span>
                <?php endif; ?>
                
                <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folder Contents</span>
                <span class="text-zinc-400">/</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-accent">ID: <?= esc($activeFolder['id']) ?></span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-text truncate">
                <?= esc($activeFolder['title']) ?>
            </h1>
        </div>
        
        <div class="flex gap-2 shrink-0 items-center">
            <?php if (session()->get('role') === 'Admin' && $activeFolder['user_id'] == session()->get('user_id')): ?>
                <button class="btn-trigger-send-all flex items-center justify-center p-3 rounded-xl text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100 cursor-pointer"
                    data-id="<?= $activeFolder['id'] ?>" 
                    data-title="<?= esc($activeFolder['title']) ?>" 
                    title="Distribute Folder Batch">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>

                <button class="btn-delete-modal flex items-center justify-center p-3 rounded-xl text-red-400 hover:bg-red-50 hover:text-red-600 transition-all border border-transparent hover:border-red-100 cursor-pointer"
                    data-id="<?= $activeFolder['id'] ?>"
                    data-desc="<?= esc($activeFolder['title']) ?>"
                    data-url="<?= site_url('folder') ?>" 
                    data-title="Delete Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            <?php endif; ?>

            <button id="btn-open-create-file" 
                data-folder-id="<?= esc($activeFolder['id']) ?>" 
                class="flex items-center gap-2 px-5 py-2.5 bg-accent hover:bg-accent-hover text-white rounded-xl font-bold text-sm transition-all shadow-md shadow-accent/20 active:scale-95 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
                Add Document
            </button>
        </div>
    </div>

    <div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 overflow-x-auto custom-scrollbar">
        <?php $isFirst = true; ?>
        <?php foreach ($groupedGuides as $index => $group): ?>
            <button id="tab-btn-guide-<?= $index ?>" 
                    class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 <?= $isFirst ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> transition-all cursor-pointer" 
                    onclick="switchDocTab('guide-<?= $index ?>')">
                Guide: <?= esc($group['superior']['role']) ?>
                <span class="ml-1.5 px-2 py-0.5 rounded-full <?= $isFirst ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors"><?= count($group['docs']) ?></span>
            </button>
            <?php $isFirst = false; ?>
        <?php endforeach; ?>
        
        <button id="tab-btn-mine" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 <?= !$hasGuides ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?> transition-all cursor-pointer" onclick="switchDocTab('mine')">
            My Submissions
            <span class="ml-1.5 px-2 py-0.5 rounded-full <?= !$hasGuides ? 'bg-accent/10 text-accent' : 'bg-zinc-100 dark:bg-zinc-800 text-text-muted' ?> text-[10px] tab-badge transition-colors"><?= count($myDocs) ?></span>
        </button>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-0 relative">
        
        <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0 z-10">
            <div class="col-span-6">Document Name</div>
            <div class="col-span-4">Status / Activity</div>
            <div class="col-span-2 text-right">Actions</div>
        </div>

        <?php $isFirst = true; ?>
        <?php foreach ($groupedGuides as $index => $group): ?>
            <div id="tab-content-guide-<?= $index ?>" class="tab-content-doc <?= $isFirst ? 'flex flex-col absolute inset-0 top-[45px]' : 'hidden' ?> bg-blue-50/20 dark:bg-blue-900/10">
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
                                    <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 hover:text-blue-800 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors cursor-pointer">
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

        <div id="tab-content-mine" class="tab-content-doc <?= $hasGuides ? 'hidden' : 'flex flex-col absolute inset-0 top-[45px]' ?> bg-surface">
            <div id="doc-scroll-container" class="overflow-y-auto custom-scrollbar flex-1">
                <div class="divide-y divide-surface-border mx-2">
                    <?php if (empty($myDocs)): ?>
                        <div id="empty-doc" class="p-24 text-center">
                            <p class="text-sm text-text-muted font-medium italic">You haven't created any documents yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myDocs as $doc): ?>
                            <div data-doc-id="<?= $doc['id'] ?>" class="doc-row grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-all group">
                                <div class="col-span-6 flex items-center gap-4">
                                    <div class="bg-accent/10 p-1.5 rounded-xl text-accent border border-accent/20 group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <a href="<?= site_url('document?Id=' . $doc['id']) ?>" class="font-bold text-sm text-text hover:text-accent transition-colors truncate">
                                            <?= esc($doc['title']) ?>
                                        </a>
                                        <span class="text-[10px] text-text-muted font-bold tracking-tight uppercase">Record</span>
                                    </div>
                                </div>

                                <div class="col-span-4 flex flex-col gap-0.5">
                                    <?php if ($doc['status'] === 'draft'): ?>
                                        <span class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">Draft</span>
                                    <?php elseif ($doc['status'] === 'submitted'): ?>
                                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">Submitted</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Evaluated</span>
                                    <?php endif; ?>
                                    <span class="text-[10px] font-semibold text-text-muted"><?= date('M d, Y', strtotime($doc['updated_at'] ?? $doc['created_at'])) ?></span>
                                </div>

                                <div class="col-span-2 flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                    <// ?php if ($doc['status'] === 'draft'): ?>
                                        <button class="btn-delete-modal p-2 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 transition-all cursor-pointer"
                                                data-id="<?= $doc['id'] ?>" 
                                                data-desc="<?= esc($doc['title']) ?>"
                                                data-url="<?= site_url('document') ?>" 
                                                data-title="Delete Document">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    <// ?php endif; ?>
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
    </script>
<?php endif; ?>
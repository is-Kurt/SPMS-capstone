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
        <button onclick="document.getElementById('btn-create-folder-modal').click()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-accent rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-accent/5 transition-all group cursor-pointer min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800 text-text-muted group-hover:text-accent mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-accent transition-colors mb-1">Create New Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Click here to start a new evaluation period and create the initial folder.</p>
        </button>
    <?php else: ?>
        <button onclick="toggleAppSidebar()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-accent rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-accent/5 transition-all group cursor-pointer lg:cursor-default min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800 text-text-muted group-hover:text-accent mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-accent transition-colors mb-1">Select a Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
        </button>
    <?php endif; ?>

<?php else: ?>
    
    <?= view('document/partials/hub/_hub_styles') ?>

    <div class="flex flex-col lg:flex-row flex-1 lg:absolute lg:inset-0 lg:min-h-[650px] bg-transparent lg:gap-6 lg:pb-6">
        
        <!-- CENTER / MAIN CONTENT CONTAINER -->
        <div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-xl border border-surface-border shadow-xs overflow-visible lg:overflow-hidden dark:bg-zinc-950 dark:border-zinc-800">
            
            <!-- Mobile active folder selector dropdown -->
            <?= view('components/mobile_folder_dropdown', [
                'activeFolder'     => $activeFolder ?? null,
                'folders'          => $sidebarFolders ?? [],
                'selectedFolderId' => $activeFolder['id'] ?? null,
                'baseUrl'          => !empty($isArchivedView) ? 'folders/archived' : 'folders',
                'containerClass'   => 'p-3.5 border-b border-surface-border dark:border-zinc-800 shrink-0 bg-surface dark:bg-zinc-950'
            ]) ?>

            <!-- OFFICIAL PERFORMANCE PAPER MAIN HUB -->
            <div id="tab-content-mine" class="flex-1 flex flex-col min-h-0 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8 bg-surface dark:bg-zinc-950">
                
                <?php 
                    $isUserAdmin = (session()->get('role') === 'Admin');
                    $isAdminRootCycle = $isUserAdmin && ($activeFolder['user_id'] == session()->get('user_id'));
                    $hubData = get_defined_vars();
                ?>

                <?php if ($isAdminRootCycle): ?>
                    <?= view('document/partials/hub/_admin_cycle_hub', $hubData) ?>
                <?php elseif (empty($myDocs)): ?>
                    <?= view('document/partials/hub/_empty_paper', $hubData) ?>
                <?php else: ?>
                    <?= view('document/partials/hub/_executive_hub', $hubData) ?>
                <?php endif; ?>

            </div>
        </div>

        <!-- RIGHT SIDEBAR (CASCADE DISTRIBUTION FOR ADMINS / FOLDER DETAILS FOR USERS) -->
        <?= view('document/partials/hub/_cascade_drawer', get_defined_vars()) ?>

        <div id="bottom-sheet-overlay" onclick="toggleBottomSheet()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    </div>

    <!-- MOV ATTACHMENTS MODAL -->
    <?= view('document/partials/hub/_mov_modal', get_defined_vars()) ?>

    <!-- HUB SCRIPTS -->
    <?= view('document/partials/hub/_hub_scripts', get_defined_vars()) ?>

<?php endif; ?>

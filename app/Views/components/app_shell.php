<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!--
    Shared two-column shell (sidebar + main content) reused by Folder, Rating,
    and Team controllers. $context picks which modals/JS to load, $sidebarView/
    $mainView pick which partial gets rendered into each column - see the
    view()/mainView calls below.
-->
<?= view('components/header') ?>

<?php $context = $context ?? 'folders'; ?>

<!-- Only load the modals relevant to the current context -->
<?php if ($context === 'folders'): ?>
    <?= view('components/create_file_modal') ?>
    <?= view('components/delete_modal') ?>
    <?= view('components/create_folder_modal') ?>
    <?= view('components/edit_folder_modal') ?>
<?php elseif ($context === 'teams'): ?>
    <?= view('components/create_team_modal') ?>
<?php endif; ?>

<div class="p-4 lg:p-8 max-w-[100rem] mx-auto flex flex-col lg:flex-row gap-4 lg:gap-8 lg:min-h-[calc(100vh-6rem)] lg:pb-4">
    
    <!-- LEFT SIDEBAR -->
    <div id="app-sidebar" class="fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-transparent lg:w-60 lg:static lg:flex flex-shrink-0 flex-col h-full overflow-hidden transition-transform duration-300 transform -translate-x-full lg:translate-x-0 border-r border-surface-border lg:border-none shadow-2xl lg:shadow-none">
        <div class="flex justify-between items-center px-4 pt-6 pb-3 lg:p-0 lg:mb-3 shrink-0">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-text-muted">
                <?= esc($sidebarTitle ?? 'Evaluation Folders') ?>
            </p>
            
            <!-- Dynamic Add Button -->
            <?php if ($context === 'folders' && session()->get('role') === 'Admin'): ?>
                <button id="btn-create-folder-modal" class="w-7 h-7 rounded-lg flex items-center justify-center text-accent hover:text-accent-hover hover:bg-accent/10 transition-colors cursor-pointer" title="New Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            <?php elseif ($context === 'teams'): ?>
                <button id="btn-create-team-modal" class="w-7 h-7 rounded-lg flex items-center justify-center text-accent hover:text-accent-hover hover:bg-accent/10 transition-colors cursor-pointer" title="New Team">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            <?php endif; ?>
            
        </div>
        <div class="overflow-y-auto custom-scrollbar flex-1 px-3 pb-6 lg:p-0 lg:pr-2">
            <!-- Dynamically injects _folder_rows.php OR teams/_sidebar.php -->
            <?= view($sidebarView ?? 'document/_folder_rows', $sidebarData ?? [
                'folders' => $sidebarFolders ?? [], 
                'selectedFolderId' => $selectedFolderId ?? null
            ]) ?>
        </div>
    </div>

    <!-- Mobile App Sidebar Overlay -->
    <div id="app-sidebar-overlay" onclick="toggleAppSidebar()" class="fixed inset-0 bg-black/50 z-[115] hidden lg:hidden opacity-0 transition-opacity duration-300"></div>

    <!-- MAIN CONTENT (Changed h-full to lg:h-full to FIX MOBILE SCROLLING) -->
    <div class="flex-1 flex flex-col min-w-0 overflow-visible relative">
        <?= view($mainView, $mainData) ?>
    </div>

</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>

<script>
    let isAppSidebarOpen = false;
    function toggleAppSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('app-sidebar-overlay');
        if (!sidebar || !overlay) return;
        
        isAppSidebarOpen = !isAppSidebarOpen;
        if (isAppSidebarOpen) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            sidebar.classList.remove('-translate-x-full');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
</script>

<!-- Only load the JS relevant to the current context -->
<?php if ($context === 'folders'): ?>
    <script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/createFileModal.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/createFolderModal.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/editFolderModal.js') ?>"></script>
<?php elseif ($context === 'teams'): ?>
    <script type="module" src="<?= base_url('assets/js/main/modals/createTeamModal.js') ?>"></script>
<?php endif; ?>

<?= $this->endSection() ?>
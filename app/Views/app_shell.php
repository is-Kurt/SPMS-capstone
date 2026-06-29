<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?php $context = $context ?? 'folders'; ?>

<!-- Only load Document/Folder Modals if we are actually in the Folders context -->
<?php if ($context === 'folders'): ?>
    <?= view('components/create_file_modal') ?>
    <?= view('components/delete_modal') ?>
    <?= view('components/create_folder_modal') ?>
<?php endif; ?>

<div class="p-4 lg:p-8 max-w-7xl mx-auto flex flex-col lg:flex-row gap-4 lg:gap-8 lg:h-[calc(100vh-6rem)]">
    
    <!-- LEFT SIDEBAR -->
    <div class="hidden lg:flex w-64 flex-shrink-0 flex-col h-full overflow-hidden">
        <div class="flex justify-between items-center mb-4 px-4 shrink-0">
            <p class="text-[10px] font-black uppercase tracking-widest text-text-muted">
                <?= esc($sidebarTitle ?? 'Evaluation Folders') ?>
            </p>
            
            <!-- Dynamic Add Button -->
            <?php if ($context === 'folders' && session()->get('role') === 'Admin'): ?>
                <button id="btn-create-folder-modal" class="text-accent hover:text-accent-hover transition-colors cursor-pointer" title="New Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            <?php elseif ($context === 'teams'): ?>
                <button onclick="document.getElementById('modal-create-team').classList.remove('hidden'); document.getElementById('modal-create-team').classList.add('flex')" class="text-accent hover:text-accent-hover transition-colors cursor-pointer" title="New Team">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            <?php endif; ?>
            
        </div>
        <div class="overflow-y-auto custom-scrollbar flex-1 pr-2">
            <!-- Dynamically injects _folder_rows.php OR teams/_sidebar.php -->
            <?= view($sidebarView ?? 'document/_folder_rows', $sidebarData ?? [
                'folders' => $sidebarFolders ?? [], 
                'selectedFolderId' => $selectedFolderId ?? null
            ]) ?>
        </div>
    </div>

    <!-- MAIN CONTENT (Changed h-full to lg:h-full to FIX MOBILE SCROLLING) -->
    <div class="flex-1 flex flex-col lg:h-full min-w-0 overflow-visible lg:overflow-hidden relative">
        <?= view($mainView, $mainData) ?>
    </div>

</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>

<!-- Only load Document/Folder JS logic if we are in the Folders context -->
<?php if ($context === 'folders'): ?>
    <script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/createFileModal.js') ?>"></script>
    <script type="module" src="<?= base_url('assets/js/main/modals/createFolderModal.js') ?>"></script>
<?php endif; ?>

<?= $this->endSection() ?>
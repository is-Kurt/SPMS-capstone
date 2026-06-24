<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>
<?= view('components/share_modal') ?>
<?= view('components/create_file_modal') ?>
<?= view('components/delete_modal') ?>
<?= view('components/send_modal') ?>
<?= view('components/create_folder_modal') ?>
    
<div class="p-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-8 h-[calc(100vh-6rem)]">
    
    <div class="w-full md:w-64 flex-shrink-0 flex flex-col h-full overflow-hidden">
        <div class="flex justify-between items-center mb-4 px-4 shrink-0">
            <p class="text-[10px] font-black uppercase tracking-widest text-text-muted">Evaluation Folders</p>
            <?php if (session()->get('role') === 'Admin'): ?>
                <button id="btn-create-folder-modal" class="text-accent hover:text-accent-hover transition-colors cursor-pointer" title="New Folder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="overflow-y-auto custom-scrollbar flex-1 pr-2">
            <?= view('document/_folder_rows', ['folders' => $sidebarFolders, 'selectedFolderId' => $selectedFolderId]) ?>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden relative">
        <?= view($mainView, $mainData) ?>
    </div>
    
</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/shareModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/createFileModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/sendModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/createFolderModal.js') ?>"></script>

<script>
    if (typeof pagination === 'function') pagination();

    document.addEventListener('DOMContentLoaded', () => {
        processBackgroundEmails();
    });
</script>

<?= $this->endSection() ?>
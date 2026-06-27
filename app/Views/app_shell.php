<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>
<?= view('components/share_modal') ?>
<?= view('components/create_file_modal') ?>
<?= view('components/delete_modal') ?>
<?= view('components/send_modal') ?>
<?= view('components/create_folder_modal') ?>

<div id="mobile-folder-sidebar" class="fixed inset-y-0 left-0 z-[110] w-72 bg-surface border-r border-surface-border shadow-2xl transform -translate-x-full transition-transform duration-300 lg:hidden">
    <div class="p-6 flex justify-between items-center border-b border-surface-border">
        <p class="text-[10px] font-black uppercase tracking-widest text-text-muted">Folders</p>
        <button onclick="toggleMobileFolders(false)" class="text-text-muted hover:text-text">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="overflow-y-auto h-full p-4">
        <?= view('document/_folder_rows', ['folders' => $sidebarFolders, 'selectedFolderId' => $selectedFolderId]) ?>
    </div>
</div>

<div id="sidebar-overlay" onclick="toggleMobileFolders(false)" class="fixed inset-0 bg-black/50 z-[105] hidden lg:hidden"></div>

<div class="p-4 lg:p-8 max-w-7xl mx-auto flex flex-col lg:flex-row gap-4 lg:gap-8 lg:h-[calc(100vh-6rem)]">
    
    <div class="hidden lg:flex w-64 flex-shrink-0 flex-col h-full overflow-hidden">
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

    <div class="flex-1 flex flex-col h-full min-w-0 overflow-visible lg:overflow-hidden relative">
        
        <button onclick="toggleMobileFolders(true)" class="lg:hidden flex items-center gap-2 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-xl mb-4 text-xs font-bold text-text cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            Select Folder
        </button>

        <?= view($mainView, $mainData) ?>
    </div>
</div>

<script>
    function toggleMobileFolders(show) {
        const sidebar = document.getElementById('mobile-folder-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (show) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<?= $this->endSection() ?>
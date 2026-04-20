<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?= view('components/share_modal') ?>
<?= view('components/create_modal') ?>
<?= view('components/delete_modal') ?>
<?= view('components/send_modal') ?>
<?= view('components/create_folder_modal') ?>
    
<div class="p-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-10">
    <div class="w-full md:w-64 flex-shrink-0">
        <nav id="sidebar-nav" class="flex flex-col gap-1.5">
            <p class="px-4 text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Navigation</p>
            <?php
            $navItems = [
                ['id' => 'all_docs', 'label' => 'All Documents'],
                ['id' => 'owned', 'label' => 'Owned Documents'],
                ['id' => 'shared', 'label' => 'Shared With Me'],
            ];

            foreach ($navItems as $item):
                $isActive = ($filter === $item['id']);
                $count = $counts[$item['id']] ?? 0;
            ?>
                <a href="<?= site_url($item['id'] === 'all_docs' ? 'documents' : 'documents?docs=' . $item['id'] ) ?>"
                   class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all flex justify-between items-center
                   <?= $isActive ? 'bg-accent text-white shadow-lg shadow-accent/20' : 'text-text-muted hover:bg-accent/10 hover:text-accent' ?>">
                    <span><?= $item['label'] ?></span>
                    
                    <span class="text-[10px] px-2 py-0.5 rounded-full min-w-[24px] text-center transition-all <?= $isActive ? 'bg-white/20 text-white' : 'bg-zinc-200 dark:bg-zinc-800 text-text-muted' ?> <?= $count == 0 ? 'invisible' : '' ?>">
                        <?= $count > 0 ? $count : '0' ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="flex-1">
        <div class="flex justify-between items-end mb-8 px-1">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-text">
                    <?= ($filter === 'all' || !$filter) ? "All Documents" : ucwords(str_replace('_', ' ', $filter)) ?>
                </h1>
                <p class="text-sm text-text-muted mt-1 font-medium italic">Manage your IPCR records.</p>
            </div>
            
            <div class="flex gap-3">
                <?php if (session()->get('role') === 'admin'): ?>
                    <button id="btn-open-folder-create" class="flex items-center gap-2 px-6 py-3 bg-surface border border-surface-border hover:border-accent/30 text-text rounded-2xl font-bold text-sm transition-all shadow-sm active:scale-95 cursor-pointer group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-text-muted group-hover:text-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        New Evaluation Folder
                    </button>
                <?php endif; ?>

                <button id="btn-open-create" class="flex items-center gap-2 px-6 py-3 bg-accent hover:bg-accent-hover text-white rounded-2xl font-bold text-sm transition-all shadow-xl shadow-accent/20 active:scale-95 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Blank Document
                </button>
            </div>
        </div>

        <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col max-h-[calc(100vh-14rem)]">
            <div class="grid grid-cols-13 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0">
                <div class="col-span-5">Name</div>
                <div class="col-span-3">Last Activity</div>
                <div class="col-span-4">Owner</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>

            <div class="overflow-y-auto custom-scrollbar flex-1 relative">
                <div id="table-body-container" class="divide-y divide-surface-border mx-2">
                    <?= view('document/_table_rows', ['docs' => $docs]) ?>
                </div>
            </div>

            <div id="pagination-ui" class="px-8 py-3 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 flex justify-between items-center shrink-0">
                <span class="text-xs text-text-muted font-bold" id="page-info">Showing...</span>

                <div class="flex gap-2">
                    <button id="prev-page" disabled
                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Prev
                    </button>
                    <button id="next-page" disabled
                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-text hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/main/functions.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/shareModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/createModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/sendModal.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main/modals/createFolderModal.js') ?>"></script>

<script>
    const AppConfig = {
        baseUrl: '<?= site_url('document') ?>',
    };

    document.addEventListener('DOMContentLoaded', () => {
        pagination();
    });
</script>

<?= $this->endSection() ?>
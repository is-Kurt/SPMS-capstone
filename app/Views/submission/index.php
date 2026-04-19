<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-10">
    <?= view('components/delete_modal') ?>
    
    <div class="w-full md:w-64 flex-shrink-0">
        <nav id="sidebar-nav" class="flex flex-col gap-1.5">
            <label class="px-4 text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Filters</label>
            <?php
            $navItems = [
                ['id' => 'all_submissions', 'label' => 'All Submissions'],
                ['id' => 'locked', 'label' => 'Locked'],
                ['id' => 'pending', 'label' => 'Pending Review'],
                ['id' => 'unevaluated', 'label' => 'Unevaluated'],
                ['id' => 'evaluated', 'label' => 'Evaluated'],
            ];

            foreach ($navItems as $item):
                $isActive = ($filter === $item['id']);
                $count = $counts[$item['id']] ?? 0;
            ?>
                <a href="<?= site_url($item['id'] === 'all_submissions' ? 'submissions' : 'submissions?docs=' . $item['id'] ) ?>"
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
                    <?= ($filter === 'all' || !$filter) ? "All Submissions" : ucwords(str_replace('_', ' ', $filter)) ?>
                </h1>
                <p class="text-sm text-text-muted mt-1 font-medium italic">Review and rate document submissions.</p>
            </div>
        </div>

        <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col max-h-[calc(100vh-14rem)]">
            <div class="grid grid-cols-13 gap-4 px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted shrink-0">
                <div class="col-span-4">Name</div>
                <div class="col-span-5">Evaluation Period</div>
                <div class="col-span-3">Status</div>
                <div class="col-span-1 text-right">Actions</div>
            </div>

            <div class="overflow-y-auto custom-scrollbar flex-1 relative">
                <div id="table-body-container" class="divide-y divide-surface-border mx-2">
                    <?= view('submission/_table_rows', ['docs' => $docs]) ?>
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
<script type="module" src="<?= base_url('assets/js/main/modals/deleteModal.js') ?>"></script>

<script>
    const AppConfig = {
        baseUrl: '<?= site_url('submission') ?>',
    };

    document.addEventListener('DOMContentLoaded', () => {
        pagination();
    });
</script>

<?= $this->endSection() ?>
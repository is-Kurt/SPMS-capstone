<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-8">
    <?= view('documents/_share_modal', ['docs' => $docs]) ?>
    
    <div class="w-full md:w-64 flex-shrink-0">
        <nav class="flex flex-col gap-1">
            <a href="<?= site_url('documents?docs=all') ?>" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $filter === 'all' ? 'bg-highlight/10 text-highlight' : 'text-sub-text hover:bg-main-border/20 hover:text-foreground' ?>">
                All
            </a>
            
            <a href="<?= site_url('documents?docs=owned') ?>" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors flex justify-between items-center <?= $filter === 'owned' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-500' : 'text-sub-text hover:bg-main-border/20 hover:text-foreground' ?>">
                Owned Documents
                </a>

            <a href="<?= site_url('documents?docs=shared') ?>" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $filter === 'shared' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-500' : 'text-sub-text hover:bg-main-border/20 hover:text-foreground' ?>">
                Shared With Me
            </a>
        </nav>
    </div>

    <div class="flex-1">
        <div class="flex justify-between items-center mb-6 px-1">
            <h1 class="text-2xl font-bold">
                <?php 
                    if ($filter === 'pending') echo "Pending Review";
                    elseif ($filter === 'evaluated') echo "Evaluated Documents";
                    else echo "All documents";
                ?>
            </h1>
            <?= form_open('document') ?>
                <button type="submit" class="button flex items-center gap-2 text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Blank Document
                </button>
            <?= form_close() ?>
        </div>

        <div class="bg-card-bg border border-main-border rounded-lg overflow-hidden">
            <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-main-border/10 border-b border-main-border text-xs font-semibold uppercase tracking-wider text-sub-text">
                <div class="col-span-7">Name</div>
                <div class="col-span-3">Last Opened</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>

            <div id="table-body-container">
                <?= view('documents/_table_rows', ['docs' => $docs]) ?>
            </div>
        </div>
    </div>
</div>

<script  src="<?= base_url('assets/main/js/functions.js') ?>"></script>
<script  src="<?= base_url('assets/main/js/shareFunctions.js') ?>"></script>

<script>
    const AppConfig = {
        deleteUrl: '<?= site_url('documents') ?>',
        shareUrl: '<?= site_url('documents/share') ?>',
        userSearchUrl: '<?= site_url('users/search') ?>',
        csrfToken: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
    };
</script>

<?= $this->endSection() ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="px-4 md:px-8 py-3 md:py-4 max-w-[1600px] mx-auto flex flex-col gap-3 md:gap-4 w-full lg:min-h-[calc(100vh-6rem)]">
    
    <div class="shrink-0">
        <h1 class="text-xl md:text-2xl font-black tracking-tight text-text">User Management</h1>
    </div>

    <div class="flex gap-6 border-b border-surface-border shrink-0 px-2 overflow-x-auto custom-scrollbar whitespace-nowrap">
        <button type="button" id="tab-btn-directory" class="tab-btn pb-2.5 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'directory' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('directory')">
            User Directory
            <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors" id="directory-count"><?= count($users) ?></span>
        </button>
        <button type="button" id="tab-btn-create" class="tab-btn pb-2.5 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'create' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('create')">
            Send Invitations
        </button>
        <button type="button" id="tab-btn-invitations" class="tab-btn pb-2.5 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'invitations' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('invitations')">
            Invitations
            <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors" id="invitations-count"><?= count($invitations) ?></span>
        </button>
        <button type="button" id="tab-btn-system" class="tab-btn pb-2.5 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'system' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('system')">
            System Data
        </button>
    </div>

    <div class="-mt-1 bg-surface lg:bg-surface/50 border-none lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm flex flex-col flex-1 lg:min-h-[550px] lg:overflow-hidden relative pb-10 lg:pb-0">
        <?= $this->include('accounts/tabs/directory') ?>
        <?= $this->include('accounts/tabs/create') ?>
        <?= $this->include('accounts/tabs/invitations') ?>
        <?= $this->include('accounts/tabs/system') ?>
    </div>
</div>

<?= $this->include('accounts/tabs/scripts') ?>


<?= $this->endSection() ?>
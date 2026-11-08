<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?php
// Sections, listed down the left like the Folders sidebar (2026-11-08): [label, count, count id, icon path]
$navItems = [
    'directory'   => ['User Directory', count($users), 'directory-count', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
    'create'      => ['Send Invitations', null, null, 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
    'invitations' => ['Invitations', count($invitations), 'invitations-count', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    'system'      => ['System Data', null, null, 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'],
    'twg'         => ['TWG Assignments', count($twgUsers ?? []), 'twg-count', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    'backups'     => ['Backups', null, null, 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
    'excel'       => ['Excel Password', null, null, 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
];
$navOn  = 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border-zinc-200 dark:border-zinc-700/60 shadow-xs';
$navOff = 'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800/40';
?>
<style>
/* 1024-1279px: the content already has its own Filters panel, so the sections become a narrow column of icons
   (names on hover, counts as small bubbles); from 1280px the full list, as in Folders. */
/* Each list's Filters panel on its right, as on Ratings and My Staff (on phones it still slides in from the left) */
@media (min-width: 1024px) {
    #directory-sidebar, #invitations-sidebar, .system-sidebar { order: 99; border-right-width: 0 !important; border-left-width: 1px !important; border-left-style: solid; }
}
@media (min-width: 1024px) and (max-width: 1279px) {
    #accounts-nav { width: 3.5rem !important; }
    #accounts-nav .acc-nav-title, #accounts-nav .tab-label { display: none; }
    #accounts-nav .tab-btn { justify-content: center; padding-left: 0; padding-right: 0; height: 2.75rem; }
    #accounts-nav .tab-badge { position: absolute; top: 0.2rem; right: 0.2rem; margin: 0; padding: 0 0.3rem; font-size: 9px; line-height: 1.1rem; }
}
</style>
<div role="main" class="px-4 md:px-8 py-3 md:py-4 max-w-[1600px] mx-auto w-full flex flex-col lg:flex-row gap-3 lg:gap-6 lg:min-h-[calc(100vh-6rem)] spms-content-slide">

    <!-- Sections, down the left like the Folders sidebar; on phones one button that opens the list -->
    <nav id="accounts-nav" class="shrink-0 lg:w-60 flex flex-col" aria-label="User Management" data-on="<?= $navOn ?>" data-off="<?= $navOff ?>">
        <p class="acc-nav-title hidden lg:block px-3 mb-3 text-[11px] font-extrabold uppercase tracking-wider text-text-muted">User Management</p>
        <button type="button" id="accounts-nav-toggle" class="lg:hidden w-full flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold border <?= $navOn ?> cursor-pointer" aria-expanded="false" aria-controls="accounts-nav-list">
            <span class="flex flex-col items-start min-w-0">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-text-muted">User Management</span>
                <span id="accounts-nav-current" class="truncate"><?= esc($navItems[$activeTab][0] ?? 'User Directory') ?></span>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-text-muted transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div id="accounts-nav-list" class="hidden lg:flex flex-col gap-2 mt-2 lg:mt-0">
            <?php foreach ($navItems as $key => [$label, $count, $countId, $icon]): $on = $activeTab === $key; ?>
                <button type="button" id="tab-btn-<?= $key ?>" onclick="switchUserTab('<?= $key ?>')" <?= $on ? 'aria-current="page"' : '' ?> aria-label="<?= esc($label, 'attr') ?>"
                        class="tab-btn relative w-full px-3.5 py-2.5 rounded-xl text-xs font-bold border transition-colors flex items-center gap-2.5 text-left cursor-pointer <?= $on ? $navOn : $navOff ?>">
                    <span class="tab-marker absolute left-0 inset-y-2.5 w-1 bg-amber-500 rounded-r-full <?= $on ? '' : 'hidden' ?>"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>" /></svg>
                    <span class="tab-label truncate"><?= esc($label) ?></span>
                    <?php if ($countId): ?>
                        <span class="ml-auto shrink-0 px-2 py-0.5 rounded-full bg-accent/10 text-accent-ink text-[10px] tab-badge transition-colors" id="<?= $countId ?>"><?= (int) $count ?></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </nav>

    <div class="flex-1 min-w-0 flex flex-col">
    <div class="bg-surface lg:bg-surface/50 border-none lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm flex flex-col flex-1 lg:min-h-[550px] lg:overflow-hidden relative pb-10 lg:pb-0">
        <?= $this->include('accounts/tabs/directory') ?>
        <?= $this->include('accounts/tabs/create') ?>
        <?= $this->include('accounts/tabs/invitations') ?>
        <?= $this->include('accounts/tabs/system') ?>
        <?= $this->include('accounts/tabs/twg_assignments') ?>
        <?= $this->include('accounts/tabs/backups') ?>
        <?= $this->include('accounts/tabs/excel_password') ?>
    <?php if (!empty($openDocRules)): ?>
        <script>document.addEventListener('DOMContentLoaded', () => window.switchSystemTab && switchSystemTab('doc_rules'));</script>
    <?php endif; ?>
    </div>
    </div>
</div>

<?= $this->include('accounts/tabs/scripts') ?>


<?= $this->endSection() ?>
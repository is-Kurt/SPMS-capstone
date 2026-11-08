<?php
/**
 * Accounts > Backups (2026-11-08): back up now, restore, delete. Data from AccountManagement::index
 * ($backups, $restoreLog: App\Libraries\Backups); actions in SystemBackups. Admin only (the whole page is).
 */
$backups = $backups ?? [];
$restoreLog = $restoreLog ?? [];
$kindBadge = [
    'nightly'        => 'bg-zinc-100 dark:bg-zinc-800 text-text-muted border-surface-border',
    'manual'         => 'bg-accent/10 text-accent-ink border-accent/30',
    'before-restore' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-500/20',
];
$size = fn(int $b) => $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : number_format(max(1, round($b / 1024))) . ' KB';
$field = 'w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold';
?>
<div id="tab-content-backups" class="tab-content <?= $activeTab === 'backups' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface p-4 lg:p-6 overflow-y-auto custom-scrollbar">
    <div class="flex flex-col md:flex-row items-start justify-between gap-4 mb-5">
        <div class="min-w-0">
            <h2 class="text-lg font-black text-text tracking-tight">Backups</h2>
            <p class="text-xs text-text-muted mt-1 max-w-2xl leading-relaxed">
                A backup holds the whole database and the uploaded files. One is made every midnight and kept for 30 days.
                Restoring puts the system back to that moment: anything saved after it is lost. The current state is backed up
                first, so a restore can be undone. The site is down for a few seconds while it restores.
            </p>
        </div>
        <button type="button" id="btn-backup-now" class="shrink-0 px-5 py-2.5 bg-accent text-accent-text rounded-xl text-sm font-bold shadow-sm hover:bg-accent-hover transition-all active:scale-[0.98] cursor-pointer">
            Back up now
        </button>
    </div>

    <div class="rounded-xl border border-surface-border overflow-hidden">
        <table class="w-full text-left border-collapse block lg:table">
            <thead class="hidden lg:table-header-group bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted">
                <tr>
                    <th class="px-6 py-2.5">When</th>
                    <th class="px-6 py-2.5">Type</th>
                    <th class="px-6 py-2.5">Size</th>
                    <th class="px-6 py-2.5">Made by</th>
                    <th class="px-6 py-2.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="backup-rows" class="block lg:table-row-group divide-y divide-surface-border">
                <?php if (!$backups): ?>
                    <tr class="block lg:table-row"><td colspan="5" class="block lg:table-cell px-6 py-10 text-center text-sm font-bold text-text-muted italic">No backups yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($backups as $b): ?>
                    <tr class="backup-row block lg:table-row p-4 lg:p-0 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20" data-id="<?= esc($b['id'], 'attr') ?>">
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3 text-sm font-bold text-text whitespace-nowrap"><?= esc(date('M j, Y g:i A', strtotime($b['at']))) ?></td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3">
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border <?= $kindBadge[$b['kind']] ?>"><?= esc(\App\Libraries\Backups::KINDS[$b['kind']]) ?></span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3 text-xs font-semibold text-text-muted whitespace-nowrap"><?= esc($size((int) $b['size'])) ?></td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3 text-xs text-text-muted max-w-xs">
                            <span class="block truncate"><?= esc($b['by'] ?: 'Automatic') ?></span>
                            <?php if ($b['note'] !== ''): ?><span class="block truncate text-[10px]"><?= esc($b['note']) ?></span><?php endif; ?>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-3 text-right whitespace-nowrap">
                            <button type="button" class="js-backup-restore px-3 py-1.5 rounded-lg text-xs font-bold border border-surface-border text-text hover:border-accent hover:text-accent transition-colors cursor-pointer" data-id="<?= esc($b['id'], 'attr') ?>" data-label="<?= esc($b['label'], 'attr') ?>">Restore</button>
                            <?php if ($b['kind'] !== 'nightly'): ?>
                                <button type="button" class="js-backup-delete ml-1 inline-flex items-center justify-center w-8 h-8 rounded-lg text-danger-500 hover:text-danger-600 hover:bg-danger-500/10 transition-colors cursor-pointer align-middle" data-id="<?= esc($b['id'], 'attr') ?>" data-label="<?= esc($b['label'], 'attr') ?>" title="Delete this backup" aria-label="Delete this backup">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($restoreLog): ?>
        <div class="mt-6">
            <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Restores</div>
            <ul class="flex flex-col gap-1.5">
                <?php foreach ($restoreLog as $r): ?>
                    <li class="text-xs text-text-muted">
                        <span class="font-bold text-text"><?= esc(date('M j, Y g:i A', strtotime($r['at'] ?? 'now'))) ?></span>:
                        <?= esc($r['by'] ?? '') ?> restored <?= esc($r['restored'] ?? '') ?>.
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- Restore: the Admin's password and RESTORE typed in -->
<div id="modal-restore" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4">
    <div class="relative w-full max-w-lg rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all my-auto">
        <h3 class="text-xl font-black text-text tracking-tight mb-1">Restore a backup</h3>
        <p id="restore-what" class="text-sm font-bold text-text mb-3"></p>
        <p class="text-xs text-text-muted leading-relaxed mb-5">
            Everything saved after this backup is lost. The current state is backed up first, so you can undo this by
            restoring that one. The site is down for a few seconds.
        </p>
        <label for="restore-password" class="block text-[10px] font-bold text-text-muted mb-1.5">Your password</label>
        <input type="password" id="restore-password" autocomplete="current-password" class="<?= $field ?> mb-4">
        <label for="restore-confirm" class="block text-[10px] font-bold text-text-muted mb-1.5">Type RESTORE to confirm</label>
        <input type="text" id="restore-confirm" autocomplete="off" class="<?= $field ?> mb-6" placeholder="RESTORE">
        <div class="grid grid-cols-2 gap-3">
            <button type="button" id="restore-cancel" class="py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer">Cancel</button>
            <button type="button" id="restore-go" class="py-3 rounded-xl bg-danger-500 hover:bg-danger-600 text-white text-sm font-bold transition-colors cursor-pointer disabled:opacity-50">Restore</button>
        </div>
    </div>
</div>

<script>
(function () {
    const $ = id => document.getElementById(id);
    const reloadTab = () => { window.location.href = '<?= site_url('accounts/backups') ?>'; };
    const busy = (btn, on, text) => { btn.disabled = on; if (text) btn.textContent = text; };

    $('btn-backup-now').addEventListener('click', () => {
        const btn = $('btn-backup-now');
        busy(btn, true, 'Backing up...');
        apiPost('<?= site_url('backups/create') ?>', new FormData(), {
            onSuccess: async (d) => { await window.appAlert(d.message || 'Backup made.', { title: 'Backed up', variant: 'success' }); reloadTab(); },
            onError: (msg) => { busy(btn, false, 'Back up now'); window.appAlert(msg || 'The backup failed.', { variant: 'danger' }); }
        });
    });

    let restoreId = null;
    const modal = $('modal-restore');
    const open = (id, label) => {
        restoreId = id;
        $('restore-what').textContent = label;
        $('restore-password').value = ''; $('restore-confirm').value = '';
        busy($('restore-go'), false, 'Restore');
        modal.classList.remove('hidden'); modal.classList.add('flex');
        setTimeout(() => $('restore-password').focus(), 50);
    };
    const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); restoreId = null; };
    $('restore-cancel').addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });

    document.addEventListener('click', async e => {
        const r = e.target.closest('.js-backup-restore');
        if (r) { open(r.dataset.id, r.dataset.label); return; }
        const d = e.target.closest('.js-backup-delete');
        if (!d) return;
        const ok = await window.appConfirm(`Delete the backup "${d.dataset.label}"? It cannot be restored afterwards.`, { title: 'Delete backup', confirmText: 'Delete', variant: 'danger' });
        if (!ok) return;
        const fd = new FormData(); fd.append('id', d.dataset.id);
        apiPost('<?= site_url('backups/delete') ?>', fd, {
            onSuccess: () => reloadTab(),
            onError: (msg) => window.appAlert(msg || 'Could not delete it.', { variant: 'danger' })
        });
    });

    $('restore-go').addEventListener('click', () => {
        if ($('restore-confirm').value.trim().toUpperCase() !== 'RESTORE') {
            window.appAlert('Type RESTORE to confirm.', { variant: 'warning' });
            return;
        }
        const fd = new FormData();
        fd.append('id', restoreId); fd.append('password', $('restore-password').value); fd.append('confirm', $('restore-confirm').value);
        busy($('restore-go'), true, 'Restoring...');
        apiPost('<?= site_url('backups/restore') ?>', fd, {
            onSuccess: async (d) => { close(); await window.appAlert(d.message || 'Restored.', { title: 'Restored', variant: 'success' }); reloadTab(); },
            onError: (msg) => { busy($('restore-go'), false, 'Restore'); window.appAlert(msg || 'The restore failed. Nothing was changed.', { variant: 'danger' }); }
        });
    });
})();
</script>

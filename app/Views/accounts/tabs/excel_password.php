<?php
/**
 * Accounts > Excel Password (2026-11-08): the password that locks downloaded Excel files, and every earlier one so
 * an older file can still be unlocked. Data from AccountManagement::index ($excelPasswords: ExcelPassword::history,
 * newest first; $excelPasswordReady); saved by ExcelPasswords::set. Admin only (the whole page is).
 */
$excelPasswords = $excelPasswords ?? [];
$ready = !empty($excelPasswordReady);
$now = $ready || $excelPasswords ? \App\Libraries\ExcelPassword::current() : null;   // the one new files get
$when = fn(?string $d) => $d ? date('M j, Y g:i A', strtotime($d)) : null;
$field = 'w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold';
$label = 'block text-[10px] font-bold text-text-muted mb-1.5';
$secret = function (?string $pw, string $id) {
    if ($pw === null) return '<span class="text-xs italic text-text-muted">cannot be read (the app key changed)</span>';
    return '<span class="inline-flex items-center gap-2"><code class="js-xl-secret xl-secret font-mono text-sm font-bold text-text" data-secret="' . esc($pw, 'attr') . '" id="' . $id . '">' . str_repeat('•', 8) . '</code>'
        . '<button type="button" class="js-xl-show xl-toggle px-2 py-0.5 rounded-md text-[10px] font-bold border border-surface-border text-text-muted hover:text-text hover:border-accent transition-colors cursor-pointer" data-for="' . $id . '">Show</button></span>';
};
?>
<div id="tab-content-excel" class="tab-content <?= $activeTab === 'excel' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface p-4 lg:p-6 overflow-y-auto custom-scrollbar">
    <div class="mb-5 min-w-0">
        <h2 class="text-lg font-black text-text tracking-tight">Excel Password</h2>
        <p class="text-xs text-text-muted mt-1 max-w-2xl leading-relaxed">
            Downloaded Excel files are locked against edits. A file keeps the password it was downloaded with: find it below by the file's Created date (File &gt; Info).
        </p>
    </div>

    <?php if (!$ready): ?>
        <div class="mb-5 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-xs font-bold text-amber-700 dark:text-amber-400">
            This needs a database update before a new password can be saved (php spark migrate). Until then files use the built-in password.
        </div>
    <?php endif; ?>

    <style>
        #tab-content-excel .xl-secret { display: inline-block; min-width: 12ch; }
        #tab-content-excel .xl-toggle { width: 3.25rem; text-align: center; }
        @media (min-width:1024px) {
            #tab-content-excel .xl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
            #xl-table { table-layout: fixed; }
        }
    </style>
    <div class="xl-grid grid grid-cols-1 gap-5 mb-6">
        <div class="rounded-xl border border-surface-border p-5">
            <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">In use now</div>
            <?php if ($now): ?>
                <div class="flex items-center gap-3 flex-wrap">
                    <?= $secret($now['password'], 'xl-now') ?>
                </div>
                <p class="text-xs text-text-muted mt-2"><?= $now['since'] ? 'Since ' . esc($when($now['since'])) . ($now['set_by'] ? ', set by ' . esc($now['set_by']) : '') : 'The built-in password (none has been set yet).' ?></p>
            <?php endif; ?>
        </div>

        <div class="rounded-xl border border-surface-border p-5">
            <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-3">Set a new password</div>
            <label for="xl-new" class="<?= $label ?>">New password</label>
            <input type="password" id="xl-new" autocomplete="new-password" aria-describedby="xl-new-hint" class="<?= $field ?>" <?= $ready ? '' : 'disabled' ?>>
            <p id="xl-new-hint" class="text-[10px] text-text-muted mt-1 mb-3"><?= \App\Libraries\ExcelPassword::MIN ?> to <?= \App\Libraries\ExcelPassword::MAX ?> characters</p>
            <label for="xl-repeat" class="<?= $label ?>">Confirm new password</label>
            <input type="password" id="xl-repeat" autocomplete="new-password" class="<?= $field ?> mb-3" <?= $ready ? '' : 'disabled' ?>>
            <label for="xl-admin" class="<?= $label ?>">Your account password</label>
            <input type="password" id="xl-admin" autocomplete="current-password" class="<?= $field ?> mb-4" <?= $ready ? '' : 'disabled' ?>>
            <button type="button" id="xl-save" class="px-5 py-2.5 bg-accent text-accent-text rounded-xl text-sm font-bold shadow-sm hover:bg-accent-hover transition-all active:scale-[0.98] cursor-pointer disabled:opacity-50" <?= $ready ? '' : 'disabled' ?>>Save new password</button>
        </div>
    </div>

    <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">All passwords</div>
    <div class="rounded-xl border border-surface-border overflow-hidden">
        <table id="xl-table" class="w-full text-left border-collapse block lg:table">
            <colgroup><col style="width: 45%"><col style="width: 35%"><col style="width: 20%"></colgroup>
            <thead class="hidden lg:table-header-group bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted">
                <tr>
                    <th class="px-6 py-2.5">Files downloaded</th>
                    <th class="px-6 py-2.5">Password</th>
                    <th class="px-6 py-2.5">Set by</th>
                </tr>
            </thead>
            <tbody id="xl-history" class="block lg:table-row-group divide-y divide-surface-border">
                <?php foreach ($excelPasswords as $i => $h): ?>
                    <tr class="xl-row block lg:table-row p-4 lg:p-0" data-no="<?= (int) $h['no'] ?>">
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3 text-sm font-bold text-text">
                            <?= $h['since'] ? esc($when($h['since'])) : 'From the start' ?>
                            <?= $h['until'] ? ' to ' . esc($when($h['until'])) : ' to now' ?>
                            <?= $now && (int) $h['no'] === (int) $now['no'] ? ' <span class="text-[10px] font-bold text-accent-ink">in use</span>' : '' ?>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3"><?= $secret($h['password'], 'xl-h' . (int) $h['no']) ?></td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-3 text-xs text-text-muted"><?= esc($h['set_by'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const $ = id => document.getElementById(id);
    document.addEventListener('click', e => {
        const b = e.target.closest('.js-xl-show');
        if (!b) return;
        const code = $(b.dataset.for);
        const shown = b.textContent === 'Hide';
        code.textContent = shown ? '••••••••' : code.dataset.secret;
        b.textContent = shown ? 'Show' : 'Hide';
    });
    const save = $('xl-save');
    if (!save) return;
    save.addEventListener('click', () => {
        if ($('xl-new').value !== $('xl-repeat').value) { window.appAlert('Passwords do not match.', { variant: 'warning' }); return; }
        const fd = new FormData();
        fd.append('password', $('xl-new').value); fd.append('password_confirm', $('xl-repeat').value); fd.append('admin_password', $('xl-admin').value);
        save.disabled = true;
        apiPost('<?= site_url('excel-password/set') ?>', fd, {
            onSuccess: async (d) => { await window.appAlert(d.message || 'Saved.', { title: 'Excel password saved', variant: 'success' }); window.location.href = '<?= site_url('accounts/excel') ?>'; },
            onError: (msg) => { save.disabled = false; window.appAlert(msg || 'Could not save it.', { variant: 'danger' }); }
        });
    });
})();
</script>

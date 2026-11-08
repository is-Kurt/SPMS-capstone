<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('components/header') ?>
<?php
/**
 * Accounts for supervisors (2026-11-07): everyone in their branch (the units they head and all below), one row per
 * person. Manage works like the admin's (2026-11-08): end a position, add one (a position they may give in a unit of
 * their branch; the role follows: a head position = unit head), and set the form type. Nobody's only position can be
 * ended here (moving someone = add the new position, then end the old one).
 * Data: $people (by user id, with their branch positions), $units (branch tree with the positions they may give in
 * each), $rules (doc-type rules), $docTypes.
 */
$jsFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$unitNames = [];
foreach ($people as $p) foreach ($p['assignments'] as $a) $unitNames[$a['unit']] = true;
$field = 'w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer';
?>
<div role="main" class="px-4 md:px-8 py-3 md:py-4 max-w-[1600px] mx-auto flex flex-col gap-3 md:gap-4 w-full lg:min-h-[calc(100vh-6rem)] spms-content-slide">
    <div class="shrink-0">
        <h1 class="text-xl md:text-2xl font-black tracking-tight text-text">Accounts</h1>
        <p class="text-xs text-text-muted font-medium mt-1">Everyone in your branch. You can add and end their positions in your units and set their form type; a head position makes them a unit head. To move someone, add the new position first, then end the old one. Employment status, new accounts and disabling accounts are handled by the administrator.</p>
    </div>

    <div class="flex-1 flex flex-col min-h-0 rounded-2xl border border-surface-border bg-surface overflow-hidden">
        <div class="px-4 py-3 border-b border-surface-border flex flex-col sm:flex-row gap-3 shrink-0">
            <input type="text" id="sa-search" placeholder="Search name or email..." autocomplete="off" style="max-width: 20rem;" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold">
            <?php if (count($unitNames) > 1): ?>
                <div class="w-full" style="max-width: 20rem;">
                    <select data-themed-select id="sa-unit" aria-label="Department / unit">
                        <option value="" data-choice>All departments / units</option>
                        <?php foreach ($units as $u): ?><option value="<?= esc($u['name']) ?>" data-level="<?= (int) $u['level'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border sticky top-0 z-10 bg-surface">
                    <tr><th class="px-4 py-2.5">Name</th><th class="px-4 py-2.5">Department / unit &middot; position</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5">Form type</th><th class="px-4 py-2.5"></th></tr>
                </thead>
                <tbody class="divide-y divide-surface-border text-sm">
                    <?php foreach ($people as $p): ?>
                        <tr class="sa-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" data-user-id="<?= (int) $p['user_id'] ?>" data-search="<?= esc(strtolower($p['name'] . ' ' . $p['email'])) ?>" data-units="<?= esc(json_encode(array_column($p['assignments'], 'unit'))) ?>">
                            <td class="px-4 py-3">
                                <div class="font-bold text-text"><?= esc($p['name']) ?></div>
                                <div class="text-[11px] text-text-muted"><?= esc($p['email']) ?></div>
                                <?php foreach ($p['flags'] as $f): ?><div class="text-[11px] font-bold text-warning-500 mt-0.5">&#9888; <?= esc($f) ?></div><?php endforeach; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php foreach ($p['assignments'] as $i => $a): ?>
                                    <div class="<?= $i ? 'mt-2' : '' ?>">
                                        <div class="font-bold text-text"><?= esc($a['unit']) ?></div>
                                        <div class="text-[11px] text-text-muted"><?= esc($a['position']) ?><?php if ($a['head']): ?> &middot; <span class="font-black uppercase tracking-widest text-accent-ink" style="font-size: 9px;">Unit head</span><?php endif; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td class="px-4 py-3 text-text"><?= esc($p['status'] ?: '—') ?></td>
                            <td class="px-4 py-3 font-bold text-text"><?= esc($p['doc'] ?: 'Not set') ?></td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="js-sa-manage text-xs font-bold text-accent-ink hover:text-accent-hover px-3 py-1.5 rounded-lg bg-accent/10 hover:bg-accent/20 cursor-pointer" data-user="<?= (int) $p['user_id'] ?>">Manage</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="sa-empty" class="hidden"><td colspan="5" class="px-4 py-10 text-center text-xs font-bold text-text-muted">Nobody matches.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Manage one person: their positions in this branch (end / add) and form type, as in the admin's Manage -->
<div id="modal-sa" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4">
    <div class="relative w-full max-w-2xl rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all my-auto">
        <h3 id="sa-name" class="text-xl font-black text-text tracking-tight mb-1"></h3>
        <p id="sa-sub" class="text-xs font-bold text-text-muted uppercase tracking-widest mb-4"></p>
        <div id="sa-flags" class="flex flex-col gap-1 mb-4"></div>

        <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Current positions</div>
        <div id="sa-assignments" class="flex flex-col gap-2 mb-4"></div>

        <div class="rounded-xl border border-surface-border p-4 mb-6">
            <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-3">Add position</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="sa-unit-pick" class="block text-[10px] font-bold text-text-muted mb-1.5">1. Department / unit</label>
                    <select data-themed-select data-search id="sa-unit-pick" class="<?= $field ?>" aria-label="Department / unit">
                        <option value="" data-placeholder>Department / unit...</option>
                        <?php foreach ($units as $u): ?><option value="<?= (int) $u['id'] ?>" data-level="<?= (int) $u['level'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="sa-position" class="block text-[10px] font-bold text-text-muted mb-1.5">2. Position</label>
                    <select data-themed-select data-search id="sa-position" class="<?= $field ?>" aria-label="Position" disabled><option value="" data-placeholder>Pick a department / unit first</option></select>
                </div>
            </div>
            <p id="sa-position-note" class="text-[10px] text-text-muted font-medium mt-2"></p>
            <div class="flex justify-end mt-3">
                <button type="button" id="sa-add" class="bg-accent hover:bg-accent-hover text-accent-text text-xs font-bold py-2.5 px-6 rounded-xl cursor-pointer">Add Position</button>
            </div>
        </div>

        <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Form type</div>
        <select data-themed-select id="sa-doc" class="<?= $field ?>" aria-label="Form type"></select>
        <p id="sa-doc-note" class="text-[10px] text-text-muted font-medium mt-2"></p>

        <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
            <button type="button" class="js-sa-close w-full sm:flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">Close</button>
            <button type="button" id="sa-save" class="w-full sm:flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-accent-text text-sm font-bold transition-all cursor-pointer">Save Form Type</button>
        </div>
    </div>
</div>

<script>
(function () {
    const PEOPLE = <?= json_encode((object) $people, $jsFlags) ?>;
    const UNITS = <?= json_encode(array_column($units, null, 'id'), $jsFlags) ?>;
    const RULES = <?= json_encode($rules, $jsFlags) ?>;
    const DOC_TYPES = <?= json_encode($docTypes, $jsFlags) ?>;
    const URL = { add: '<?= site_url('staff-accounts/assignment/add') ?>', end: '<?= site_url('staff-accounts/assignment/end') ?>', update: '<?= site_url('staff-accounts/update') ?>', page: '<?= site_url('staff-accounts') ?>' };
    const $ = id => document.getElementById(id);
    const esc = v => { const d = document.createElement('div'); d.textContent = v ?? ''; return d.innerHTML; };
    let current = null;   // user id

    function filter() {
        const q = ($('sa-search').value || '').toLowerCase().trim(), unit = $('sa-unit')?.value || '';
        let shown = 0;
        document.querySelectorAll('.sa-row').forEach(r => {
            const ok = (!q || r.dataset.search.includes(q)) && (!unit || JSON.parse(r.dataset.units || '[]').includes(unit));
            r.classList.toggle('hidden', !ok); if (ok) shown++;
        });
        $('sa-empty').classList.toggle('hidden', shown > 0);
    }
    $('sa-search').addEventListener('input', filter);
    $('sa-unit')?.addEventListener('change', filter);

    const allowedFor = (role, status) => {
        const r = RULES.roles[role] ?? null, s = RULES.statuses[status] ?? null;
        return DOC_TYPES.filter(t => (r === null || r.includes(t)) && (s === null || s.includes(t)));
    };
    // Department first, then position: in a unit below you its head position(s) and the positions under it; in your
    // own unit only the positions under it.
    function fillPositions() {
        const u = UNITS[$('sa-unit-pick').value], sel = $('sa-position');
        if (!u) {
            sel.innerHTML = '<option value="" data-placeholder>Pick a department / unit first</option>'; sel.disabled = true;
            $('sa-position-note').textContent = '';
        } else {
            const heads = u.positions.filter(x => x.head), rest = u.positions.filter(x => !x.head);
            const opt = x => `<option value="${x.id}">${esc(x.title)}</option>`;
            sel.innerHTML = '<option value="" data-placeholder>Position...</option>'
                + (heads.length ? `<optgroup label="Head of this unit (makes them a unit head)">${heads.map(opt).join('')}</optgroup>` : '')
                + (rest.length ? `<optgroup label="Members">${rest.map(opt).join('')}</optgroup>` : '');
            sel.disabled = !u.positions.length;
            $('sa-position-note').textContent = heads.length ? 'A head position makes them a unit head; any other makes them a member.' : 'Your own unit: member positions only.';
        }
        sel._themed?.refresh();
    }
    function fillDocs() {
        const p = PEOPLE[current], allowed = allowedFor(p.role, p.status), fits = allowed.includes(p.doc);
        const list = allowed.slice();
        if (p.doc && !fits) list.unshift(p.doc);
        $('sa-doc').innerHTML = (list.length ? list : ['']).map(t => `<option value="${esc(t)}" ${t === p.doc ? 'selected' : ''}>${esc(t || 'Not set')}${t && !allowed.includes(t) ? ' (current, not allowed)' : ''}</option>`).join('');
        $('sa-doc').disabled = !allowed.length || (fits && allowed.length < 2);
        $('sa-save').disabled = $('sa-doc').disabled;
        $('sa-save').classList.toggle('opacity-50', $('sa-save').disabled);
        $('sa-doc')._themed?.refresh();
        $('sa-doc-note').textContent = !allowed.length ? 'Nothing is allowed for this role and status: ask the administrator.'
            : (fits && allowed.length < 2 ? 'Set by their role and employment status.' : 'Changing it converts their papers still in target setting; you will see what changes first.');
    }
    function render() {
        const p = PEOPLE[current], only = p.total <= 1;
        $('sa-name').textContent = p.name;
        $('sa-sub').textContent = `${p.email} · ${p.role === 'Supervisor' ? 'Unit head' : 'Member'} · ${p.status || 'no status'}`;
        $('sa-flags').innerHTML = p.flags.map(f => `<span class="text-xs font-bold text-warning-500">&#9888; ${esc(f)}</span>`).join('');
        $('sa-assignments').innerHTML = p.assignments.map(a => `
            <div class="flex items-center justify-between gap-3 rounded-xl border border-surface-border px-4 py-2.5">
                <div class="flex flex-col min-w-0"><span class="text-sm font-bold text-text truncate">${esc(a.position)}${a.head ? ' <span class="text-[9px] font-black uppercase tracking-widest text-accent-ink">Head</span>' : ''}</span>
                <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">${esc(a.unit)}${a.since ? ' · since ' + esc(a.since) : ''}</span></div>
                <button type="button" data-end="${a.id}" ${only ? 'disabled title="Their only position: add the new one first, then end this one."' : ''} class="text-xs font-bold text-danger-500 hover:text-danger-600 px-3 py-1.5 rounded-lg bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">End</button>
            </div>`).join('')
            + (only ? '<p class="text-[10px] text-text-muted font-medium">Their only position cannot be ended here: to move them, add the new position first, then end this one.</p>' : '');
        fillDocs();
    }
    // After a change: take this person's fresh details and row from the page, without reloading; the modal stays open.
    async function refreshPerson(id) {
        const res = await fetch(URL.page, { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } });
        const fresh = new DOMParser().parseFromString(await res.text(), 'text/html');
        const line = Array.from(fresh.scripts).map(s => s.textContent).join('\n').split('\n').find(l => l.trim().startsWith('const PEOPLE = '));
        if (!line) throw new Error('no data');
        const people = JSON.parse(line.trim().replace(/^const PEOPLE = /, '').replace(/;$/, ''));
        const row = document.querySelector(`.sa-row[data-user-id="${id}"]`), next = fresh.querySelector(`.sa-row[data-user-id="${id}"]`);
        if (!people[id]) { delete PEOPLE[id]; row?.remove(); close(); filter(); return; }   // no longer in the branch
        PEOPLE[id] = people[id];
        if (row && next) {
            Array.from(row.attributes).forEach(a => row.removeAttribute(a.name));
            Array.from(next.attributes).forEach(a => row.setAttribute(a.name, a.value));
            row.innerHTML = next.innerHTML;
        }
        filter();
    }
    function post(url, fields, onConfirm) {
        const fd = new FormData();
        Object.entries(fields).forEach(([k, v]) => fd.append(k, v));
        apiPost(url, fd, {
            onSuccess: async d => {
                await window.appAlert(d.message || 'Saved.', { title: 'Accounts', variant: 'success' });
                try { await refreshPerson(current); if (PEOPLE[current]) { $('sa-unit-pick').value = ''; $('sa-unit-pick')._themed?.refresh(); fillPositions(); render(); } }
                catch (e) { window.location.reload(); }
            },
            onError: m => window.appAlert(m || 'Something went wrong.', { variant: 'danger' }),
            onDefault: async d => { if (d && d.status === 'confirm' && onConfirm && await window.appConfirm(d.message, { title: 'Confirm Change', confirmText: 'Save' })) onConfirm(); },
        });
    }
    function open(id) {
        if (!PEOPLE[id]) return;
        current = id;
        $('sa-unit-pick').value = ''; $('sa-unit-pick')._themed?.refresh(); fillPositions(); render();
        const m = $('modal-sa'); m.classList.remove('hidden'); m.classList.add('flex');
    }
    function close() { const m = $('modal-sa'); m.classList.add('hidden'); m.classList.remove('flex'); }
    $('sa-unit-pick').addEventListener('change', fillPositions);
    document.addEventListener('click', e => {
        const b = e.target.closest('.js-sa-manage'); if (b) open(+b.dataset.user);
        if (e.target.closest('.js-sa-close')) close();
    });
    $('sa-add').addEventListener('click', () => {
        if (!$('sa-unit-pick').value || !$('sa-position').value) return window.appAlert('Pick a department / unit and a position.', { variant: 'warning' });
        const fields = { user_id: current, unit_id: $('sa-unit-pick').value, position_id: $('sa-position').value };
        post(URL.add, fields, () => post(URL.add, Object.assign({}, fields, { confirm: 1 })));
    });
    $('sa-assignments').addEventListener('click', async e => {
        const b = e.target.closest('[data-end]');
        if (!b || b.disabled) return;
        if (!await window.appConfirm('End this position today? It stays in their history.', { title: 'End Position', confirmText: 'End It' })) return;
        post(URL.end, { plantilla_id: b.dataset.end });
    });
    $('sa-save').addEventListener('click', () => {
        const p = PEOPLE[current];
        if ($('sa-doc').value === p.doc) return window.appAlert('No changes.', { title: 'Accounts' });
        const fields = { plantilla_id: p.assignments[0].id, doc_type: $('sa-doc').value };
        post(URL.update, fields, () => post(URL.update, Object.assign({}, fields, { confirm: 1 })));
    });
})();
</script>
<?= $this->endSection() ?>

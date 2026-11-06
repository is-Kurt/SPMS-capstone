<?php
/**
 * User Directory > Manage (2026-11-07): a person's assignments (add / end, never deleted), employment
 * status and form type. Only for Employees and Supervisors. Built from data passed by
 * AccountManagement::index; every change is checked again on the server.
 */
$jsFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$people = [];
foreach ($users as $u) {
    $people[(int) $u['id']] = [
        'name' => trim($u['first_name'] . ' ' . $u['last_name']), 'email' => $u['email'],
        'role' => explode(', ', (string) ($u['role_name'] ?? ''))[0], 'doc' => strtoupper((string) ($u['doc_type'] ?? '')),
        'status' => $u['employment_status'] ?? '', 'flags' => $u['flags'] ?? [],
        'assignments' => array_map(fn($a) => ['id' => (int) $a['id'], 'unit' => $a['unit_name'], 'position' => $a['position_title'], 'head' => (bool) $a['is_head'], 'since' => $a['started_at']], $u['assignments'] ?? []),
    ];
}
$rules = ['roles' => [], 'statuses' => []];
foreach ($docRules['roles'] as $r) $rules['roles'][$r['name']] = $r['allowed'];
foreach ($docRules['statuses'] as $st) $rules['statuses'][$st['name']] = $st['allowed'];
$positionsJs = array_map(fn($p) => ['id' => (int) $p['id'], 'title' => $p['title'], 'level' => $p['level'], 'general' => $p['is_general'], 'root' => (bool) $p['is_general_root']], $positionTree);
$unitTreeRows = [];
foreach ($units as $un) $unitTreeRows[$un['parent_id'] ?: 0][] = $un;
$field = 'w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer';
?>
<div id="modal-manage-user" class="fixed inset-0 z-[200] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    <div class="relative w-full max-w-2xl rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all m-4">
        <h3 class="text-xl font-black text-text tracking-tight mb-1" id="mu-name"></h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-4" id="mu-sub"></p>
        <div id="mu-flags" class="flex flex-col gap-1 mb-4"></div>

        <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Current assignments</div>
        <div id="mu-assignments" class="flex flex-col gap-2 mb-4"></div>

        <div class="rounded-xl border border-surface-border p-4 mb-6">
            <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-3">Add assignment</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <select id="mu-unit" class="<?= $field ?>" aria-label="Department / unit">
                    <option value="">Department / unit...</option>
                    <?php
                        $render = function ($parent, $level) use (&$render, $unitTreeRows) {
                            foreach ($unitTreeRows[$parent] ?? [] as $un) {
                                echo '<option value="' . (int) $un['id'] . '">' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . esc($un['name']) . '</option>';
                                $render($un['id'], $level + 1);
                            }
                        };
                        $render(0, 0);
                    ?>
                </select>
                <select id="mu-position" class="<?= $field ?>" aria-label="Position"><option value="">Position...</option></select>
            </div>
            <div class="flex justify-end mt-3">
                <button type="button" id="mu-add" class="bg-accent hover:bg-accent-hover text-accent-text text-xs font-bold py-2.5 px-6 rounded-xl cursor-pointer">Add Assignment</button>
            </div>
        </div>

        <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Employment status &amp; form type</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <select id="mu-status" class="<?= $field ?>" aria-label="Employment status">
                <?php foreach ($employmentStatuses as $st): ?>
                    <option value="<?= esc($st['name']) ?>"><?= esc($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="mu-doc" class="<?= $field ?>" aria-label="Form type"></select>
        </div>
        <p id="mu-doc-note" class="text-[10px] text-text-muted font-medium mt-2"></p>

        <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
            <button type="button" class="btn-close-modal w-full sm:flex-1 px-6 py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">Close</button>
            <button type="button" id="mu-save" class="w-full sm:flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-hover text-accent-text text-sm font-bold transition-all cursor-pointer">Save Status &amp; Form Type</button>
        </div>
    </div>
</div>
<script>
(function () {
    const PEOPLE = <?= json_encode($people, $jsFlags) ?>;
    const RULES = <?= json_encode($rules, $jsFlags) ?>;
    const DOC_TYPES = <?= json_encode($docTypes, $jsFlags) ?>;
    const POSITIONS = <?= json_encode($positionsJs, $jsFlags) ?>;
    const FIT = <?= json_encode($unitFit, $jsFlags) ?>;
    const URL = { add: '<?= site_url('account/user/assignment/add') ?>', end: '<?= site_url('account/user/assignment/end') ?>', details: '<?= site_url('account/user/details') ?>' };
    const $ = id => document.getElementById(id);
    const esc = v => { const d = document.createElement('div'); d.textContent = v ?? ''; return d.innerHTML; };
    let current = null;

    const allowedFor = (role, status) => {
        const r = RULES.roles[role] ?? null, s = RULES.statuses[status] ?? null;
        return DOC_TYPES.filter(t => (r === null || r.includes(t)) && (s === null || s.includes(t)));
    };
    function fillDocs() {
        const p = PEOPLE[current], allowed = allowedFor(p.role, $('mu-status').value);
        const opts = allowed.map(t => `<option value="${t}" ${t === p.doc ? 'selected' : ''}>${t}</option>`);
        if (p.doc && !allowed.includes(p.doc)) opts.unshift(`<option value="${p.doc}" selected>${p.doc} (current, not allowed: keep for now)</option>`);
        if (!opts.length) opts.push('<option value="">Nothing allowed for this role and status</option>');
        $('mu-doc').innerHTML = opts.join('');
        $('mu-doc-note').textContent = 'Changing the form type converts their target papers still in target setting; you will see what changes before it happens.';
    }
    function fillPositions() {
        const fit = FIT[$('mu-unit').value] || { heads: [], members: [] };
        const head = POSITIONS.filter(p => fit.heads.includes(p.id));
        const mem = POSITIONS.filter(p => fit.members.includes(p.id) && !p.general);
        const gen = POSITIONS.filter(p => fit.members.includes(p.id) && p.general);
        const other = POSITIONS.filter(p => !p.root && !fit.heads.includes(p.id) && !fit.members.includes(p.id));
        const group = (label, list) => list.length ? `<optgroup label="${label}">${list.map(p => `<option value="${p.id}">${esc(p.title)}</option>`).join('')}</optgroup>` : '';
        $('mu-position').innerHTML = '<option value="">Position...</option>' + group('Head of this unit', head) + group('Members', mem) + group('General', gen) + group('Other positions', other);
    }
    function render() {
        const p = PEOPLE[current];
        $('mu-name').textContent = p.name;
        $('mu-sub').textContent = `${p.email} · ${p.role}`;
        $('mu-flags').innerHTML = p.flags.map(f => `<span class="text-xs font-bold text-warning-500">&#9888; ${esc(f)}</span>`).join('');
        $('mu-assignments').innerHTML = p.assignments.length ? p.assignments.map(a => `
            <div class="flex items-center justify-between gap-3 rounded-xl border border-surface-border px-4 py-2.5">
                <div class="flex flex-col min-w-0"><span class="text-sm font-bold text-text truncate">${esc(a.position)}${a.head ? ' <span class="text-[9px] font-black uppercase tracking-widest text-accent-ink">Head</span>' : ''}</span>
                <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate">${esc(a.unit)}${a.since ? ' · since ' + esc(a.since) : ''}</span></div>
                <button type="button" data-end="${a.id}" class="text-xs font-bold text-danger-500 hover:text-danger-600 px-3 py-1.5 rounded-lg bg-danger-50 hover:bg-danger-100 dark:bg-danger-500/10 cursor-pointer">End</button>
            </div>`).join('') : '<p class="text-sm font-bold text-text-muted">No current assignment.</p>';
        $('mu-status').value = p.status || $('mu-status').value;
        fillDocs();
    }
    function post(url, fields, done) {
        const fd = new FormData();
        Object.entries(Object.assign({ user_id: current }, fields)).forEach(([k, v]) => fd.append(k, v));
        apiPost(url, fd, {
            onSuccess: async (d) => { await window.appAlert(d.message || 'Saved.', { title: 'Saved', variant: 'success' }); window.location.reload(); },
            onError: (m) => window.appAlert(m || 'Something went wrong.', { variant: 'danger' }),
            onDefault: done || null,
        });
    }
    window.openManageUser = function (id) {
        if (!PEOPLE[id]) return;
        current = id;
        $('mu-unit').value = ''; fillPositions(); render();
        const m = $('modal-manage-user'); m.classList.remove('hidden'); m.classList.add('flex');
    };
    $('mu-unit').addEventListener('change', fillPositions);
    $('mu-status').addEventListener('change', fillDocs);
    $('mu-add').addEventListener('click', () => {
        if (!$('mu-unit').value || !$('mu-position').value) return window.appAlert('Pick a department / unit and a position.', { variant: 'warning' });
        post(URL.add, { unit_id: $('mu-unit').value, position_id: $('mu-position').value });
    });
    $('mu-assignments').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-end]');
        if (!b) return;
        if (!await window.appConfirm('End this assignment today? It stays in their history.', { title: 'End Assignment', confirmText: 'End It' })) return;
        post(URL.end, { plantilla_id: b.dataset.end });
    });
    $('mu-save').addEventListener('click', () => {
        const fields = { employment_status: $('mu-status').value, doc_type: $('mu-doc').value };
        post(URL.details, fields, async (d) => {
            if (d.status !== 'confirm') return;
            if (await window.appConfirm(d.message, { title: 'Change Form Type', confirmText: 'Change It' })) post(URL.details, Object.assign(fields, { confirm: 1 }));
        });
    });
    document.querySelectorAll('#modal-manage-user .btn-close-modal').forEach(b => b.addEventListener('click', () => {
        const m = $('modal-manage-user'); m.classList.add('hidden'); m.classList.remove('flex');
    }));
})();
</script>

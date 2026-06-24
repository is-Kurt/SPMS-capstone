<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-8 pb-20 h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
    
    <div class="shrink-0">
        <h1 class="text-3xl font-black tracking-tight text-text">User Management</h1>
        <p class="text-sm text-text-muted mt-1 font-medium italic">Manage directories and invite personnel to the system.</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="px-6 py-4 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 text-sm font-bold mb-2">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="px-6 py-4 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm font-bold mb-2">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="flex gap-6 border-b border-surface-border shrink-0 px-2">
        <button id="tab-btn-directory" class="tab-btn pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-all cursor-pointer" onclick="switchUserTab('directory')">
            User Directory
            <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors" id="directory-count"><?= count($users) ?></span>
        </button>
        <button id="tab-btn-create" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchUserTab('create')">
            Send Invitations
        </button>
        <button id="tab-btn-system" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer" onclick="switchUserTab('system')">
            System Data
        </button>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-[500px] overflow-hidden relative">
        
        <div id="tab-content-directory" class="tab-content flex flex-col absolute inset-0">
            
            <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0">
                <input type="text" id="filter-search" placeholder="Search users by name or email..." 
                       class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <select id="filter-role" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text cursor-pointer">
                            <option value="all">All Roles</option>
                            <option value="Admin">System Admin</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Dean">Dean</option>
                            <option value="Director">Director</option>
                            <option value="Head of Office">Head of Office</option>
                            <option value="Employee">Employee</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="relative">
                        <select id="filter-dept" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text cursor-pointer">
                            <option value="all">All Departments/Units</option>
                            <option value="Academics">Academics</option>
                            <option value="Administration">Administration</option>
                            <option value="Finance">Finance & Accounting</option>
                            <option value="HR">Human Resources</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="relative">
                        <select id="filter-status" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option value="1">Active Accounts</option>
                            <option value="0">Disabled Accounts</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto overflow-x-auto custom-scrollbar flex-1">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-20 bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border shadow-sm">
                        <tr>
                            <th class="px-6 py-4">Name / Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Department / Position</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border" id="user-table-body">
                        <?php foreach ($users as $u): ?>
                            <tr class="user-dir-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors <?= (isset($u['is_active']) && $u['is_active'] == 0) ? 'opacity-60' : '' ?>"
                                data-name="<?= strtolower(esc($u['first_name'] . ' ' . $u['last_name'])) ?>"
                                data-email="<?= strtolower(esc($u['email'])) ?>"
                                data-role="<?= esc($u['role_name'] ?? 'No Role') ?>"
                                data-dept="<?= esc($u['department'] ?? 'No Unit') ?>"
                                data-status="<?= $u['is_active'] ?? 1 ?>">
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-text truncate"><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                        <span class="text-[10px] font-bold text-text-muted tracking-widest truncate"><?= esc($u['email']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-blue-50 dark:bg-blue-500/10 text-blue-600 border border-blue-200 dark:border-blue-500/20 uppercase tracking-widest">
                                        <?= esc($u['role_name'] ?? 'No Role') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-text truncate"><?= esc($u['department'] ?? 'No Unit') ?></span>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position'] ?? 'No Position') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (isset($u['is_active']) && $u['is_active'] == 1): ?>
                                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Active</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <div class="flex justify-end gap-2">
                                            <?= form_open('account/toggle', ['class' => 'inline']) ?>
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg transition-colors cursor-pointer <?= $u['is_active'] == 1 ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' ?>">
                                                    <?= $u['is_active'] == 1 ? 'Disable' : 'Enable' ?>
                                                </button>
                                            <?= form_close() ?>

                                            <?= form_open('account', ['class' => 'inline', 'onsubmit' => "return confirm('Are you sure you want to permanently delete this user?');"]) ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer">
                                                    Delete
                                                </button>
                                            <?= form_close() ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr id="empty-filter-state" class="hidden">
                            <td colspan="5" class="px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                No users matched your specific search criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-content-create" class="tab-content hidden flex-col absolute inset-0 overflow-y-auto custom-scrollbar">
            <?= form_open('account/sendInvites', ['class' => 'p-8 flex flex-col gap-6 h-full']) ?>
                
                <div class="shrink-0">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Assign Role</label>
                    <div class="relative max-w-md">
                        <select name="role_id" required class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
                            <option value="" disabled selected>Select a role for this batch...</option>
                            <?php foreach($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Mass Email Invitations</label>
                    <p class="text-xs text-text-muted mb-4 font-medium">Paste a list of email addresses below. Separate emails with commas, semicolons, or newlines.</p>
                    
                    <textarea name="emails" required placeholder="john.doe@school.edu&#10;jane.smith@school.edu" 
                        class="w-full flex-1 min-h-[250px] bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-medium resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-surface-border shrink-0">
                    <button type="submit" class="flex items-center gap-2 bg-accent hover:bg-accent-hover text-white text-xs font-bold py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Queue Invitations
                    </button>
                </div>
            <?= form_close() ?>
        </div>

        <div id="tab-content-system" class="tab-content hidden flex-col absolute inset-0 overflow-y-auto custom-scrollbar bg-zinc-50/50 dark:bg-zinc-900/30">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 h-full">
                
                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">System Roles</h2>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border">
                            <?php foreach ($roles as $role): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <span class="text-sm font-bold text-text"><?= esc($role['name']) ?></span>
                                    <?= form_open('account/role/delete', ['onsubmit' => "return confirm('Delete this role?');"]) ?>
                                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= form_open('account/role/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30']) ?>
                        <div class="flex gap-2">
                            <input type="text" name="name" required placeholder="New Role Name" class="flex-1 bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text">
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">+</button>
                        </div>
                    <?= form_close() ?>
                </div>

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Job Positions</h2>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border">
                            <?php foreach ($positions as $pos): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-text"><?= esc($pos['title']) ?></span>
                                        <span class="text-[9px] font-black tracking-widest uppercase <?= $pos['is_teaching'] ? 'text-amber-500' : 'text-zinc-400' ?>"><?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?></span>
                                    </div>
                                    <?= form_open('account/position/delete', ['onsubmit' => "return confirm('Delete this position?');"]) ?>
                                        <input type="hidden" name="id" value="<?= $pos['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= form_open('account/position/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-2']) ?>
                        <input type="text" name="title" required placeholder="New Position Title" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-text-muted font-bold">
                                <input type="checkbox" name="is_teaching" value="1" class="rounded border-surface-border text-accent focus:ring-accent">
                                Is Teaching Staff?
                            </label>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Departments & Units</h2>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border">
                            <?php foreach ($units as $unit): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-text"><?= esc($unit['name']) ?></span>
                                        <?php if ($unit['parent_id']): ?>
                                            <?php 
                                                // Find parent name quickly
                                                $parentName = 'Top Level';
                                                foreach($units as $u) { if($u['id'] == $unit['parent_id']) { $parentName = $u['name']; break; } }
                                            ?>
                                            <span class="text-[9px] font-bold text-text-muted uppercase tracking-widest">Parent: <?= esc($parentName) ?></span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-bold text-blue-500 uppercase tracking-widest">Top Level Node</span>
                                        <?php endif; ?>
                                    </div>
                                    <?= form_open('account/unit/delete', ['onsubmit' => "return confirm('Delete this unit?');"]) ?>
                                        <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= form_open('account/unit/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-2']) ?>
                        <input type="text" name="name" required placeholder="New Unit Name" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex gap-2">
                            <select name="parent_id" class="flex-1 bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2 py-2 text-xs text-text outline-none cursor-pointer">
                                <option value="">No Parent (Top Level)</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        processEmailQueue();
    });

    function processEmailQueue() {
        const formData = new FormData();

        apiPost('/account/process-queue', formData, {
            onSuccess: (data) => {
                if (data.queue_state === 'working' && data.remaining > 0) {
                    console.log(`Sent a batch of emails. ${data.remaining} remaining...`);
                    setTimeout(processEmailQueue, 2000); 
                } else {
                    console.log('All emails have been sent successfully.');
                }
            },
            onError: (errorMessage) => {
                console.error('Error processing queue:', errorMessage);
            }
        });
    }

    // --- TAB SWITCHING LOGIC ---
    function switchUserTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'absolute', 'inset-0');
        });
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted');
        });

        document.getElementById('tab-content-' + tabId).classList.remove('hidden');
        document.getElementById('tab-content-' + tabId).classList.add('flex', 'absolute', 'inset-0');
        
        document.getElementById('tab-btn-' + tabId).classList.remove('border-transparent', 'text-text-muted');
        document.getElementById('tab-btn-' + tabId).classList.add('border-accent', 'text-accent');
    }

    // --- SEARCH AND FILTER LOGIC ---
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('filter-search');
        const roleFilter  = document.getElementById('filter-role');
        const deptFilter  = document.getElementById('filter-dept');
        const statusFilter = document.getElementById('filter-status');
        const rows        = document.querySelectorAll('.user-dir-row');
        const emptyState  = document.getElementById('empty-filter-state');
        const countBadge  = document.getElementById('directory-count');

        function filterUsers() {
            const query  = searchInput.value.toLowerCase();
            const role   = roleFilter.value;
            const dept   = deptFilter.value;
            const status = statusFilter.value;
            
            let visibleCount = 0;

            rows.forEach(row => {
                const rowName   = row.getAttribute('data-name');
                const rowEmail  = row.getAttribute('data-email');
                const rowRole   = row.getAttribute('data-role');
                const rowDept   = row.getAttribute('data-dept');
                const rowStatus = row.getAttribute('data-status');

                const matchesSearch = rowName.includes(query) || rowEmail.includes(query);
                const matchesRole   = (role === 'all') || (rowRole === role);
                const matchesDept   = (dept === 'all') || (rowDept === dept);
                const matchesStatus = (status === 'all') || (rowStatus === status);

                if (matchesSearch && matchesRole && matchesDept && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countBadge.textContent = visibleCount;
            emptyState.style.display = visibleCount === 0 ? 'table-row' : 'none';
        }

        searchInput.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);
        deptFilter.addEventListener('change', filterUsers);
        statusFilter.addEventListener('change', filterUsers);
    });
</script>

<?= $this->endSection() ?>
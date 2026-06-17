<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<?php $orgConfig = config('Organization'); ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-8 pb-20 h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
    
    <div class="shrink-0">
        <h1 class="text-3xl font-black tracking-tight text-text">User Management</h1>
        <p class="text-sm text-text-muted mt-1 font-medium italic">Manage system access, create accounts, and assign roles.</p>
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
            Create Account
        </button>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col flex-1 min-h-[500px] overflow-hidden relative">
        
        <div id="tab-content-directory" class="tab-content flex flex-col absolute inset-0">
            
            <div class="px-6 py-4 border-b border-surface-border bg-zinc-50/50 dark:bg-zinc-800/30 flex flex-col md:flex-row gap-4 shrink-0 items-center">
                
                <div class="flex-1 relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="filter-search" placeholder="Search users by name or email..." 
                           class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-10 pr-4 py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                </div>

                <div class="flex gap-4 w-full md:w-auto shrink-0">
                    <div class="relative">
                        <select id="filter-role" class="appearance-none bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer pr-10">
                            <option value="all">All Roles</option>
                            <option value="Admin">System Admin</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Campus Administrator">Campus Administrator</option>
                            <option value="Dean">Dean</option>
                            <option value="Director">Director</option>
                            <option value="Head of Office">Head of Office</option>
                            <option value="Employee">Employee</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>


                    <div class="relative">
                        <select id="filter-dept" class="appearance-none bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer pr-10">
                            <option value="all">All Departments</option>
                            <option value="Academics">Academics</option>
                            <option value="Administration">Administration</option>
                            <option value="Finance">Finance & Accounting</option>
                            <option value="HR">Human Resources (HR)</option>
                            <option value="ICT">Information & Comms Tech (ICT)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
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
                            <tr class="user-dir-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors <?= $u['is_active'] == 0 ? 'opacity-60' : '' ?>"
                                data-name="<?= strtolower(esc($u['first_name'] . ' ' . $u['last_name'])) ?>"
                                data-email="<?= strtolower(esc($u['email'])) ?>"
                                data-role="<?= esc($u['role']) ?>"
                                data-dept="<?= esc($u['department']) ?>">
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-text truncate"><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                        <span class="text-[10px] font-bold text-text-muted tracking-widest truncate"><?= esc($u['email']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black bg-blue-50 dark:bg-blue-500/10 text-blue-600 border border-blue-200 dark:border-blue-500/20 uppercase tracking-widest">
                                        <?= esc($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-text truncate"><?= esc($u['department']) ?></span>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($u['is_active'] == 1): ?>
                                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Active</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <?= form_open('account/toggle', ['class' => 'inline']) ?>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg transition-colors cursor-pointer <?= $u['is_active'] == 1 ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' ?>">
                                                <?= $u['is_active'] == 1 ? 'Disable' : 'Enable' ?>
                                            </button>
                                        <?= form_close() ?>
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
            <?= form_open('account/store', ['class' => 'p-8 flex flex-col gap-8']) ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">First Name</label>
                        <input type="text" name="first_name" value="<?= old('first_name') ?>" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('first_name') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Last Name</label>
                        <input type="text" name="last_name" value="<?= old('last_name') ?>" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('last_name') ?></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Email Address</label>
                        <input type="email" name="email" value="<?= old('email') ?>" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('email') ?></p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Temporary Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('password') ?></p>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-surface-border">
                        <h3 class="text-sm font-bold text-text mb-4">Organizational Placement</h3>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">System Role</label>
                        <div class="relative">
                            <select id="role-select" name="role" required class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer">
                                <option value="" disabled <?= empty(old('role')) ? 'selected' : '' ?>>Select Role</option>
                                <?php foreach ($orgConfig->roles as $role): ?>
                                    <option value="<?= esc($role) ?>" <?= old('role') === $role ? 'selected' : '' ?>>
                                        <?= esc($role === 'Admin' ? 'System Admin' : $role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('role') ?></p>
                    </div>

                    <div id="field-department">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Department</label>
                        <div class="relative">
                            <select name="department" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-4 pr-10 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer">
                                <option value="" disabled <?= empty(old('department')) ? 'selected' : '' ?>>Select Department</option>
                                <?php foreach ($orgConfig->departments as $department): ?>
                                    <option value="<?= esc($department) ?>" <?= old('department') === $department ? 'selected' : '' ?>></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('department') ?></p>
                    </div>

                    <div id="field-position">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Position / Title</label>
                        <input type="text" name="position" value="<?= old('position') ?>" placeholder="e.g. Instructor 1" 
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-wider pl-1"><?= validation_show_error('position') ?></p>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-surface-border mt-4">
                    <button type="submit" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        Create Account
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    // --- TAB SWITCHING LOGIC ---
    function switchUserTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'absolute', 'inset-0');
        });
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted');
            const badge = btn.querySelector('.tab-badge');
            if(badge) {
                badge.classList.remove('bg-accent/10', 'text-accent');
                badge.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
            }
        });

        const target = document.getElementById('tab-content-' + tabId);
        if (target) {
            target.classList.remove('hidden');
            target.classList.add('flex', 'absolute', 'inset-0');
        }

        const btnElement = document.getElementById('tab-btn-' + tabId);
        if (btnElement) {
            btnElement.classList.remove('border-transparent', 'text-text-muted');
            btnElement.classList.add('border-accent', 'text-accent');
            const activeBadge = btnElement.querySelector('.tab-badge');
            if(activeBadge) {
                activeBadge.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'text-text-muted');
                activeBadge.classList.add('bg-accent/10', 'text-accent');
            }
        }
    }

    <?php if (validation_errors() || old('first_name')): ?>
        switchUserTab('create');
    <?php endif; ?>


    // --- SEARCH AND FILTER LOGIC ---
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('filter-search');
        const roleFilter  = document.getElementById('filter-role');
        const deptFilter  = document.getElementById('filter-dept');
        const rows        = document.querySelectorAll('.user-dir-row');
        const emptyState  = document.getElementById('empty-filter-state');
        const countBadge  = document.getElementById('directory-count');

        function filterUsers() {
            const query = searchInput.value.toLowerCase();
            const role  = roleFilter.value;
            const dept  = deptFilter.value;
            
            let visibleCount = 0;

            rows.forEach(row => {
                const rowName  = row.getAttribute('data-name');
                const rowEmail = row.getAttribute('data-email');
                const rowRole  = row.getAttribute('data-role');
                const rowDept  = row.getAttribute('data-dept');

                const matchesSearch = rowName.includes(query) || rowEmail.includes(query);
                const matchesRole   = (role === 'all') || (rowRole === role);
                const matchesDept   = (dept === 'all') || (rowDept === dept);

                if (matchesSearch && matchesRole && matchesDept) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countBadge.textContent = visibleCount;
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        if (searchInput && roleFilter && deptFilter) {
            searchInput.addEventListener('input', filterUsers);
            roleFilter.addEventListener('change', filterUsers);
            deptFilter.addEventListener('change', filterUsers);
        }
    });

    // --- DYNAMIC ROLE FORM LOGIC ---
    document.addEventListener('DOMContentLoaded', () => {
        const roleSelect = document.getElementById('role-select');
        const deptField  = document.getElementById('field-department');
        const posField   = document.getElementById('field-position');
        
        if (!roleSelect || !deptField || !posField) return;

        const deptSelect = deptField.querySelector('select');
        const posInput   = posField.querySelector('input');

        // MAP OF ROLES TO THEIR REQUIREMENTS
        const roleConfig = {
            'Admin':                { dept: false, pos: false },
            'Vice President':       { dept: false, pos: false },
            'Campus Administrator': { dept: false, pos: false },
            'Dean':                 { dept: true,  pos: false },
            'Director':             { dept: true,  pos: true },
            'Head of Office':       { dept: true,  pos: true },
            'Employee':             { dept: true,  pos: true }
        };

        function toggleFields() {
            const role = roleSelect.value;
            if (!role || !roleConfig[role]) return;

            const config = roleConfig[role];

            if (config.dept) {
                deptField.style.display = 'block';
                deptSelect.setAttribute('required', 'required');
            } else {
                deptField.style.display = 'none';
                deptSelect.removeAttribute('required');
                deptSelect.value = ''; 
            }

            if (config.pos) {
                posField.style.display = 'block';
                posInput.setAttribute('required', 'required');
            } else {
                posField.style.display = 'none';
                posInput.removeAttribute('required');
                posInput.value = ''; 
            }
        }

        roleSelect.addEventListener('change', toggleFields);
        toggleFields(); // Run on load to set initial state
    });
</script>

<?= $this->endSection() ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-4 md:p-8 max-w-7xl mx-auto flex flex-col gap-6 md:gap-8 pb-20 lg:h-[calc(100vh-6rem)] lg:overflow-hidden">
    
    <div class="shrink-0">
        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-text">User Management</h1>
        <p class="text-xs md:text-sm text-text-muted mt-1 font-medium italic">Manage directories and invite personnel to the system.</p>
    </div>

    <div class="flex gap-6 border-b border-surface-border shrink-0 px-2 overflow-x-auto custom-scrollbar whitespace-nowrap">
        <button id="tab-btn-directory" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'directory' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('directory')">
            User Directory
            <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors" id="directory-count"><?= count($users) ?></span>
        </button>
        <button id="tab-btn-create" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'create' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('create')">
            Send Invitations
        </button>
        <button id="tab-btn-invitations" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'invitations' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('invitations')">
            Invitations
            <span class="ml-1.5 px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] tab-badge transition-colors" id="invitations-count"><?= count($invitations) ?></span>
        </button>
        <button id="tab-btn-system" class="tab-btn pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer <?= $activeTab === 'system' ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text hover:border-surface-border' ?>" onclick="switchUserTab('system')">
            System Data
        </button>
    </div>

    <div class="bg-surface lg:bg-surface/50 border-none lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm flex flex-col flex-1 lg:min-h-0 lg:overflow-hidden relative pb-10 lg:pb-0">
        
        <div id="tab-content-directory" class="tab-content <?= $activeTab === 'directory' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface">
            
            <div class="p-0 lg:p-4 mb-4 lg:mb-0 border-b-0 lg:border-b border-surface-border bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800/30 space-y-3 shrink-0">
                <input type="text" id="filter-search" placeholder="Search users by name or email..." 
                       class="w-full bg-surface lg:bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-3 lg:py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm lg:shadow-none">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <select id="filter-role" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
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
                        <select id="filter-dept" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
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
                        <select id="filter-status" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
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

            <div class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border shadow-sm" data-frozen-header>
                <table class="w-full text-left border-collapse table-fixed">
                    <colgroup>
                        <col style="width:28%">
                        <col style="width:12%">
                        <col style="width:22%">
                        <col style="width:12%">
                        <col style="width:26%">
                    </colgroup>
                    <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted">
                        <tr>
                            <th class="px-6 py-4">Name / Email</th>
                            <th class="px-6 py-4 min-w-[90px]">Role</th>
                            <th class="px-6 py-4">Department / Position</th>
                            <th class="px-6 py-4 min-w-[80px] text-center">Status</th>
                            <th class="px-6 py-4 min-w-[190px] text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 lg:bg-surface" data-frozen-body>
                <table class="w-full text-left border-collapse block lg:table lg:table-fixed">
                    <colgroup>
                        <col style="width:28%">
                        <col style="width:12%">
                        <col style="width:22%">
                        <col style="width:12%">
                        <col style="width:26%">
                    </colgroup>
                    <tbody class="block lg:table-row-group divide-y lg:divide-y-0 divide-transparent lg:divide-surface-border" id="user-table-body">
                        <?php foreach ($users as $u): ?>
                            <tr class="user-dir-row block lg:table-row bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors <?= (isset($u['is_active']) && $u['is_active'] == 0) ? 'opacity-60 lg:opacity-60' : '' ?>"
                                data-name="<?= strtolower(esc($u['first_name'] . ' ' . $u['last_name'])) ?>"
                                data-email="<?= strtolower(esc($u['email'])) ?>"
                                data-role="<?= esc($u['role_name'] ?? 'No Role') ?>"
                                data-dept="<?= esc($u['department'] ?? 'No Unit') ?>"
                                data-status="<?= $u['is_active'] ?? 1 ?>">
                                
                                <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                    <div class="flex justify-between items-start lg:items-center">
                                        <div class="flex flex-col min-w-0 pr-4">
                                            <span class="text-sm font-bold text-text truncate"><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                            <span class="text-[10px] font-bold text-text-muted tracking-widest truncate"><?= esc($u['email']) ?></span>
                                        </div>
                                        <div class="lg:hidden shrink-0 mt-0.5">
                                            <?php if (isset($u['is_active']) && $u['is_active'] == 1): ?>
                                                <span class="js-status-badge-mobile px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border bg-success-50 text-success-600 border-success-200">Active</span>
                                            <?php else: ?>
                                                <span class="js-status-badge-mobile px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border bg-danger-50 text-danger-600 border-danger-200">Disabled</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 border-t border-surface-border lg:border-none mt-2 lg:mt-0 lg:min-w-[90px]">
                                    <div class="flex flex-col gap-2 lg:block">
                                        <?php if ($u['id'] != session()->get('user_id')): ?>
                                            <?php $currentRoleId = explode(',', $u['role_id'] ?? '')[0] ?? null; ?>
                                            <?= form_open('account/update-role', ['class' => 'inline-block', 'data-ajax' => 'change-role']) ?>
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <div class="relative inline-block w-max">
                                                    <select name="role_id" onchange="this.form.requestSubmit()" data-previous="<?= esc($currentRoleId) ?>"
                                                            class="js-role-select appearance-none pl-3 pr-7 py-1.5 rounded-lg text-[10px] font-black bg-surface lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border uppercase tracking-widest text-text cursor-pointer focus:outline-none focus:border-accent">
                                                        <?php foreach ($roles as $role): ?>
                                                            <option value="<?= $role['id'] ?>" <?= $currentRoleId == $role['id'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-text-muted">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                    </div>
                                                </div>
                                            <?= form_close() ?>
                                        <?php else: ?>
                                            <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest lg:w-auto">
                                                <?= esc($u['role_name'] ?? 'No Role') ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="flex flex-col min-w-0 lg:hidden mt-1">
                                            <span class="text-xs font-bold text-text truncate"><?= esc($u['department'] ?? 'No Unit') ?></span>
                                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position'] ?? 'No Position') ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden lg:table-cell px-6 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-text truncate"><?= esc($u['department'] ?? 'No Unit') ?></span>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest truncate"><?= esc($u['position'] ?? 'No Position') ?></span>
                                    </div>
                                </td>

                                <td class="hidden lg:table-cell px-6 py-4 text-center lg:min-w-[80px]">
                                    <?php if (isset($u['is_active']) && $u['is_active'] == 1): ?>
                                        <span class="js-status-badge-desktop text-[10px] font-bold text-success-500 uppercase tracking-widest">Active</span>
                                    <?php else: ?>
                                        <span class="js-status-badge-desktop text-[10px] font-bold text-danger-500 uppercase tracking-widest">Disabled</span>
                                    <?php endif; ?>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right border-t border-surface-border lg:border-none mt-2 lg:mt-0 lg:min-w-[190px]">
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <div class="flex justify-end gap-2 lg:gap-2">
                                            <?= form_open('account/toggle', ['class' => 'inline flex-1 lg:flex-none', 'data-ajax' => 'toggle-status']) ?>
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="js-toggle-btn w-full lg:w-auto text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg border lg:border-transparent transition-colors cursor-pointer <?= $u['is_active'] == 1 ? 'border-warning-200 bg-warning-50 lg:bg-transparent text-warning-600 hover:bg-warning-100 lg:hover:bg-warning-50 dark:hover:bg-warning-500/10' : 'border-success-200 bg-success-50 lg:bg-transparent text-success-600 hover:bg-success-100 lg:hover:bg-success-50 dark:hover:bg-success-500/10' ?>">
                                                    <?= $u['is_active'] == 1 ? 'Disable' : 'Enable' ?>
                                                </button>
                                            <?= form_close() ?>

                                            <?= form_open('account', [
                                                'class' => 'inline flex-1 lg:flex-none',
                                                'data-ajax' => 'delete-user',
                                                'data-confirm' => 'Are you sure you want to permanently delete this user?',
                                                'data-confirm-title' => 'Delete User Account',
                                                'data-confirm-text' => 'Delete Permanently'
                                            ]) ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="w-full lg:w-auto text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg border border-danger-200 lg:border-transparent bg-danger-50 lg:bg-transparent text-danger-500 hover:bg-danger-100 lg:hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors cursor-pointer">
                                                    Delete
                                                </button>
                                            <?= form_close() ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr id="empty-filter-state" class="lg:table-row" style="display:none;">
                            <td colspan="5" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic bg-surface border border-surface-border rounded-xl lg:border-none lg:bg-transparent">
                                No users matched your specific search criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-content-create" class="tab-content <?= $activeTab === 'create' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar bg-surface lg:bg-transparent rounded-xl border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <?= form_open('account/sendInvites', ['class' => 'p-4 md:p-8 flex flex-col gap-6 h-full', 'data-ajax' => 'send-invites']) ?>
                
                <div class="shrink-0">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Assign Role</label>
                    <div class="relative w-full lg:max-w-md">
                        <select name="role_id" id="select-invite-role" required class="appearance-none w-full bg-zinc-50 lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent outline-none text-text cursor-pointer font-bold">
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
                    <p class="text-[10px] lg:text-xs text-text-muted mb-4 font-medium">Paste a list of email addresses below. Separate emails with commas, semicolons, or newlines.</p>
                    
                    <textarea name="emails" required placeholder="john.doe@school.edu&#10;jane.smith@school.edu" 
                        class="w-full flex-1 min-h-[250px] lg:min-h-[300px] bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-medium resize-none"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-surface-border shrink-0">
                    <button type="submit" class="w-full lg:w-auto flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-white text-xs font-bold py-3.5 lg:py-3 px-8 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Queue Invitations
                    </button>
                </div>
            <?= form_close() ?>
        </div>

        <div id="tab-content-invitations" class="tab-content <?= $activeTab === 'invitations' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface">

            <div class="p-0 lg:p-4 mb-4 lg:mb-0 border-b-0 lg:border-b border-surface-border bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800/30 space-y-3 shrink-0">
                <input type="text" id="invite-filter-search" placeholder="Search invitations by email..."
                       class="w-full bg-surface lg:bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-3 lg:py-2.5 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold shadow-sm lg:shadow-none">

                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <select id="invite-filter-status" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted (Account Created)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="relative flex-1">
                        <select id="invite-filter-expiry" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
                            <option value="all">Expired or Not</option>
                            <option value="expired">Expired</option>
                            <option value="not-expired">Not Expired</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <div class="relative flex-1">
                        <select id="invite-filter-role" class="appearance-none w-full bg-surface lg:bg-white dark:bg-zinc-900 dark:[color-scheme:dark] border border-surface-border rounded-xl px-3 py-3 lg:py-2 text-sm focus:border-accent outline-none text-text cursor-pointer shadow-sm lg:shadow-none">
                            <option value="all">All Roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role['name']) ?>"><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <button type="button" id="btn-delete-filtered" class="shrink-0 flex items-center justify-center gap-1.5 text-xs font-bold text-danger-500 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-500/10 px-3 py-2.5 lg:py-2 rounded-lg transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Filtered (<span id="invite-filtered-count">0</span>)
                    </button>
                </div>
            </div>

            <div class="hidden lg:block shrink-0 overflow-hidden bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border shadow-sm" data-frozen-header>
                <table class="w-full text-left border-collapse table-fixed">
                    <colgroup>
                        <col style="width:34%">
                        <col style="width:16%">
                        <col style="width:14%">
                        <col style="width:20%">
                        <col style="width:16%">
                    </colgroup>
                    <thead class="text-[10px] font-black uppercase tracking-widest text-text-muted">
                        <tr>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Invited Role</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Expires</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 p-4 lg:p-0 lg:bg-surface" data-frozen-body>
                <table class="w-full text-left border-collapse block lg:table lg:table-fixed">
                    <colgroup>
                        <col style="width:34%">
                        <col style="width:16%">
                        <col style="width:14%">
                        <col style="width:20%">
                        <col style="width:16%">
                    </colgroup>
                    <tbody class="block lg:table-row-group divide-y lg:divide-y-0 divide-transparent lg:divide-surface-border" id="invitations-table-body">
                        <?php foreach ($invitations as $inv):
                            $isExpired = $inv['status'] === 'pending' && strtotime($inv['expires_at']) < time();
                            if ($inv['status'] === 'accepted') {
                                $statusLabel = 'Accepted';
                                $statusClass = 'text-success-500';
                            } elseif ($isExpired) {
                                $statusLabel = 'Expired';
                                $statusClass = 'text-danger-500';
                            } else {
                                $statusLabel = 'Pending';
                                $statusClass = 'text-warning-500';
                            }
                        ?>
                            <tr class="invite-row block lg:table-row bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors"
                                data-id="<?= $inv['id'] ?>" data-email="<?= strtolower(esc($inv['email'])) ?>"
                                data-status="<?= esc($inv['status']) ?>" data-expired="<?= $isExpired ? '1' : '0' ?>" data-role="<?= esc($inv['role_name'] ?? '') ?>">
                                <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                    <div class="flex flex-col min-w-0 pr-4">
                                        <span class="text-sm font-bold text-text truncate"><?= esc($inv['email']) ?></span>
                                        <span class="text-[10px] font-bold text-text-muted tracking-widest truncate">Invited <?= date('M d, Y', strtotime($inv['created_at'])) ?></span>
                                    </div>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                                    <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest">
                                        <?= esc($inv['role_name'] ?? 'No Role') ?>
                                    </span>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 text-left lg:text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-widest <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4">
                                    <span class="text-xs font-bold text-text"><?= date('M d, Y - h:i A', strtotime($inv['expires_at'])) ?></span>
                                </td>

                                <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                                    <?= form_open('account/invite/delete', [
                                        'class' => 'inline',
                                        'data-ajax' => 'delete-invite',
                                        'data-confirm' => 'Delete this invitation? If it hasn\'t been accepted yet, the invite link will stop working.',
                                        'data-confirm-title' => 'Delete Invitation'
                                    ]) ?>
                                        <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                        <button type="submit" class="w-full lg:w-auto text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg border border-danger-200 lg:border-transparent bg-danger-50 lg:bg-transparent text-danger-500 hover:bg-danger-100 lg:hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors cursor-pointer">
                                            Delete
                                        </button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($invitations)): ?>
                            <tr class="block lg:table-row" id="invitations-empty-state">
                                <td colspan="5" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic bg-surface border border-surface-border rounded-xl lg:border-none lg:bg-transparent">
                                    No invitations have been sent yet.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr id="invitations-filter-empty-state" class="lg:table-row" style="display:none;">
                            <td colspan="5" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic bg-surface border border-surface-border rounded-xl lg:border-none lg:bg-transparent">
                                No invitations matched your search/filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-content-system" class="tab-content <?= $activeTab === 'system' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 overflow-y-auto custom-scrollbar lg:bg-zinc-50/50 lg:dark:bg-zinc-900/30">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-0 lg:p-6 lg:h-full">
                
                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col lg:h-full lg:overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">System Roles</h2>
                    </div>
                    <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border" id="roles-list">
                            <?php foreach ($roles as $role): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <span class="text-sm font-bold text-text"><?= esc($role['name']) ?></span>
                                    <?= form_open('account/role/delete', ['data-ajax' => 'delete-role', 'data-confirm' => 'Delete this role?', 'data-confirm-title' => 'Delete Role']) ?>
                                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                        <!-- Trash icon (icon-only button): deletes this role. Only shows on hover/tap; fails server-side if any user still holds it -->
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <!-- No "add role" form here on purpose: role names are hardcoded into access-control
                         checks throughout the app, so they're managed in the backend, not this UI. -->
                </div>

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col lg:h-full lg:overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Job Positions</h2>
                    </div>
                    <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border" id="positions-list">
                            <?php foreach ($positions as $pos): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-text"><?= esc($pos['title']) ?></span>
                                        <span class="text-[9px] font-black tracking-widest uppercase <?= $pos['is_teaching'] ? 'text-warning-500' : 'text-zinc-400' ?>"><?= $pos['is_teaching'] ? 'Teaching' : 'Non-Teaching' ?></span>
                                    </div>
                                    <?= form_open('account/position/delete', ['data-ajax' => 'delete-position', 'data-confirm' => 'Delete this position? Staff currently assigned to it will keep their account but lose that designation.', 'data-confirm-title' => 'Delete Position']) ?>
                                        <input type="hidden" name="id" value="<?= $pos['id'] ?>">
                                        <!-- Trash icon (icon-only button): deletes this position. Any staff assigned to it get their position un-set, not blocked -->
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= form_open('account/position/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0', 'data-ajax' => 'add-position']) ?>
                        <input type="text" name="title" required placeholder="New Position Title" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2.5 lg:py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-text-muted font-bold">
                                <input type="checkbox" name="is_teaching" value="1" class="checkbox">
                                Is Teaching Staff?
                            </label>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2.5 lg:py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>

                <div class="bg-surface border border-surface-border rounded-2xl shadow-sm flex flex-col lg:h-full lg:overflow-hidden">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 shrink-0">
                        <h2 class="text-sm font-black text-text tracking-tight uppercase">Departments & Units</h2>
                    </div>
                    <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-2">
                        <ul class="divide-y divide-surface-border" id="units-list">
                            <?php foreach ($units as $unit): ?>
                                <li class="flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group">
                                    <div class="flex flex-col pr-2">
                                        <span class="text-sm font-bold text-text"><?= esc($unit['name']) ?></span>
                                        <?php if ($unit['parent_id']): ?>
                                            <?php
                                                $parentName = 'Top Level';
                                                foreach($units as $u) { if($u['id'] == $unit['parent_id']) { $parentName = $u['name']; break; } }
                                            ?>
                                            <span class="text-[9px] font-bold text-text-muted uppercase tracking-widest mt-0.5 leading-tight">Parent: <?= esc($parentName) ?></span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-bold text-info-500 uppercase tracking-widest mt-0.5">Top Level Node</span>
                                        <?php endif; ?>
                                    </div>
                                    <?= form_open('account/unit/delete', ['data-ajax' => 'delete-unit', 'data-confirm' => 'Delete this unit? Staff currently assigned to it will keep their account but lose that unit. Sub-units under it will be deleted too.', 'data-confirm-title' => 'Delete Unit']) ?>
                                        <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                        <!-- Trash icon (icon-only button): deletes this unit. Any staff assigned to it get their unit un-set, not blocked. Sub-units are cascade-deleted -->
                                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    <?= form_close() ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= form_open('account/unit/add', ['class' => 'p-4 border-t border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0', 'data-ajax' => 'add-unit']) ?>
                        <input type="text" name="name" required placeholder="New Unit Name" class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-3 py-2.5 lg:py-2 text-xs focus:border-accent outline-none text-text">
                        <div class="flex gap-2">
                            <select name="parent_id" id="select-unit-parent" class="flex-1 bg-white dark:bg-zinc-900 border border-surface-border rounded-lg px-2 py-2.5 lg:py-2 text-xs text-text outline-none cursor-pointer">
                                <option value="">No Parent (Top Level)</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-accent hover:bg-accent-hover text-white px-4 py-2.5 lg:py-2 rounded-lg text-xs font-bold transition-colors">Add</button>
                        </div>
                    <?= form_close() ?>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    // ==========================================
    // AJAX FORM HANDLING (no more full-page reloads for accounts actions)
    // Registered synchronously (not inside DOMContentLoaded) so this listener
    // attaches before confirmModal.js's global data-confirm auto-submit
    // listener does - stopImmediatePropagation() below then keeps that older
    // listener from ever seeing these same submits.
    // ==========================================
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        const action = form.dataset.ajax;
        if (!action) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        if (form.dataset.confirm) {
            const ok = await window.appConfirm(form.dataset.confirm, {
                title: form.dataset.confirmTitle || undefined,
                confirmText: form.dataset.confirmText || 'Delete'
            });
            if (!ok) return;
        }

        if (action === 'change-role') {
            const select = form.querySelector('.js-role-select');
            const newLabel = select.options[select.selectedIndex].text;
            const ok = await window.appConfirm(`Change this user's role to "${newLabel}"? This updates their system permissions immediately.`, {
                title: 'Change Role',
                confirmText: 'Change Role',
                variant: 'warning'
            });
            if (!ok) {
                select.value = select.dataset.previous;
                return;
            }
        }

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        apiPost(form.getAttribute('action'), formData, {
            onSuccess: (data) => {
                handleAccountsAjaxSuccess(action, form, data);
                if (submitBtn) submitBtn.disabled = false;
            },
            onError: (errMsg) => {
                if (action === 'change-role') {
                    const select = form.querySelector('.js-role-select');
                    select.value = select.dataset.previous;
                }
                window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function handleAccountsAjaxSuccess(action, form, data) {
        switch (action) {
            case 'toggle-status': {
                const row = form.closest('.user-dir-row');
                const isActive = data.is_active == 1;
                row.dataset.status = isActive ? '1' : '0';
                row.classList.toggle('opacity-60', !isActive);

                row.querySelectorAll('.js-status-badge-desktop').forEach(el => {
                    el.textContent = isActive ? 'Active' : 'Disabled';
                    el.className = `js-status-badge-desktop text-[10px] font-bold uppercase tracking-widest ${isActive ? 'text-success-500' : 'text-danger-500'}`;
                });
                row.querySelectorAll('.js-status-badge-mobile').forEach(el => {
                    el.textContent = isActive ? 'Active' : 'Disabled';
                    el.className = `js-status-badge-mobile px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border ${isActive ? 'bg-success-50 text-success-600 border-success-200' : 'bg-danger-50 text-danger-600 border-danger-200'}`;
                });

                const btn = form.querySelector('.js-toggle-btn');
                btn.textContent = isActive ? 'Disable' : 'Enable';
                btn.className = `js-toggle-btn w-full lg:w-auto text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg border lg:border-transparent transition-colors cursor-pointer ${isActive ? 'border-warning-200 bg-warning-50 lg:bg-transparent text-warning-600 hover:bg-warning-100 lg:hover:bg-warning-50 dark:hover:bg-warning-500/10' : 'border-success-200 bg-success-50 lg:bg-transparent text-success-600 hover:bg-success-100 lg:hover:bg-success-50 dark:hover:bg-success-500/10'}`;
                break;
            }

            case 'delete-user': {
                form.closest('.user-dir-row').remove();
                document.getElementById('directory-count').textContent = document.querySelectorAll('.user-dir-row').length;
                break;
            }

            case 'change-role': {
                form.closest('.user-dir-row').dataset.role = data.role_name;
                const select = form.querySelector('.js-role-select');
                select.dataset.previous = select.value;
                window.appAlert(`Role updated to "${data.role_name}".`, { title: 'Role Updated', variant: 'success' });
                break;
            }

            case 'delete-role':
            case 'delete-position':
            case 'delete-unit': {
                form.closest('li').remove();
                break;
            }

            case 'add-position': {
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group';
                const isTeaching = data.item.is_teaching == 1;
                li.innerHTML = `
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-text">${escapeHtml(data.item.title)}</span>
                        <span class="text-[9px] font-black tracking-widest uppercase ${isTeaching ? 'text-warning-500' : 'text-zinc-400'}">${isTeaching ? 'Teaching' : 'Non-Teaching'}</span>
                    </div>
                    <form action="${form.getAttribute('action').replace('/add', '/delete')}" data-ajax="delete-position" data-confirm="Delete this position? Staff currently assigned to it will keep their account but lose that designation." data-confirm-title="Delete Position">
                        <input type="hidden" name="id" value="${data.item.id}">
                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                `;
                document.getElementById('positions-list').appendChild(li);
                form.reset();
                break;
            }

            case 'add-unit': {
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 rounded-lg transition-colors group';
                const parentLabel = data.item.parent_name
                    ? `<span class="text-[9px] font-bold text-text-muted uppercase tracking-widest mt-0.5 leading-tight">Parent: ${escapeHtml(data.item.parent_name)}</span>`
                    : `<span class="text-[9px] font-bold text-info-500 uppercase tracking-widest mt-0.5">Top Level Node</span>`;
                li.innerHTML = `
                    <div class="flex flex-col pr-2">
                        <span class="text-sm font-bold text-text">${escapeHtml(data.item.name)}</span>
                        ${parentLabel}
                    </div>
                    <form action="${form.getAttribute('action').replace('/add', '/delete')}" data-ajax="delete-unit" data-confirm="Delete this unit? Staff currently assigned to it will keep their account but lose that unit. Sub-units under it will be deleted too." data-confirm-title="Delete Unit">
                        <input type="hidden" name="id" value="${data.item.id}">
                        <button type="submit" class="text-danger-400 hover:text-danger-600 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                `;
                document.getElementById('units-list').appendChild(li);

                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.textContent = data.item.name;
                document.getElementById('select-unit-parent').appendChild(opt);

                form.reset();
                break;
            }

            case 'send-invites': {
                form.reset();

                const emptyState = document.getElementById('invitations-empty-state');
                if (emptyState) emptyState.remove();

                const tbody = document.getElementById('invitations-table-body');
                (data.invitations || []).forEach((inv) => {
                    const tr = document.createElement('tr');
                    tr.className = 'invite-row block lg:table-row bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors';
                    tr.dataset.id = inv.id;
                    tr.dataset.email = inv.email.toLowerCase();
                    tr.dataset.status = 'pending';
                    tr.dataset.expired = '0';
                    tr.dataset.role = inv.role_name;
                    tr.innerHTML = `
                        <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                            <div class="flex flex-col min-w-0 pr-4">
                                <span class="text-sm font-bold text-text truncate">${escapeHtml(inv.email)}</span>
                                <span class="text-[10px] font-bold text-text-muted tracking-widest truncate">Invited ${escapeHtml(inv.created_display)}</span>
                            </div>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                            <span class="w-max px-3 py-1 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest">
                                ${escapeHtml(inv.role_name)}
                            </span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4 text-left lg:text-center">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-warning-500">Pending</span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 py-2 lg:py-4">
                            <span class="text-xs font-bold text-text">${escapeHtml(inv.expires_display)}</span>
                        </td>
                        <td class="block lg:table-cell px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right border-t border-surface-border lg:border-none mt-2 lg:mt-0">
                            <form action="/account/invite/delete" data-ajax="delete-invite" data-confirm="Delete this invitation? If it hasn't been accepted yet, the invite link will stop working." data-confirm-title="Delete Invitation">
                                <input type="hidden" name="id" value="${inv.id}">
                                <button type="submit" class="w-full lg:w-auto text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg border border-danger-200 lg:border-transparent bg-danger-50 lg:bg-transparent text-danger-500 hover:bg-danger-100 lg:hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </td>
                    `;
                    tbody.prepend(tr);
                });

                filterInvitations();

                window.appAlert(data.message, { title: 'Invitations Sent', variant: 'info' });
                break;
            }

            case 'delete-invite': {
                form.closest('.invite-row').remove();
                filterInvitations();
                break;
            }
        }
    }

    // Note: the email queue poller used to live here (processEmailQueue()), but it's
    // now handled globally by header.js's processBackgroundEmails() on every
    // authenticated page, not just this one - removed to stop this page from
    // double-polling /account/process-queue alongside the global poller.

    // --- TAB SWITCHING LOGIC (Updated to toggle lg:absolute) ---
    // Reflect the active tab in the URL (/accounts/<tab>, or bare /accounts for the
    // default directory tab) so a refresh lands back on the same tab instead of
    // always resetting to User Directory.
    const ACCOUNTS_BASE_URL = "<?= site_url('accounts') ?>";

    function switchUserTab(tabId, pushUrl = true) {
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('flex', 'lg:absolute', 'lg:inset-0');
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-accent', 'text-accent');
            btn.classList.add('border-transparent', 'text-text-muted');
        });

        document.getElementById('tab-content-' + tabId).classList.remove('hidden');
        document.getElementById('tab-content-' + tabId).classList.add('flex', 'lg:absolute', 'lg:inset-0');

        document.getElementById('tab-btn-' + tabId).classList.remove('border-transparent', 'text-text-muted');
        document.getElementById('tab-btn-' + tabId).classList.add('border-accent', 'text-accent');

        if (pushUrl) {
            const url = tabId === 'directory' ? ACCOUNTS_BASE_URL : ACCOUNTS_BASE_URL + '/' + tabId;
            history.pushState({ tab: tabId }, '', url);
        }
    }

    // The initial page load's history entry has no pushState-set state yet - give
    // it one so navigating back to it (after switching tabs) restores the right tab.
    history.replaceState({ tab: '<?= $activeTab ?>' }, '', window.location.href);

    window.addEventListener('popstate', (e) => {
        switchUserTab((e.state && e.state.tab) || 'directory', false);
    });

    // --- SEARCH AND FILTER LOGIC (Updated to handle mobile card displays) ---
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
                    // Inline style, not the "hidden" class: these rows also carry
                    // lg:table-row, and at lg+ widths that responsive utility wins the
                    // CSS cascade over a same-specificity "hidden" class regardless of
                    // class order in the HTML - inline style always takes precedence.
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countBadge.textContent = visibleCount;
            emptyState.style.display = visibleCount === 0 ? '' : 'none';
        }

        searchInput.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);
        deptFilter.addEventListener('change', filterUsers);
        statusFilter.addEventListener('change', filterUsers);
    });

    // --- INVITATIONS TAB: SEARCH, STATUS/EXPIRY/ROLE FILTERS, AND "DELETE FILTERED" ---
    // These filters combine (AND, not OR): e.g. status=pending + expiry=expired
    // shows only pending invites that are also past their expires_at.
    function filterInvitations() {
        const searchInput = document.getElementById('invite-filter-search');
        const statusFilter = document.getElementById('invite-filter-status');
        const expiryFilter = document.getElementById('invite-filter-expiry');
        const roleFilter = document.getElementById('invite-filter-role');
        if (!searchInput || !statusFilter || !expiryFilter || !roleFilter) return;

        const query = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const expiry = expiryFilter.value;
        const role = roleFilter.value;
        const rows = document.querySelectorAll('.invite-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const matchesSearch = row.dataset.email.includes(query);
            const matchesStatus = status === 'all' || row.dataset.status === status;
            const matchesExpiry = expiry === 'all' || (expiry === 'expired') === (row.dataset.expired === '1');
            const matchesRole = role === 'all' || row.dataset.role === role;

            if (matchesSearch && matchesStatus && matchesExpiry && matchesRole) {
                // Inline style, not the "hidden" class: these rows also carry
                // lg:table-row, and at lg+ widths that responsive utility wins the
                // CSS cascade over a same-specificity "hidden" class regardless of
                // class order in the HTML - inline style always takes precedence.
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('invitations-count');
        if (countBadge) countBadge.textContent = visibleCount;

        const filteredCountLabel = document.getElementById('invite-filtered-count');
        if (filteredCountLabel) filteredCountLabel.textContent = visibleCount;

        const filterEmptyState = document.getElementById('invitations-filter-empty-state');
        if (filterEmptyState) {
            filterEmptyState.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const inviteSearch = document.getElementById('invite-filter-search');
        const inviteStatus = document.getElementById('invite-filter-status');
        const inviteExpiry = document.getElementById('invite-filter-expiry');
        const inviteRole = document.getElementById('invite-filter-role');
        const btnDeleteFiltered = document.getElementById('btn-delete-filtered');

        if (inviteSearch) inviteSearch.addEventListener('input', filterInvitations);
        if (inviteStatus) inviteStatus.addEventListener('change', filterInvitations);
        if (inviteExpiry) inviteExpiry.addEventListener('change', filterInvitations);
        if (inviteRole) inviteRole.addEventListener('change', filterInvitations);

        if (btnDeleteFiltered) {
            btnDeleteFiltered.addEventListener('click', async () => {
                const visibleRows = Array.from(document.querySelectorAll('.invite-row')).filter(row => row.style.display !== 'none');
                if (visibleRows.length === 0) {
                    window.appAlert('No invitations match the current filter.', { variant: 'warning' });
                    return;
                }

                const ok = await window.appConfirm(
                    `Delete these ${visibleRows.length} invitation(s)? Any that haven't been accepted yet will stop working.`,
                    { title: 'Delete Filtered Invitations', confirmText: 'Delete All' }
                );
                if (!ok) return;

                btnDeleteFiltered.disabled = true;

                const formData = new FormData();
                visibleRows.forEach(row => formData.append('ids[]', row.dataset.id));

                apiPost('/account/invite/delete-bulk', formData, {
                    onSuccess: () => {
                        visibleRows.forEach(row => row.remove());
                        filterInvitations();
                        window.appAlert('Deleted successfully.', { title: 'Invitations Deleted', variant: 'info' });
                        btnDeleteFiltered.disabled = false;
                    },
                    onError: (errMsg) => {
                        window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                        btnDeleteFiltered.disabled = false;
                    }
                });
            });
        }

        filterInvitations();
    });
</script>

<?= $this->endSection() ?>
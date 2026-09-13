<div id="tab-content-invitations" class="tab-content <?= $activeTab === 'invitations' ? 'flex' : 'hidden' ?> flex-col lg:flex-row lg:absolute lg:inset-0 bg-transparent lg:bg-surface">
    
    <!-- MOBILE FILTER OVERLAY -->
    <div id="mobile-invite-filter-overlay" class="fixed inset-0 z-[115] bg-black/50 hidden lg:hidden transition-opacity opacity-0" aria-hidden="true"></div>

    <!-- SIDEBAR FILTERS (Left) -->
    <div id="invitations-sidebar" class="fixed inset-y-0 left-0 z-[120] w-72 bg-surface lg:bg-zinc-50 lg:dark:bg-zinc-800/30 border-r border-surface-border shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 lg:static shrink-0 flex flex-col h-full">
        <div class="px-5 py-3 border-b border-surface-border flex justify-between items-center shrink-0 bg-transparent lg:bg-zinc-50 lg:dark:bg-zinc-800">
            <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest">Filters</h2>
            <button type="button" id="close-mobile-invite-filters" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-3.5 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3">
            <!-- Search -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <input type="text" id="invite-filter-search" placeholder="Search by email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-xs focus:border-accent outline-none text-text font-bold shadow-sm">
            </div>

            <!-- Roles Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Roles</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <?php foreach ($roles as $role): ?>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                            <input type="checkbox" name="invite_filter_role[]" value="<?= esc($role['name']) ?>" class="checkbox invite-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                            <?= esc($role['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="shrink-0 pb-4 border-b border-surface-border/50">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Status</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="invite_filter_status[]" value="pending" class="checkbox invite-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Pending
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="invite_filter_status[]" value="accepted" class="checkbox invite-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Accepted
                    </label>
                </div>
            </div>

            <!-- Expiry Filter -->
            <div class="shrink-0 pb-2">
                <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-3 group" onclick="
                    this.nextElementSibling.classList.toggle('hidden');
                    this.querySelector('svg').classList.toggle('-rotate-180');
                ">
                    <span>Expiry</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="invite_filter_expired[]" value="valid" class="checkbox invite-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Active / Valid
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                        <input type="checkbox" name="invite_filter_expired[]" value="expired" class="checkbox invite-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer">
                        Expired
                    </label>
                </div>
            </div>
        </div>
        <div class="p-3 border-t border-surface-border shrink-0 bg-surface lg:bg-transparent flex flex-col gap-2">
            <button type="button" id="clear-invite-filters" onclick="clearAllInviteFilters()" class="w-full py-1.5 text-xs font-bold text-text-muted hover:text-text border border-surface-border rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                Clear All Filters
            </button>
            <button type="button" id="delete-filtered-invites" class="w-full py-1.5 text-xs font-bold text-danger-500 hover:text-danger-600 border border-danger-200 dark:border-danger-500/20 bg-danger-50/50 dark:bg-danger-500/5 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-lg transition-colors cursor-pointer">
                Delete Filtered Invites
            </button>
        </div>
    </div>

    <!-- MAIN CONTENT (Right) -->
    <div class="flex-1 flex flex-col h-full min-w-0 bg-transparent lg:bg-surface relative">
        <!-- Mobile Filter Toggle Bar -->
        <div class="lg:hidden p-4 mb-2 bg-surface border-b border-surface-border shrink-0 flex items-center justify-between">
            <span class="text-xs font-bold text-text">Invitations <span class="text-text-muted font-normal">(<span id="mobile-invitations-count"><?= count($invitations) ?></span> invites)</span></span>
            <button type="button" id="open-mobile-invite-filters" class="flex items-center gap-2 text-xs font-bold text-accent bg-accent/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filters
            </button>
        </div>

        <div class="flex-1 overflow-x-auto min-h-0 overflow-y-hidden">
            <table class="w-full text-left border-collapse block lg:table lg:table-fixed">
                <colgroup>
                    <col style="width:34%">
                    <col style="width:16%">
                    <col style="width:14%">
                    <col style="width:20%">
                    <col style="width:16%">
                </colgroup>
                <thead class="hidden lg:table-header-group bg-zinc-50 dark:bg-zinc-800 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted">
                    <tr>
                        <th class="px-6 py-2.5">Email</th>
                        <th class="px-6 py-2.5">Invited Role</th>
                        <th class="px-6 py-2.5 text-center">Status</th>
                        <th class="px-6 py-2.5">Expires</th>
                        <th class="px-6 py-2.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="block lg:table-row-group divide-y divide-surface-border" id="invitations-table-body">
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
                        <tr class="invite-row block lg:table-row bg-surface p-4 lg:p-0 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors"
                            data-id="<?= $inv['id'] ?>" data-email="<?= strtolower(esc($inv['email'])) ?>"
                            data-status="<?= esc($inv['status']) ?>" data-expired="<?= $isExpired ? '1' : '0' ?>" data-role="<?= esc($inv['role_name'] ?? '') ?>">
                            <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                <div class="flex flex-col min-w-0 pr-4">
                                    <span class="text-sm font-bold text-text truncate"><?= esc($inv['email']) ?></span>
                                    <span class="text-[10px] font-bold text-text-muted tracking-widest truncate">Invited <?= date('M d, Y', strtotime($inv['created_at'])) ?></span>
                                </div>
                            </td>
                            
                            <td class="block lg:table-cell px-0 lg:px-6 py-1.5 lg:py-4">
                                <div class="flex justify-between items-center lg:block">
                                    <span class="w-max px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-info-50 dark:bg-info-500/10 text-info-600 border border-info-200 dark:border-info-500/20 uppercase tracking-widest">
                                        <?= esc($inv['role_name'] ?? 'No Role') ?>
                                    </span>
                                    <span class="lg:hidden px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border bg-zinc-50 dark:bg-zinc-800/50 border-surface-border <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </div>
                            </td>

                            <td class="hidden lg:table-cell px-0 lg:px-6 lg:py-4 text-left lg:text-center">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest border bg-zinc-50 dark:bg-zinc-800/50 border-surface-border <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>

                            <td class="block lg:table-cell px-0 lg:px-6 py-1.5 lg:py-4">
                                <div class="flex items-center gap-2 text-xs font-bold text-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="<?= $isExpired ? 'text-danger-500' : '' ?>">
                                        <?= date('M d, Y g:i A', strtotime($inv['expires_at'])) ?>
                                    </span>
                                </div>
                            </td>

                            <td class="block lg:table-cell px-0 lg:px-6 pt-2 pb-0 lg:py-4 text-right">
                                <div class="flex items-center justify-between lg:justify-end gap-2 w-full">
                                    <?php if ($inv['status'] !== 'accepted'): ?>
                                        <?= form_open('account/invite/resend', ['class' => 'flex-1 lg:flex-none inline-block', 'data-ajax' => 'resend-invitation']) ?>
                                            <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                            <button type="submit" class="w-full px-3 py-2 lg:py-1 rounded-lg text-xs font-bold text-accent bg-accent/10 lg:bg-transparent hover:bg-accent/20 lg:hover:bg-accent/10 border border-transparent transition-colors cursor-pointer text-center" title="Resend Email">Resend</button>
                                        <?= form_close() ?>
                                        
                                        <?= form_open('account/invite/delete', ['class' => 'flex-1 lg:flex-none inline-block', 'data-ajax' => 'delete-invitation', 'data-confirm' => 'Revoke this invitation? The link will immediately stop working.', 'data-confirm-title' => 'Revoke Invitation']) ?>
                                            <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                            <button type="submit" class="w-full px-3 py-2 lg:py-1 rounded-lg text-xs font-bold text-danger-500 bg-danger-50 dark:bg-danger-500/10 lg:bg-transparent dark:lg:bg-transparent hover:bg-danger-100 dark:hover:bg-danger-500/20 border border-transparent transition-colors cursor-pointer text-center" title="Revoke Invitation">Revoke</button>
                                        <?= form_close() ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div id="invitations-empty-state" class="<?= empty($invitations) ? '' : 'hidden' ?> px-6 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-surface-border mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" /></svg>
                <p class="text-sm font-bold text-text">No active invitations.</p>
                <p class="text-xs text-text-muted mt-1">Generate a new link or send an email from the "Send Invitations" tab.</p>
            </div>
            
            <div id="invitations-filter-empty-state" class="hidden px-6 py-16 text-center">
                <p class="text-sm font-bold text-text">No invitations match your filters.</p>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="px-6 py-2 border-t border-surface-border flex flex-col sm:flex-row justify-between items-center gap-3 bg-surface shrink-0 hidden" id="invitations-pagination">
            <div class="flex items-center gap-4">
                <div class="text-xs font-bold text-text-muted">
                    Showing <span id="inv-page-start" class="text-text font-black">0</span> to <span id="inv-page-end" class="text-text font-black">0</span> of <span id="inv-page-total" class="text-text font-black">0</span>
                </div>
                <div class="flex items-center gap-1.5 border-l border-surface-border pl-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Per Page:</span>
                    <select class="js-page-size bg-surface border border-surface-border rounded-lg px-2 py-1 text-xs font-bold text-text cursor-pointer outline-none focus:border-accent">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" class="js-page-first p-1.5 rounded-lg border border-surface-border text-xs text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center" title="First Page">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                </button>
                <button type="button" class="js-page-prev p-1.5 rounded-lg border border-surface-border text-xs text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center" title="Previous Page">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <div class="js-page-numbers flex items-center gap-1"></div>
                <button type="button" class="js-page-next p-1.5 rounded-lg border border-surface-border text-xs text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center" title="Next Page">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>
                <button type="button" class="js-page-last p-1.5 rounded-lg border border-surface-border text-xs text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer flex items-center justify-center" title="Last Page">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>
        
    </div>
</div>

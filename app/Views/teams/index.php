<!-- Mobile Dropdown Header (Hidden on Desktop, Visible on Mobile) -->
<div class="relative flex flex-col shrink-0 mb-6 px-1 lg:hidden" id="mobile-team-dropdown-container">
    
    <button onclick="toggleMobileTeamDropdown()" class="flex items-center justify-between w-full text-left group cursor-pointer lg:cursor-default border border-surface-border bg-surface px-4 py-3 rounded-xl shadow-sm">
        <span class="text-sm font-black tracking-tight text-text truncate group-hover:text-accent transition-colors">
            <?= $activeTeam ? esc($activeTeam['name']) : 'Select a Team...' ?>
        </span>
        <svg id="mobile-team-dropdown-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-text-muted transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
    </button>
    
    <p class="text-[10px] md:text-xs font-bold text-text-muted mt-2 uppercase tracking-widest pl-1">
        Manage Teams
    </p>

    <!-- The Dropdown Menu (Hidden by default, shown via JS on mobile) -->
    <div id="mobile-team-dropdown-menu" class="hidden absolute top-[52px] left-0 w-full max-w-sm bg-surface border border-surface-border rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto custom-scrollbar">
        <div class="p-2 border-b border-surface-border">
            <button onclick="document.getElementById('modal-create-team').classList.remove('hidden'); document.getElementById('modal-create-team').classList.add('flex'); toggleMobileTeamDropdown();" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-bold text-accent bg-accent/10 hover:bg-accent/20 transition-colors flex justify-between items-center cursor-pointer">
                + Create New Team
            </button>
        </div>
        <div class="flex flex-col p-2 gap-1">
            <?php if(empty($presets)): ?>
                <p class="px-3 py-2 text-xs text-text-muted italic">No teams saved yet.</p>
            <?php else: ?>
                <?php foreach($presets as $preset): ?>
                    <?php $isActive = ($activeTeam && $activeTeam['id'] == $preset['id']); ?>
                    <div class="relative group flex items-center justify-between px-3 py-1.5 rounded-lg text-sm font-bold transition-colors <?= $isActive ? 'bg-accent text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800/50 text-text-muted hover:text-text' ?>">
                        
                        <!-- Invisible link covering the row for navigation -->
                        <a href="<?= site_url('teams?team_id=' . $preset['id']) ?>" class="absolute inset-0 z-10 rounded-lg"></a>
                        
                        <div class="flex items-center min-w-0 pr-2 pointer-events-none">
                            <span class="truncate"><?= esc($preset['name']) ?></span>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0 relative z-20">
                            <span class="text-[10px] <?= $isActive ? 'text-white/80' : 'opacity-70' ?> mt-0.5">
                                <?= $preset['member_count'] ?>
                            </span>
                            
                            <!-- Delete Button -->
                            <?= form_open('teams/delete', ['onsubmit' => "return confirm('Delete this team?');", 'class' => 'flex items-center']) ?>
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="preset_id" value="<?= $preset['id'] ?>">
                                <button type="submit" class="p-1.5 rounded-md <?= $isActive ? 'text-white/60 hover:text-white hover:bg-white/20' : 'text-zinc-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10' ?> transition-colors cursor-pointer" title="Delete Team">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            <?= form_close() ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$activeTeam): ?>
    <div class="flex-1 border-2 border-dashed border-surface-border rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 min-h-[400px]">
        <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 mb-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-text mb-1">Select or Create a Team</h3>
        <p class="text-sm text-text-muted max-w-sm">Choose a distribution list from the sidebar or dropdown to edit its members, or create a new one.</p>
    </div>
<?php else: ?>

    <form action="<?= site_url('teams/store') ?>" method="POST" class="flex-1 flex flex-col lg:h-full lg:min-h-0">
        <?= csrf_field() ?>
        <input type="hidden" name="team_id" value="<?= $activeTeam['id'] ?>">

        <!-- Team Info Inputs -->
        <div class="mb-4 shrink-0 flex flex-col sm:flex-row gap-3 lg:gap-4 bg-surface lg:bg-transparent p-4 lg:p-0 rounded-2xl lg:rounded-none border border-surface-border lg:border-none shadow-sm lg:shadow-none">
            <div class="w-full sm:w-[35%]">
                <label class="block text-[10px] font-black uppercase tracking-widest text-accent mb-1 ml-1 lg:ml-0">Team Title</label>
                <input type="text" name="name" required value="<?= esc($activeTeam['name']) ?>" placeholder="Team Name" class="w-full bg-transparent text-xl lg:text-2xl font-black text-text placeholder-zinc-400 dark:placeholder-zinc-600 focus:outline-none focus:ring-0 px-1 border-b border-transparent focus:border-accent/30 transition-colors pb-1">
            </div>
            <div class="w-full sm:w-[65%]">
                <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-1 ml-1 lg:ml-0">Description</label>
                <input type="text" name="description" value="<?= esc($activeTeam['description']) ?>" placeholder="Optional description..." class="w-full bg-transparent text-sm font-medium text-text-muted placeholder-zinc-300 dark:placeholder-zinc-700 focus:outline-none focus:ring-0 px-1 border-b border-transparent focus:border-accent/30 transition-colors pb-1">
            </div>
        </div>

        <!-- Mobile Tabs Navigation (Hidden on Desktop) -->
        <div class="flex gap-6 border-b border-surface-border mb-4 px-2 shrink-0 lg:hidden overflow-x-auto custom-scrollbar">
            <button type="button" id="tab-btn-available" class="tab-btn pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-all cursor-pointer whitespace-nowrap" onclick="switchTeamPanel('available')">
                Directory Filter
            </button>
            <button type="button" id="tab-btn-selected" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text hover:border-surface-border transition-all cursor-pointer whitespace-nowrap" onclick="switchTeamPanel('selected')">
                Selected Members
                <span class="ml-1.5 px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-text-muted text-[10px] tab-badge transition-colors" id="tab-selected-count">0</span>
            </button>
        </div>

        <!-- The Dual-Panel Grid (Notice lg:absolute lg:inset-0 to enable native mobile scrolling) -->
        <div class="flex-1 lg:min-h-0 relative pb-[80px] lg:pb-0">
            <div class="flex flex-col lg:absolute lg:inset-0 lg:flex-row gap-0 lg:gap-6">
                
                <!-- Panel 1: Directory Search -->
                <div id="panel-available" class="flex-1 flex-col bg-surface lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm lg:overflow-hidden flex lg:flex lg:h-full">
                    <div class="p-4 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30 space-y-3 shrink-0">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xs font-black uppercase tracking-widest text-text-muted">Available Users</h2>
                            <button type="button" onclick="selectAllVisible()" class="text-[10px] font-black uppercase tracking-widest text-accent hover:text-accent-hover transition-colors cursor-pointer flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Select Visible
                            </button>
                        </div>
                        
                        <input type="text" id="filter-search" placeholder="Search name or email..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-4 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all text-text">
                        
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3">
                            <div class="relative">
                                <select id="filter-staff-type" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text">
                                    <option value="all">All Staff</option>
                                    <option value="teaching">Teaching Only</option>
                                    <option value="non-teaching">Non-Teaching Only</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                            <div class="relative">
                                <select id="filter-unit" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text">
                                    <option value="">All Units</option>
                                    <?php foreach($units as $u): ?><option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                            <div class="relative">
                                <select id="filter-position" class="appearance-none w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl px-3 py-2 text-sm focus:border-accent outline-none text-text">
                                    <option value="">All Positions</option>
                                    <?php foreach($positions as $p): ?><option value="<?= $p['id'] ?>" data-teaching="<?= $p['is_teaching'] ?>"><?= esc($p['title']) ?></option><?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                            </div>
                        </div>
                    </div>

                    <!-- Allows natural expansion on mobile with overflow-visible -->
                    <div class="flex-1 overflow-visible lg:overflow-y-auto p-2 space-y-1 custom-scrollbar" id="directory-list">
                        <?php foreach($users as $user): ?>
                            <div class="user-card flex items-center justify-between p-3 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 cursor-pointer transition-all border border-transparent hover:border-surface-border min-w-0"
                                 data-id="<?= $user['user_id'] ?>" data-name="<?= strtolower(esc($user['first_name'] . ' ' . $user['last_name'])) ?>" data-email="<?= strtolower(esc($user['email'])) ?>" data-unit="<?= $user['unit_id'] ?>" data-position="<?= $user['position_id'] ?>" data-teaching="<?= $user['is_teaching'] ?>" onclick="moveToSelected(this)">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="size-9 rounded-full bg-accent/10 text-accent flex items-center justify-center font-black text-sm shrink-0">
                                        <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                                    </div>
                                    <div class="flex flex-col min-w-0 flex-1 pr-2">
                                        <span class="text-sm font-bold text-text truncate"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-text-muted truncate">
                                            <?= esc($user['position'] ?? 'No Position') ?> • <?= esc($user['department'] ?? 'No Unit') ?>
                                        </span>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-300 dark:text-zinc-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </div>
                        <?php endforeach; ?>
                        <div id="no-results" class="hidden text-center py-8 text-sm font-bold text-text-muted italic">No users match your filters.</div>
                    </div>
                </div>

                <!-- Panel 2: Selected Team Members (Hidden on Mobile initially) -->
                <div id="panel-selected" class="flex-1 flex-col bg-surface lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm lg:overflow-hidden hidden lg:flex lg:h-full">
                    
                    <div class="p-4 border-b border-surface-border bg-emerald-50 dark:bg-emerald-500/10 shrink-0 flex justify-between items-center">
                        <h2 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Selected Team Members (<span id="selected-count">0</span>)</h2>
                        
                        <button type="button" id="btn-clear-list" onclick="clearAllSelected()" class="hidden text-[10px] font-black uppercase tracking-widest text-red-500 hover:text-red-600 transition-colors cursor-pointer items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Clear List
                        </button>
                    </div>

                    <!-- Allows natural expansion on mobile with overflow-visible -->
                    <div class="flex-1 overflow-visible lg:overflow-y-auto p-2 space-y-1 bg-zinc-50/30 dark:bg-zinc-900/20 custom-scrollbar" id="selected-list">
                        <div id="empty-state" class="text-center py-12 text-sm font-bold text-text-muted italic flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-300 dark:text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Select users to add them here.
                        </div>
                    </div>

                    <!-- Desktop Save Button Location -->
                    <div class="hidden lg:block p-4 border-t border-surface-border bg-white dark:bg-zinc-800 shrink-0">
                        <button type="submit" id="btn-save-desktop" class="w-full py-2.5 rounded-xl bg-accent text-white text-sm font-bold shadow-lg shadow-accent/20 hover:bg-accent-hover transition-all active:scale-[0.98] cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Floating Save Button Location -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 p-4 bg-surface/90 backdrop-blur-md border-t border-surface-border z-50">
            <button type="submit" id="btn-save-mobile" class="w-full py-3.5 rounded-xl bg-accent text-white text-sm font-bold shadow-lg shadow-accent/20 active:scale-95 transition-all cursor-pointer">
                Save Team Changes
            </button>
        </div>
    </form>
<?php endif; ?>

<!-- CREATE TEAM MODAL -->
<div id="modal-create-team" class="fixed inset-0 z-[110] hidden overflow-y-auto items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    <div class="relative w-full max-w-md rounded-2xl bg-surface border border-surface-border p-8 shadow-2xl transition-all m-4">
        <h3 class="text-xl font-black text-text tracking-tight mb-2">Create New Team</h3>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mb-6">Distribution List</p>

        <form action="<?= site_url('teams/create-shell') ?>" method="POST" onsubmit="handleCreateTeam(this)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Team Name</label>
                    <input type="text" name="name" required placeholder="e.g., OVPAA Staff" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Description (Optional)</label>
                    <input type="text" name="description" placeholder="Who is in this list?" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none text-text transition-all" />
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="document.getElementById('modal-create-team').classList.add('hidden'); document.getElementById('modal-create-team').classList.remove('flex')" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl border border-surface-border text-sm font-bold text-text hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-create-team" class="w-full sm:flex-1 px-6 py-3.5 sm:py-3 rounded-xl bg-accent hover:bg-accent-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-accent/20 transition-all active:scale-95 cursor-pointer">
                    Create & Select
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // MOBILE DROPDOWN LOGIC
    function toggleMobileTeamDropdown() {
        const menu = document.getElementById('mobile-team-dropdown-menu');
        const icon = document.getElementById('mobile-team-dropdown-icon');
        if(menu && icon) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }

    document.addEventListener('click', function(event) {
        const container = document.getElementById('mobile-team-dropdown-container');
        const menu = document.getElementById('mobile-team-dropdown-menu');
        if (container && menu && !container.contains(event.target) && !menu.classList.contains('hidden')) {
            toggleMobileTeamDropdown();
        }
    });

<?php if ($activeTeam): ?>
    // MOBILE TABS LOGIC
    function switchTeamPanel(panel) {
        const avail = document.getElementById('panel-available');
        const selected = document.getElementById('panel-selected');
        const btnAvail = document.getElementById('tab-btn-available');
        const btnSelected = document.getElementById('tab-btn-selected');

        if (panel === 'available') {
            avail.classList.remove('hidden');
            avail.classList.add('flex');
            selected.classList.remove('flex');
            selected.classList.add('hidden');

            btnAvail.classList.add('border-accent', 'text-accent');
            btnAvail.classList.remove('border-transparent', 'text-text-muted');
            
            btnSelected.classList.remove('border-accent', 'text-accent');
            btnSelected.classList.add('border-transparent', 'text-text-muted');
            btnSelected.querySelector('.tab-badge').classList.replace('bg-accent/10', 'bg-zinc-100');
            btnSelected.querySelector('.tab-badge').classList.replace('text-accent', 'text-text-muted');
        } else {
            selected.classList.remove('hidden');
            selected.classList.add('flex');
            avail.classList.remove('flex');
            avail.classList.add('hidden');

            btnSelected.classList.add('border-accent', 'text-accent');
            btnSelected.classList.remove('border-transparent', 'text-text-muted');
            btnSelected.querySelector('.tab-badge').classList.replace('bg-zinc-100', 'bg-accent/10');
            btnSelected.querySelector('.tab-badge').classList.replace('text-text-muted', 'text-accent');

            btnAvail.classList.remove('border-accent', 'text-accent');
            btnAvail.classList.add('border-transparent', 'text-text-muted');
        }

        // Re-enforce lg layout overriding mobile hiding
        avail.classList.add('lg:flex');
        selected.classList.add('lg:flex');
    }

    // Modal safety
    function handleCreateTeam(form) {
        const btn = document.getElementById('btn-create-team');
        btn.disabled = true;
        btn.innerText = 'Creating...';
    }

    window.addEventListener('pageshow', function(e) {
        const btn = document.getElementById('btn-create-team');
        if (btn) {
            btn.disabled = false;
            btn.innerText = 'Create & Select';
        }
    });

    // PRE-LOADER LOGIC
    const activeMemberIds = <?= json_encode($activeMemberIds ?? []) ?>;

    window.addEventListener('DOMContentLoaded', () => {
        activeMemberIds.forEach(id => {
            const cardEl = document.querySelector(`#directory-list .user-card[data-id="${id}"]`);
            if (cardEl) window.moveToSelected(cardEl);
        });
    });

    // Filtering Logic
    const searchInput = document.getElementById('filter-search');
    const unitFilter = document.getElementById('filter-unit');
    const positionFilter = document.getElementById('filter-position');
    const staffTypeFilter = document.getElementById('filter-staff-type');
    
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const unit = unitFilter.value;
        const staffType = staffTypeFilter.value;

        const posOptions = positionFilter.querySelectorAll('option[data-teaching]');
        let currentSelectedHidden = false;

        posOptions.forEach(opt => {
            const isTeaching = opt.getAttribute('data-teaching') === "1";
            if (staffType === 'teaching' && !isTeaching) {
                opt.hidden = true; opt.disabled = true;
                if (opt.selected) currentSelectedHidden = true;
            } else if (staffType === 'non-teaching' && isTeaching) {
                opt.hidden = true; opt.disabled = true;
                if (opt.selected) currentSelectedHidden = true;
            } else {
                opt.hidden = false; opt.disabled = false;
            }
        });

        if (currentSelectedHidden) positionFilter.value = "";
        const position = positionFilter.value;

        const cards = document.querySelectorAll('#directory-list .user-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const matchSearch = card.getAttribute('data-name').includes(searchTerm) || card.getAttribute('data-email').includes(searchTerm);
            const unitData = card.getAttribute('data-unit');
            const matchUnit = unit === "" || (unitData && unitData.split(',').includes(unit));
            const posData = card.getAttribute('data-position');
            const matchPosition = position === "" || (posData && posData.split(',').includes(position));
            
            const isTeachingUser = card.getAttribute('data-teaching') === "1";
            let matchStaffType = true;
            if (staffType === 'teaching' && !isTeachingUser) matchStaffType = false;
            if (staffType === 'non-teaching' && isTeachingUser) matchStaffType = false;

            const isAlreadySelected = card.classList.contains('is-selected');

            if (matchSearch && matchUnit && matchPosition && matchStaffType && !isAlreadySelected) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('no-results').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', applyFilters);
    unitFilter.addEventListener('change', applyFilters);
    positionFilter.addEventListener('change', applyFilters);
    staffTypeFilter.addEventListener('change', applyFilters);

    // ==========================================
    // MASS SELECTION LOGIC
    // ==========================================
    window.selectAllVisible = function() {
        const cards = document.querySelectorAll('#directory-list .user-card');
        cards.forEach(card => {
            if (card.style.display !== 'none' && !card.classList.contains('is-selected')) {
                window.moveToSelected(card);
            }
        });
    };

    window.clearAllSelected = function() {
        if(!confirm("Are you sure you want to clear all selected users?")) return;
        const selectedCards = document.querySelectorAll('#selected-list .user-card');
        selectedCards.forEach(clone => {
            const id = clone.getAttribute('data-id');
            window.moveToDirectory(clone, id);
        });
    };

    // Dual-Pane Logic
    const directoryList = document.getElementById('directory-list');
    const selectedList = document.getElementById('selected-list');
    const emptyState = document.getElementById('empty-state');
    const countDisplay = document.getElementById('selected-count');
    const mobileTabCountDisplay = document.getElementById('tab-selected-count');
    const clearBtn = document.getElementById('btn-clear-list');
    
    let selectedIds = new Set();

    window.moveToSelected = function(el) {
        const id = el.getAttribute('data-id');
        if (selectedIds.has(id)) return;

        const clone = el.cloneNode(true);
        clone.onclick = function() { moveToDirectory(this, id); };
        
        clone.querySelector('svg').outerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-400 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
        clone.insertAdjacentHTML('beforeend', `<input type="hidden" name="user_ids[]" value="${id}">`);

        selectedList.appendChild(clone);
        selectedIds.add(id);
        
        el.style.display = 'none';
        el.classList.add('is-selected'); 
        
        updateUI();
    };

    window.moveToDirectory = function(cloneEl, id) {
        cloneEl.remove();
        selectedIds.delete(id);

        const originalEl = document.querySelector(`#directory-list .user-card[data-id="${id}"]`);
        if (originalEl) {
            originalEl.classList.remove('is-selected');
            applyFilters();
        }
        updateUI();
    };

    function updateUI() {
        const count = selectedIds.size;
        countDisplay.innerText = count;
        if(mobileTabCountDisplay) mobileTabCountDisplay.innerText = count;
        
        emptyState.style.display = count === 0 ? 'flex' : 'none';
        
        if (count > 0) {
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('flex');
        } else {
            clearBtn.classList.add('hidden');
            clearBtn.classList.remove('flex');
        }
    }
<?php endif; ?>
</script>
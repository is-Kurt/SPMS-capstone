<!-- CASCADE OPCR TARGETS MODAL WITH EMBEDDED TEAM BUILDER -->
<div id="opcr-cascade-modal" class="fixed inset-0 z-[150] bg-black/70 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-6 transition-all duration-200" aria-modal="true" role="dialog">
    <div class="bg-surface border border-surface-border rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="opcr-cascade-card">
        
        <!-- MODAL HEADER -->
        <div class="flex-none flex items-center justify-between p-4 sm:p-5 border-b border-surface-border bg-surface-header gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 sm:p-2.5 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-black text-text truncate">Cascade OPCR Commitments</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0">
                            Apex Paper
                        </span>
                    </div>
                    <p class="text-xs text-text-muted truncate mt-0.5">
                        Institutional commitments finalize upon cascade and serve as the reference basis for subordinates.
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeCascadeModal()" class="text-text-muted hover:text-text p-2 rounded-lg hover:bg-surface-border/40 transition-colors cursor-pointer shrink-0" title="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- TAB NAVIGATION (Shown when teams already exist) -->
        <div id="cascade-tab-nav" class="flex-none flex items-center border-b border-surface-border px-4 sm:px-6 bg-surface-header/50 gap-2">
            <button type="button" id="tab-btn-choose" onclick="switchCascadeTab('choose')" class="py-3 px-4 text-xs font-bold border-b-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 cursor-pointer transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Select Team</span>
                <span id="tab-teams-count-badge" class="px-1.5 py-0.2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] rounded-full font-black ml-1">0</span>
            </button>
            <button type="button" id="tab-btn-builder" onclick="switchCascadeTab('builder')" class="py-3 px-4 text-xs font-bold border-b-2 border-transparent text-text-muted hover:text-text flex items-center gap-1.5 cursor-pointer transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Team Builder</span>
            </button>
        </div>

        <!-- MODAL BODY -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar space-y-4">
            
            <!-- VIEW 1: CHOOSE EXISTING TEAM -->
            <div id="view-choose-team" class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-[11px] font-bold text-text-muted uppercase tracking-wider">
                        Choose Destination Team
                    </label>
                    <button type="button" onclick="switchCascadeTab('builder')" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                        + Create New Team
                    </button>
                </div>

                <!-- Teams Radio Cards Container -->
                <div id="existing-teams-list" class="space-y-2.5 max-h-[340px] overflow-y-auto custom-scrollbar pr-1">
                    <!-- Populated dynamically via JS -->
                </div>

                <!-- Empty State (if no teams exist) -->
                <div id="existing-teams-empty" class="hidden p-6 text-center rounded-xl border border-dashed border-surface-border bg-surface/50 flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="text-xs font-bold text-text">No Teams Created Yet</div>
                    <p class="text-[11px] text-text-muted max-w-sm leading-relaxed">
                        You have not created any distribution teams yet. Build your team below to cascade your OPCR commitments.
                    </p>
                    <button type="button" onclick="switchCascadeTab('builder')" class="mt-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition-all cursor-pointer inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Build Your First Team</span>
                    </button>
                </div>
            </div>

            <!-- VIEW 2: INLINE TEAM BUILDER -->
            <div id="view-team-builder" class="hidden space-y-4">
                <div class="bg-surface-header/40 p-3 sm:p-4 rounded-xl border border-surface-border space-y-3">
                    <div>
                        <label for="builder-team-name" class="block text-[11px] font-bold text-text-muted uppercase tracking-wider mb-1">
                            Team Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="builder-team-name" placeholder="e.g. College Deans, Collegiate Division, Executive Units..." 
                               class="w-full bg-surface text-xs font-bold text-text px-3.5 py-2.5 rounded-xl border border-surface-border focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all">
                    </div>
                    <div>
                        <label for="builder-team-desc" class="block text-[11px] font-bold text-text-muted uppercase tracking-wider mb-1">
                            Description <span class="text-text-muted/60 font-normal">(Optional)</span>
                        </label>
                        <input type="text" id="builder-team-desc" placeholder="Brief note about this distribution team..." 
                               class="w-full bg-surface text-xs font-medium text-text px-3.5 py-2 rounded-xl border border-surface-border focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all">
                    </div>
                </div>

                <!-- Member Selection Section -->
                <div class="space-y-2.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-bold text-text-muted uppercase tracking-wider">
                                Select Team Members
                            </label>
                            <span id="builder-selected-pill" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                0 Selected
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleSelectAllVisible(true)" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                                Select All Filtered
                            </button>
                            <span class="text-text-muted/40">•</span>
                            <button type="button" onclick="toggleSelectAllVisible(false)" class="text-[11px] font-bold text-rose-500 hover:underline cursor-pointer">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="builder-search-input" oninput="filterBuilderMembers()" placeholder="Search by name, position, or department..." 
                                   class="w-full bg-surface text-xs text-text pl-9 pr-3.5 py-2 rounded-xl border border-surface-border focus:border-emerald-500 outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-text-muted absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 overflow-x-auto scrollbar-hide py-0.5">
                            <button type="button" onclick="applyBuilderFilter('all', this)" class="builder-filter-chip px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-emerald-600 text-white cursor-pointer transition-colors">
                                All
                            </button>
                            <button type="button" onclick="applyBuilderFilter('dean', this)" class="builder-filter-chip px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-surface-header text-text-muted hover:text-text border border-surface-border cursor-pointer transition-colors">
                                Deans
                            </button>
                            <button type="button" onclick="applyBuilderFilter('teaching', this)" class="builder-filter-chip px-2.5 py-1.5 rounded-lg text-[10px] font-bold bg-surface-header text-text-muted hover:text-text border border-surface-border cursor-pointer transition-colors">
                                Teaching
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Members Roster List -->
                    <div id="builder-members-container" class="max-h-[260px] overflow-y-auto border border-surface-border rounded-xl divide-y divide-surface-border custom-scrollbar bg-surface">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>

        </div>

        <!-- MODAL FOOTER -->
        <div class="flex-none p-4 sm:p-5 border-t border-surface-border bg-surface-header flex items-center justify-between gap-3">
            <button type="button" onclick="closeCascadeModal()" class="px-4 py-2.5 text-xs font-bold text-text-muted hover:text-text rounded-xl border border-surface-border hover:bg-surface transition-colors cursor-pointer">
                Cancel
            </button>

            <!-- Action buttons for View 1 (Choose Team) -->
            <div id="footer-actions-choose" class="flex items-center gap-2">
                <button type="button" id="btn-execute-cascade" onclick="confirmAndExecuteCascade()" 
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all active:scale-[0.98] cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span id="btn-cascade-label">Cascade Commitments</span>
                </button>
            </div>

            <!-- Action buttons for View 2 (Team Builder) -->
            <div id="footer-actions-builder" class="hidden flex items-center gap-2">
                <button type="button" id="btn-save-team-only" onclick="saveBuilderTeam(false)" 
                        class="px-4 py-2.5 text-xs font-bold text-text bg-surface hover:bg-surface-border/50 rounded-xl border border-surface-border transition-colors cursor-pointer">
                    Save Team Only
                </button>
                <button type="button" id="btn-save-and-cascade" onclick="saveBuilderTeam(true)" 
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all active:scale-[0.98] cursor-pointer flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Save & Cascade</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    // State management for OPCR Cascade Modal
    window.cascadeUserTeams = <?= json_encode(array_values($userTeams ?? [])) ?>;
    window.cascadeEligibleUsers = <?= json_encode(array_values($eligibleUsers ?? [])) ?>;
    window.selectedCascadeTeamId = window.cascadeUserTeams.length > 0 ? window.cascadeUserTeams[0].id : null;
    let currentBuilderFilter = 'all';

    function openCascadeModal() {
        const modal = document.getElementById('opcr-cascade-modal');
        const card = document.getElementById('opcr-cascade-card');
        if (!modal || !card) return;

        // Render current teams list
        renderExistingTeams();

        // Switch to appropriate view
        if (window.cascadeUserTeams && window.cascadeUserTeams.length > 0) {
            switchCascadeTab('choose');
        } else {
            switchCascadeTab('builder');
        }

        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCascadeModal() {
        const modal = document.getElementById('opcr-cascade-modal');
        const card = document.getElementById('opcr-cascade-card');
        if (!modal || !card) return;

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    function switchCascadeTab(tab) {
        const tabBtnChoose = document.getElementById('tab-btn-choose');
        const tabBtnBuilder = document.getElementById('tab-btn-builder');
        const viewChoose = document.getElementById('view-choose-team');
        const viewBuilder = document.getElementById('view-team-builder');
        const footerChoose = document.getElementById('footer-actions-choose');
        const footerBuilder = document.getElementById('footer-actions-builder');

        if (tab === 'choose') {
            tabBtnChoose?.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
            tabBtnChoose?.classList.remove('border-transparent', 'text-text-muted');
            tabBtnBuilder?.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
            tabBtnBuilder?.classList.add('border-transparent', 'text-text-muted');

            viewChoose?.classList.remove('hidden');
            viewBuilder?.classList.add('hidden');
            footerChoose?.classList.remove('hidden');
            footerBuilder?.classList.add('hidden');
        } else {
            tabBtnBuilder?.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
            tabBtnBuilder?.classList.remove('border-transparent', 'text-text-muted');
            tabBtnChoose?.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
            tabBtnChoose?.classList.add('border-transparent', 'text-text-muted');

            viewChoose?.classList.add('hidden');
            viewBuilder?.classList.remove('hidden');
            footerChoose?.classList.add('hidden');
            footerBuilder?.classList.remove('hidden');

            renderBuilderMembers();
            document.getElementById('builder-team-name')?.focus();
        }
    }

    function renderExistingTeams() {
        const list = document.getElementById('existing-teams-list');
        const empty = document.getElementById('existing-teams-empty');
        const badge = document.getElementById('tab-teams-count-badge');
        const cascadeBtn = document.getElementById('btn-execute-cascade');
        const cascadeLabel = document.getElementById('btn-cascade-label');

        if (badge) badge.innerText = window.cascadeUserTeams.length;

        if (!window.cascadeUserTeams || window.cascadeUserTeams.length === 0) {
            if (list) list.innerHTML = '';
            list?.classList.add('hidden');
            empty?.classList.remove('hidden');
            if (cascadeBtn) cascadeBtn.disabled = true;
            return;
        }

        empty?.classList.add('hidden');
        list?.classList.remove('hidden');

        // Check if selectedCascadeTeamId is still valid
        const teamIds = window.cascadeUserTeams.map(t => t.id);
        if (!window.selectedCascadeTeamId || !teamIds.includes(window.selectedCascadeTeamId)) {
            window.selectedCascadeTeamId = window.cascadeUserTeams[0].id;
        }

        let html = '';
        window.cascadeUserTeams.forEach(team => {
            const isSelected = (team.id == window.selectedCascadeTeamId);
            const mCount = parseInt(team.member_count) || 0;
            const mLabel = mCount === 1 ? 'member' : 'members';

            html += `
                <div onclick="selectCascadeTeam('${team.id}')" 
                     class="team-card p-3.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3 ${
                         isSelected 
                             ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-500 ring-2 ring-emerald-500/20 shadow-xs' 
                             : 'bg-surface hover:bg-surface-header border-surface-border'
                     }">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 ${
                            isSelected ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-surface-border bg-surface'
                        }">
                            ${isSelected ? '<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : ''}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-text truncate">${escapeHtml(team.name)}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-surface-header border border-surface-border text-text-muted shrink-0">
                                    ${mCount} ${mLabel}
                                </span>
                            </div>
                            ${team.description ? `<p class="text-[11px] text-text-muted truncate mt-0.5">${escapeHtml(team.description)}</p>` : ''}
                        </div>
                    </div>
                    <span class="text-xs font-bold ${isSelected ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-muted'} shrink-0">
                        ${isSelected ? 'Selected' : 'Select'}
                    </span>
                </div>
            `;
        });

        if (list) list.innerHTML = html;

        const activeTeam = window.cascadeUserTeams.find(t => t.id == window.selectedCascadeTeamId);
        if (cascadeBtn) {
            cascadeBtn.disabled = !activeTeam;
            if (cascadeLabel && activeTeam) {
                cascadeLabel.innerText = `Cascade to ${activeTeam.name}`;
            }
        }
    }

    function selectCascadeTeam(teamId) {
        window.selectedCascadeTeamId = teamId;
        renderExistingTeams();
    }

    function renderBuilderMembers() {
        const container = document.getElementById('builder-members-container');
        if (!container) return;

        if (!window.cascadeEligibleUsers || window.cascadeEligibleUsers.length === 0) {
            container.innerHTML = '<div class="p-4 text-center text-xs text-text-muted">No eligible team members found in the system.</div>';
            return;
        }

        let html = '';
        window.cascadeEligibleUsers.forEach(user => {
            const pos = user.position || 'Employee';
            const dept = user.department || 'Academic Division';
            const initial = (user.first_name || 'U').charAt(0).toUpperCase();

            html += `
                <label class="builder-member-row flex items-center justify-between p-2.5 sm:p-3 hover:bg-surface-header/60 cursor-pointer transition-colors gap-3" 
                       data-user-id="${user.user_id}" 
                       data-name="${escapeHtml((user.first_name + ' ' + user.last_name).toLowerCase())}"
                       data-email="${escapeHtml((user.email || '').toLowerCase())}"
                       data-position="${escapeHtml(pos.toLowerCase())}"
                       data-department="${escapeHtml(dept.toLowerCase())}"
                       data-teaching="${user.is_teaching || 0}">
                    <div class="flex items-center gap-3 min-w-0">
                        <input type="checkbox" value="${user.user_id}" onchange="updateBuilderCount()" 
                               class="builder-member-check w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-surface-border cursor-pointer shrink-0">
                        <div class="w-7 h-7 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs flex items-center justify-center shrink-0 border border-emerald-500/20">
                            ${initial}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-text truncate">
                                ${escapeHtml(user.first_name + ' ' + user.last_name)}
                            </div>
                            <div class="text-[10px] text-text-muted truncate">
                                ${escapeHtml(user.email)}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-header text-text-muted border border-surface-border truncate max-w-[130px]">
                            ${escapeHtml(pos)}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium text-text-muted/80 truncate max-w-[120px] hidden sm:inline">
                            ${escapeHtml(dept)}
                        </span>
                    </div>
                </label>
            `;
        });

        container.innerHTML = html;
        filterBuilderMembers();
    }

    function filterBuilderMembers() {
        const query = (document.getElementById('builder-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.builder-member-row');

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const position = row.getAttribute('data-position') || '';
            const dept = row.getAttribute('data-department') || '';
            const isTeaching = row.getAttribute('data-teaching') == 1;

            const matchesQuery = !query || name.includes(query) || email.includes(query) || position.includes(query) || dept.includes(query);
            
            let matchesFilter = true;
            if (currentBuilderFilter === 'dean') {
                matchesFilter = position.includes('dean');
            } else if (currentBuilderFilter === 'teaching') {
                matchesFilter = isTeaching;
            }

            if (matchesQuery && matchesFilter) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });

        updateBuilderCount();
    }

    function applyBuilderFilter(filter, btn) {
        currentBuilderFilter = filter;
        document.querySelectorAll('.builder-filter-chip').forEach(c => {
            c.classList.remove('bg-emerald-600', 'text-white');
            c.classList.add('bg-surface-header', 'text-text-muted');
        });
        btn.classList.remove('bg-surface-header', 'text-text-muted');
        btn.classList.add('bg-emerald-600', 'text-white');
        filterBuilderMembers();
    }

    function toggleSelectAllVisible(select) {
        const rows = document.querySelectorAll('.builder-member-row:not(.hidden)');
        rows.forEach(row => {
            const check = row.querySelector('.builder-member-check');
            if (check) check.checked = select;
        });
        updateBuilderCount();
    }

    function updateBuilderCount() {
        const checked = document.querySelectorAll('.builder-member-check:checked');
        const pill = document.getElementById('builder-selected-pill');
        if (pill) {
            pill.innerText = `${checked.length} Selected`;
        }
    }

    async function saveBuilderTeam(cascadeImmediately = false) {
        const nameInput = document.getElementById('builder-team-name');
        const descInput = document.getElementById('builder-team-desc');
        const name = (nameInput?.value || '').trim();
        const description = (descInput?.value || '').trim();

        if (!name) {
            await window.appAlert("Please enter a name for the distribution team.", { title: "Team Name Required" });
            nameInput?.focus();
            return;
        }

        const checkedBoxes = Array.from(document.querySelectorAll('.builder-member-check:checked'));
        if (checkedBoxes.length === 0) {
            await window.appAlert("Please select at least one member to include in this distribution team.", { title: "No Members Selected" });
            return;
        }

        const userIds = checkedBoxes.map(cb => cb.value);

        const btnSave = cascadeImmediately ? document.getElementById('btn-save-and-cascade') : document.getElementById('btn-save-team-only');
        const origText = btnSave?.innerHTML;
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = `
                <svg class="animate-spin h-3.5 w-3.5 text-current inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Saving...</span>
            `;
        }

        try {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', description);
            userIds.forEach(uid => formData.append('user_ids[]', uid));

            const resp = await fetch('<?= site_url('teams') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await resp.json();
            if (data.status === 'success' && data.team) {
                // Add new team to state
                window.cascadeUserTeams.unshift(data.team);
                window.selectedCascadeTeamId = data.team.id;

                if (cascadeImmediately) {
                    await executeCascade(data.team.id);
                } else {
                    switchCascadeTab('choose');
                    renderExistingTeams();
                    if (btnSave) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = origText;
                    }
                }
            } else {
                throw new Error(data.message || 'Failed to save team.');
            }
        } catch (err) {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origText;
            }
            await window.appAlert(err.message || "An unexpected error occurred while saving the team.");
        }
    }

    async function confirmAndExecuteCascade() {
        if (!window.selectedCascadeTeamId) {
            await window.appAlert("Please select a distribution team first.");
            return;
        }

        const team = window.cascadeUserTeams.find(t => t.id == window.selectedCascadeTeamId);
        if (!team) return;

        const mCount = parseInt(team.member_count) || 0;
        const mLabel = mCount === 1 ? 'member' : 'members';

        const ok = await window.appConfirm(
            `Are you done formulating institutional OPCR targets?\n\nCascading will finalize these commitments and immediately distribute them to "${team.name}" (${mCount} ${mLabel}) as their mandatory reference basis without requiring approval.`,
            {
                title: 'Cascade OPCR Targets',
                confirmText: `Cascade to ${team.name}`,
                cancelText: 'Cancel',
                variant: 'success'
            }
        );

        if (!ok) return;

        await executeCascade(team.id);
    }

    async function executeCascade(teamId) {
        if (typeof saveDocument === 'function') {
            await saveDocument(true);
        }

        const btn = document.getElementById('btn-execute-cascade');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-3.5 w-3.5 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Cascading...</span>
            `;
        }

        const formData = new FormData();
        formData.append('folder_id', '<?= $doc['document_folder_id'] ?? '' ?>');
        formData.append('team_id', teamId);

        apiPost('<?= site_url('folder/cascade-team') ?>', formData, {
            onSuccess: () => {
                window.location.reload();
            },
            onError: async (errMsg) => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Cascade Commitments</span>
                    `;
                }
                await window.appAlert(errMsg || "An error occurred while cascading OPCR.");
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>

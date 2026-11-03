<?php
    $sysRole = session()->get('role') ?? ($userRole ?? 'Admin');
    $phaseSchedule = $phaseSchedule ?? [];
    $p1 = $phaseSchedule['phase1'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
    $p2 = $phaseSchedule['phase2'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
    $totalPersonnel = $totalPersonnel ?? count($cycleFolders ?? []);
?>
<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-xl border border-surface-border shadow-xs overflow-visible lg:overflow-hidden">
    
    <!-- Dashboard Header -->
    <div class="px-3.5 sm:px-6 lg:px-8 py-3.5 sm:py-5 border-b border-surface-border shrink-0">

        <!-- Active evaluation cycle dropdown (mobile) -->
        <?= view('components/mobile_folder_dropdown', [
            'activeCycle'      => $activeCycle ?? null,
            'folders'          => $rootFolders ?? ($sidebarFolders ?? []),
            'selectedFolderId' => $activeCycle['id'] ?? null,
            'baseUrl'          => !empty($isArchivedView) ? 'dashboard/archived' : 'dashboard',
            'containerClass'   => 'mb-3'
        ]) ?>

        <!-- MOBILE VIEW SWITCHER (Analytics vs Masterlist) - Full Width Grid on Mobile directly under Folder Card -->
        <div class="lg:hidden mb-3.5">
            <div id="dash-view-switcher-mobile" class="relative grid grid-cols-2 p-1 rounded-xl border shadow-2xs w-full spms-tab-container dark:bg-zinc-950 dark:border-zinc-800">
                <!-- Sliding Indicator Pill (Mobile) -->
                <div id="dash-view-indicator-mobile" 
                     class="absolute rounded-lg bg-white dark:bg-zinc-800 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 pointer-events-none"
                     style="top: 0; left: 0; width: 0; height: 0; opacity: 0; will-change: transform, width;"></div>

                <button type="button" id="btn-view-analytics-mobile" onclick="switchDashboardView('analytics')"
                        class="relative z-10 w-full py-2 px-3 rounded-lg text-xs font-bold transition-colors cursor-pointer inline-flex items-center justify-center leading-none spms-tab-active">
                    <span>Overview Analytics</span>
                </button>
                <button type="button" id="btn-view-masterlist-mobile" onclick="switchDashboardView('masterlist')"
                        class="relative z-10 w-full py-2 px-3 rounded-lg text-xs font-bold transition-colors cursor-pointer inline-flex items-center justify-center gap-1.5 leading-none spms-tab-inactive">
                    <span>Master List</span>
                    <span class="inline-flex items-center justify-center px-1.5 min-w-[18px] h-4 rounded-full text-[10px] font-black leading-none spms-tab-badge">
                        <?= count($cycleFolders) ?>
                    </span>
                </button>
            </div>
        </div>

        <!-- MOBILE ANALYTICS HEADER (Shown only when Overview Analytics is active on mobile) -->
        <div id="analytics-mobile-header" class="lg:hidden space-y-2.5 mb-1">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 truncate">
                    <?= ($sysRole === 'Supervisor' && empty($isPresidentScope)) ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
            </div>
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <h1 class="text-xl font-black tracking-tight text-zinc-900 dark:text-white truncate">
                        <?= ($sysRole === 'Supervisor' && empty($isPresidentScope)) ? (!empty($isChairScope) ? 'Department Overview' : 'College Overview') : 'Executive Overview' ?>
                    </h1>
                    <?php if (!empty($isArchivedView)): ?>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shrink-0">
                            Archived
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $colleges = [];
            $adminOffices = [];
            $subDepts = [];
            $selectedUnitDisplayName = 'All Departments & Units';
            if (!empty($allUnits)) {
                foreach ($allUnits as $u) {
                    if (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$u['id']) {
                        $selectedUnitDisplayName = $u['name'];
                    }
                    if (empty($u['parent_id'])) {
                        if (stripos($u['name'], 'College of') !== false || stripos($u['name'], 'Graduate School') !== false) {
                            $colleges[] = $u;
                        } else {
                            $adminOffices[] = $u;
                        }
                    } else {
                        $subDepts[] = $u;
                    }
                }
            }
            ?>
            <!-- Mobile College Filter Dropdown -->
            <?php if (!empty($allUnits)): ?>
            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0 relative" id="college-dropdown-container-mobile">
                    <button type="button" 
                            id="college-dropdown-btn-mobile"
                            onclick="toggleCollegeDropdown('mobile')"
                            class="w-full flex items-center justify-between gap-3 text-xs font-semibold px-3.5 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 cursor-pointer shadow-2xs spms-select-dark transition-all active:scale-[0.98]">
                        <span id="college-dropdown-label-mobile" class="truncate text-left">
                            <?= esc($selectedUnitDisplayName) ?>
                        </span>
                        <svg id="college-dropdown-chevron-mobile" xmlns="http://www.w3.org/2000/svg" 
                             class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-500 shrink-0 transition-transform duration-200" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Mobile Custom Slide-Down Menu -->
                    <div id="college-dropdown-menu-mobile"
                         class="hidden absolute left-0 right-0 mt-1.5 w-full rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl z-[120] overflow-hidden"
                         style="transform-origin: top center;">
                        
                        <!-- Search Filter Input -->
                        <div class="p-2 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-950/60">
                            <div class="relative">
                                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" 
                                       id="college-dropdown-search-mobile" 
                                       oninput="filterCollegeDropdownOptions(this.value, 'mobile')"
                                       placeholder="Filter departments & units..." 
                                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                            </div>
                        </div>

                        <!-- Scrollable Options List -->
                        <div id="college-dropdown-list-mobile" class="max-h-60 overflow-y-auto p-1.5 space-y-0.5 text-xs">
                            <!-- Reset / All Option -->
                            <button type="button" 
                                    onclick="selectCollegeOption('', 'All Departments & Units')"
                                    class="college-opt-item-mobile w-full flex items-center justify-between px-3 py-2 rounded-lg font-bold transition-colors <?= empty($selectedUnitId) ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                <span class="truncate">All Departments &amp; Units</span>
                                <?php if (empty($selectedUnitId)): ?>
                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <?php endif; ?>
                            </button>

                            <?php if (!empty($colleges)): ?>
                                <div class="college-opt-group-mobile pt-1.5 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                    Colleges
                                </div>
                                <?php foreach ($colleges as $c): 
                                    $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$c['id']);
                                ?>
                                    <button type="button"
                                            onclick="selectCollegeOption('<?= $c['id'] ?>', '<?= esc(addslashes($c['name'])) ?>')"
                                            data-opt-name="<?= esc(strtolower($c['name'])) ?>"
                                            class="college-opt-item-mobile w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                        <span class="truncate text-left"><?= esc($c['name']) ?></span>
                                        <?php if ($isSelected): ?>
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($adminOffices)): ?>
                                <div class="college-opt-group-mobile pt-2 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                    Administrative Departments &amp; Units
                                </div>
                                <?php foreach ($adminOffices as $ao): 
                                    $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$ao['id']);
                                ?>
                                    <button type="button"
                                            onclick="selectCollegeOption('<?= $ao['id'] ?>', '<?= esc(addslashes($ao['name'])) ?>')"
                                            data-opt-name="<?= esc(strtolower($ao['name'])) ?>"
                                            class="college-opt-item-mobile w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                        <span class="truncate text-left"><?= esc($ao['name']) ?></span>
                                        <?php if ($isSelected): ?>
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($subDepts)): ?>
                                <div class="college-opt-group-mobile pt-2 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                    Degree Programs / Sub-Departments
                                </div>
                                <?php foreach ($subDepts as $sd): 
                                    $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$sd['id']);
                                ?>
                                    <button type="button"
                                            onclick="selectCollegeOption('<?= $sd['id'] ?>', '<?= esc(addslashes($sd['name'])) ?>')"
                                            data-opt-name="<?= esc(strtolower($sd['name'])) ?>"
                                            class="college-opt-item-mobile w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                        <span class="truncate text-left"><?= esc($sd['name']) ?></span>
                                        <?php if ($isSelected): ?>
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($selectedUnitId)): ?>
                    <button onclick="applyCollegeFilter('')"
                            class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 transition-colors shadow-2xs shrink-0"
                            title="Reset filter">
                        <span>&times;</span>
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- DESKTOP TOP HEADER / TOOLBAR (hidden on mobile, visible lg:flex) -->
        <div class="hidden lg:block">
            <!-- Eyebrow Row -->
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 truncate">
                    <?= ($sysRole === 'Supervisor' && empty($isPresidentScope)) ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
            </div>
            
            <!-- Main Title & Desktop Toolbar -->
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-zinc-900 dark:text-white truncate">
                            <?= ($sysRole === 'Supervisor' && empty($isPresidentScope)) ? (!empty($isChairScope) ? 'Department Overview' : 'College Overview') : 'Executive Overview' ?>
                        </h1>
                        <?php if (!empty($isArchivedView)): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shrink-0">
                                Archived Cycle
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($sysRole === 'Supervisor'): ?>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                            <?php if (!empty($isPresidentScope)): ?>
                                Executive oversight tracking sector &amp; Vice Presidential OPCR performance across <span class="font-bold text-zinc-700 dark:text-zinc-200">Benguet State University</span>
                            <?php else: ?>
                                Tracking faculty &amp; staff submissions for <span class="font-bold text-zinc-700 dark:text-zinc-200"><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Your Department' : 'Your College')) ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <!-- Desktop View Switcher -->
                    <div id="dash-view-switcher" class="relative inline-flex p-1 rounded-xl border shadow-2xs spms-tab-container dark:bg-zinc-950 dark:border-zinc-800">
                        <!-- Sliding Indicator Pill -->
                        <div id="dash-view-indicator" 
                             class="absolute rounded-lg bg-white dark:bg-zinc-800 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 pointer-events-none"
                             style="top: 0; left: 0; width: 0; height: 0; opacity: 0; will-change: transform, width;"></div>

                        <button type="button" id="btn-view-analytics" onclick="switchDashboardView('analytics')"
                                class="relative z-10 py-2 px-4 rounded-lg text-xs font-bold transition-colors cursor-pointer inline-flex items-center justify-center leading-none spms-tab-active">
                            <span>Overview Analytics</span>
                        </button>
                        <button type="button" id="btn-view-masterlist" onclick="switchDashboardView('masterlist')"
                                class="relative z-10 py-2 px-4 rounded-lg text-xs font-bold transition-colors cursor-pointer inline-flex items-center justify-center gap-1.5 leading-none spms-tab-inactive">
                            <span>Master List</span>
                            <span class="inline-flex items-center justify-center px-1.5 min-w-[18px] h-4 rounded-full text-[10px] font-black leading-none spms-tab-badge">
                                <?= count($cycleFolders) ?>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. OVERVIEW ANALYTICS VIEW -->
    <div id="dashboard-view-analytics" class="p-3.5 sm:p-5 lg:p-6 xl:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-3.5 sm:space-y-5">
        <?php if (empty($activeCycle)): ?>
            <div class="p-8 text-center rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <svg class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white">No <?= !empty($isArchivedView) ? 'Archived' : 'Active' ?> Evaluation Cycles Found</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    <?= !empty($isArchivedView) ? 'There are currently no closed or archived evaluation periods to display.' : 'There are no active evaluation periods currently running.' ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($sysRole === 'Supervisor'): ?>

        <!-- 1. TOP SUMMARY CARDS: UNIT HEALTH & QUALITY (OPTION A: 1, 2, 4) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Card 1: Total College Headcount -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"><?= !empty($isChairScope) ? 'Department Headcount' : 'College Headcount' ?></span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-info-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Personnel</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    <span><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Department roster' : 'College roster')) ?></span>
                </div>
            </div>

            <!-- Card 2: Average Performance Rating -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Average Rating</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= $overallAverage > 0 ? number_format($overallAverage, 2) : '——' ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">/ 5.00</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border <?= $adjectivalBadgeClass ?>">
                        <?= !empty($adjectivalLabel) && $overallAverage > 0 ? esc($adjectivalLabel) : 'NOT YET RATED' ?>
                    </span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <?= number_format($totalRated) ?> of <?= number_format($totalPersonnel) ?> Rated
                    </span>
                </div>
            </div>

            <!-- Card 3: Evidence & MOVs Attached -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Evidence / MOVs Attached</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= number_format($totalMovCount) ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Files Attached</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    <span>Stage 2 Monitoring &amp; Coaching Evidence</span>
                </div>
            </div>

        </div>

        <!-- 2. CSC SPMS 2-PHASE LIFECYCLE BREAKDOWN -->
        <?php
            $p1 = $phaseSchedule['phase1'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
            $p2 = $phaseSchedule['phase2'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
            $targetUnstarted = (int)($pipeline['target']['unstarted'] ?? 0);
            $evalUnstarted = (int)($pipeline['evaluation']['unstarted'] ?? 0);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Phase 1 Card: Target Commitment Phase -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border transition-all duration-200 <?= !empty($p1['is_ongoing']) ? 'border-emerald-500/80 dark:border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'border-zinc-200 dark:border-zinc-800 shadow-xs' ?>">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black shrink-0">1</span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">Target Commitment Phase</h3>
                            <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate"><?= esc($p1['timeframe']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2">
                        <?php if (!empty($p1['is_ongoing'])): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-[#102a1e] dark:text-emerald-400 border border-emerald-200 dark:border-[#1b4330] shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Ongoing
                            </span>
                        <?php elseif ($p1['status'] === 'closed'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700/80">
                                Closed
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-400 dark:bg-zinc-800/80 dark:text-zinc-500 border border-zinc-200/60 dark:border-zinc-700/60">
                                <?= esc($p1['label']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200" title="<?= $targetUnstarted > 0 ? "{$targetUnstarted} personnel pending cascade / unstarted" : '' ?>">
                        <span class="block text-xl font-black"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block"><?= $targetUnstarted > 0 ? 'Pending Cascade' : 'Draft (Missing)' ?></span>
                    </div>
                </div>
            </div>

            <!-- Phase 2 Card: Accomplishment Report Phase -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border transition-all duration-200 <?= !empty($p2['is_ongoing']) ? ($p2['status'] === 'calibration' ? 'border-purple-500/80 dark:border-purple-500 ring-2 ring-purple-500/20 shadow-md' : 'border-emerald-500/80 dark:border-emerald-500 ring-2 ring-emerald-500/20 shadow-md') : 'border-zinc-200 dark:border-zinc-800 shadow-xs' ?>">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black shrink-0">2</span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">Accomplishment Report Phase</h3>
                            <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate"><?= esc($p2['timeframe']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2">
                        <?php if ($p2['status'] === 'ongoing'): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-[#102a1e] dark:text-emerald-400 border border-emerald-200 dark:border-[#1b4330] shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Ongoing
                            </span>
                        <?php elseif ($p2['status'] === 'calibration'): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                                Calibration
                            </span>
                        <?php elseif ($p2['status'] === 'monitoring'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                Monitoring
                            </span>
                        <?php elseif ($p2['status'] === 'closed'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700/80">
                                Closed
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-400 dark:bg-zinc-800/80 dark:text-zinc-500 border border-zinc-200/60 dark:border-zinc-700/60">
                                <?= esc($p2['label']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200" title="<?= $evalUnstarted > 0 ? "{$evalUnstarted} personnel pending cascade / unstarted" : '' ?>">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block"><?= $evalUnstarted > 0 ? 'Pending Cascade' : 'Draft (Missing)' ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2.5 TOP PERFORMING PERSONNEL -->
        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-sm">
                            ★
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Top Performing Personnel</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Highest rated faculty &amp; staff in this unit</p>
                        </div>
                    </div>
                    <?php
                        $supervisorPerformerCounts = [
                            'all'               => count($topPerformers),
                            'outstanding'       => 0,
                            'very_satisfactory' => 0,
                            'satisfactory'      => 0,
                            'unsatisfactory'    => 0,
                            'poor'              => 0,
                        ];
                        foreach ($topPerformers as $tp) {
                            $sVal = (float)($tp['rating_num'] ?? 0);
                            if ($sVal >= 4.50) {
                                $supervisorPerformerCounts['outstanding']++;
                            } elseif ($sVal >= 3.50) {
                                $supervisorPerformerCounts['very_satisfactory']++;
                            } elseif ($sVal >= 2.50) {
                                $supervisorPerformerCounts['satisfactory']++;
                            } elseif ($sVal >= 1.50) {
                                $supervisorPerformerCounts['unsatisfactory']++;
                            } elseif ($sVal > 0) {
                                $supervisorPerformerCounts['poor']++;
                            }
                        }
                    ?>
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                        <!-- Adjectival Rating Filter Pills -->
                        <div class="performer-tier-pills-container flex items-center gap-1.5 overflow-x-auto custom-scrollbar py-0.5">
                            <button type="button" onclick="filterTopPerformersByTier('all', 'supervisor-performers-grid', this)"
                                    data-tier="all"
                                    class="performer-tier-pill active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shrink-0 bg-zinc-900 text-white dark:bg-zinc-800 dark:text-white border border-zinc-900 dark:border-zinc-600 shadow-2xs">
                                <span>All (<?= $supervisorPerformerCounts['all'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('outstanding', 'supervisor-performers-grid', this)"
                                    data-tier="outstanding"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Outstanding (<?= $supervisorPerformerCounts['outstanding'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('very_satisfactory', 'supervisor-performers-grid', this)"
                                    data-tier="very_satisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                <span>Very Satisfactory (<?= $supervisorPerformerCounts['very_satisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('satisfactory', 'supervisor-performers-grid', this)"
                                    data-tier="satisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                <span>Satisfactory (<?= $supervisorPerformerCounts['satisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('unsatisfactory', 'supervisor-performers-grid', this)"
                                    data-tier="unsatisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                                <span>Unsatisfactory (<?= $supervisorPerformerCounts['unsatisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('poor', 'supervisor-performers-grid', this)"
                                    data-tier="poor"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                <span>Poor (<?= $supervisorPerformerCounts['poor'] ?>)</span>
                            </button>
                        </div>

                        <!-- Sort Filter (Top to Least / Least to Top) -->
                        <div class="relative shrink-0">
                            <select id="supervisor-top-performers-sort" onchange="sortTopPerformers(this.value, 'supervisor-performers-grid')"
                                    class="appearance-none bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg pl-2.5 pr-7 py-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-200 outline-none cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors shadow-2xs">
                                <option value="desc">Highest to Lowest</option>
                                <option value="asc">Lowest to Highest</option>
                            </select>
                            <svg class="w-3 h-3 text-zinc-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <?php if (!empty($topPerformers)): ?>
                    <div id="supervisor-performers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[320px] overflow-y-auto custom-scrollbar pr-1">
                        <?php foreach ($topPerformers as $idx => $tp): 
                            $sVal = (float)($tp['rating_num'] ?? 0);
                            $tKey = ($sVal >= 4.50) ? 'outstanding' :
                                    (($sVal >= 3.50) ? 'very_satisfactory' :
                                    (($sVal >= 2.50) ? 'satisfactory' :
                                    (($sVal >= 1.50) ? 'unsatisfactory' : 'poor')));
                        ?>
                            <div class="performer-card p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3 transition-colors hover:bg-zinc-100/70 dark:hover:bg-zinc-800/40 cursor-pointer"
                                 data-rating="<?= $sVal ?>"
                                 data-adjectival-tier="<?= $tKey ?>"
                                 data-initial-index="<?= $idx ?>"
                                 onclick="openMasterlistDocument('<?= !empty($tp['folder_id']) ? site_url('document/' . $tp['folder_id'] . '?from=dashboard') : '' ?>', event)"
                                 title="Click to view evaluation document">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="performer-rank-badge w-7 h-7 rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-zinc-900 dark:text-white truncate">
                                            <?= esc($tp['full_name'] ?? 'Personnel') ?>
                                        </div>
                                        <div class="text-[10px] text-zinc-400 truncate">
                                            <?= esc($tp['position'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <div class="text-xs font-black text-zinc-900 dark:text-white tabular-nums">
                                            <?= number_format($tp['rating_num'], 2) ?>
                                        </div>
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8.5px] font-extrabold uppercase border <?= $tp['adjectival_badge'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                                            <?= esc($tp['adjectival_label'] ?? 'Outstanding') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="performer-tier-empty col-span-full py-8 text-center text-zinc-400 dark:text-zinc-500 italic text-xs hidden">
                            No personnel discovered in this rating tier.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="py-8 text-center text-zinc-400 dark:text-zinc-500 italic text-xs">
                        Performance ratings in progress. Top performers will appear here upon completion.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- 1. TOP SUMMARY CARDS: INSTITUTIONAL HEALTH & QUALITY -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Card 1: Total University Headcount -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">University Headcount</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-info-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Personnel</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    <span>Benguet State University &bull; <?= count($deptLeaderboard) ?> Units</span>
                </div>
            </div>

            <!-- Card 2: Average Performance Rating -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Average Rating</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= $overallAverage > 0 ? number_format($overallAverage, 2) : '——' ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">/ 5.00</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border <?= $adjectivalBadgeClass ?>">
                        <?= !empty($adjectivalLabel) && $overallAverage > 0 ? esc($adjectivalLabel) : 'NOT YET RATED' ?>
                    </span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <?= number_format($totalRated) ?> of <?= number_format($totalPersonnel) ?> Rated
                    </span>
                </div>
            </div>

            <!-- Card 3: Evidence & MOVs Attached -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Evidence / MOVs Attached</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= number_format($totalMovCount) ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">Files Attached</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400 truncate">
                    <span>Stage 2 Monitoring &amp; Coaching Evidence</span>
                </div>
            </div>

        </div>

        <!-- 2. CSC SPMS 2-PHASE LIFECYCLE BREAKDOWN -->
        <?php
            $p1 = $phaseSchedule['phase1'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
            $p2 = $phaseSchedule['phase2'] ?? ['is_ongoing' => false, 'timeframe' => 'Schedule Not Set', 'label' => 'Upcoming', 'status' => 'upcoming'];
            $targetUnstarted = (int)($pipeline['target']['unstarted'] ?? 0);
            $evalUnstarted = (int)($pipeline['evaluation']['unstarted'] ?? 0);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Phase 1 Card: Target Commitment Phase -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border transition-all duration-200 <?= !empty($p1['is_ongoing']) ? 'border-emerald-500/80 dark:border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'border-zinc-200 dark:border-zinc-800 shadow-xs' ?>">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black shrink-0">1</span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">Target Commitment Phase</h3>
                            <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate"><?= esc($p1['timeframe']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2">
                        <?php if (!empty($p1['is_ongoing'])): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-[#102a1e] dark:text-emerald-400 border border-emerald-200 dark:border-[#1b4330] shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Ongoing
                            </span>
                        <?php elseif ($p1['status'] === 'closed'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700/80">
                                Closed
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-400 dark:bg-zinc-800/80 dark:text-zinc-500 border border-zinc-200/60 dark:border-zinc-700/60">
                                <?= esc($p1['label']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200" title="<?= $targetUnstarted > 0 ? "{$targetUnstarted} personnel pending cascade / unstarted" : '' ?>">
                        <span class="block text-xl font-black"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block"><?= $targetUnstarted > 0 ? 'Pending Cascade' : 'Draft (Missing)' ?></span>
                    </div>
                </div>
            </div>

            <!-- Phase 2 Card: Accomplishment Report Phase -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border transition-all duration-200 <?= !empty($p2['is_ongoing']) ? ($p2['status'] === 'calibration' ? 'border-purple-500/80 dark:border-purple-500 ring-2 ring-purple-500/20 shadow-md' : 'border-emerald-500/80 dark:border-emerald-500 ring-2 ring-emerald-500/20 shadow-md') : 'border-zinc-200 dark:border-zinc-800 shadow-xs' ?>">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black shrink-0">2</span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">Accomplishment Report Phase</h3>
                            <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate"><?= esc($p2['timeframe']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2">
                        <?php if ($p2['status'] === 'ongoing'): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-[#102a1e] dark:text-emerald-400 border border-emerald-200 dark:border-[#1b4330] shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Ongoing
                            </span>
                        <?php elseif ($p2['status'] === 'calibration'): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                                Calibration
                            </span>
                        <?php elseif ($p2['status'] === 'monitoring'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                Monitoring
                            </span>
                        <?php elseif ($p2['status'] === 'closed'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200/80 dark:border-zinc-700/80">
                                Closed
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-400 dark:bg-zinc-800/80 dark:text-zinc-500 border border-zinc-200/60 dark:border-zinc-700/60">
                                <?= esc($p2['label']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200" title="<?= $evalUnstarted > 0 ? "{$evalUnstarted} personnel pending cascade / unstarted" : '' ?>">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block"><?= $evalUnstarted > 0 ? 'Pending Cascade' : 'Draft (Missing)' ?></span>
                    </div>
                </div>
            </div>

        </div>


        <!-- 2.5 SPMS 4-STAGE PERFORMANCE LIFECYCLE (CSC MC No. 6, s. 2012) -->
        <?php
            // Dynamic Institutional Active Stage Determination (Synchronized with Folder Tracker / _admin_cycle_hub.php)
            $hubTargetStart = $activeCycle['ipcr_target_start'] ?? $activeCycle['opcr_target_start'] ?? $activeCycle['dpcr_target_start'] ?? $activeCycle['iperf_target_start'] ?? ($phaseSchedule['phase1']['start'] ?? null);
            $hubTargetEnd   = $activeCycle['ipcr_target_end']   ?? $activeCycle['opcr_target_end']   ?? $activeCycle['dpcr_target_end']   ?? $activeCycle['iperf_target_end']   ?? ($phaseSchedule['phase1']['end'] ?? null);
            $hubEvalStart   = $activeCycle['ipcr_eval_start']   ?? $activeCycle['opcr_eval_start']   ?? $activeCycle['dpcr_eval_start']   ?? $activeCycle['iperf_eval_start']   ?? ($phaseSchedule['phase2']['start'] ?? null);
            $hubEvalEnd     = $activeCycle['ipcr_eval_end']     ?? $activeCycle['opcr_eval_end']     ?? $activeCycle['dpcr_eval_end']     ?? $activeCycle['iperf_eval_end']     ?? ($phaseSchedule['phase2']['end'] ?? null);

            $nowStr = date('Y-m-d H:i:s');
            $isCycleArchived = !empty($isArchivedView) || !empty($activeCycle['is_archived']) || !empty($activeCycle['deleted_at']) || (($activeCycle['status'] ?? '') === 'archived');

            if ($isCycleArchived) {
                // Stage 4: Calibration & Archival (Frozen)
                $activeStageNum = 4;
                $activeStageName = 'Rewarding';
            } elseif (!empty($hubEvalEnd) && $nowStr > $hubEvalEnd) {
                // Stage 4: Calibration & Rewarding (Evaluation Window Concluded — PMT Ratings Calibration)
                $activeStageNum = 4;
                $activeStageName = 'Rewarding';
            } elseif (!empty($hubEvalStart) && $nowStr >= $hubEvalStart) {
                // Stage 3: Review & Evaluation (Accomplishment Phase Active)
                $activeStageNum = 3;
                $activeStageName = 'Review';
            } elseif (!empty($hubTargetEnd) && $nowStr > $hubTargetEnd) {
                // Stage 2: Performance Monitoring & Coaching (Execution & Evidence compiling)
                $activeStageNum = 2;
                $activeStageName = 'Coaching';
            } elseif (!empty($hubTargetStart) && $nowStr >= $hubTargetStart) {
                // Stage 1: Performance Planning & Commitment (Target formulation)
                $activeStageNum = 1;
                $activeStageName = 'Planning';
            } else {
                // Stage 1: Setup / Upcoming Target formulation
                $activeStageNum = 1;
                $activeStageName = 'Planning';
            }
            
            $lifecycleStages = [
                1 => 'Planning',
                2 => 'Coaching',
                3 => 'Review',
                4 => 'Rewarding',
            ];

            $isStage1Current = ($activeStageNum === 1);
            $isStage2Current = ($activeStageNum === 2);
            $isStage3Current = ($activeStageNum === 3);
            $isStage4Current = ($activeStageNum === 4);
        ?>
        <div class="p-4 sm:p-6 rounded-2xl shadow-xs spms-lifecycle-container">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white tracking-tight">SPMS 4-Stage Performance Lifecycle</h2>
                    <p class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">CSC MC No. 6, S. 2012 • Standard University Strategic Calibration Cycle</p>
                </div>

                <!-- DESKTOP VIEW (sm:flex): Full Stepper Flow (Capsule Pills) -->
                <div class="hidden sm:flex items-center gap-1.5 sm:gap-2 text-[11px] shrink-0">
                    <?php foreach ($lifecycleStages as $sNum => $sTitle): ?>
                        <?php if ($sNum > 1): ?>
                            <span class="w-2 sm:w-3 h-px <?= $sNum <= $activeStageNum ? 'bg-amber-500/60' : 'bg-zinc-300 dark:bg-zinc-700' ?> shrink-0"></span>
                        <?php endif; ?>

                        <?php if ($sNum === $activeStageNum): ?>
                            <div class="spms-stepper-pill-active inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold shrink-0 shadow-2xs">
                                <span class="w-4 h-4 rounded-full bg-amber-500 text-zinc-950 text-[9px] font-black flex items-center justify-center shrink-0"><?= $sNum ?></span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php elseif ($sNum < $activeStageNum): ?>
                            <div class="spms-stepper-pill-completed inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold shrink-0">
                                <span class="w-4 h-4 rounded-full bg-amber-500 text-zinc-950 text-[9px] font-bold flex items-center justify-center shrink-0">✓</span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="spms-stepper-pill-inactive inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold shrink-0">
                                <span class="w-4 h-4 rounded-full border border-zinc-300 dark:border-zinc-600 text-[9px] font-bold flex items-center justify-center shrink-0"><?= $sNum ?></span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 4 Stage Cards Grid on PC (4 Columns) / Stacked on Mobile -->
            <div class="spms-lifecycle-grid">
                
                <!-- STAGE 1: Target Commitment (Planning) -->
                <div class="p-3.5 sm:p-4 rounded-xl <?= $isStage1Current ? 'spms-stage-box-active' : 'spms-stage-box' ?> flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">STAGE 1</span>
                            <?php if ($isStage1Current): ?>
                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-extrabold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    CURRENT
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Target Commitment</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Target Setting Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= (int)($pipeline['stage1']['approved'] ?? 0) ?>
                                    <span class="text-zinc-400 dark:text-zinc-400 font-normal">/ <?= $totalPersonnel ?> Approved</span>
                                </span>
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= $totalPersonnel > 0 ? round(((int)($pipeline['stage1']['approved'] ?? 0) / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1 overflow-hidden">
                                <div class="bg-amber-500 h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(((int)($pipeline['stage1']['approved'] ?? 0) / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Approved Targets</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage1']['approved'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>In Review</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage1']['pending'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Needs Revision</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage1']['returned'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Draft Mode</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage1']['draft'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: Monitoring & Coaching -->
                <div class="p-3.5 sm:p-4 rounded-xl <?= $isStage2Current ? 'spms-stage-box-active' : 'spms-stage-box' ?> flex flex-col justify-between transition-all hover:border-zinc-300 dark:hover:border-zinc-600">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(2)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 2</span>
                                    <?php if ($isStage2Current): ?>
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-extrabold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            CURRENT
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <svg id="stage-2-chevron" class="w-4 h-4 text-zinc-400 dark:text-zinc-400 transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white mt-1.5">Monitoring &amp; Coaching</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Execution &amp; Evidence</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-2-content" class="<?= $isStage2Current ? '' : 'hidden' ?> pt-3 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Active Execution</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage2']['active_execution'] ?? $pipeline['stage1']['approved'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>MOV Attachments</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage2']['mov_count'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Coaching Feedback</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage2']['coaching_notes'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: Review & Evaluation -->
                <div class="p-3.5 sm:p-4 rounded-xl <?= $isStage3Current ? 'spms-stage-box-active' : 'spms-stage-box' ?> flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 3</span>
                            <?php if ($isStage3Current): ?>
                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-extrabold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    CURRENT
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Review &amp; Evaluation</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Accomplishment Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= (int)($pipeline['stage3']['completed'] ?? 0) ?>
                                    <span class="text-zinc-400 dark:text-zinc-400 font-normal">/ <?= $totalPersonnel ?> Evaluated</span>
                                </span>
                                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-400">
                                    <?= $totalPersonnel > 0 ? round(((int)($pipeline['stage3']['completed'] ?? 0) / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1 overflow-hidden">
                                <div class="bg-zinc-700 dark:bg-zinc-500 h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(((int)($pipeline['stage3']['completed'] ?? 0) / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Approved Ratings</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage3']['completed'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Under Evaluation</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage3']['evaluating'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Submitted Awaiting</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage3']['submitted'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Draft Accomplishment</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage3']['draft'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 4: Rewarding & Dev. -->
                <div class="p-3.5 sm:p-4 rounded-xl <?= $isStage4Current ? 'spms-stage-box-active' : 'spms-stage-box' ?> flex flex-col justify-between transition-all hover:border-zinc-300 dark:hover:border-zinc-600">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(4)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 4</span>
                                    <?php if ($isStage4Current): ?>
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-extrabold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            CURRENT
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <svg id="stage-4-chevron" class="w-4 h-4 text-zinc-400 dark:text-zinc-400 transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white mt-1.5">Rewarding &amp; Dev.</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Incentives &amp; HR Phase</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-4-content" class="<?= $isStage4Current ? '' : 'hidden' ?> pt-3 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>PBB / Bonus Eligible</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage4']['pbb_eligible'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Development Needed</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= (int)($pipeline['stage4']['dev_needed'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-sm">
                            ★
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white">Top Performing Personnel</h3>
                            <p class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400">Dean &amp; Institutional Honor Roster (Ratings ≥ 4.50)</p>
                        </div>
                    </div>
                    <?php
                        $adminPerformerCounts = [
                            'all'               => count($topPerformers),
                            'outstanding'       => 0,
                            'very_satisfactory' => 0,
                            'satisfactory'      => 0,
                            'unsatisfactory'    => 0,
                            'poor'              => 0,
                        ];
                        foreach ($topPerformers as $tp) {
                            $sVal = (float)($tp['rating_num'] ?? 0);
                            if ($sVal >= 4.50) {
                                $adminPerformerCounts['outstanding']++;
                            } elseif ($sVal >= 3.50) {
                                $adminPerformerCounts['very_satisfactory']++;
                            } elseif ($sVal >= 2.50) {
                                $adminPerformerCounts['satisfactory']++;
                            } elseif ($sVal >= 1.50) {
                                $adminPerformerCounts['unsatisfactory']++;
                            } elseif ($sVal > 0) {
                                $adminPerformerCounts['poor']++;
                            }
                        }
                    ?>
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                        <!-- Adjectival Rating Filter Pills -->
                        <div class="performer-tier-pills-container flex items-center gap-1.5 overflow-x-auto custom-scrollbar py-0.5">
                            <button type="button" onclick="filterTopPerformersByTier('all', 'admin-performers-grid', this)"
                                    data-tier="all"
                                    class="performer-tier-pill active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shrink-0 bg-zinc-900 text-white dark:bg-zinc-800 dark:text-white border border-zinc-900 dark:border-zinc-600 shadow-2xs">
                                <span>All (<?= $adminPerformerCounts['all'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('outstanding', 'admin-performers-grid', this)"
                                    data-tier="outstanding"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Outstanding (<?= $adminPerformerCounts['outstanding'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('very_satisfactory', 'admin-performers-grid', this)"
                                    data-tier="very_satisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                <span>Very Satisfactory (<?= $adminPerformerCounts['very_satisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('satisfactory', 'admin-performers-grid', this)"
                                    data-tier="satisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                <span>Satisfactory (<?= $adminPerformerCounts['satisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('unsatisfactory', 'admin-performers-grid', this)"
                                    data-tier="unsatisfactory"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                                <span>Unsatisfactory (<?= $adminPerformerCounts['unsatisfactory'] ?>)</span>
                            </button>
                            <button type="button" onclick="filterTopPerformersByTier('poor', 'admin-performers-grid', this)"
                                    data-tier="poor"
                                    class="performer-tier-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                <span>Poor (<?= $adminPerformerCounts['poor'] ?>)</span>
                            </button>
                        </div>

                        <!-- Sort Filter (Top to Least / Least to Top) -->
                        <div class="relative shrink-0">
                            <select id="admin-top-performers-sort" onchange="sortTopPerformers(this.value, 'admin-performers-grid')"
                                    class="appearance-none bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg pl-2.5 pr-7 py-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-200 outline-none cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors shadow-2xs">
                                <option value="desc">Highest to Lowest</option>
                                <option value="asc">Lowest to Highest</option>
                            </select>
                            <svg class="w-3 h-3 text-zinc-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <?php if (!empty($topPerformers)): ?>
                    <div id="admin-performers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[380px] overflow-y-auto custom-scrollbar pr-1">
                        <?php foreach ($topPerformers as $idx => $tp): 
                            $sVal = (float)($tp['rating_num'] ?? 0);
                            $tKey = ($sVal >= 4.50) ? 'outstanding' :
                                    (($sVal >= 3.50) ? 'very_satisfactory' :
                                    (($sVal >= 2.50) ? 'satisfactory' :
                                    (($sVal >= 1.50) ? 'unsatisfactory' : 'poor')));
                        ?>
                            <div class="performer-card p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3 transition-colors hover:bg-zinc-100/70 dark:hover:bg-zinc-800/40 cursor-pointer"
                                 data-rating="<?= $sVal ?>"
                                 data-adjectival-tier="<?= $tKey ?>"
                                 data-initial-index="<?= $idx ?>"
                                 onclick="openMasterlistDocument('<?= !empty($tp['folder_id']) ? site_url('document/' . $tp['folder_id'] . '?from=dashboard') : '' ?>', event)"
                                 title="Click to view evaluation document">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Rank Badge -->
                                    <span class="performer-rank-badge w-8 h-8 rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        <?= $idx + 1 ?>
                                    </span>

                                    <!-- User Details -->
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-xs sm:text-sm text-zinc-900 dark:text-white truncate">
                                                <?= esc($tp['full_name'] ?? 'Personnel') ?>
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider <?= ($tp['is_teaching'] == 1) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'bg-zinc-200/80 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' ?>">
                                                <?= ($tp['is_teaching'] == 1) ? 'Faculty' : 'Staff' ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                            <?= esc($tp['department'] ?? 'Department') ?> &bull; <span class="text-zinc-400 dark:text-zinc-500"><?= esc($tp['position'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rating & Badge -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <div class="text-sm font-black text-zinc-900 dark:text-white tabular-nums">
                                            <?= number_format($tp['rating_num'], 2) ?>
                                        </div>
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8.5px] font-extrabold uppercase tracking-wider border <?= $tp['adjectival_badge'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                                            <?= esc($tp['adjectival_label'] ?? 'Outstanding') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="performer-tier-empty col-span-full py-8 text-center text-zinc-400 dark:text-zinc-500 italic text-xs hidden">
                            No personnel discovered in this rating tier.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="py-12 px-4 text-center rounded-xl bg-zinc-50/50 dark:bg-zinc-950/40 border border-dashed border-zinc-200 dark:border-zinc-800">
                        <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-500 mx-auto flex items-center justify-center mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Evaluations In Progress</h4>
                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500 max-w-xs mx-auto mt-1">
                            Top performing personnel will populate automatically as soon as accomplishment ratings are finalized and approved.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. DEPARTMENT & UNIT COMPLIANCE MATRIX -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white">Department &amp; Unit Compliance</h3>
                    <p class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Submission compliance, coaching adjustments, and average ratings across all institutional units.
                    </p>
                </div>

                <!-- Search Bar for Departments & Units -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="office-table-search" oninput="filterOfficeLeaderboard(this.value)"
                           placeholder="Search department or unit..."
                           class="w-full text-xs font-medium py-2 pl-9 pr-3 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 shadow-2xs" />
                </div>
            </div>

            <!-- Department & Unit Table -->
            <div class="overflow-x-auto custom-scrollbar rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left border-collapse" id="office-leaderboard-table">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 text-[10px] sm:text-[11px] font-bold uppercase text-zinc-500 dark:text-zinc-400 tracking-wider">
                            <th class="py-3 px-3.5">Department / Unit</th>
                            <th class="py-3 px-3 text-center">Headcount</th>
                            <th class="py-3 px-3 text-center">Phase 1: Targets</th>
                            <th class="py-3 px-3 text-center">Phase 2: Accomplishments</th>
                            <th class="py-3 px-3 text-center">Average Rating</th>
                            <th class="py-3 px-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-[#1a2b22] text-xs">
                        <?php 
                        $unitsToRender = !empty($hierarchicalUnits) ? $hierarchicalUnits : (!empty($deptLeaderboard) ? $deptLeaderboard : []);
                        ?>
                        <?php if (empty($unitsToRender)): ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-zinc-400 italic">
                                    No department / unit records available for this cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unitsToRender as $hu): ?>
                                <?php
                                    $isTree = isset($hu['level']);
                                    $level = $hu['level'] ?? 0;
                                    $uId = $hu['id'] ?? '';
                                    $uName = $hu['name'] ?? '';
                                    $parentId = $hu['parent_id'] ?? 0;
                                    $headcount = $hu['headcount'] ?? 0;
                                    $directHeadcount = $hu['direct_headcount'] ?? $headcount;
                                    $targetApproved = $hu['target_approved'] ?? 0;
                                    $evalCompleted = $hu['eval_completed'] ?? 0;
                                    $tPct = $headcount > 0 ? round(($targetApproved / $headcount) * 100) : 0;
                                    $ePct = $headcount > 0 ? round(($evalCompleted / $headcount) * 100) : 0;
                                    $avgRating = $hu['average_rating'] ?? 0;
                                    $descendantIds = !empty($hu['descendant_ids']) ? implode(',', $hu['descendant_ids']) : (string)$uId;
                                    $ancestorIds = !empty($hu['ancestor_ids']) ? implode(',', $hu['ancestor_ids']) : '';
                                    $hasKids = !empty($hu['has_children']);
                                    $kidsCount = count($hu['children_ids'] ?? []);
                                ?>
                                <tr class="compliance-unit-row hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors <?= $hasKids ? 'cursor-pointer' : '' ?>"
                                    data-unit-id="<?= esc($uId) ?>"
                                    data-name="<?= esc(strtolower($uName)) ?>"
                                    data-parent-id="<?= esc($parentId) ?>"
                                    data-level="<?= $level ?>"
                                    data-has-children="<?= $hasKids ? '1' : '0' ?>"
                                    data-descendant-ids="<?= esc($descendantIds) ?>"
                                    data-ancestor-ids="<?= esc($ancestorIds) ?>"
                                    data-expanded="false"
                                    <?= $hasKids ? 'onclick="toggleUnitHierarchy(\'' . esc($uId) . '\', event)"' : '' ?>
                                    style="<?= $level === 0 ? '' : 'display: none;' ?>">
                                    <td class="py-3 px-3.5 whitespace-nowrap" style="padding-left: <?= ($level > 0 ? ($level * 1.75 + 0.875) : 0.875) ?>rem;">
                                        <div class="flex items-center gap-2">
                                            <?php if ($hasKids): ?>
                                                <button type="button" 
                                                        onclick="event.stopPropagation(); toggleUnitHierarchy('<?= esc($uId) ?>', event)"
                                                        class="compliance-dropdown-btn inline-flex items-center justify-center w-6 h-6 rounded-md text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shrink-0"
                                                        title="Click to toggle <?= $kidsCount ?> sub-units">
                                                    <svg class="w-3.5 h-3.5 transition-transform duration-200 compliance-chevron text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <span class="w-6 shrink-0"></span>
                                            <?php endif; ?>

                                            <?php if ($level === 0): ?>
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <span class="font-bold text-zinc-900 dark:text-white truncate block max-w-[280px]" title="<?= esc($uName) ?>">
                                                    <?= esc($uName) ?>
                                                </span>
                                                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shrink-0">Executive Sector</span>
                                            <?php elseif ($hasKids): ?>
                                                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                                <span class="font-bold text-zinc-900 dark:text-white truncate block max-w-[260px]" title="<?= esc($uName) ?>">
                                                    <?= esc($uName) ?>
                                                </span>
                                                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shrink-0">College</span>
                                            <?php elseif ($level === 1): ?>
                                                <span class="w-2 h-2 rounded-full bg-zinc-400 shrink-0"></span>
                                                <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate block max-w-[260px]" title="<?= esc($uName) ?>">
                                                    <?= esc($uName) ?>
                                                </span>
                                                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-zinc-500/10 text-zinc-500 dark:text-zinc-400 border border-zinc-500/20 shrink-0">Office</span>
                                            <?php else: ?>
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                                                <span class="font-medium text-zinc-700 dark:text-zinc-300 truncate block max-w-[240px]" title="<?= esc($uName) ?>">
                                                    <?= esc($uName) ?>
                                                </span>
                                                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0">Program</span>
                                            <?php endif; ?>

                                            <?php if ($hasKids): ?>
                                                <span class="inline-flex items-center text-[10px] font-bold text-zinc-400 dark:text-zinc-500 bg-zinc-100 dark:bg-zinc-800/80 px-1.5 py-0.5 rounded shrink-0">
                                                    <?= $kidsCount ?> <?= $kidsCount === 1 ? 'sub-unit' : 'sub-units' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-zinc-900 dark:text-white">
                                        <?= $headcount ?>
                                        <?php if ($directHeadcount > 0 && $directHeadcount !== $headcount): ?>
                                            <span class="text-[10px] font-normal text-zinc-400 block" title="Direct personnel: <?= $directHeadcount ?>, Branch total: <?= $headcount ?>">(<?= $directHeadcount ?> direct)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= min(100, $tPct) ?>%;"></div>
                                            </div>
                                            <span class="font-bold text-[11px] text-zinc-900 dark:text-white"><?= $targetApproved ?>/<?= $headcount ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 dark:bg-indigo-500 h-1.5 rounded-full" style="width: <?= min(100, $ePct) ?>%;"></div>
                                            </div>
                                            <span class="font-bold text-[11px] text-zinc-900 dark:text-white"><?= $evalCompleted ?>/<?= $headcount ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap font-black text-zinc-900 dark:text-white">
                                        <?= $avgRating > 0 ? number_format($avgRating, 2) : '——' ?>
                                    </td>
                                    <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                        <button type="button" onclick="event.stopPropagation(); filterMasterlistByUnit('<?= esc($uId) ?>', '<?= esc(addslashes($uName)) ?>')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-amber-500 hover:text-amber-400 dark:text-amber-400 border border-amber-500/30 hover:bg-amber-500/10 transition-all cursor-pointer shadow-2xs group"
                                                title="Filter Master List by <?= esc($uName) ?>">
                                            <span>View in Master List</span>
                                            <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="compliance-empty-row" class="hidden">
                            <td colspan="6" class="py-8 px-4 text-center text-zinc-400 italic">
                                No departments or units match the selected hierarchy filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>
    </div>
    <!-- /END OF #dashboard-view-analytics -->

    <!-- 2. RATINGS MASTERLIST / SUBMISSION ROSTER VIEW -->
    <div id="dashboard-view-masterlist" class="hidden flex-1 min-h-0 flex flex-col lg:flex-row bg-surface overflow-hidden relative">
        <!-- MOBILE FILTER OVERLAY -->
        <div id="masterlist-filter-overlay" onclick="toggleMasterlistFilterSidebar()" class="fixed inset-0 bg-black/50 z-[125] hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

        <!-- MAIN RESULTS (Left Column on Desktop) -->
                <div class="flex-1 flex flex-col h-full min-w-0 bg-surface relative">

                    <!-- Mobile Filter Toggle Bar (Visible only on < lg) -->
                    <div class="lg:hidden p-4 bg-surface border-b border-surface-border shrink-0 flex items-center justify-between">
                        <span class="text-xs font-bold text-text">
                            Master List 
                            <span class="text-text-muted font-normal">(<span id="masterlist-mobile-records-count"><?= count($cycleFolders) ?></span> ratees)</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleMasterlistFilterSidebar()" class="flex items-center gap-1.5 text-xs font-bold text-accent bg-accent/10 px-3 py-1.5 rounded-lg active:scale-95 transition-transform cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                <span>Filters</span>
                                <span id="mobile-masterlist-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[9px] font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">0</span>
                            </button>
                            <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-200 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all shadow-xs cursor-pointer"
                               title="Export Excel workbook">
                                <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Export</span>
                            </a>
                        </div>
                    </div>

                    <!-- Header & Lifecycle Tabs (Desktop & Tablet) -->
                    <div class="p-4 sm:p-5 border-b border-surface-border bg-surface shrink-0 flex flex-col gap-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        CSC MC NO. 6, S. 2012 PRESCRIBED
                                    </span>
                                    <span class="text-xs font-semibold text-text-muted truncate">
                                        <?= esc($selectedUnitName ?? ($supervisorCollegeName ?? (!empty($isChairScope) ? 'Department Roster' : 'University-Wide Roster'))) ?>
                                    </span>
                                </div>
                                <h2 class="text-xl lg:text-2xl font-black text-text tracking-tight truncate">
                                    Master List
                                </h2>
                            </div>

                            <div class="hidden sm:flex items-center gap-2 shrink-0">
                                <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#114232] hover:bg-[#16533f] border border-[#1b5e47] transition-all shadow-xs cursor-pointer"
                                   title="Export official CSC Excel workbook">
                                    <svg class="w-3.5 h-3.5 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Export Master List (.xlsx)</span>
                                </a>
                            </div>
                        </div>

                        <!-- Lifecycle Tabs -->
                        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pt-0.5" id="masterlist-lifecycle-pills">
                            <button type="button" onclick="setMasterlistLifecyclePill('all', this)"
                                    data-lifecycle="all"
                                    class="masterlist-tab-btn masterlist-lifecycle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shrink-0">
                                <span>All Personnel (<?= $stageCounts['all'] ?? count($cycleFolders) ?>)</span>
                            </button>
                            <button type="button" onclick="setMasterlistLifecyclePill('target_phase', this)"
                                    data-lifecycle="target_phase"
                                    class="masterlist-tab-btn masterlist-lifecycle-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                <span>Target Phase in Progress (<?= $stageCounts['target_phase'] ?? 0 ?>)</span>
                            </button>
                            <button type="button" onclick="setMasterlistLifecyclePill('eval_phase', this)"
                                    data-lifecycle="eval_phase"
                                    class="masterlist-tab-btn masterlist-lifecycle-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                <span>Evaluation Phase in Progress (<?= $stageCounts['eval_phase'] ?? 0 ?>)</span>
                            </button>
                            <button type="button" onclick="setMasterlistLifecyclePill('completed', this)"
                                    data-lifecycle="completed"
                                    class="masterlist-tab-btn masterlist-lifecycle-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer shrink-0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Cycle Completed / Finalized (<?= $stageCounts['completed'] ?? 0 ?>)</span>
                            </button>
                        </div>
                    </div>

                    <!-- MOBILE CARDS LIST (< md) -->
                    <div class="block md:hidden p-3.5 space-y-2.5 overflow-y-auto max-h-[600px] custom-scrollbar" id="masterlist-mobile-cards-list">
                        <?php if (empty($cycleFolders)): ?>
                            <div class="py-10 text-center text-text-muted italic text-xs">
                                No personnel records discovered for this evaluation period.
                            </div>
                        <?php else: ?>
                            <?php foreach ($cycleFolders as $idx => $f): ?>
                                <?php $sType = $f['staff_type'] ?? \App\Enums\StaffType::fromBoolean($f['is_teaching'] ?? 0); ?>
                                <div class="masterlist-row-card p-3 rounded-xl bg-surface border border-surface-border shadow-xs space-y-2 transition-all cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-600"
                                     data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                     data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                     data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                     data-unit-id="<?= esc($f['unit_id'] ?? '') ?>"
                                     data-ancestor-unit-ids="<?= esc(implode(',', $f['ancestor_unit_ids'] ?? [])) ?>"
                                     data-dept-chain="<?= esc(strtolower(($f['department'] ?? '') . ' ' . implode(' ', $f['ancestor_unit_names'] ?? []))) ?>"
                                     data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                     data-staff-type="<?= $sType->value ?>"
                                     data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>"
                                     data-lifecycle-stage="<?= esc($f['lifecycle_stage'] ?? 'target_phase') ?>"
                                     data-rating="<?= esc($f['rating_num'] ?? '') ?>"
                                     data-adjectival="<?= esc(strtolower($f['adjectival_display'] ?? $f['adjectival_label'] ?? '')) ?>"
                                     onclick="openMasterlistDocument('<?= !empty($f['folder_id']) ? site_url('document/' . $f['folder_id'] . '?from=dashboard') : '' ?>', event)"
                                     title="Click to view evaluation document">
                                    
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs text-text truncate">
                                                    <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                                </span>
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8.5px] font-black uppercase tracking-wider shrink-0 <?= $sType->badgeClass() ?>">
                                                    <?= $sType->label() ?>
                                                </span>
                                            </div>
                                            <div class="text-[10px] font-medium text-text-muted truncate mt-0.5">
                                                <?= esc($f['position'] ?? '—') ?>
                                            </div>
                                            <div class="text-[10px] text-text-muted/80 truncate">
                                                <?= esc($f['department'] ?? '—') ?> • <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0">
                                            <?= esc($f['employment_status'] ?? 'Permanent') ?>
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-surface-border/50">
                                        <div>
                                            <span class="text-text-muted text-[10px] block">P1 (Targets):</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border <?= $f['target_badge'] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                                <?= esc($f['target_label'] ?? 'Draft') ?>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-text-muted text-[10px] block">P2 (Accomplishments):</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border <?= $f['eval_badge'] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                                <?= esc($f['eval_label'] ?? 'Pending') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="pt-1.5 border-t border-surface-border/50 flex items-center justify-between text-[11px]">
                                        <span class="text-text-muted">Final Rating:</span>
                                        <?php if ($f['rating_num'] !== null && (float)$f['rating_num'] > 0): ?>
                                            <span class="font-black text-text"><?= number_format($f['rating_num'], 2) ?> (<?= esc($f['adjectival_display'] ?? $f['adjectival_label'] ?? '—') ?>)</span>
                                        <?php else: ?>
                                            <span class="text-text-muted italic">—</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div id="masterlist-mobile-empty" class="hidden py-8 text-center text-xs text-text-muted">
                            No personnel records found matching filters.
                        </div>
                    </div>

                    <!-- DESKTOP TABLE VIEW (hidden md:block) -->
                    <div class="hidden md:block flex-1 overflow-auto custom-scrollbar min-h-0">
                        <table class="w-full text-left border-collapse table-auto" id="masterlist-table">
                            <thead class="bg-surface dark:bg-zinc-800/40 border-b border-surface-border text-[10px] font-black uppercase tracking-widest text-text-muted sticky top-0 z-10">
                                <tr>
                                    <th class="py-2.5 px-4 w-12 text-center">#</th>
                                    <th class="py-2.5 px-4 min-w-[220px]">Personnel / Ratee</th>
                                    <th class="py-2.5 px-4 text-center whitespace-nowrap min-w-[130px]">Employment Status</th>
                                    <th class="py-2.5 px-4 text-center whitespace-nowrap min-w-[140px]">Phase 1: Targets</th>
                                    <th class="py-2.5 px-4 text-center whitespace-nowrap min-w-[140px]">Phase 2: Accomplishments</th>
                                    <th class="py-2.5 px-4 text-center whitespace-nowrap min-w-[120px]">Final Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-border text-xs" id="masterlist-table-body">
                                <?php if (empty($cycleFolders)): ?>
                                    <tr>
                                        <td colspan="6" class="py-12 px-4 text-center text-text-muted italic">
                                            No personnel records discovered for this evaluation period.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cycleFolders as $idx => $f): ?>
                                        <?php $sType = $f['staff_type'] ?? \App\Enums\StaffType::fromBoolean($f['is_teaching'] ?? 0); ?>
                                        <tr class="masterlist-row cursor-pointer hover:bg-zinc-100/70 dark:hover:bg-zinc-800/50 transition-colors group"
                                            data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                            data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                            data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                            data-unit-id="<?= esc($f['unit_id'] ?? '') ?>"
                                            data-ancestor-unit-ids="<?= esc(implode(',', $f['ancestor_unit_ids'] ?? [])) ?>"
                                            data-dept-chain="<?= esc(strtolower(($f['department'] ?? '') . ' ' . implode(' ', $f['ancestor_unit_names'] ?? []))) ?>"
                                            data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                            data-staff-type="<?= $sType->value ?>"
                                            data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>"
                                            data-lifecycle-stage="<?= esc($f['lifecycle_stage'] ?? 'target_phase') ?>"
                                            data-rating="<?= esc($f['rating_num'] ?? '') ?>"
                                            data-adjectival="<?= esc(strtolower($f['adjectival_display'] ?? $f['adjectival_label'] ?? '')) ?>"
                                            onclick="openMasterlistDocument('<?= !empty($f['folder_id']) ? site_url('document/' . $f['folder_id'] . '?from=dashboard') : '' ?>', event)"
                                            title="Click to view evaluation document">
                                            
                                            <!-- Index -->
                                            <td class="py-3 px-4 text-center font-bold text-text-muted w-12 masterlist-row-idx">
                                                <?= $idx + 1 ?>
                                            </td>

                                            <!-- Personnel / Ratee: Avatar + Name + StaffType badge, Position, Department • Email beneath -->
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-surface-border flex items-center justify-center font-bold text-xs text-text shrink-0">
                                                        <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                                    </div>
                                                    <div class="min-w-0 flex flex-col">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold text-text truncate">
                                                                <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                                            </span>
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider shrink-0 <?= $sType->badgeClass() ?>">
                                                                <?= $sType->label() ?>
                                                            </span>
                                                        </div>
                                                        <div class="text-[11px] font-medium text-text-muted truncate mt-0.5" title="<?= esc($f['position'] ?? '—') ?>">
                                                            <?= esc($f['position'] ?? '—') ?>
                                                        </div>
                                                        <div class="text-[10px] text-text-muted/80 truncate" title="<?= esc($f['department'] ?? '—') ?> • <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>">
                                                            <?= esc($f['department'] ?? '—') ?> • <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Employment Status -->
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    <?= esc($f['employment_status'] ?? 'Permanent') ?>
                                                </span>
                                            </td>

                                            <!-- Phase 1: Targets -->
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $f['target_badge'] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                                    <?= esc($f['target_label'] ?? 'Draft') ?>
                                                </span>
                                            </td>

                                            <!-- Phase 2: Accomplishments -->
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $f['eval_badge'] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                                    <?= esc($f['eval_label'] ?? 'Pending Target Phase') ?>
                                                </span>
                                            </td>

                                            <!-- Final Rating -->
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                <?php if ($f['rating_num'] !== null && (float)$f['rating_num'] > 0): ?>
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <span class="font-black text-xs text-text tabular-nums tracking-tight">
                                                            <?= number_format($f['rating_num'], 2) ?>
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border <?= $f['adjectival_badge'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                                                            <?= esc($f['adjectival_display'] ?? $f['adjectival_label'] ?? '—') ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-text-muted text-xs font-normal italic">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <tr id="masterlist-empty-row" class="hidden">
                                    <td colspan="6" class="py-12 px-4 text-center">
                                        <div class="flex flex-col items-center justify-center text-text-muted">
                                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <p class="text-xs font-semibold">No personnel records found matching your filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer (Desktop) -->
                    <div class="px-5 py-3 border-t border-surface-border bg-surface flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                            <span class="text-text-muted">
                                Showing <span id="masterlist-visible-range" class="font-bold text-text">0</span> of <span id="masterlist-visible-total" class="font-bold text-text"><?= count($cycleFolders) ?></span> personnel
                            </span>
                            
                            <div class="flex items-center gap-1.5 text-text-muted">
                                <span class="hidden sm:inline text-[11px] font-medium">Rows:</span>
                                <select id="masterlist-per-page" onchange="changeMasterlistPerPage(this.value)"
                                        class="text-[11px] font-bold px-2 py-1 rounded-lg bg-surface border border-surface-border text-text focus:outline-none cursor-pointer shadow-xs">
                                    <option value="25">25 / page</option>
                                    <option value="50" selected>50 / page</option>
                                    <option value="100">100 / page</option>
                                    <option value="all">Show All</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pagination Button Controls -->
                        <div class="flex items-center gap-1 w-full sm:w-auto justify-center sm:justify-end" id="masterlist-pagination-controls">
                        </div>
                    </div>
                </div>

        <!-- SIDEBAR FILTERS (Right Column on Desktop) -->
        <div id="masterlist-filter-sidebar" class="
            fixed inset-y-0 right-0 z-[130] lg:z-0 w-80 transform translate-x-full transition-transform duration-300 flex flex-col h-full bg-surface border-l border-surface-border shadow-2xl
            lg:static lg:w-64 xl:w-72 lg:translate-x-0 lg:transition-none lg:shadow-none lg:border-l lg:border-r-0 border-surface-border lg:flex lg:flex-col shrink-0
        ">
            <!-- Header -->
            <div style="height: 54px; min-height: 54px; max-height: 54px;" class="h-[54px] min-h-[54px] max-h-[54px] px-5 border-b border-surface-border flex justify-between items-center shrink-0 bg-surface box-border">
                <div class="flex items-center gap-2">
                    <h2 class="text-[10px] font-black text-text-muted uppercase tracking-widest leading-none">Filters</h2>
                    <span id="active-masterlist-filter-badge" class="hidden inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[9px] font-black leading-none bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">0</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="reset-masterlist-filters-btn" onclick="clearAllMasterlistFilters()" class="text-[10px] font-bold text-text-muted hover:text-text transition-colors cursor-pointer leading-none hidden">
                        Reset all
                    </button>
                    <button type="button" onclick="toggleMasterlistFilterSidebar()" class="lg:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            
            <!-- Scrollable Filters List -->
            <div class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-3.5 bg-surface">
                        <!-- Search -->
                        <div class="shrink-0 pb-3 border-b border-surface-border/50">
                            <div class="relative">
                                <input type="text" id="masterlist-search-sidebar" oninput="if (document.getElementById('masterlist-search-mobile')) document.getElementById('masterlist-search-mobile').value = this.value; filterMasterlist(true);" placeholder="Search employee name..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-xl pl-8 pr-3 py-2 text-xs focus:border-accent outline-none transition-all text-text font-bold shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 absolute left-2.5 top-2.5 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Staff Type Filter -->
                        <div class="shrink-0 pb-3 border-b border-surface-border/50">
                            <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-2.5 group cursor-pointer" onclick="
                                this.nextElementSibling.classList.toggle('hidden');
                                this.querySelector('svg').classList.toggle('-rotate-180');
                            ">
                                <span>Staff Type</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                                    <input type="checkbox" name="masterlist_filter_type[]" value="teaching" onchange="filterMasterlist(true)" class="checkbox masterlist-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                    <span>Teaching</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                                    <input type="checkbox" name="masterlist_filter_type[]" value="non_teaching" onchange="filterMasterlist(true)" class="checkbox masterlist-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                    <span>Non-Teaching</span>
                                </label>
                            </div>
                        </div>

                        <!-- Employment Status Filter (dynamic from DB) -->
                        <div class="shrink-0 pb-3 border-b border-surface-border/50">
                            <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-2.5 group cursor-pointer" onclick="
                                this.nextElementSibling.classList.toggle('hidden');
                                this.querySelector('svg').classList.toggle('-rotate-180');
                            ">
                                <span>Employment Status</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="space-y-1">
                                <?php if (!empty($employmentStatuses)): ?>
                                    <?php foreach ($employmentStatuses as $es): 
                                        $stName = is_array($es) ? ($es['name'] ?? '') : (string)$es;
                                    ?>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors">
                                            <input type="checkbox" name="masterlist_filter_emp[]" value="<?= strtolower(esc($stName)) ?>" onchange="filterMasterlist(true)" class="checkbox masterlist-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                            <span><?= esc($stName) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-xs text-text-muted italic">No employment statuses found.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Dynamic Cascading Unit / Department Filter (Supervisor Style) -->
                        <div id="dynamic-unit-filter-container" class="shrink-0 pb-4 border-b border-surface-border/50 space-y-3">
                            <!-- Populated dynamically by JavaScript -->
                        </div>

                        <!-- Positions Filter with mini-search -->
                        <div class="shrink-0 pb-2">
                            <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text focus:outline-none mb-2.5 group cursor-pointer" onclick="
                                this.nextElementSibling.classList.toggle('hidden');
                                this.querySelector('svg').classList.toggle('-rotate-180');
                            ">
                                <span>Positions</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="flex flex-col">
                                <div class="relative mb-2.5 shrink-0">
                                    <input type="text" id="mini-search-positions" oninput="filterMasterlistPositions(this.value)" placeholder="Find position..." class="w-full bg-white dark:bg-zinc-900 border border-surface-border rounded-lg pl-7 pr-2 py-1.5 text-[10px] focus:border-accent outline-none text-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 absolute left-2.5 top-2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <div class="space-y-1 pr-1 max-h-48 overflow-y-auto custom-scrollbar" id="positions-checkbox-list">
                                    <?php if (!empty($filterPositions)): ?>
                                        <?php foreach ($filterPositions as $pos): ?>
                                            <label class="position-filter-label flex items-center gap-2 cursor-pointer text-xs text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 p-1 rounded-md transition-colors" data-title="<?= strtolower(esc($pos)) ?>">
                                                <input type="checkbox" name="masterlist_filter_pos[]" value="<?= esc($pos) ?>" onchange="filterMasterlist(true)" class="checkbox masterlist-filter-checkbox rounded border-surface-border text-accent focus:ring-accent cursor-pointer shrink-0">
                                                <span class="truncate" title="<?= esc($pos) ?>"><?= esc($pos) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-xs text-text-muted italic">No positions found.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <div class="p-3.5 border-t border-surface-border shrink-0 bg-surface">
                        <button type="button" id="clear-masterlist-filters" onclick="clearAllMasterlistFilters()" class="w-full py-1.5 text-xs font-bold text-text-muted hover:text-text border border-surface-border rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                            Clear All Filters
                        </button>
                    </div>
                </div>
    </div><!-- /END OF #dashboard-view-masterlist -->
</div>

<script>
// --- GLOBAL CONFIG & UNIT HIERARCHY FOR MASTERLIST DYNAMIC FILTERING ---
window.SPMS_USER_ROLE = '<?= esc($sysRole ?? 'Admin') ?>';
window.SPMS_SCOPED_UNIT_IDS = <?= json_encode(array_values(array_map('intval', $scopedUnitIds ?? []))) ?>;
window.SPMS_SUPERVISOR_COLLEGE = '<?= esc($supervisorCollegeName ?? '') ?>';
window.SPMS_MASTERLIST_UNITS = <?= json_encode(array_map(fn($u) => [
    'id' => (int)$u['id'],
    'name' => $u['name'],
    'parent_id' => !empty($u['parent_id']) ? (int)$u['parent_id'] : 0
], $allUnitsList ?? $allUnits ?? [])) ?>;

let currentMasterlistLifecycle = 'all';
let masterlistCurrentPage = 1;
let masterlistPerPage = (typeof window !== 'undefined' && window.innerWidth < 768) ? 6 : 50;
window._activeMasterlistUnitFilterId = null;

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

const masterlistUnitParents = {};
const masterlistUnitChildren = {};

function initMasterlistUnitTree() {
    const units = window.SPMS_MASTERLIST_UNITS || [];
    Object.keys(masterlistUnitParents).forEach(k => delete masterlistUnitParents[k]);
    Object.keys(masterlistUnitChildren).forEach(k => delete masterlistUnitChildren[k]);

    units.forEach(u => {
        const uId = String(u.id);
        const pId = u.parent_id ? String(u.parent_id) : null;
        masterlistUnitParents[uId] = pId;
        if (pId) {
            if (!masterlistUnitChildren[pId]) masterlistUnitChildren[pId] = [];
            masterlistUnitChildren[pId].push(uId);
        }
    });
}
initMasterlistUnitTree();

function getDescendantUnitIds(rootId) {
    if (Object.keys(masterlistUnitChildren).length === 0) {
        initMasterlistUnitTree();
    }
    const rId = String(rootId);
    const result = new Set([rId]);
    const stack = [rId];
    while (stack.length) {
        const current = stack.pop();
        (masterlistUnitChildren[current] || []).forEach(childId => {
            if (!result.has(childId)) {
                result.add(childId);
                stack.push(childId);
            }
        });
    }
    return result;
}

const DYNAMIC_UNIT_THEME = {
    labelColor: 'text-text-muted',
    focusRing: 'focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20',
    activeBorder: 'border-amber-500 dark:border-amber-400',
    connector: 'border-zinc-300 dark:border-zinc-700'
};

function getMasterlistLevelTitle(level, parentUnit) {
    if (level === 1) {
        return 'DEPARTMENT / UNIT';
    }
    return 'SUB-UNIT';
}

function getMasterlistDefaultOptionLabel(level, parentUnit) {
    if (level === 1) {
        return 'All Departments &amp; Units';
    }
    return 'All Sub-Units';
}

function renderDynamicUnitLevel(level, parentUnitId, targetContainer) {
    if (!targetContainer) return;
    const wasCollapsed = level === 1 && Boolean(targetContainer.querySelector(':scope > .dynamic-unit-level > .js-unit-level-body.hidden'));
    targetContainer.innerHTML = '';

    const units = window.SPMS_MASTERLIST_UNITS || [];
    const unitsById = {};
    units.forEach(u => { unitsById[String(u.id)] = u; });

    let availableUnits = [];
    let parentUnit = null;

    if (level === 1) {
        if (window.SPMS_USER_ROLE === 'Admin' || window.SPMS_USER_ROLE === 'PMT' || window.SPMS_USER_ROLE === 'President' || (window.SPMS_SCOPED_UNIT_IDS || []).length === 0) {
            availableUnits = units.filter(u => !u.parent_id || !unitsById[String(u.parent_id)]);
        } else {
            const scopedIds = new Set((window.SPMS_SCOPED_UNIT_IDS || []).map(String));
            availableUnits = units.filter(u => scopedIds.has(String(u.id)) && (!u.parent_id || !scopedIds.has(String(u.parent_id))));
            if (availableUnits.length === 0) {
                availableUnits = units.filter(u => scopedIds.has(String(u.id)));
            }
        }
    } else {
        const numParentId = parseInt(parentUnitId, 10);
        parentUnit = unitsById[String(numParentId)] || null;
        availableUnits = units.filter(u => {
            const pId = parseInt(u.parent_id, 10);
            const uId = parseInt(u.id, 10);
            if (pId !== numParentId) return false;
            // CRITICAL: Strictly exclude parent itself from child options so OVPAA or parent never repeats!
            if (uId === numParentId) return false;
            if (parentUnit && u.name.trim().toLowerCase() === parentUnit.name.trim().toLowerCase()) return false;
            return true;
        });
    }

    if (availableUnits.length === 0) {
        targetContainer.classList.add('hidden');
        return;
    }

    targetContainer.classList.remove('hidden');

    const theme = DYNAMIC_UNIT_THEME;
    const title = getMasterlistLevelTitle(level, parentUnit);
    const defaultLabel = getMasterlistDefaultOptionLabel(level, parentUnit);

    let optionsHtml = `<option value="">${defaultLabel}</option>`;
    availableUnits.forEach(u => {
        optionsHtml += `<option value="${u.id}">${escapeHtml(u.name)}</option>`;
    });

    const levelWrapper = document.createElement('div');
    levelWrapper.className = 'dynamic-unit-level';
    levelWrapper.dataset.level = level;

    const headerSpacing = level === 1 ? 'mb-2.5 tracking-widest' : 'mb-1.5 tracking-wider';
    const headerHtml = `
        <button type="button" class="w-full flex justify-between items-center text-[10px] font-black uppercase ${headerSpacing} ${theme.labelColor} hover:text-text focus:outline-none group cursor-pointer" onclick="
            this.nextElementSibling.classList.toggle('hidden');
            this.querySelector('svg').classList.toggle('-rotate-180');
        ">
            <span class="truncate pr-2 text-left" title="${title}">${title}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 ${wasCollapsed ? '-rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
        </button>`;

    levelWrapper.innerHTML = `
        ${headerHtml}
        <div class="js-unit-level-body ${wasCollapsed ? 'hidden' : ''}">
            <div class="relative w-full">
                <select id="masterlist-filter-unit-level-${level}"
                    class="w-full appearance-none bg-white dark:bg-zinc-900 border border-surface-border ${theme.focusRing} rounded-xl pl-3 pr-8 py-2 text-xs font-semibold text-text outline-none cursor-pointer transition-all shadow-xs [color-scheme:light] dark:[color-scheme:dark]">
                    ${optionsHtml}
                </select>
                <div class="pointer-events-none absolute inset-y-0 flex items-center text-text-muted" style="right: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <div id="masterlist-dynamic-unit-children-${level}" class="relative pl-3.5 ml-2.5 border-l-2 border-dashed ${theme.connector} space-y-3 pt-2 hidden"></div>
        </div>
    `;

    targetContainer.appendChild(levelWrapper);

    const selectEl = levelWrapper.querySelector(`#masterlist-filter-unit-level-${level}`);
    selectEl.addEventListener('change', (e) => {
        onDynamicUnitLevelChange(level, e.target.value, true);
    });
}

function onDynamicUnitLevelChange(level, selectedUnitId, triggerFilter = true) {
    const selectEl = document.getElementById(`masterlist-filter-unit-level-${level}`);
    const childrenContainer = document.getElementById(`masterlist-dynamic-unit-children-${level}`);
    const theme = DYNAMIC_UNIT_THEME;

    if (selectEl) {
        const activeClasses = theme.activeBorder.split(' ');
        if (selectedUnitId) {
            selectEl.classList.add(...activeClasses);
        } else {
            selectEl.classList.remove(...activeClasses);
        }
    }

    if (childrenContainer) {
        childrenContainer.innerHTML = '';
        childrenContainer.classList.add('hidden');

        if (selectedUnitId) {
            const numId = parseInt(selectedUnitId, 10);
            const units = window.SPMS_MASTERLIST_UNITS || [];
            const childUnits = units.filter(u => {
                const pId = parseInt(u.parent_id, 10);
                const uId = parseInt(u.id, 10);
                return pId === numId && uId !== numId;
            });

            if (childUnits.length > 0) {
                renderDynamicUnitLevel(level + 1, selectedUnitId, childrenContainer);
            }
        }
    }

    // Keep active unit filter ID in sync with current dropdown hierarchy
    window._activeMasterlistUnitFilterId = getActiveDynamicUnitId();

    if (triggerFilter) {
        filterMasterlist(true);
    }
}
window.onDynamicUnitLevelChange = onDynamicUnitLevelChange;

function initDynamicUnitFilters() {
    const rootContainer = document.getElementById('dynamic-unit-filter-container');
    if (!rootContainer) return;
    renderDynamicUnitLevel(1, null, rootContainer);
}

function getActiveDynamicUnitId() {
    const container = document.getElementById('dynamic-unit-filter-container');
    if (!container) return window._activeMasterlistUnitFilterId || null;
    const selects = Array.from(container.querySelectorAll('select'));
    for (let i = selects.length - 1; i >= 0; i--) {
        const val = (selects[i].value || '').trim();
        if (val) return val;
    }
    return window._activeMasterlistUnitFilterId || null;
}

function setDynamicUnitFilterTo(targetUnitId, targetUnitName) {
    const rootContainer = document.getElementById('dynamic-unit-filter-container');
    if (!rootContainer) return;

    // Detect if arguments were passed in reverse order (e.g. name, id)
    if (targetUnitId && isNaN(targetUnitId) && targetUnitName && !isNaN(targetUnitName)) {
        const temp = targetUnitId;
        targetUnitId = targetUnitName;
        targetUnitName = temp;
    }

    if (!targetUnitId && !targetUnitName) {
        window._activeMasterlistUnitFilterId = null;
        initDynamicUnitFilters();
        return;
    }

    const units = window.SPMS_MASTERLIST_UNITS || [];
    const unitsById = {};
    units.forEach(u => { unitsById[String(u.id)] = u; });

    let targetUnit = null;
    if (targetUnitId && unitsById[String(targetUnitId)]) {
        targetUnit = unitsById[String(targetUnitId)];
    } else if (targetUnitName) {
        const cleanName = String(targetUnitName).toLowerCase().replace(/\s*\(direct staff\)\s*$/i, '').trim();
        targetUnit = units.find(u => u.name.toLowerCase().trim() === cleanName)
                  || units.find(u => u.name.toLowerCase().includes(cleanName));
    }

    if (!targetUnit) {
        window._activeMasterlistUnitFilterId = null;
        initDynamicUnitFilters();
        return;
    }

    // Explicitly record the chosen unit ID
    window._activeMasterlistUnitFilterId = String(targetUnit.id);

    // Build path from root down to targetUnit (e.g. [OVPAA (2), College of Nursing (12), BS Nursing (13)])
    const path = [];
    let curr = targetUnit;
    const visited = new Set();
    while (curr && !visited.has(String(curr.id))) {
        visited.add(String(curr.id));
        path.unshift(curr);
        const pId = curr.parent_id ? String(curr.parent_id) : null;
        curr = (pId && unitsById[pId]) ? unitsById[pId] : null;
    }

    // 1. Initialize level 1
    initDynamicUnitFilters();

    // 2. Cascade down each level in the path
    for (let i = 0; i < path.length; i++) {
        const level = i + 1;
        const u = path[i];
        const selectEl = document.getElementById(`masterlist-filter-unit-level-${level}`);
        if (!selectEl) break;

        selectEl.value = String(u.id);
        // Spawn next level without triggering filterMasterlist until full path is populated
        onDynamicUnitLevelChange(level, String(u.id), false);
    }

    // Re-affirm active filter ID after cascading
    window._activeMasterlistUnitFilterId = String(targetUnit.id);
}
window.setDynamicUnitFilterTo = setDynamicUnitFilterTo;

// --- DASHBOARD VIEW SWITCHER (Analytics vs Masterlist) ---
function updateDashboardPill(view, animate = true) {
    const pairs = [
        {
            container: document.getElementById('dash-view-switcher'),
            indicator: document.getElementById('dash-view-indicator'),
            target: document.getElementById(view === 'masterlist' ? 'btn-view-masterlist' : 'btn-view-analytics')
        },
        {
            container: document.getElementById('dash-view-switcher-mobile'),
            indicator: document.getElementById('dash-view-indicator-mobile'),
            target: document.getElementById(view === 'masterlist' ? 'btn-view-masterlist-mobile' : 'btn-view-analytics-mobile')
        }
    ];

    pairs.forEach(({ container, indicator, target }) => {
        if (!container || !indicator || !target) return;
        const left = target.offsetLeft;
        const top = target.offsetTop;
        const width = target.offsetWidth;
        const height = target.offsetHeight;

        if (width === 0 || height === 0) {
            if (!container._pillRetryCount) container._pillRetryCount = 0;
            if (container._pillRetryCount < 8) {
                container._pillRetryCount++;
                setTimeout(() => updateDashboardPill(view, false), 35 * container._pillRetryCount);
            }
            return;
        }
        container._pillRetryCount = 0;

        if (animate) {
            indicator.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), width 0.22s cubic-bezier(0.16, 1, 0.3, 1), height 0.18s ease, opacity 0.15s ease';
        } else {
            indicator.style.transition = 'none';
        }

        indicator.style.transform = `translate3d(${left}px, ${top}px, 0)`;
        indicator.style.width = `${width}px`;
        indicator.style.height = `${height}px`;
        indicator.style.opacity = '1';
    });
}

function switchDashboardView(view, animate = true) {
    const analyticsView = document.getElementById('dashboard-view-analytics');
    const masterlistView = document.getElementById('dashboard-view-masterlist');
    const mobileAnalyticsHeader = document.getElementById('analytics-mobile-header');

    const btnsAnalytics = [
        document.getElementById('btn-view-analytics'),
        document.getElementById('btn-view-analytics-mobile')
    ];
    const btnsMasterlist = [
        document.getElementById('btn-view-masterlist'),
        document.getElementById('btn-view-masterlist-mobile')
    ];

    if (!analyticsView || !masterlistView) return;

    // 1. Instantly animate the indicator pill
    updateDashboardPill(view, animate);

    // 2. Toggle views & button classes
    if (view === 'masterlist') {
        btnsAnalytics.forEach(b => {
            if (b) {
                b.classList.remove('spms-tab-active');
                b.classList.add('spms-tab-inactive');
            }
        });
        btnsMasterlist.forEach(b => {
            if (b) {
                b.classList.remove('spms-tab-inactive');
                b.classList.add('spms-tab-active');
            }
        });

        analyticsView.classList.add('hidden');
        masterlistView.classList.remove('hidden');
        if (animate) {
            masterlistView.classList.remove('spms-view-enter');
            void masterlistView.offsetWidth;
            masterlistView.classList.add('spms-view-enter');
        }
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.add('hidden');

        localStorage.setItem('spms_dash_view', 'masterlist');
        filterMasterlist(false);
    } else {
        btnsMasterlist.forEach(b => {
            if (b) {
                b.classList.remove('spms-tab-active');
                b.classList.add('spms-tab-inactive');
            }
        });
        btnsAnalytics.forEach(b => {
            if (b) {
                b.classList.remove('spms-tab-inactive');
                b.classList.add('spms-tab-active');
            }
        });

        masterlistView.classList.add('hidden');
        analyticsView.classList.remove('hidden');
        if (animate) {
            analyticsView.classList.remove('spms-view-enter');
            void analyticsView.offsetWidth;
            analyticsView.classList.add('spms-view-enter');
        }
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.remove('hidden');

        localStorage.setItem('spms_dash_view', 'analytics');
    }

    // 3. Re-align pill once the DOM layout settles
    requestAnimationFrame(() => updateDashboardPill(view, animate));
}

// Immediate alignment on script load and resize
function initDashboardView() {
    const initialView = localStorage.getItem('spms_dash_view') || 'analytics';
    switchDashboardView(initialView, false);

    // Keep pill positioned on layout/window changes with ResizeObserver
    if (typeof ResizeObserver !== 'undefined') {
        ['dash-view-switcher', 'dash-view-switcher-mobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el._hasObserver) {
                el._hasObserver = true;
                new ResizeObserver(() => {
                    const cv = localStorage.getItem('spms_dash_view') || 'analytics';
                    updateDashboardPill(cv, false);
                }).observe(el);
            }
        });
    }

    // Secondary rAF / micro-timeout safety passes
    requestAnimationFrame(() => {
        const cv = localStorage.getItem('spms_dash_view') || 'analytics';
        updateDashboardPill(cv, false);
        setTimeout(() => updateDashboardPill(cv, false), 50);
        setTimeout(() => updateDashboardPill(cv, false), 150);
    });
}
window.initDashboardView = initDashboardView;
window.updateDashboardPill = updateDashboardPill;
window.switchDashboardView = switchDashboardView;

function initDashboardMasterlistFilters() {
    if (typeof initMasterlistUnitTree === 'function') {
        initMasterlistUnitTree();
    }
    const initialSelectedUnitId = <?= !empty($selectedUnitId) ? (int)$selectedUnitId : 'null' ?>;
    if (initialSelectedUnitId && typeof setDynamicUnitFilterTo === 'function') {
        setDynamicUnitFilterTo(initialSelectedUnitId);
    } else if (typeof initDynamicUnitFilters === 'function') {
        initDynamicUnitFilters();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initDashboardMasterlistFilters();
        initDashboardView();
        filterMasterlist(true);
    });
} else {
    initDashboardMasterlistFilters();
    initDashboardView();
    filterMasterlist(true);
}

window.addEventListener('resize', () => {
    const currentView = localStorage.getItem('spms_dash_view') || 'analytics';
    updateDashboardPill(currentView, false);
});

window.addEventListener('load', () => {
    const currentView = localStorage.getItem('spms_dash_view') || 'analytics';
    updateDashboardPill(currentView, false);
});

// --- TOP PERFORMING PERSONNEL TIER FILTER (ALL / O / VS / S / US / P) ---
function filterTopPerformersByTier(tier, gridId, btn) {
    const grid = document.getElementById(gridId);
    if (!grid) return;

    // Update active pill styling
    const container = btn ? btn.closest('.performer-tier-pills-container') : null;
    if (container) {
        container.querySelectorAll('.performer-tier-pill').forEach(b => {
            b.classList.remove('active', 'bg-zinc-900', 'text-white', 'dark:bg-zinc-800', 'dark:text-white', 'border-zinc-900', 'dark:border-zinc-600', 'font-bold', 'shadow-2xs');
            b.classList.add('bg-white', 'dark:bg-zinc-900', 'text-zinc-600', 'dark:text-zinc-400', 'border-zinc-200', 'dark:border-zinc-800', 'font-semibold');
        });
        if (btn) {
            btn.classList.remove('bg-white', 'dark:bg-zinc-900', 'text-zinc-600', 'dark:text-zinc-400', 'border-zinc-200', 'dark:border-zinc-800', 'font-semibold');
            btn.classList.add('active', 'bg-zinc-900', 'text-white', 'dark:bg-zinc-800', 'dark:text-white', 'border-zinc-900', 'dark:border-zinc-600', 'font-bold', 'shadow-2xs');
        }
    }

    const cards = grid.querySelectorAll('.performer-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardTier = card.getAttribute('data-adjectival-tier') || '';
        const matches = (tier === 'all' || cardTier === tier);
        if (matches) {
            card.classList.remove('hidden');
            card.style.display = '';
            visibleCount++;
        } else {
            card.classList.add('hidden');
            card.style.display = 'none';
        }
    });

    const emptyMsg = grid.querySelector('.performer-tier-empty');
    if (emptyMsg) {
        if (visibleCount === 0 && cards.length > 0) {
            emptyMsg.classList.remove('hidden');
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.classList.add('hidden');
            emptyMsg.style.display = 'none';
        }
    }
}
window.filterTopPerformersByTier = filterTopPerformersByTier;

// --- TOP PERFORMING PERSONNEL SORTING (TOP TO LEAST / LEAST TO TOP) ---
function sortTopPerformers(order, gridId) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.performer-card'));
    if (cards.length === 0) return;

    cards.sort((a, b) => {
        const ratingA = parseFloat(a.getAttribute('data-rating')) || 0;
        const ratingB = parseFloat(b.getAttribute('data-rating')) || 0;
        const idxA = parseInt(a.getAttribute('data-initial-index')) || 0;
        const idxB = parseInt(b.getAttribute('data-initial-index')) || 0;

        if (order === 'asc') {
            if (ratingA === ratingB) return idxB - idxA;
            return ratingA - ratingB;
        } else {
            if (ratingA === ratingB) return idxA - idxB;
            return ratingB - ratingA;
        }
    });

    cards.forEach((card, index) => {
        const badge = card.querySelector('.performer-rank-badge');
        if (badge) {
            badge.textContent = index + 1;
        }
        grid.appendChild(card);
    });
}

// --- STAGE ACCORDION TOGGLE (MOBILE) ---
function toggleStageAccordion(stageNum) {
    const content = document.getElementById('stage-' + stageNum + '-content');
    const chevron = document.getElementById('stage-' + stageNum + '-chevron');
    if (!content) return;
    const isHidden = content.classList.contains('hidden');
    if (isHidden) {
        content.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
}

// --- MASTERLIST FILTERING & RESPONSIVE PAGINATION ---

function setMasterlistLifecyclePill(stage, btn) {
    currentMasterlistLifecycle = stage;
    document.querySelectorAll('.masterlist-lifecycle-btn').forEach(b => {
        if (b.getAttribute('data-lifecycle') === stage) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
    filterMasterlist(true);
}

// Backward compatibility alias for any existing caller
function setMasterlistPill(stage, btn) {
    setMasterlistLifecyclePill(stage, btn);
}

// --- MASTERLIST SIDEBAR TOGGLE & POSITION FILTER ---
function toggleMasterlistFilterSidebar() {
    const sidebar = document.getElementById('masterlist-filter-sidebar');
    const overlay = document.getElementById('masterlist-filter-overlay');
    
    if (sidebar && overlay) {
        if (window.innerWidth < 1024) {
            const isClosed = sidebar.classList.contains('translate-x-full') || sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('translate-x-full');
                sidebar.classList.remove('-translate-x-full');
            } else {
                overlay.classList.add('opacity-0');
                sidebar.classList.add('translate-x-full');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
    }
}

function filterMasterlistPositions(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('#positions-checkbox-list .position-filter-label').forEach(label => {
        const title = (label.getAttribute('data-title') || '').toLowerCase();
        label.style.display = (!q || title.includes(q)) ? '' : 'none';
    });
}

function filterMasterlistMobile() {
    const mobileInput = document.getElementById('masterlist-search-mobile');
    const sideInput   = document.getElementById('masterlist-search-sidebar');
    if (mobileInput && sideInput) {
        sideInput.value = mobileInput.value;
    }
    filterMasterlist(true);
}

function clearAllMasterlistFilters() {
    const s1 = document.getElementById('masterlist-search-sidebar');
    if (s1) s1.value = '';
    const s2 = document.getElementById('masterlist-search-mobile');
    if (s2) s2.value = '';

    document.querySelectorAll('input[name="masterlist_filter_type[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="masterlist_filter_emp[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="masterlist_filter_pos[]"]').forEach(cb => cb.checked = false);

    const miniPos = document.getElementById('mini-search-positions');
    if (miniPos) {
        miniPos.value = '';
        filterMasterlistPositions('');
    }

    window._activeMasterlistUnitFilterId = null;

    if (typeof initDynamicUnitFilters === 'function') {
        initDynamicUnitFilters();
    }

    filterMasterlist(true);
}

function filterMasterlist(resetPage = true) {
    if (resetPage) {
        masterlistCurrentPage = 1;
    }

    const searchInputDesk   = document.getElementById('masterlist-search-sidebar');
    const searchInputMobile = document.getElementById('masterlist-search-mobile');

    const activeUnitId = (typeof getActiveDynamicUnitId === 'function') ? getActiveDynamicUnitId() : null;
    const units = window.SPMS_MASTERLIST_UNITS || [];
    const unitsById = {};
    units.forEach(u => { unitsById[String(u.id)] = u; });
    const activeUnit = activeUnitId ? unitsById[String(activeUnitId)] : null;
    const activeUnitName = activeUnit ? activeUnit.name.toLowerCase().trim() : '';
    const allowedUnitIds = (activeUnitId && typeof getDescendantUnitIds === 'function') ? getDescendantUnitIds(String(activeUnitId)) : null;

    const checkedTypes = Array.from(document.querySelectorAll('input[name="masterlist_filter_type[]"]:checked')).map(cb => cb.value.toLowerCase());
    const checkedEmp   = Array.from(document.querySelectorAll('input[name="masterlist_filter_emp[]"]:checked')).map(cb => cb.value.toLowerCase());
    const checkedPos   = Array.from(document.querySelectorAll('input[name="masterlist_filter_pos[]"]:checked')).map(cb => cb.value.toLowerCase());

    const query = ((searchInputDesk && searchInputDesk.value.trim() !== '') 
                    ? searchInputDesk.value 
                    : (searchInputMobile ? searchInputMobile.value : '')).toLowerCase().trim();

    // Active filters count badge calculation
    let activeFilterCount = 0;
    if (query) activeFilterCount++;
    if (activeUnitId) activeFilterCount++;
    if (checkedTypes.length > 0) activeFilterCount += checkedTypes.length;
    if (checkedEmp.length > 0) activeFilterCount += checkedEmp.length;
    if (checkedPos.length > 0) activeFilterCount += checkedPos.length;

    const countBadge = document.getElementById('active-masterlist-filter-badge');
    if (countBadge) {
        countBadge.textContent = activeFilterCount;
        countBadge.classList.toggle('hidden', activeFilterCount === 0);
    }
    const mobileBadge1 = document.getElementById('mobile-masterlist-filter-badge');
    if (mobileBadge1) {
        mobileBadge1.textContent = activeFilterCount;
        mobileBadge1.classList.toggle('hidden', activeFilterCount === 0);
    }
    const mobileBadge2 = document.getElementById('mobile-masterlist-filter-badge-desk');
    if (mobileBadge2) {
        mobileBadge2.textContent = activeFilterCount;
        mobileBadge2.classList.toggle('hidden', activeFilterCount === 0);
    }
    const resetBtn = document.getElementById('reset-masterlist-filters-btn');
    if (resetBtn) {
        resetBtn.classList.toggle('hidden', activeFilterCount === 0);
    }

    const desktopRows = document.querySelectorAll('.masterlist-row');
    const mobileCards = document.querySelectorAll('.masterlist-row-card');

    let matchingIndexes = [];
    desktopRows.forEach((row, idx) => {
        const name         = row.getAttribute('data-name') || '';
        const email        = row.getAttribute('data-email') || '';
        const dept         = (row.getAttribute('data-dept') || '').toLowerCase();
        const unitId       = (row.getAttribute('data-unit-id') || '').trim();
        const ancestorIds  = (row.getAttribute('data-ancestor-unit-ids') || '').split(',').map(s => s.trim()).filter(Boolean);
        const deptChain    = (row.getAttribute('data-dept-chain') || '').toLowerCase();
        const pos          = row.getAttribute('data-pos') || '';
        const staffType    = (row.getAttribute('data-staff-type') || '').toLowerCase();
        const rowEmpStatus = (row.getAttribute('data-emp-status') || '').toLowerCase();
        const stage        = row.getAttribute('data-lifecycle-stage') || 'target_phase';
        const rating       = row.getAttribute('data-rating') || '';
        const adjectival   = (row.getAttribute('data-adjectival') || '').toLowerCase();

        // 1. Search Query
        const matchesSearch = !query || name.includes(query) || email.includes(query) || dept.includes(query) || pos.includes(query) || rating.includes(query) || adjectival.includes(query);

        // 2. Department / Unit (Dynamic Cascading Hierarchical Tree Match)
        let matchesDept = true;
        if (activeUnitId && allowedUnitIds) {
            const isIdMatch   = (unitId !== '' && allowedUnitIds.has(String(unitId)))
                             || ancestorIds.some(aId => allowedUnitIds.has(String(aId)) || aId === String(activeUnitId));
            const isNameMatch = (!unitId && activeUnitName) && (dept === activeUnitName || deptChain.includes(activeUnitName));
            matchesDept = isIdMatch || isNameMatch;
        }

        // 3. Staff Type (teaching / non_teaching)
        const matchesType = checkedTypes.length === 0 || checkedTypes.includes(staffType);

        // 4. Employment Status (permanent, temporary, casual, etc.)
        const matchesEmp = checkedEmp.length === 0 || checkedEmp.includes(rowEmpStatus);

        // 5. Job Position
        const matchesPos = checkedPos.length === 0 || checkedPos.some(p => pos.includes(p));

        // 6. Mutually-Exclusive Lifecycle Pill
        const matchesLifecycle = (currentMasterlistLifecycle === 'all') || (stage === currentMasterlistLifecycle);

        if (matchesSearch && matchesDept && matchesType && matchesEmp && matchesPos && matchesLifecycle) {
            matchingIndexes.push(idx);
        }
    });

    const totalMatching = matchingIndexes.length;
    const perPageNum = masterlistPerPage === 'all' ? totalMatching : parseInt(masterlistPerPage, 10);
    const totalPages = Math.max(1, Math.ceil(totalMatching / perPageNum));

    if (masterlistCurrentPage > totalPages) {
        masterlistCurrentPage = totalPages;
    }

    const startIndex = (masterlistCurrentPage - 1) * perPageNum;
    const endIndex = Math.min(startIndex + perPageNum, totalMatching);

    const visibleIndexSet = new Set(matchingIndexes.slice(startIndex, endIndex));

    // Show/hide desktop rows and update index numbers
    let displayRank = startIndex + 1;
    desktopRows.forEach((row, idx) => {
        if (visibleIndexSet.has(idx)) {
            row.style.display = '';
            const idxCell = row.querySelector('.masterlist-row-idx');
            if (idxCell) {
                idxCell.textContent = displayRank++;
            }
        } else {
            row.style.display = 'none';
        }
    });

    // Show/hide mobile cards
    mobileCards.forEach((card, idx) => {
        card.style.display = visibleIndexSet.has(idx) ? '' : 'none';
    });

    // Update empty states
    const desktopEmptyRow = document.getElementById('masterlist-empty-row');
    if (desktopEmptyRow) {
        desktopEmptyRow.classList.toggle('hidden', totalMatching > 0);
    }
    const mobileEmptyState = document.getElementById('masterlist-mobile-empty');
    if (mobileEmptyState) {
        mobileEmptyState.classList.toggle('hidden', totalMatching > 0);
    }
    const mobileCountLabel = document.getElementById('masterlist-mobile-records-count');
    if (mobileCountLabel) {
        mobileCountLabel.textContent = totalMatching;
    }

    // Update visible counts
    const rangeEl = document.getElementById('masterlist-visible-range');
    const totalEl = document.getElementById('masterlist-visible-total');
    if (rangeEl) {
        rangeEl.textContent = totalMatching === 0 ? '0' : `${startIndex + 1}–${endIndex}`;
    }
    if (totalEl) {
        totalEl.textContent = totalMatching;
    }

    const mobileRangeEl = document.getElementById('masterlist-mobile-range');
    const mobileTotalEl = document.getElementById('masterlist-mobile-total');
    if (mobileRangeEl) {
        mobileRangeEl.textContent = totalMatching === 0 ? '0' : `${startIndex + 1}–${endIndex}`;
    }
    if (mobileTotalEl) {
        mobileTotalEl.textContent = totalMatching;
    }

    renderMasterlistPagination(totalPages);
}

function setRosterFilter(filter, btn) {
    if (filter === 'all') setMasterlistLifecyclePill('all', btn);
    else if (filter === 'missing') setMasterlistLifecyclePill('target_phase', btn);
    else if (filter === 'review') setMasterlistLifecyclePill('eval_phase', btn);
    else if (filter === 'completed') setMasterlistLifecyclePill('completed', btn);
    else filterMasterlist(true);
}

function filterRoster() {
    filterMasterlist(true);
}

function applyCollegeFilter(unitId) {
    const url = new URL(window.location.href);
    if (unitId) {
        url.searchParams.set('unit_id', unitId);
    } else {
        url.searchParams.delete('unit_id');
    }
    window.location.href = url.toString();
}

// --- CUSTOM ANIMATED COLLEGE DROPDOWN CONTROLLER ---
let openCollegeDropdownScope = null;

function openCollegeDropdown(scope) {
    const isMobile = scope === 'mobile';
    const menu = document.getElementById(isMobile ? 'college-dropdown-menu-mobile' : 'college-dropdown-menu');
    const chevron = document.getElementById(isMobile ? 'college-dropdown-chevron-mobile' : 'college-dropdown-chevron');
    const search = document.getElementById(isMobile ? 'college-dropdown-search-mobile' : 'college-dropdown-search');
    if (!menu) return;

    if (openCollegeDropdownScope && openCollegeDropdownScope !== scope) {
        closeCollegeDropdown(openCollegeDropdownScope);
    }

    openCollegeDropdownScope = scope;
    menu.classList.remove('hidden', 'profile-dropdown-leave');
    menu.classList.add('profile-dropdown-enter');
    chevron?.classList.add('rotate-180');
    if (search) {
        search.value = '';
        filterCollegeDropdownOptions('', scope);
        setTimeout(() => search.focus(), 60);
    }
}

function closeCollegeDropdown(scope) {
    const targetScope = scope || openCollegeDropdownScope;
    if (!targetScope) return;
    const isMobile = targetScope === 'mobile';
    const menu = document.getElementById(isMobile ? 'college-dropdown-menu-mobile' : 'college-dropdown-menu');
    const chevron = document.getElementById(isMobile ? 'college-dropdown-chevron-mobile' : 'college-dropdown-chevron');
    if (!menu) return;

    openCollegeDropdownScope = null;
    chevron?.classList.remove('rotate-180');
    menu.classList.remove('profile-dropdown-enter');
    menu.classList.add('profile-dropdown-leave');
    setTimeout(() => {
        if (!openCollegeDropdownScope) {
            menu.classList.add('hidden');
            menu.classList.remove('profile-dropdown-leave');
        }
    }, 120);
}

function toggleCollegeDropdown(scope = 'desktop') {
    if (openCollegeDropdownScope === scope) {
        closeCollegeDropdown(scope);
    } else {
        openCollegeDropdown(scope);
    }
}

function selectCollegeOption(unitId, unitName) {
    closeCollegeDropdown();
    applyCollegeFilter(unitId);
}

function filterCollegeDropdownOptions(query, scope = 'desktop') {
    const q = query.toLowerCase().trim();
    const itemClass = scope === 'mobile' ? '.college-opt-item-mobile' : '.college-opt-item-desktop';
    const groupClass = scope === 'mobile' ? '.college-opt-group-mobile' : '.college-opt-group-desktop';
    const items = document.querySelectorAll(itemClass);
    items.forEach(item => {
        const name = item.getAttribute('data-opt-name') || item.textContent.toLowerCase();
        if (!q || name.includes(q)) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });

    document.querySelectorAll(groupClass).forEach(group => {
        let next = group.nextElementSibling;
        let hasVisible = false;
        while (next && !next.classList.contains(groupClass.replace('.', ''))) {
            if (!next.classList.contains('hidden')) {
                hasVisible = true;
                break;
            }
            next = next.nextElementSibling;
        }
        if (!q || hasVisible) {
            group.classList.remove('hidden');
        } else {
            group.classList.add('hidden');
        }
    });
}

// Global click outside and Escape key listeners
document.addEventListener('click', (e) => {
    if (!openCollegeDropdownScope) return;
    const isMobile = openCollegeDropdownScope === 'mobile';
    const container = document.getElementById(isMobile ? 'college-dropdown-container-mobile' : 'college-dropdown-container');
    if (container && !container.contains(e.target)) {
        closeCollegeDropdown(openCollegeDropdownScope);
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && openCollegeDropdownScope) {
        closeCollegeDropdown(openCollegeDropdownScope);
    }
});

function filterOfficeLeaderboard() {
    const input = document.getElementById('office-table-search');
    const q = input ? input.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.office-row');
    let visible = 0;
    rows.forEach(r => {
        const name = r.getAttribute('data-name') || '';
        const show = !q || name.includes(q);
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const empty = document.getElementById('office-empty-row');
    if (empty) empty.classList.toggle('hidden', visible > 0);
}

// --- Masterlist Quick Shortcut from Compliance Matrix & Rows ---
function filterMasterlistByUnit(arg1, arg2, isDirectStaff = false) {
    let deptName = '';
    let unitId = '';

    if (arg1 && (!isNaN(arg1) || typeof arg1 === 'number')) {
        unitId = String(arg1);
        deptName = arg2 ? String(arg2) : '';
    } else if (arg2 && (!isNaN(arg2) || typeof arg2 === 'number')) {
        deptName = arg1 ? String(arg1) : '';
        unitId = String(arg2);
    } else {
        deptName = arg1 ? String(arg1) : '';
        unitId = arg2 ? String(arg2) : '';
    }

    // 1. Reset free-text search fields so unit filtering is prominent
    const searchDesk = document.getElementById('masterlist-search-sidebar');
    if (searchDesk) searchDesk.value = '';
    const searchMob = document.getElementById('masterlist-search-mobile');
    if (searchMob) searchMob.value = '';

    // 2. Uncheck all checkboxes so staff isn't accidentally excluded by type or status
    document.querySelectorAll('input[name="masterlist_filter_type[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="masterlist_filter_emp[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="masterlist_filter_pos[]"]').forEach(cb => cb.checked = false);

    // 3. Reset position search
    const miniPos = document.getElementById('mini-search-positions');
    if (miniPos) {
        miniPos.value = '';
        if (typeof filterMasterlistPositions === 'function') {
            filterMasterlistPositions('');
        }
    }

    // 4. Reset lifecycle filter pill to 'all' without triggering premature filtering
    currentMasterlistLifecycle = 'all';
    document.querySelectorAll('.masterlist-lifecycle-btn').forEach(b => {
        if (b.getAttribute('data-lifecycle') === 'all') {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });

    // 5. Select the specific unit in the dynamic cascading unit filter FIRST
    if (typeof setDynamicUnitFilterTo === 'function') {
        setDynamicUnitFilterTo(unitId, deptName);
    }

    // 6. Switch to Master List View without animation jump
    if (typeof switchDashboardView === 'function') {
        switchDashboardView('masterlist', false);
    }

    // 7. Execute filtering and update views with resetPage = true
    filterMasterlist(true);

    // 8. Accurately position user at top of Master List without awkward scroll jumps
    const resetScrollPosition = () => {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
        const dtScroll = document.querySelector('#masterlist-table')?.parentElement || document.querySelector('#dashboard-view-masterlist .overflow-auto');
        if (dtScroll) {
            dtScroll.scrollTop = 0;
            dtScroll.scrollLeft = 0;
        }
        const mbScroll = document.getElementById('masterlist-mobile-cards-list');
        if (mbScroll) mbScroll.scrollTop = 0;
        const sbFilter = document.getElementById('masterlist-filter-sidebar');
        if (sbFilter) {
            const sc = sbFilter.querySelector('.overflow-y-auto') || sbFilter;
            sc.scrollTop = 0;
        }
        const mlView = document.getElementById('dashboard-view-masterlist');
        if (mlView) mlView.scrollTop = 0;
    };

    resetScrollPosition();
    requestAnimationFrame(resetScrollPosition);
    setTimeout(resetScrollPosition, 50);
}
window.filterMasterlistByUnit = filterMasterlistByUnit;

// --- Department & Unit Compliance Dropdown / Expandable Tree ---
function toggleUnitHierarchy(unitId, event) {
    if (event && event.target && event.target.closest('button.group, a, input, select')) {
        return; // Avoid triggering when clicking the View in Master List button
    }
    const parentRow = document.querySelector(`.compliance-unit-row[data-unit-id="${unitId}"]`);
    if (!parentRow) return;

    const isExpanded = parentRow.getAttribute('data-expanded') === 'true';
    const newExpanded = !isExpanded;
    parentRow.setAttribute('data-expanded', newExpanded ? 'true' : 'false');

    const chevron = parentRow.querySelector('.compliance-chevron');
    if (chevron) {
        chevron.style.transform = newExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
    }

    if (!newExpanded) {
        collapseComplianceDescendants(unitId);
    } else {
        document.querySelectorAll(`.compliance-unit-row[data-parent-id="${unitId}"]`).forEach(r => {
            r.style.display = '';
        });
    }
}
window.toggleUnitHierarchy = toggleUnitHierarchy;

function collapseComplianceDescendants(unitId) {
    document.querySelectorAll(`.compliance-unit-row[data-parent-id="${unitId}"]`).forEach(r => {
        r.style.display = 'none';
        r.setAttribute('data-expanded', 'false');
        const ch = r.querySelector('.compliance-chevron');
        if (ch) ch.style.transform = 'rotate(0deg)';
        const childId = r.getAttribute('data-unit-id');
        if (childId) {
            collapseComplianceDescendants(childId);
        }
    });
}

function filterOfficeLeaderboard(query) {
    const q = (query || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.compliance-unit-row');
    const emptyRow = document.getElementById('compliance-empty-row');

    if (!q) {
        rows.forEach(r => {
            const isTop = (r.getAttribute('data-level') === '0');
            r.style.display = isTop ? '' : 'none';
            r.setAttribute('data-expanded', 'false');
            const ch = r.querySelector('.compliance-chevron');
            if (ch) ch.style.transform = 'rotate(0deg)';
        });
        if (emptyRow) emptyRow.classList.add('hidden');
        return;
    }

    let matchCount = 0;
    rows.forEach(r => {
        const name = (r.getAttribute('data-name') || '').toLowerCase();
        if (name.includes(q)) {
            r.style.display = '';
            matchCount++;
            const ancestors = (r.getAttribute('data-ancestor-ids') || '').split(',').map(s => s.trim()).filter(Boolean);
            ancestors.forEach(aId => {
                const anc = document.querySelector(`.compliance-unit-row[data-unit-id="${aId}"]`);
                if (anc) {
                    anc.style.display = '';
                    anc.setAttribute('data-expanded', 'true');
                    const ch = anc.querySelector('.compliance-chevron');
                    if (ch) ch.style.transform = 'rotate(90deg)';
                }
            });
        } else {
            r.style.display = 'none';
        }
    });

    if (emptyRow) {
        emptyRow.classList.toggle('hidden', matchCount > 0);
    }
}
window.filterOfficeLeaderboard = filterOfficeLeaderboard;

function openMasterlistDocument(docUrl, event) {
    if (!docUrl) return;
    // Don't navigate if user clicked an interactive child element (button, link, input, select, label)
    if (event && event.target && event.target.closest('button, a, input, select, label')) {
        return;
    }
    window.location.href = docUrl;
}
window.openMasterlistDocument = openMasterlistDocument;
</script>

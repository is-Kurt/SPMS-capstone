<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-xl border border-surface-border shadow-xs overflow-visible lg:overflow-hidden">
    
    <!-- Dashboard Header -->
    <div class="px-3.5 sm:px-6 lg:px-8 py-3.5 sm:py-5 border-b border-surface-border shrink-0">

        <!-- Active evaluation cycle dropdown (mobile) -->
        <?= view('components/mobile_folder_dropdown', [
            'activeCycle'      => $activeCycle ?? null,
            'folders'          => $rootFolders ?? ($sidebarFolders ?? []),
            'selectedFolderId' => $activeCycle['id'] ?? null,
            'baseUrl'          => 'dashboard',
            'containerClass'   => 'mb-3'
        ]) ?>

        <?php if ($sysRole === 'Admin'): ?>
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
        <?php endif; ?>

        <!-- MOBILE ANALYTICS HEADER (Shown only when Overview Analytics is active on mobile) -->
        <div id="analytics-mobile-header" class="lg:hidden space-y-2.5 mb-1">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 truncate">
                    <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700 border border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 shrink-0">
                    <?= ($sysRole === 'Supervisor') ? esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Departmental Oversight' : 'Collegiate Oversight')) : 'University-Wide Oversight' ?>
                </span>
            </div>
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-xl font-black tracking-tight text-zinc-900 dark:text-white truncate">
                    <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Overview' : 'College Overview') : 'Executive Overview' ?>
                </h1>
                <a href="<?= site_url('ratings') ?>" 
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-colors shadow-2xs shrink-0 spms-btn-queue">
                    <span>Queue</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
            <?php
            $colleges = [];
            $adminOffices = [];
            $subDepts = [];
            $selectedUnitDisplayName = 'All Colleges & Divisions';
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
                                       placeholder="Filter colleges & divisions..." 
                                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                            </div>
                        </div>

                        <!-- Scrollable Options List -->
                        <div id="college-dropdown-list-mobile" class="max-h-60 overflow-y-auto p-1.5 space-y-0.5 text-xs">
                            <!-- Reset / All Option -->
                            <button type="button" 
                                    onclick="selectCollegeOption('', 'All Colleges & Divisions')"
                                    class="college-opt-item-mobile w-full flex items-center justify-between px-3 py-2 rounded-lg font-bold transition-colors <?= empty($selectedUnitId) ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                <span class="truncate">All Colleges &amp; Divisions</span>
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
                                    Administrative Offices / Divisions
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
            <div class="flex items-center justify-between gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 truncate">
                    <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700 border border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 shrink-0">
                    <?= ($sysRole === 'Supervisor') ? esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Departmental Oversight' : 'Collegiate Oversight')) : 'University-Wide Oversight' ?>
                </span>
            </div>
            
            <!-- Main Title & Desktop Toolbar -->
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-zinc-900 dark:text-white truncate">
                        <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Overview' : 'College Overview') : 'Executive Overview' ?>
                    </h1>
                    <?php if ($sysRole === 'Supervisor'): ?>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Tracking faculty &amp; staff submissions for <span class="font-bold text-zinc-700 dark:text-zinc-200"><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Your Department' : 'Your College')) ?></span>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <?php if ($sysRole === 'Admin'): ?>
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

                    <!-- Desktop College Filter & Queue -->
                    <div class="flex items-center gap-2">
                        <?php if (!empty($allUnits)): ?>
                        <div class="relative" id="college-dropdown-container">
                            <button type="button" 
                                    id="college-dropdown-btn"
                                    onclick="toggleCollegeDropdown('desktop')"
                                    class="flex items-center justify-between gap-3 text-xs font-semibold px-3.5 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 cursor-pointer shadow-2xs spms-select-dark w-56 sm:w-64 transition-all active:scale-[0.98]">
                                <span id="college-dropdown-label" class="truncate text-left">
                                    <?= esc($selectedUnitDisplayName) ?>
                                </span>
                                <svg id="college-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" 
                                     class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-500 shrink-0 transition-transform duration-200" 
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Custom Slide-Down Menu -->
                            <div id="college-dropdown-menu"
                                 class="hidden absolute left-0 mt-1.5 w-80 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl z-[120] overflow-hidden"
                                 style="transform-origin: top left;">
                                
                                <!-- Search Filter Input -->
                                <div class="p-2 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-950/60">
                                    <div class="relative">
                                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text" 
                                               id="college-dropdown-search" 
                                               oninput="filterCollegeDropdownOptions(this.value, 'desktop')"
                                               placeholder="Filter colleges & divisions..." 
                                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                                    </div>
                                </div>

                                <!-- Scrollable Options List -->
                                <div id="college-dropdown-list" class="max-h-64 overflow-y-auto p-1.5 space-y-0.5 text-xs">
                                    <!-- Reset / All Option -->
                                    <button type="button" 
                                            onclick="selectCollegeOption('', 'All Colleges & Divisions')"
                                            class="college-opt-item-desktop w-full flex items-center justify-between px-3 py-2 rounded-lg font-bold transition-colors <?= empty($selectedUnitId) ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                        <span class="truncate">All Colleges &amp; Divisions</span>
                                        <?php if (empty($selectedUnitId)): ?>
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        <?php endif; ?>
                                    </button>

                                    <?php if (!empty($colleges)): ?>
                                        <div class="college-opt-group-desktop pt-1.5 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                            Colleges
                                        </div>
                                        <?php foreach ($colleges as $c): 
                                            $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$c['id']);
                                        ?>
                                            <button type="button"
                                                    onclick="selectCollegeOption('<?= $c['id'] ?>', '<?= esc(addslashes($c['name'])) ?>')"
                                                    data-opt-name="<?= esc(strtolower($c['name'])) ?>"
                                                    class="college-opt-item-desktop w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                                <span class="truncate text-left"><?= esc($c['name']) ?></span>
                                                <?php if ($isSelected): ?>
                                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($adminOffices)): ?>
                                        <div class="college-opt-group-desktop pt-2 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                            Administrative Offices / Divisions
                                        </div>
                                        <?php foreach ($adminOffices as $ao): 
                                            $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$ao['id']);
                                        ?>
                                            <button type="button"
                                                    onclick="selectCollegeOption('<?= $ao['id'] ?>', '<?= esc(addslashes($ao['name'])) ?>')"
                                                    data-opt-name="<?= esc(strtolower($ao['name'])) ?>"
                                                    class="college-opt-item-desktop w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
                                                <span class="truncate text-left"><?= esc($ao['name']) ?></span>
                                                <?php if ($isSelected): ?>
                                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($subDepts)): ?>
                                        <div class="college-opt-group-desktop pt-2 pb-0.5 px-2 text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                            Degree Programs / Sub-Departments
                                        </div>
                                        <?php foreach ($subDepts as $sd): 
                                            $isSelected = (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$sd['id']);
                                        ?>
                                            <button type="button"
                                                    onclick="selectCollegeOption('<?= $sd['id'] ?>', '<?= esc(addslashes($sd['name'])) ?>')"
                                                    data-opt-name="<?= esc(strtolower($sd['name'])) ?>"
                                                    class="college-opt-item-desktop w-full flex items-center justify-between px-3 py-1.5 rounded-lg font-medium transition-colors <?= $isSelected ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' ?>">
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
                        <?php endif; ?>

                        <?php if (!empty($selectedUnitId)): ?>
                            <button onclick="applyCollegeFilter('')"
                                    class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 transition-colors shadow-2xs shrink-0"
                                    title="Reset filter">
                                <span>&times;</span>
                            </button>
                        <?php endif; ?>

                        <a href="<?= site_url('ratings') ?>" 
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-colors shadow-2xs shrink-0 spms-btn-queue">
                            <span>Evaluator Queue</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                    <?php else: ?>
                    <a href="<?= site_url('ratings') ?>" 
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-colors shadow-2xs shrink-0 spms-btn-queue">
                        <span>Evaluator Queue</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php if ($sysRole === 'Supervisor'): ?>
    <!-- SUPERVISOR (DEAN) VIEW: COLLEGE SUBMISSION COMPLIANCE MONITOR -->
    <div class="p-6 lg:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-6">

        <!-- 1. TOP SUMMARY CARDS: WHO SUBMITTED VS WHO HAS NOT -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
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

            <!-- Card 2: Targets Submitted & Approved -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Target Commitments</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">/ <?= $totalPersonnel ?> Approved (<?= $targetComplianceRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Missing Target Submissions (Draft) -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border <?= ($pipeline['target']['draft'] > 0) ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20 dark:bg-zinc-900' : 'border-zinc-200 dark:border-zinc-800' ?> shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Missing Targets</span>
                        <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-danger-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-rose-600 dark:text-rose-400"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-xs font-bold text-rose-500 dark:text-rose-400">Still in Draft</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs font-semibold truncate">
                    <?php if ($pipeline['target']['draft'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                            <svg class="w-3.5 h-3.5 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Faculty still need to submit targets</span>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-3.5 h-3.5 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>All targets submitted</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 4: Accomplishments Finalized -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Final Evaluations</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">/ <?= $totalPersonnel ?> Finalized (<?= $evalCompletionRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. PHASE SUBMISSION BREAKDOWN (TARGETS VS ACCOMPLISHMENTS) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Phase 1 Card -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">1</span>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Target Commitment Phase</h3>
                            <span class="text-xs text-zinc-400">Submission & Approval status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                        <?= $totalPersonnel ?> Total
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">In Review</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['draft'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Draft (Missing)</span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:border-amber-800/80 dark:text-amber-400 border border-amber-200">
                        <span class="block text-xl font-black"><?= $pipeline['target']['returned'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Revision</span>
                    </div>
                </div>
            </div>

            <!-- Phase 2 Card -->
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black">2</span>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Accomplishment Report Phase</h3>
                            <span class="text-xs text-zinc-400">Evaluation & Grading status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                        <?= $totalPersonnel ?> Total
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 dark:bg-[#102a1e] dark:border-[#1b4330] dark:text-emerald-400 border border-emerald-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Approved</span>
                    </div>
                    <div class="p-3 rounded-xl bg-indigo-50 text-indigo-800 dark:bg-highlight-500/20 dark:border-highlight-500/20 dark:text-highlight-400 border border-indigo-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['action'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Evaluating</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-800 dark:bg-info-500/10 dark:border-info-500/20 dark:text-blue-400 border border-blue-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['submitted'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Submitted</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 dark:bg-danger-500/10 dark:border-danger-500/20 dark:text-rose-400 border border-rose-200">
                        <span class="block text-xl font-black"><?= $pipeline['evaluation']['pending'] ?></span>
                        <span class="text-[11px] font-bold mt-0.5 block">Draft (Missing)</span>
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
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                        <?= count($topPerformers) ?> Ranked
                    </span>
                </div>

                <?php if (!empty($topPerformers)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[320px] overflow-y-auto custom-scrollbar pr-1">
                        <?php 
                        $topList = array_slice($topPerformers, 0, 6);
                        foreach ($topList as $idx => $tp): 
                            $rank = $idx + 1;
                            $rankBadge = 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700';
                            $rankEmoji = "#{$rank}";
                            if ($rank === 1) {
                                $rankBadge = 'bg-amber-400 text-zinc-950 font-black shadow-xs border-amber-300';
                                $rankEmoji = '1 🥇';
                            } elseif ($rank === 2) {
                                $rankBadge = 'bg-slate-300 text-zinc-900 font-black shadow-xs border-slate-400';
                                $rankEmoji = '2 🥈';
                            } elseif ($rank === 3) {
                                $rankBadge = 'bg-amber-700 text-amber-50 font-black shadow-xs border-amber-800';
                                $rankEmoji = '3 🥉';
                            }
                        ?>
                            <div class="p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3 transition-colors hover:bg-zinc-100/70 dark:hover:bg-zinc-800/40">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] border <?= $rankBadge ?> shrink-0">
                                        <?= $rankEmoji ?>
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
                                    <?php if (!empty($tp['folder_id'])): ?>
                                        <a href="<?= site_url('ratings/show/' . $tp['folder_id']) ?>" class="p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-8 text-center text-zinc-400 dark:text-zinc-500 italic text-xs">
                        Performance ratings in progress. Top performers will appear here upon completion.
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                <span>PBB Eligible: <strong class="text-emerald-600 dark:text-emerald-400"><?= $pipeline['stage4']['pbb_eligible'] ?? 0 ?> personnel</strong></span>
            </div>
        </div>

        <!-- 3. "WHO HAS SUBMITTED & WHO HAS NOT" COMPLIANCE ROSTER TABLE -->
        <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-white"><?= !empty($isChairScope) ? 'Department Personnel Submission Roster' : 'College Personnel Submission Roster' ?></h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Track who has submitted their targets/accomplishments and who is still missing or in draft.
                    </p>
                </div>

                <!-- Search & Department Filter Controls -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '')) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-[#114232] hover:bg-[#16533f] text-white border border-[#1b5e47] transition-all shadow-xs cursor-pointer"
                       title="Download CSC SLIR Excel Sheet for this unit">
                        <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Master List (.xlsx)</span>
                    </a>

                    <?php if (!empty($collegeDepartments) && count($collegeDepartments) > 1 && empty($isChairScope)): ?>
                        <select id="roster-dept-filter" onchange="filterRoster()"
                                class="text-xs font-semibold px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 cursor-pointer">
                            <option value="">All Sub-Departments</option>
                            <?php foreach ($collegeDepartments as $cd): ?>
                                <option value="<?= esc($cd) ?>"><?= esc($cd) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div class="relative w-64 max-w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="roster-search" onkeyup="filterRoster()"
                               placeholder="Search faculty name or position..."
                               class="w-full text-xs font-medium py-2 pl-9 pr-3 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 transition-all shadow-2xs" />
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pill Tabs -->
            <div class="flex items-center gap-2 pb-4 overflow-x-auto custom-scrollbar border-b border-zinc-100 dark:border-zinc-800">
                <button type="button" onclick="setRosterFilter('all', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border border-zinc-900 dark:border-white shadow-2xs">
                    All Personnel (<?= count($cycleFolders) ?>)
                </button>
                <button type="button" onclick="setRosterFilter('missing', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Missing Submissions (Draft) (<?= $pipeline['target']['draft'] + ($pipeline['evaluation']['draft'] ?? $pipeline['evaluation']['pending']) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('review', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Submitted (In Review) (<?= $pipeline['target']['pending'] + $pipeline['evaluation']['action'] + $pipeline['evaluation']['submitted'] ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('revision', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Needs Revision (<?= $pipeline['target']['returned'] + ($pipeline['evaluation']['returned'] ?? 0) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('completed', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Completed / Approved (<?= $pipeline['evaluation']['completed'] ?>)</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto mt-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left border-collapse" id="roster-table">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400 tracking-wider">
                            <th class="py-3 px-4">Faculty / Personnel</th>
                            <th class="py-3 px-4">Department / Unit</th>
                            <th class="py-3 px-4 text-center">Phase 1: Targets</th>
                            <th class="py-3 px-4 text-center">Phase 2: Accomplishments</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($cycleFolders)): ?>
                            <tr>
                                <td colspan="5" class="py-10 px-4 text-center text-zinc-400 dark:text-zinc-500 italic">
                                    No personnel records found for this evaluation cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cycleFolders as $rf): ?>
                                <tr class="roster-row hover:bg-zinc-50/80 dark:hover:bg-white/5 transition-colors"
                                    data-filter="<?= esc($rf['submission_filter'] ?? 'all') ?>"
                                    data-target-state="<?= esc($rf['target_state'] ?? 'draft') ?>"
                                    data-eval-state="<?= esc($rf['eval_state'] ?? 'draft') ?>"
                                    data-name="<?= strtolower(esc($rf['full_name'] . ' ' . $rf['email'] . ' ' . $rf['position'])) ?>"
                                    data-dept="<?= esc($rf['department']) ?>">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                                            <span><?= esc($rf['full_name']) ?></span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider <?= ($rf['is_teaching'] == 1) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' ?>">
                                                <?= ($rf['is_teaching'] == 1) ? 'Teaching' : 'Non-Teaching' ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-zinc-400 dark:text-zinc-500 font-normal"><?= esc($rf['position']) ?> &bull; <?= esc($rf['email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                                        <?= esc($rf['department']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $rf['target_badge'] ?> shadow-2xs">
                                            <?= esc($rf['target_label']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $rf['eval_badge'] ?> shadow-2xs">
                                            <?= esc($rf['eval_label']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <?php if (!empty($rf['folder_id']) && !in_array($rf['folder_status'] ?? '', [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value, 'unstarted'])): ?>
                                            <a href="<?= site_url('ratings/show/' . $rf['folder_id']) ?>"
                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 dark:hover:bg-white/5 transition-colors shadow-2xs">
                                                Inspect
                                            </a>
                                        <?php elseif (in_array($rf['folder_status'] ?? '', [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value])): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-400 dark:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/40">
                                                Drafting
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500 italic">No Folder</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="roster-empty-message" class="hidden">
                            <td colspan="5" class="py-10 px-4 text-center text-zinc-400 dark:text-zinc-500 italic">
                                No employees found matching the selected filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php else: ?>
    <!-- MAIN SCROLLABLE CONTENT (ADMIN EXECUTIVE ANALYTICS & MASTERLIST) -->
    <div class="p-3.5 sm:p-5 lg:p-6 xl:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-3.5 sm:space-y-5">

        <!-- 1. EXECUTIVE ANALYTICS VIEW -->
        <div id="dashboard-view-analytics" class="space-y-5">

        <!-- 1. TOP KPI SUMMARY METRIC CARDS (2x2 Grid on Mobile, 4-Cols on Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            
            <!-- Card 1: Total Ratees -->
            <div class="p-3.5 sm:p-5 rounded-xl spms-kpi-card shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider spms-kpi-label">Total Ratees</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 spms-kpi-icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight spms-kpi-val"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-[11px] sm:text-xs font-semibold spms-kpi-sub">Personnel</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400 dark:bg-zinc-500 shrink-0"></span>
                    <span>100% active roster</span>
                </div>
            </div>

            <!-- Card 2: Overall Average Rating -->
            <div class="p-3.5 sm:p-5 rounded-xl spms-kpi-card shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider spms-kpi-label">Average Rating</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 spms-kpi-icon-amber">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight spms-kpi-val"><?= $overallAverage > 0 ? number_format($overallAverage, 2) : '——' ?></span>
                        <span class="text-[11px] sm:text-xs font-semibold spms-kpi-sub">/ 5.00</span>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-black uppercase tracking-wider spms-badge-amber">
                        <?= !empty($adjectivalLabel) && $overallAverage > 0 ? esc($adjectivalLabel) : 'NOT YET RATED' ?>
                    </span>
                </div>
            </div>

            <!-- Card 3: Target Compliance -->
            <div class="p-3.5 sm:p-5 rounded-xl spms-kpi-card shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider spms-kpi-label">Target Compliance</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 spms-kpi-icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight spms-kpi-val"><?= $targetComplianceRate ?>%</span>
                        <span class="text-[10px] sm:text-xs font-normal spms-kpi-sub">(<?= number_format($pipeline['stage1']['approved']) ?> of <?= number_format($totalPersonnel) ?>)</span>
                    </div>
                </div>
                <div class="w-full rounded-full h-1 overflow-hidden mt-1 spms-progress-track">
                    <div class="h-1 rounded-full transition-all duration-500 spms-progress-fill" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                </div>
            </div>

            <!-- Card 4: Cycle Completion -->
            <div class="p-3.5 sm:p-5 rounded-xl spms-kpi-card shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider spms-kpi-label">Cycle Completion</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 spms-kpi-icon-amber">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight spms-kpi-val"><?= $evalCompletionRate ?>%</span>
                        <span class="text-[10px] sm:text-xs font-normal spms-kpi-sub">(<?= number_format($pipeline['stage3']['completed']) ?> of <?= number_format($totalPersonnel) ?> finalized)</span>
                    </div>
                </div>
                <div class="w-full rounded-full h-1 overflow-hidden mt-1 spms-progress-track">
                    <div class="h-1 rounded-full transition-all duration-500 spms-progress-fill" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                </div>
            </div>

        </div>

        <!-- 2. SPMS 4-STAGE LIFECYCLE PIPELINE (CSC MC No. 6, s. 2012) -->
        <?php
            // Dynamic Active Stage Resolution
            $activeStageNum = 1;
            $activeStageName = 'Planning';
            if (($pipeline['stage3']['completed'] ?? 0) > 0 && ($totalPersonnel > 0 && $pipeline['stage3']['completed'] >= $totalPersonnel)) {
                $activeStageNum = 4;
                $activeStageName = 'Rewarding';
            } elseif (($pipeline['stage3']['completed'] ?? 0) > 0 || ($pipeline['stage3']['evaluating'] ?? 0) > 0 || ($pipeline['stage3']['submitted'] ?? 0) > 0) {
                $activeStageNum = 3;
                $activeStageName = 'Review';
            } elseif (($pipeline['stage1']['approved'] ?? 0) > 0 && (($pipeline['stage2']['mov_count'] ?? 0) > 0 || ($pipeline['stage2']['coaching_notes'] ?? 0) > 0)) {
                $activeStageNum = 2;
                $activeStageName = 'Coaching';
            }
            
            $lifecycleStages = [
                1 => 'Planning',
                2 => 'Coaching',
                3 => 'Review',
                4 => 'Rewarding',
            ];
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
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold shrink-0 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border border-zinc-900 dark:border-white shadow-2xs">
                                <span class="w-4 h-4 rounded-full bg-amber-500 text-zinc-950 text-[9px] font-black flex items-center justify-center shrink-0"><?= $sNum ?></span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php elseif ($sNum < $activeStageNum): ?>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold shrink-0 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                <span class="w-4 h-4 rounded-full bg-amber-500 text-zinc-950 text-[9px] font-bold flex items-center justify-center shrink-0">✓</span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold shrink-0 bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/80 text-zinc-500 dark:text-zinc-400">
                                <span class="w-4 h-4 rounded-full border border-zinc-300 dark:border-zinc-600 text-[9px] font-bold flex items-center justify-center shrink-0"><?= $sNum ?></span>
                                <span><?= esc($sTitle) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 4 Stage Cards Grid on PC (4 Columns) / Stacked on Mobile -->
            <div class="spms-lifecycle-grid">
                
                <!-- STAGE 1: Target Commitment (Expanded / Current) -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box-active flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">STAGE 1</span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 font-extrabold">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                CURRENT
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Target Commitment</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Target Setting Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= $pipeline['stage1']['approved'] ?>
                                    <span class="text-zinc-400 dark:text-zinc-400 font-normal">/ <?= $totalPersonnel ?> Approved</span>
                                </span>
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1 overflow-hidden">
                                <div class="bg-amber-500 h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Approved Targets</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage1']['approved'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>In Review</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage1']['pending'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Needs Revision</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage1']['returned'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Draft Mode</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage1']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: Monitoring & Coaching -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between transition-all hover:border-zinc-300 dark:hover:border-zinc-600">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(2)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 2</span>
                                <svg id="stage-2-chevron" class="w-4 h-4 text-zinc-400 dark:text-zinc-400 transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white mt-1.5">Monitoring &amp; Coaching</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Execution &amp; Evidence</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-2-content" class="hidden pt-3 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Active Execution</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage2']['active_execution'] ?? $pipeline['stage1']['approved'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>MOV Attachments</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage2']['mov_count'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Coaching Feedback</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage2']['coaching_notes'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: Review & Evaluation (Matches Image 2) -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 3</span>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Review &amp; Evaluation</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Accomplishment Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">
                                    <?= $pipeline['stage3']['completed'] ?>
                                    <span class="text-zinc-400 dark:text-zinc-400 font-normal">/ <?= $totalPersonnel ?> Evaluated</span>
                                </span>
                                <span class="text-xs font-bold text-zinc-400 dark:text-zinc-400">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1 overflow-hidden">
                                <div class="bg-zinc-700 dark:bg-zinc-500 h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Approved Ratings</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage3']['completed'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Under Evaluation</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage3']['evaluating'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Submitted Awaiting</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage3']['submitted'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Draft Accomplishment</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage3']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 4: Rewarding & Dev. -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between transition-all hover:border-zinc-300 dark:hover:border-zinc-600">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(4)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">STAGE 4</span>
                                <svg id="stage-4-chevron" class="w-4 h-4 text-zinc-400 dark:text-zinc-400 transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white mt-1.5">Rewarding &amp; Dev.</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Incentives &amp; HR Phase</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-4-content" class="hidden pt-3 border-t border-zinc-200/70 dark:border-zinc-800 space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>PBB / Bonus Eligible</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage4']['pbb_eligible'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                            <span>Development Needed</span>
                            <span class="font-bold text-zinc-900 dark:text-white"><?= $pipeline['stage4']['dev_needed'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. TOP PERFORMING PERSONNEL LEADERBOARD -->
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
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                        <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span><?= count($topPerformers) ?> Ranked</span>
                    </span>
                </div>

                <?php if (!empty($topPerformers)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[380px] overflow-y-auto custom-scrollbar pr-1">
                        <?php 
                        $topList = array_slice($topPerformers, 0, 9);
                        foreach ($topList as $idx => $tp): 
                            $rank = $idx + 1;
                            $rankBadge = 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700';
                            $rankEmoji = "#{$rank}";
                            if ($rank === 1) {
                                $rankBadge = 'bg-amber-400 text-zinc-950 font-black shadow-xs border-amber-300';
                                $rankEmoji = '1 🥇';
                            } elseif ($rank === 2) {
                                $rankBadge = 'bg-slate-300 text-zinc-900 font-black shadow-xs border-slate-400';
                                $rankEmoji = '2 🥈';
                            } elseif ($rank === 3) {
                                $rankBadge = 'bg-amber-700 text-amber-50 font-black shadow-xs border-amber-800';
                                $rankEmoji = '3 🥉';
                            }
                        ?>
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3 transition-colors hover:bg-zinc-100/70 dark:hover:bg-zinc-800/40">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Rank Badge -->
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs border <?= $rankBadge ?> shrink-0">
                                        <?= $rankEmoji ?>
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
                                    <?php if (!empty($tp['folder_id'])): ?>
                                        <a href="<?= site_url('ratings/show/' . $tp['folder_id']) ?>" 
                                           class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-white dark:hover:bg-zinc-800 transition-colors shadow-2xs"
                                           title="Inspect evaluation details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

            <!-- Footer Action -->
            <div class="pt-3 mt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                <span>Incentive &amp; PBB Eligible: <strong class="text-emerald-600 dark:text-emerald-400"><?= $pipeline['stage4']['pbb_eligible'] ?? 0 ?> personnel</strong></span>
                <button type="button" onclick="switchDashboardView('masterlist')" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">
                    View All in Master List &rarr;
                </button>
            </div>
        </div>

        <!-- 4. STAGE 2: MONITORING, COACHING & TARGET CALIBRATION (PER-OFFICE VIEW) -->
        <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            STAGE 2 MONITORING
                        </span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Target Calibration &amp; Per-Office Compliance</h3>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Monitoring and coaching status across all academic colleges and administrative delivery units.
                    </p>
                </div>

                <!-- Search Bar for Offices -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="office-table-search" onkeyup="filterOfficeLeaderboard()"
                           placeholder="Search college or office..."
                           class="w-full text-xs font-medium py-2 pl-9 pr-3 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400/40 shadow-2xs" />
                </div>
            </div>

            <!-- 4 Mini Metric Cards for Stage 2 Calibration -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Calibrated Targets</div>
                    <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5"><?= $pipeline['stage1']['approved'] ?></div>
                    <div class="text-[10px] text-zinc-400 mt-0.5">Locked target commitments</div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Under Coaching / Review</div>
                    <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5"><?= $pipeline['stage1']['pending'] ?></div>
                    <div class="text-[10px] text-zinc-400 mt-0.5">Awaiting supervisor review</div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Calibration Adjustments</div>
                    <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5"><?= $pipeline['stage1']['returned'] ?></div>
                    <div class="text-[10px] text-zinc-400 mt-0.5">Returned for target alignment</div>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-100 dark:border-zinc-800">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Evidence Attachments</div>
                    <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5"><?= $pipeline['stage2']['mov_count'] ?? 0 ?></div>
                    <div class="text-[10px] text-zinc-400 mt-0.5">MOV files &amp; cloud drive links</div>
                </div>
            </div>

            <!-- Per-Office Table -->
            <div class="overflow-x-auto custom-scrollbar rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left border-collapse" id="office-leaderboard-table">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 text-[10px] sm:text-[11px] font-bold uppercase text-zinc-500 dark:text-zinc-400 tracking-wider">
                            <th class="py-3 px-3.5">Office / Academic College</th>
                            <th class="py-3 px-3 text-center">Headcount</th>
                            <th class="py-3 px-3 text-center">Target Calibration</th>
                            <th class="py-3 px-3 text-center">Revisions</th>
                            <th class="py-3 px-3 text-center">Final Evaluations</th>
                            <th class="py-3 px-3 text-center">Average Rating</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3.5 text-right">Drill Down</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-[#1a2b22] text-xs">
                        <?php if (empty($deptLeaderboard)): ?>
                            <tr>
                                <td colspan="8" class="py-8 px-4 text-center text-zinc-400 italic">
                                    No office records available for this cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deptLeaderboard as $dept): ?>
                                <tr class="office-row hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors" data-name="<?= strtolower(esc($dept['name'])) ?>">
                                    <td class="py-3 px-3.5 font-bold text-zinc-900 dark:text-white whitespace-nowrap">
                                        <span class="truncate block max-w-[220px]" title="<?= esc($dept['name']) ?>">
                                            <?= esc($dept['name']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-semibold text-zinc-700 dark:text-zinc-300">
                                        <?= $dept['headcount'] ?>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php 
                                        $tPct = $dept['headcount'] > 0 ? round(($dept['target_approved'] / $dept['headcount']) * 100) : 0;
                                        ?>
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= min(100, $tPct) ?>%;"></div>
                                            </div>
                                            <span class="font-bold text-[11px] text-zinc-900 dark:text-white"><?= $dept['target_approved'] ?>/<?= $dept['headcount'] ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <?php if ($dept['revisions_needed'] > 0): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                                                <?= $dept['revisions_needed'] ?> notes
                                            </span>
                                        <?php else: ?>
                                            <span class="text-zinc-400 dark:text-zinc-500 text-[11px]">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= min(100, $dept['compliance_pct']) ?>%;"></div>
                                            </div>
                                            <span class="font-bold text-[11px] text-zinc-900 dark:text-white"><?= $dept['eval_completed'] ?>/<?= $dept['headcount'] ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap font-black text-zinc-900 dark:text-white">
                                        <?= $dept['average_rating'] > 0 ? number_format($dept['average_rating'], 2) : '——' ?>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $dept['badge_class'] ?>">
                                            <?= esc($dept['status_badge']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                        <?php
                                        $matchedUnitId = '';
                                        foreach ($allUnits as $u) {
                                            if (trim(strtolower($u['name'])) === trim(strtolower($dept['name']))) {
                                                $matchedUnitId = $u['id'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($matchedUnitId)): ?>
                                            <button type="button" onclick="selectCollegeOption('<?= $matchedUnitId ?>', '<?= esc(addslashes($dept['name'])) ?>')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all shadow-2xs cursor-pointer">
                                                <span>Drill Down</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[11px] text-zinc-400 italic">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="office-empty-row" class="hidden">
                            <td colspan="8" class="py-8 px-4 text-center text-zinc-400 italic">
                                No offices matching search query.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        </div>
        <!-- /END OF #dashboard-view-analytics -->

        <!-- 2. RATINGS MASTERLIST VIEW (CSC SLIR - SUMMARY LIST OF INDIVIDUAL RATINGS) -->
        <div id="dashboard-view-masterlist" class="hidden space-y-3 sm:space-y-5">
            
            <!-- ======================================================== -->
            <!-- A. MOBILE MASTER LIST VIEW (block md:hidden)             -->
            <!-- ======================================================== -->
            <div class="block md:hidden space-y-2.5">
                
                <!-- 1. EYEBROW & TITLE ROW -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-zinc-800 dark:text-emerald-400 dark:border-zinc-700">
                            CSC MC NO. 6, S. 2012
                        </span>
                        <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                            <?= esc($selectedUnitName ?? 'University-Wide Roster') ?>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-xl font-black text-zinc-900 dark:text-white tracking-tight">
                            Master List
                        </h2>
                        <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-zinc-700 dark:text-zinc-200 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all shadow-xs cursor-pointer"
                           title="Export official CSC Excel workbook">
                            <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Export (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- 2. 2x2 QUICK METRICS CARDS -->
                <div class="grid grid-cols-2 gap-2">
                    <!-- Card 1: TOTAL PERSONNEL -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            TOTAL PERSONNEL
                        </div>
                        <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5">
                            <?= number_format(count($cycleFolders)) ?>
                        </div>
                    </div>

                    <!-- Card 2: PERMANENT -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            PERMANENT
                        </div>
                        <div class="text-xl font-black text-zinc-900 dark:text-white mt-0.5">
                            <?= number_format($empStatusCounts['permanent'] ?? 0) ?>
                        </div>
                    </div>

                    <!-- Card 3: TEMPORARY -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            TEMPORARY
                        </div>
                        <div class="text-xl font-black text-amber-500 dark:text-[#f59e0b] mt-0.5">
                            <?= number_format($empStatusCounts['temporary'] ?? 0) ?>
                        </div>
                    </div>

                    <!-- Card 4: CASUAL & CONTR. -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            CASUAL &amp; CONTR.
                        </div>
                        <div class="text-xl font-black text-sky-500 dark:text-sky-400 mt-0.5">
                            <?= number_format(($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0)) ?>
                        </div>
                    </div>
                </div>

                <!-- 3. SEARCH INPUT (Mobile) -->
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 dark:text-[#4e6b5c]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="masterlist-search-mobile" onkeyup="filterMasterlistMobile()"
                           placeholder="Search name, dept, or position..."
                           style="padding-left: 36px !important;"
                           class="w-full text-xs font-medium py-2 pr-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-[#4e6b5c] focus:outline-none focus:ring-1 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 shadow-2xs" />
                </div>

                <!-- 4. STATUS FILTER PILLS (Mobile - Fits 100% of Screen Width) -->
                <div class="spms-mobile-pills-grid grid grid-cols-4 gap-1.5 w-full py-0.5">
                    <button type="button" onclick="setMasterlistPill('all', this)"
                            data-pill="all"
                            title="All Personnel (<?= count($cycleFolders) ?>)"
                            class="masterlist-tab-btn active flex items-center justify-center py-1.5 px-0.5 rounded-full text-[10px] sm:text-[11px] font-bold transition-all cursor-pointer">
                        <span class="truncate">All (<?= count($cycleFolders) ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('permanent', this)"
                            data-pill="permanent"
                            title="Permanent (<?= $empStatusCounts['permanent'] ?? 0 ?>)"
                            class="masterlist-tab-btn flex items-center justify-center gap-1 py-1.5 px-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold transition-all cursor-pointer">
                        <span class="spms-pill-dot spms-pill-dot-perm"></span>
                        <span class="truncate">Perm (<?= $empStatusCounts['permanent'] ?? 0 ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('temporary', this)"
                            data-pill="temporary"
                            title="Temporary (<?= $empStatusCounts['temporary'] ?? 0 ?>)"
                            class="masterlist-tab-btn flex items-center justify-center gap-1 py-1.5 px-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold transition-all cursor-pointer">
                        <span class="spms-pill-dot spms-pill-dot-temp"></span>
                        <span class="truncate">Temp (<?= $empStatusCounts['temporary'] ?? 0 ?>)</span>
                    </button>
                    <button type="button" onclick="setMasterlistPill('casual_contr', this)"
                            data-pill="casual_contr"
                            title="Casual &amp; Contractual (<?= ($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0) ?>)"
                            class="masterlist-tab-btn flex items-center justify-center gap-1 py-1.5 px-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold transition-all cursor-pointer">
                        <span class="spms-pill-dot spms-pill-dot-casual"></span>
                        <span class="truncate">Casual (<?= ($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0) ?>)</span>
                    </button>
                </div>

                <!-- 5. SECTION SUBHEADER -->
                <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 pt-0.5">
                    PERSONNEL RECORDS (<span id="masterlist-mobile-records-count"><?= count($cycleFolders) ?></span>)
                </div>

                <!-- 6. PERSONNEL CARDS LIST (Mobile) -->
                <div class="space-y-2" id="masterlist-mobile-cards-list">
                    <?php if (empty($cycleFolders)): ?>
                        <div class="py-10 text-center text-zinc-400 dark:text-zinc-500 italic text-xs">
                            No personnel records discovered for this evaluation period.
                        </div>
                    <?php else: ?>
                        <?php foreach ($cycleFolders as $idx => $f): ?>
                            <?php
                            $st = strtolower($f['employment_status'] ?? 'permanent');
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-zinc-800 dark:text-emerald-400 dark:border-zinc-700';
                            if ($st === 'temporary') {
                                $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-[#241a08] dark:text-[#f59e0b] dark:border-[#47340f]';
                            } elseif ($st === 'casual' || $st === 'contractual') {
                                $badgeClass = 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-[#082230] dark:text-sky-400 dark:border-[#0f435c]';
                            }
                            ?>
                            <div class="masterlist-row-card py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-1.5 transition-all"
                                 data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                 data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                 data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                 data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                 data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>"
                                 data-rating="<?= esc($f['rating_num'] ?? '') ?>"
                                 data-adjectival="<?= esc(strtolower($f['adjectival_display'] ?? $f['adjectival_label'] ?? '')) ?>">
                                
                                <!-- Top Row: Avatar + Name + Email | Status Badge -->
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-200 shrink-0">
                                            <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-xs sm:text-sm text-zinc-900 dark:text-white truncate">
                                                <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                            </div>
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">
                                                <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider shrink-0 border <?= $badgeClass ?>">
                                        <?= esc(strtoupper($f['employment_status'] ?? 'Permanent')) ?>
                                    </span>
                                </div>

                                <!-- Middle Row: Department (left) | Position (right) -->
                                <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-zinc-100 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400">
                                    <div class="truncate min-w-0 flex-1 pr-2 text-[11px] font-medium">
                                        <?= esc($f['department'] ?? '—') ?>
                                    </div>
                                    <div class="font-bold text-[11px] text-zinc-900 dark:text-white shrink-0 text-right">
                                        <?= esc($f['position'] ?? '—') ?>
                                    </div>
                                </div>

                                <!-- Bottom Row: Grade (Numerical Rating) & Evaluation (Adjectival Rating) -->
                                <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-zinc-100 dark:border-zinc-800/60">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Grade:</span>
                                        <?php if ($f['rating_num'] !== null): ?>
                                            <span class="font-black text-xs text-zinc-900 dark:text-white tabular-nums">
                                                <?= number_format($f['rating_num'], 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500 italic">—</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border <?= $f['adjectival_badge'] ?? 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60' ?>">
                                            <?= esc($f['adjectival_display'] ?? $f['adjectival_label'] ?? 'Not Yet Rated') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Empty State (Mobile) -->
                    <div id="masterlist-mobile-empty" class="hidden py-10 text-center">
                        <div class="flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500">
                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="text-xs font-semibold">No personnel records found matching your filter criteria.</p>
                        </div>
                    </div>
                </div>

                <!-- 7. COMPACT CHEVRON PAGINATION BAR (Mobile) -->
                <div class="rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 px-3.5 py-2 flex items-center justify-between shadow-xs">
                    <button type="button" id="masterlist-mobile-prev-btn" onclick="goToMasterlistPage(masterlistCurrentPage - 1)"
                            class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white p-1.5 rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 select-none">
                        Showing <span id="masterlist-mobile-range" class="font-bold text-zinc-900 dark:text-white">1 – 6</span> of <span id="masterlist-mobile-total" class="font-bold text-zinc-900 dark:text-white"><?= count($cycleFolders) ?></span> records
                    </div>
                    <button type="button" id="masterlist-mobile-next-btn" onclick="goToMasterlistPage(masterlistCurrentPage + 1)"
                            class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white p-1.5 rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- B. DESKTOP MASTER LIST VIEW (hidden md:block)            -->
            <!-- ======================================================== -->
            <div class="hidden md:block space-y-5">
                 <!-- MASTERLIST HEADER & EXPORT ACTION CARD (Desktop) -->
                <div class="p-4 sm:p-5 lg:p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                                    CSC MC No. 6, s. 2012 Prescribed
                                </span>
                                <span class="text-xs font-semibold text-zinc-400">|</span>
                                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                    <?= esc($selectedUnitName ?? 'University-Wide Roster') ?>
                                </span>
                            </div>
                            <h2 class="text-xl lg:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">
                                Master List
                            </h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-3xl">
                                Consolidated institutional master list of all active plantilla faculty and staff for <?= esc($activeCycle['title'] ?? 'this evaluation period') ?>. Formatted in accordance with Civil Service Commission Strategic Performance Management System guidelines.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                               class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs font-black text-white bg-[#114232] hover:bg-[#16533f] text-white border border-[#1b5e47] transition-all shadow-md hover:shadow-lg hover:scale-[1.01] active:scale-[0.99] cursor-pointer"
                               title="Generate and download official CSC landscape Excel workbook">
                                <svg class="w-4 h-4 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Export Master List (.xlsx)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Masterlist Stats Row (Desktop) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 sm:pt-5 mt-4 sm:mt-5 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 truncate">Total Personnel</div>
                            <div class="text-base font-black text-zinc-900 dark:text-white mt-0.5"><?= number_format(count($cycleFolders)) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 truncate">Permanent</div>
                            <div class="text-base font-black text-zinc-900 dark:text-white mt-0.5"><?= number_format($empStatusCounts['permanent'] ?? 0) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 truncate">Temporary</div>
                            <div class="text-base font-black text-amber-600 dark:text-amber-400 mt-0.5"><?= number_format($empStatusCounts['temporary'] ?? 0) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400 truncate" title="Casual &amp; Contractual">Casual &amp; Contr.</div>
                            <div class="text-base font-black text-sky-600 dark:text-sky-400 mt-0.5"><?= number_format(($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>

                <!-- SEARCH & FILTER TOOLBAR (Desktop) -->
                <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-lg">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" id="masterlist-search" onkeyup="filterMasterlist()"
                                   placeholder="Search personnel name, email, department, or position..."
                                   class="w-full text-xs font-medium py-2.5 pl-10 pr-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 shadow-2xs" />
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <select id="masterlist-status-filter" onchange="filterMasterlist()"
                                    class="text-xs font-semibold px-3 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-zinc-400/40 dark:focus:ring-zinc-600/40 cursor-pointer shadow-2xs [color-scheme:light] dark:[color-scheme:dark]">
                                <option value="">All Appointments</option>
                                <option value="permanent">Permanent</option>
                                <option value="temporary">Temporary</option>
                                <option value="casual">Casual</option>
                                <option value="contractual">Contractual</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Filter Pill Buttons (Desktop) -->
                    <div class="flex items-center gap-2 pb-1 overflow-x-auto custom-scrollbar pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" onclick="setMasterlistPill('all', this)"
                                data-pill="all"
                                class="masterlist-tab-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            All Personnel (<?= count($cycleFolders) ?>)
                        </button>
                        <button type="button" onclick="setMasterlistPill('permanent', this)"
                                data-pill="permanent"
                                class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span>Permanent (<?= $empStatusCounts['permanent'] ?? 0 ?>)</span>
                        </button>
                        <button type="button" onclick="setMasterlistPill('temporary', this)"
                                data-pill="temporary"
                                class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Temporary (<?= $empStatusCounts['temporary'] ?? 0 ?>)</span>
                        </button>
                        <button type="button" onclick="setMasterlistPill('casual_contr', this)"
                                data-pill="casual_contr"
                                class="masterlist-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                            <span>Casual &amp; Contr. (<?= ($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0) ?>)</span>
                        </button>
                    </div>
                </div>

                <!-- MASTERLIST DATA CONTAINER (Desktop Table & Pagination) -->
                <div id="masterlist-container" class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse" id="masterlist-table">
                            <thead>
                                <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-zinc-500 dark:text-zinc-400 tracking-wider">
                                    <th class="py-3 px-3 w-10 text-center">#</th>
                                    <th class="py-3 px-3">Personnel / Ratee</th>
                                    <th class="py-3 px-3">College / Division</th>
                                    <th class="py-3 px-3">Position</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Employment Status</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Numerical Rating</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Adjectival Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-[#1a2b22] text-xs">
                                <?php if (empty($cycleFolders)): ?>
                                    <tr>
                                        <td colspan="7" class="py-12 px-4 text-center text-zinc-400 dark:text-zinc-500 italic">
                                            No personnel records discovered for this evaluation period.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cycleFolders as $idx => $f): ?>
                                        <tr class="masterlist-row hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors"
                                            data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                            data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                            data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                            data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                            data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>"
                                            data-rating="<?= esc($f['rating_num'] ?? '') ?>"
                                            data-adjectival="<?= esc(strtolower($f['adjectival_display'] ?? $f['adjectival_label'] ?? '')) ?>">
                                            
                                            <!-- Index -->
                                            <td class="py-3 px-3 text-center font-bold text-zinc-400 dark:text-zinc-500 w-10">
                                                <?= $idx + 1 ?>
                                            </td>

                                            <!-- Personnel Name & Email -->
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-7 h-7 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center font-black text-[11px] text-zinc-700 dark:text-zinc-200 shrink-0">
                                                        <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                                    </div>
                                                    <div class="min-w-0 max-w-[130px] sm:max-w-[170px] xl:max-w-none">
                                                        <div class="font-bold text-zinc-900 dark:text-white truncate">
                                                            <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                                        </div>
                                                        <div class="text-[11px] text-zinc-400 truncate">
                                                            <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- College / Department -->
                                            <td class="py-3 px-3 text-zinc-700 dark:text-zinc-300 font-medium">
                                                <span class="truncate block max-w-[130px] sm:max-w-[160px] xl:max-w-[220px]" title="<?= esc($f['department'] ?? '—') ?>">
                                                    <?= esc($f['department'] ?? '—') ?>
                                                </span>
                                            </td>

                                            <!-- Plantilla Position -->
                                            <td class="py-3 px-3 text-zinc-500 dark:text-zinc-400 font-medium">
                                                <span class="truncate block max-w-[110px] sm:max-w-[140px] xl:max-w-[180px]" title="<?= esc($f['position'] ?? '—') ?>">
                                                    <?= esc($f['position'] ?? '—') ?>
                                                </span>
                                            </td>

                                            <!-- Employment Status -->
                                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $f['emp_status_badge'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#102a1e] dark:text-emerald-400 dark:border-[#1b4330]' ?>">
                                                    <?= esc($f['employment_status'] ?? 'Permanent') ?>
                                                </span>
                                            </td>

                                            <!-- Numerical Rating (Grade) -->
                                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                                <?php if ($f['rating_num'] !== null): ?>
                                                    <span class="font-black text-xs text-zinc-900 dark:text-white tabular-nums tracking-tight">
                                                        <?= number_format($f['rating_num'], 2) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-zinc-400 dark:text-zinc-500 text-xs font-normal italic">—</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Adjectival Rating (Evaluation) -->
                                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $f['adjectival_badge'] ?? 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800/60 dark:text-zinc-400 dark:border-zinc-700/60' ?>">
                                                    <?= esc($f['adjectival_display'] ?? $f['adjectival_label'] ?? 'Not Yet Rated') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <tr id="masterlist-empty-row" class="hidden">
                                    <td colspan="7" class="py-12 px-4 text-center">
                                        <div class="flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500">
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

                    <!-- Masterlist Responsive Pagination Footer (Desktop) -->
                    <div class="px-4 sm:px-6 py-3.5 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                            <span class="text-zinc-500 dark:text-zinc-400">
                                Showing <span id="masterlist-visible-range" class="font-bold text-zinc-800 dark:text-white">0</span> of <span id="masterlist-visible-total" class="font-bold text-zinc-800 dark:text-white"><?= count($cycleFolders) ?></span> personnel
                            </span>
                            
                            <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                                <span class="hidden sm:inline text-[11px] font-medium">Rows:</span>
                                <select id="masterlist-per-page" onchange="changeMasterlistPerPage(this.value)"
                                        class="text-[11px] font-bold px-2 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-zinc-500 cursor-pointer shadow-2xs">
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

            </div>

        </div>
        <!-- /END OF #dashboard-view-masterlist -->

    </div>
<?php endif; ?>
</div>

<script>
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
        if (container.offsetWidth === 0 || container.offsetHeight === 0) return;

        const left = target.offsetLeft;
        const top = target.offsetTop;
        const width = target.offsetWidth;
        const height = target.offsetHeight;

        if (width === 0 || height === 0) return;

        if (animate) {
            indicator.style.transition = 'transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), width 0.24s cubic-bezier(0.16, 1, 0.3, 1), height 0.18s ease, opacity 0.15s ease';
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

    if (view === 'masterlist') {
        analyticsView.classList.add('hidden');
        masterlistView.classList.remove('hidden');
        if (animate) {
            masterlistView.classList.remove('spms-view-enter');
            void masterlistView.offsetWidth;
            masterlistView.classList.add('spms-view-enter');
        }
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.add('hidden');

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
        localStorage.setItem('spms_dash_view', 'masterlist');
        filterMasterlist(false);
    } else {
        masterlistView.classList.add('hidden');
        analyticsView.classList.remove('hidden');
        if (animate) {
            analyticsView.classList.remove('spms-view-enter');
            void analyticsView.offsetWidth;
            analyticsView.classList.add('spms-view-enter');
        }
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.remove('hidden');

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
        localStorage.setItem('spms_dash_view', 'analytics');
    }

    updateDashboardPill(view, animate);
}

// Immediate alignment on script load
requestAnimationFrame(() => {
    const initialView = localStorage.getItem('spms_dash_view') || 'analytics';
    updateDashboardPill(initialView, false);
});

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
let currentMasterlistPill = 'all';
let masterlistCurrentPage = 1;
let masterlistPerPage = (typeof window !== 'undefined' && window.innerWidth < 768) ? 6 : 50;

function setMasterlistPill(pillType, btn) {
    currentMasterlistPill = pillType;
    document.querySelectorAll('.masterlist-tab-btn').forEach(b => {
        if (b.getAttribute('data-pill') === pillType) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
    filterMasterlist(true);
}

function filterMasterlistMobile() {
    const mobileInput = document.getElementById('masterlist-search-mobile');
    const deskInput   = document.getElementById('masterlist-search');
    if (mobileInput && deskInput) {
        deskInput.value = mobileInput.value;
    }
    filterMasterlist(true);
}

function filterMasterlist(resetPage = true) {
    if (resetPage) {
        masterlistCurrentPage = 1;
    }

    const searchInputDesk   = document.getElementById('masterlist-search');
    const searchInputMobile = document.getElementById('masterlist-search-mobile');
    const statusFilter      = document.getElementById('masterlist-status-filter');

    const query = ((searchInputMobile && searchInputMobile.value.trim() !== '') 
                    ? searchInputMobile.value 
                    : (searchInputDesk ? searchInputDesk.value : '')).toLowerCase().trim();
    const empStatus = statusFilter ? statusFilter.value.trim().toLowerCase() : '';

    const desktopRows = document.querySelectorAll('.masterlist-row');
    const mobileCards = document.querySelectorAll('.masterlist-row-card');

    let matchingIndexes = [];
    desktopRows.forEach((row, idx) => {
        const name         = row.getAttribute('data-name') || '';
        const email        = row.getAttribute('data-email') || '';
        const dept         = row.getAttribute('data-dept') || '';
        const pos          = row.getAttribute('data-pos') || '';
        const rowEmpStatus = (row.getAttribute('data-emp-status') || '').toLowerCase();
        const rating       = row.getAttribute('data-rating') || '';
        const adjectival   = (row.getAttribute('data-adjectival') || '').toLowerCase();

        // Pill filter
        let matchesPill = false;
        if (currentMasterlistPill === 'all') {
            matchesPill = true;
        } else if (currentMasterlistPill === 'permanent') {
            matchesPill = rowEmpStatus === 'permanent';
        } else if (currentMasterlistPill === 'temporary') {
            matchesPill = rowEmpStatus === 'temporary';
        } else if (currentMasterlistPill === 'casual') {
            matchesPill = rowEmpStatus === 'casual';
        } else if (currentMasterlistPill === 'contractual') {
            matchesPill = rowEmpStatus === 'contractual';
        } else if (currentMasterlistPill === 'casual_contr') {
            matchesPill = (rowEmpStatus === 'casual' || rowEmpStatus === 'contractual');
        }

        // Search match
        const matchesSearch = !query || name.includes(query) || email.includes(query) || dept.includes(query) || pos.includes(query) || rating.includes(query) || adjectival.includes(query);

        // Employment status dropdown match
        const matchesStatus = !empStatus || rowEmpStatus === empStatus;

        if (matchesPill && matchesSearch && matchesStatus) {
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

    // Show/hide desktop rows
    desktopRows.forEach((row, idx) => {
        row.style.display = visibleIndexSet.has(idx) ? '' : 'none';
    });

    // Show/hide mobile cards
    mobileCards.forEach((card, idx) => {
        card.style.display = visibleIndexSet.has(idx) ? '' : 'none';
    });

    // Empty state rows
    const desktopEmpty = document.getElementById('masterlist-empty-row');
    const mobileEmpty = document.getElementById('masterlist-mobile-empty');
    if (desktopEmpty) desktopEmpty.classList.toggle('hidden', totalMatching > 0);
    if (mobileEmpty) mobileEmpty.classList.toggle('hidden', totalMatching > 0);

    // Range & Total counters (Desktop)
    const rangeElem = document.getElementById('masterlist-visible-range');
    const totalElem = document.getElementById('masterlist-visible-total');
    if (rangeElem) {
        rangeElem.textContent = (totalMatching === 0) ? '0' : `${startIndex + 1}–${endIndex}`;
    }
    if (totalElem) {
        totalElem.textContent = totalMatching;
    }

    // Range & Total counters (Mobile)
    const mobileRangeElem    = document.getElementById('masterlist-mobile-range');
    const mobileTotalElem    = document.getElementById('masterlist-mobile-total');
    const mobileRecordsBadge = document.getElementById('masterlist-mobile-records-count');
    if (mobileRangeElem) {
        mobileRangeElem.textContent = (totalMatching === 0) ? '0' : `${startIndex + 1} – ${endIndex}`;
    }
    if (mobileTotalElem) {
        mobileTotalElem.textContent = totalMatching;
    }
    if (mobileRecordsBadge) {
        mobileRecordsBadge.textContent = totalMatching;
    }

    // Mobile prev/next buttons
    const prevBtn = document.getElementById('masterlist-mobile-prev-btn');
    const nextBtn = document.getElementById('masterlist-mobile-next-btn');
    if (prevBtn) {
        prevBtn.disabled = masterlistCurrentPage <= 1;
    }
    if (nextBtn) {
        nextBtn.disabled = masterlistCurrentPage >= totalPages;
    }

    // Render pagination buttons (Desktop)
    renderMasterlistPagination(totalPages, masterlistCurrentPage, totalMatching);
}

function renderMasterlistPagination(totalPages, currentPage, totalMatching) {
    const container = document.getElementById('masterlist-pagination-controls');
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Prev Button
    const prevDisabled = currentPage <= 1;
    html += `<button type="button" onclick="goToMasterlistPage(${currentPage - 1})" ${prevDisabled ? 'disabled' : ''} 
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer spms-page-btn ${prevDisabled ? 'opacity-40 cursor-not-allowed' : ''}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Prev</span>
            </button>`;

    // Mobile indicator: "Page X of Y"
    html += `<span class="sm:hidden px-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">
                ${currentPage} / ${totalPages}
            </span>`;

    // Numbered page pills for tablet/desktop
    let pages = [];
    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        pages.push(1);
        if (currentPage > 3) pages.push('...');
        
        let start = Math.max(2, currentPage - 1);
        let end = Math.min(totalPages - 1, currentPage + 1);
        for (let i = start; i <= end; i++) {
            if (!pages.includes(i)) pages.push(i);
        }

        if (currentPage < totalPages - 2) pages.push('...');
        if (!pages.includes(totalPages)) pages.push(totalPages);
    }

    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="hidden sm:inline-flex px-1.5 py-1 text-xs text-zinc-400 dark:text-zinc-600 font-bold">...</span>`;
        } else {
            const isActive = p === currentPage;
            html += `<button type="button" onclick="goToMasterlistPage(${p})" 
                        class="hidden sm:inline-flex w-7 h-7 items-center justify-center rounded-lg text-xs font-bold transition-all cursor-pointer ${isActive ? 'spms-page-btn-active shadow-2xs' : 'spms-page-btn'}">
                        ${p}
                    </button>`;
        }
    });

    // Next Button
    const nextDisabled = currentPage >= totalPages;
    html += `<button type="button" onclick="goToMasterlistPage(${currentPage + 1})" ${nextDisabled ? 'disabled' : ''} 
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer spms-page-btn ${nextDisabled ? 'opacity-40 cursor-not-allowed' : ''}">
                <span class="hidden sm:inline">Next</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>`;

    container.innerHTML = html;
}

function goToMasterlistPage(page) {
    masterlistCurrentPage = page;
    filterMasterlist(false);

    const isMobile = window.innerWidth < 768;
    const targetElem = isMobile 
        ? document.getElementById('masterlist-mobile-cards-list') 
        : document.getElementById('masterlist-container');
    if (targetElem) {
        targetElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function changeMasterlistPerPage(val) {
    masterlistPerPage = val;
    masterlistCurrentPage = 1;
    filterMasterlist(false);
}

// Restore saved view & initialize responsive masterlist pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('spms_dash_view') || 'analytics';
    if (savedView === 'masterlist') {
        switchDashboardView('masterlist', false);
    } else {
        updateDashboardPill('analytics', false);
    }

    const isMobile = window.innerWidth < 768;
    masterlistPerPage = isMobile ? 6 : 50;
    const perPageSelect = document.getElementById('masterlist-per-page');
    if (perPageSelect && !isMobile) {
        perPageSelect.value = "50";
    }
    filterMasterlist(true);
});

window.addEventListener('load', function() {
    const currentView = localStorage.getItem('spms_dash_view') || 'analytics';
    updateDashboardPill(currentView, false);
});

window.addEventListener('resize', function() {
    const currentView = localStorage.getItem('spms_dash_view') || 'analytics';
    updateDashboardPill(currentView, false);
}, { passive: true });

// --- ROSTER / DEAN FILTERING ---
let currentRosterFilter = 'all';

function setRosterFilter(filterType, btn) {
    currentRosterFilter = filterType;
    document.querySelectorAll('.roster-tab-btn').forEach(b => {
        b.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700';
    });
    if (btn) {
        btn.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border border-zinc-900 dark:border-white shadow-2xs';
    }
    filterRoster();
}

function filterRoster() {
    const searchInput = document.getElementById('roster-search');
    const deptSelect  = document.getElementById('roster-dept-filter');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const dept  = deptSelect ? deptSelect.value.trim() : '';

    const rows = document.querySelectorAll('.roster-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowFilter = row.getAttribute('data-filter') || '';
        const rowTarget = row.getAttribute('data-target-state') || '';
        const rowEval   = row.getAttribute('data-eval-state') || '';
        const rowName   = row.getAttribute('data-name') || '';
        const rowDept   = row.getAttribute('data-dept') || '';

        let matchesTab = false;
        if (currentRosterFilter === 'all') {
            matchesTab = true;
        } else if (currentRosterFilter === 'missing') {
            matchesTab = (rowTarget === 'draft' || rowEval === 'draft' || rowFilter === 'missing');
        } else if (currentRosterFilter === 'review') {
            matchesTab = (rowTarget === 'submitted' || rowEval === 'submitted' || rowEval === 'evaluating' || rowFilter === 'review');
        } else if (currentRosterFilter === 'revision') {
            matchesTab = (rowTarget === 'returned' || rowEval === 'returned' || rowFilter === 'revision');
        } else if (currentRosterFilter === 'completed') {
            matchesTab = (rowEval === 'approved' || rowFilter === 'completed');
        }

        const matchesSearch = !query || rowName.includes(query);
        const matchesDept   = !dept || rowDept === dept;

        if (matchesTab && matchesSearch && matchesDept) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyMsg = document.getElementById('roster-empty-message');
    if (emptyMsg) {
        emptyMsg.classList.toggle('hidden', visibleCount > 0);
    }
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

function toggleStageAccordion(stageNum) {
    if (window.innerWidth >= 1024) return;
    const content = document.getElementById(`stage-${stageNum}-content`);
    const chevron = document.getElementById(`stage-${stageNum}-chevron`);
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
</script>

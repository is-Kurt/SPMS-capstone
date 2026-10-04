<div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-visible lg:overflow-hidden">
    
    <style>
        /* SPMS Dashboard Responsive & Dark Theme Engine */
        .dark .spms-mockup-card,
        .dark [class*="dark:bg-[#0c1510]"],
        .dark [class*="dark:bg-[#061810]"] { 
            background-color: #061810 !important; 
        }

        /* Top Mobile Folder Card */
        .spms-folder-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .dark .spms-folder-card,
        .dark [class*="dark:border-[#14422b]"] {
            background-color: #061810 !important;
            border-color: #14422b !important;
        }

        /* View Switcher */
        .spms-tab-container {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
        }
        .dark .spms-tab-container,
        .dark [class*="dark:bg-[#04170e]"] {
            background-color: #04170e !important;
        }
        .dark .spms-tab-container,
        .dark [class*="dark:border-[#0e3a25]"] {
            border-color: #0e3a25 !important;
        }
        .spms-tab-active {
            background-color: #064e3b !important;
            border-color: #047857 !important;
            color: #ffffff !important;
        }
        .dark .spms-tab-active,
        .dark [class*="dark:bg-[#0e422d]"] {
            background-color: #0e422d !important;
            border-color: #195e3f !important;
            color: #ffffff !important;
        }
        .spms-tab-inactive {
            background-color: transparent !important;
            border-color: transparent !important;
            color: #64748b !important;
        }
        .spms-tab-inactive:hover {
            color: #0f172a !important;
        }
        .dark .spms-tab-inactive {
            color: #7f998c !important;
        }
        .dark .spms-tab-inactive:hover {
            color: #ffffff !important;
        }
        .spms-tab-badge {
            background-color: #d1fae5 !important;
            color: #065f46 !important;
        }
        .dark .spms-tab-badge,
        .dark [class*="dark:bg-[#0d2a1d]"] {
            background-color: #0d2a1d !important;
            color: #00df82 !important;
        }

        /* Controls: Select & Queue */
        .spms-select-dark {
            background-color: #ffffff;
            border-color: #e2e8f0;
            color: #0f172a;
        }
        .dark .spms-select-dark,
        .dark [class*="dark:bg-[#061e14]"] {
            background-color: #061e14 !important;
            border-color: #123d27 !important;
            color: #ffffff !important;
        }
        .spms-btn-queue {
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            color: #047857 !important;
        }
        .spms-btn-queue:hover {
            background-color: #d1fae5 !important;
            border-color: #6ee7b7 !important;
            color: #065f46 !important;
        }
        .dark .spms-btn-queue,
        .dark [class*="dark:bg-[#072418]"] {
            background-color: #072418 !important;
            border-color: #144730 !important;
            color: #00df82 !important;
        }
        .dark .spms-btn-queue:hover {
            background-color: #0c3322 !important;
        }

        /* 4 KPI Summary Metric Cards */
        .spms-kpi-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .dark .spms-kpi-card {
            background-color: #061810 !important;
            border: 1px solid #123d27 !important;
        }
        .spms-kpi-val {
            color: #0f172a;
        }
        .dark .spms-kpi-val {
            color: #ffffff !important;
        }
        .spms-kpi-label,
        .spms-kpi-sub {
            color: #64748b;
        }
        .dark .spms-kpi-label,
        .dark .spms-kpi-sub {
            color: #7f998c !important;
        }

        /* Circular KPI Icons */
        .spms-kpi-icon-green {
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            color: #047857 !important;
        }
        .dark .spms-kpi-icon-green,
        .dark [class*="dark:bg-[#0b291c]"] {
            background-color: #0b291c !important;
            border: 1px solid #144730 !important;
            color: #00df82 !important;
        }
        .spms-kpi-icon-amber {
            background-color: #fffbeb !important;
            border: 1px solid #fde68a !important;
            color: #b45309 !important;
        }
        .dark .spms-kpi-icon-amber,
        .dark [class*="dark:bg-[#241a08]"] {
            background-color: #241a08 !important;
            border: 1px solid #453412 !important;
            color: #f59e0b !important;
        }

        /* Progress Bars & Badges */
        .spms-progress-track {
            background-color: #e2e8f0 !important;
        }
        .dark .spms-progress-track,
        .dark [class*="dark:bg-[#0d2a1d]"] {
            background-color: #0d2a1d !important;
        }
        .spms-progress-fill {
            background-color: #059669 !important;
        }
        .dark .spms-progress-fill,
        .dark [class*="dark:bg-[#00df82]"] {
            background-color: #00df82 !important;
        }
        .spms-badge-amber {
            background-color: #fef3c7 !important;
            border: 1px solid #fde68a !important;
            color: #92400e !important;
        }
        .dark .spms-badge-amber,
        .dark [class*="dark:bg-[#221706]"] {
            background-color: #241a08 !important;
            border-color: #47340f !important;
            color: #f59e0b !important;
        }
        .dark [class*="dark:border-[#47340f]"] {
            border-color: #47340f !important;
        }

        /* Lifecycle Container & Stage Boxes */
        .spms-lifecycle-container {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .dark .spms-lifecycle-container,
        .dark [class*="dark:bg-[#05140d]"] {
            background-color: #05140d !important;
            border-color: #113320 !important;
        }
        .dark [class*="dark:border-[#113320]"] {
            border-color: #113320 !important;
        }
        .spms-stage-box {
            background-color: rgba(248, 250, 252, 0.7);
            border: 1px solid #e2e8f0;
        }
        .dark .spms-stage-box,
        .dark [class*="dark:bg-[#05130c]"] {
            background-color: #05130c !important;
            border-color: #142a1e !important;
        }
        .spms-stage-box-active {
            background-color: rgba(248, 250, 252, 0.9);
            border: 1px solid #10b981;
        }
        .dark .spms-stage-box-active {
            background-color: #05130c !important;
            border-color: #18422b !important;
        }

        /* SPMS 4-Stage Performance Lifecycle Grid: 4 Columns on PC, Stacked on Mobile */
        .spms-lifecycle-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .spms-lifecycle-grid {
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                gap: 0.75rem !important;
                align-items: stretch !important;
            }
            .spms-stage-box,
            .spms-stage-box-active {
                min-height: 250px !important;
                height: 100% !important;
            }
            .spms-stage-accordion-btn {
                cursor: default !important;
                pointer-events: none !important;
            }
        }

        @media (max-width: 1023px) {
            .spms-lifecycle-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.75rem !important;
            }
            .spms-stage-box,
            .spms-stage-box-active {
                min-height: auto !important;
            }
        }
        .dark [class*="dark:border-[#18422b]"] {
            border-color: #18422b !important;
        }
        .dark [class*="dark:border-[#142a1e]"] {
            border-color: #142a1e !important;
        }
        .dark [class*="dark:border-[#123d27]"] {
            border-color: #123d27 !important;
        }
        .dark [class*="dark:border-[#144730]"] {
            border-color: #144730 !important;
        }
        .dark [class*="dark:bg-[#0d2317]"] {
            background-color: #0d2317 !important;
        }
        .dark [class*="dark:border-[#173826]"] {
            border-color: #173826 !important;
        }
        .dark [class*="dark:bg-[#0c442b]"] {
            background-color: #0c442b !important;
        }
        .dark [class*="dark:border-[#176641]"] {
            border-color: #176641 !important;
        }
        .dark [class*="dark:bg-[#0b2b1d]"] {
            background-color: #0b2b1d !important;
        }
        .dark [class*="dark:border-[#145334]"] {
            border-color: #145334 !important;
        }
        .dark [class*="dark:bg-[#0c3924]"] {
            background-color: #0c3924 !important;
        }
        .dark [class*="dark:bg-[#032316]"] {
            background-color: #032316 !important;
        }
        .dark [class*="dark:border-[#0c4a33]"] {
            border-color: #0c4a33 !important;
        }
        .dark [class*="dark:border-[#1a2b22]"] {
            border-color: #1a2b22 !important;
        }

        /* Color text overrides */
        .dark [class*="dark:text-[#7f998c]"] {
            color: #7f998c !important;
        }
        .dark [class*="dark:text-[#00df82]"] {
            color: #00df82 !important;
        }
        .dark [class*="dark:text-[#f59e0b]"] {
            color: #f59e0b !important;
        }
        .dark [class*="dark:text-[#38bdf8]"] {
            color: #38bdf8 !important;
        }
        .dark [class*="dark:text-[#4e6b5c]"] {
            color: #4e6b5c !important;
        }
        .dark [class*="dark:placeholder-[#4e6b5c]"]::placeholder {
            color: #4e6b5c !important;
        }

        /* Dark mode backgrounds & borders for Master List */
        .dark [class*="dark:bg-[#072e1e]"] { background-color: #072e1e !important; }
        .dark [class*="dark:hover:bg-[#0c442b]"]:hover { background-color: #0c442b !important; }
        .dark [class*="dark:border-[#155237]"] { border-color: #155237 !important; }
        .dark [class*="dark:bg-[#082230]"] { background-color: #082230 !important; }
        .dark [class*="dark:border-[#0f435c]"] { border-color: #0f435c !important; }
        .dark [class*="dark:border-[#123022]"] { border-color: #123022 !important; }

        /* Stepper elements */
        .dark .spms-stepper-line { background-color: #1b3829 !important; }
        .dark .spms-stepper-inactive-circle { border-color: #274736 !important; color: #8fa89b !important; }
        .dark .spms-stepper-inactive-text { color: #8fa89b !important; }

        /* Masterlist Filter Pills */
        .masterlist-tab-btn {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .dark .masterlist-tab-btn {
            background-color: #061810 !important;
            border: 1px solid #123d27 !important;
            color: #ffffff !important;
        }
        .dark .masterlist-tab-btn:hover {
            background-color: #0c2d1e !important;
            border-color: #1a4f33 !important;
        }
        .masterlist-tab-btn.active {
            background-color: #059669 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
        }
        .dark .masterlist-tab-btn.active {
            background-color: #0e422d !important;
            border-color: #195e3f !important;
            color: #ffffff !important;
        }

        /* Masterlist Pagination & Cards */
        .spms-page-btn {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        .spms-page-btn:hover {
            background-color: #e2e8f0;
        }
        .dark .spms-page-btn {
            background-color: #071d13 !important;
            border-color: #143d28 !important;
            color: #7f998c !important;
        }
        .dark .spms-page-btn:hover {
            background-color: #0c3322 !important;
            color: #ffffff !important;
        }
        .spms-page-btn-active {
            background-color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #ffffff !important;
        }
        .dark .spms-page-btn-active {
            background-color: #0e422d !important;
            border-color: #195e3f !important;
            color: #ffffff !important;
        }
        .dark .masterlist-row-card {
            background-color: #061810 !important;
            border-color: #123d27 !important;
        }

        /* Suppress scrollbars across all browsers */
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Mobile Search Input Padding */
        #masterlist-search-mobile {
            padding-left: 36px !important;
        }

        /* Masterlist Filter Pill Dots */
        .spms-pill-dot {
            display: inline-block !important;
            width: 6px !important;
            height: 6px !important;
            min-width: 6px !important;
            min-height: 6px !important;
            border-radius: 9999px !important;
            flex-shrink: 0 !important;
        }
        .spms-pill-dot-perm {
            background-color: #00df82 !important;
        }
        .spms-pill-dot-temp {
            background-color: #f59e0b !important;
        }
        .spms-pill-dot-casual {
            background-color: #38bdf8 !important;
        }

        /* Mobile 4-Column Full-Width Pills Grid */
        .spms-mobile-pills-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 0.375rem !important;
            width: 100% !important;
        }
    </style>
    
    <!-- FOLDER / DASHBOARD HEADER -->
    <div class="px-3.5 sm:px-6 lg:px-8 py-3.5 sm:py-5 border-b border-surface-border shrink-0">

        <!-- MOBILE ACTIVE EVALUATION CYCLE FOLDER DROPDOWN (PULL-DOWN MENU SPEC) -->
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
            <div class="grid grid-cols-2 p-1 rounded-xl border shadow-2xs w-full spms-tab-container dark:bg-[#04170e] dark:border-[#0e3a25]">
                <button type="button" id="btn-view-analytics-mobile" onclick="switchDashboardView('analytics')"
                        class="w-full py-2 px-3 rounded-lg text-xs font-bold text-center transition-all shadow-xs cursor-pointer border spms-tab-active">
                    <span>Overview Analytics</span>
                </button>
                <button type="button" id="btn-view-masterlist-mobile" onclick="switchDashboardView('masterlist')"
                        class="w-full py-2 px-3 rounded-lg text-xs font-bold text-center transition-all cursor-pointer flex items-center justify-center gap-1.5 spms-tab-inactive">
                    <span>Master List</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black spms-tab-badge">
                        <?= count($cycleFolders) ?>
                    </span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- MOBILE ANALYTICS HEADER (Shown only when Overview Analytics is active on mobile) -->
        <div id="analytics-mobile-header" class="lg:hidden space-y-2.5 mb-1">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c] truncate">
                    <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-300 dark:bg-[#0b2b1d] dark:text-[#00df82] dark:border-[#145334] shrink-0">
                    <?= ($sysRole === 'Supervisor') ? esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Departmental Oversight' : 'Collegiate Oversight')) : 'University-Wide Oversight' ?>
                </span>
            </div>
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white truncate">
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
            <!-- Mobile College Filter Dropdown -->
            <?php if (!empty($allUnits)): ?>
            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <select onchange="applyCollegeFilter(this.value)"
                            class="w-full text-xs font-semibold px-3 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs spms-select-dark [color-scheme:light] dark:[color-scheme:dark]">
                        <option value="">All Colleges &amp; Divisions</option>
                        <?php
                        $colleges = [];
                        $adminOffices = [];
                        $subDepts = [];
                        foreach ($allUnits as $u) {
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
                        ?>
                        <optgroup label="Colleges">
                            <?php foreach ($colleges as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$c['id']) ? 'selected' : '' ?>>
                                    <?= esc($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php if (!empty($adminOffices)): ?>
                        <optgroup label="Administrative Offices / Divisions">
                            <?php foreach ($adminOffices as $ao): ?>
                                <option value="<?= $ao['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$ao['id']) ? 'selected' : '' ?>>
                                    <?= esc($ao['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($subDepts)): ?>
                        <optgroup label="Degree Programs / Sub-Departments">
                            <?php foreach ($subDepts as $sd): ?>
                                <option value="<?= $sd['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$sd['id']) ? 'selected' : '' ?>>
                                    <?= esc($sd['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                    </select>
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
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c] truncate">
                    <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Submission Compliance' : 'College Submission Compliance') : 'Executive Performance Analytics' ?>
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-300 dark:bg-[#0b2b1d] dark:text-[#00df82] dark:border-[#145334] shrink-0">
                    <?= ($sysRole === 'Supervisor') ? esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Departmental Oversight' : 'Collegiate Oversight')) : 'University-Wide Oversight' ?>
                </span>
            </div>
            
            <!-- Main Title & Desktop Toolbar -->
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-slate-900 dark:text-white truncate">
                        <?= ($sysRole === 'Supervisor') ? (!empty($isChairScope) ? 'Department Overview' : 'College Overview') : 'Executive Overview' ?>
                    </h1>
                    <?php if ($sysRole === 'Supervisor'): ?>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                            Tracking faculty &amp; staff submissions for <span class="font-bold text-slate-700 dark:text-slate-200"><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Your Department' : 'Your College')) ?></span>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <?php if ($sysRole === 'Admin'): ?>
                    <!-- Desktop View Switcher -->
                    <div class="inline-flex p-1 rounded-xl border shadow-2xs spms-tab-container dark:bg-[#04170e] dark:border-[#0e3a25]">
                        <button type="button" id="btn-view-analytics" onclick="switchDashboardView('analytics')"
                                class="py-2 px-4 rounded-lg text-xs font-bold text-center transition-all shadow-xs cursor-pointer border spms-tab-active">
                            <span>Overview Analytics</span>
                        </button>
                        <button type="button" id="btn-view-masterlist" onclick="switchDashboardView('masterlist')"
                                class="py-2 px-4 rounded-lg text-xs font-bold text-center transition-all cursor-pointer flex items-center justify-center gap-1.5 spms-tab-inactive">
                            <span>Master List</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black spms-tab-badge">
                                <?= count($cycleFolders) ?>
                            </span>
                        </button>
                    </div>

                    <!-- Desktop College Filter & Queue -->
                    <div class="flex items-center gap-2">
                        <select id="college-filter" onchange="applyCollegeFilter(this.value)"
                                class="text-xs font-semibold px-3 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs spms-select-dark [color-scheme:light] dark:[color-scheme:dark]">
                            <option value="">All Colleges &amp; Divisions</option>
                            <?php if (!empty($allUnits)): ?>
                                <optgroup label="Colleges">
                                    <?php foreach ($colleges as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$c['id']) ? 'selected' : '' ?>>
                                            <?= esc($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php if (!empty($adminOffices)): ?>
                                <optgroup label="Administrative Offices / Divisions">
                                    <?php foreach ($adminOffices as $ao): ?>
                                        <option value="<?= $ao['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$ao['id']) ? 'selected' : '' ?>>
                                            <?= esc($ao['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                                <?php if (!empty($subDepts)): ?>
                                <optgroup label="Degree Programs / Sub-Departments">
                                    <?php foreach ($subDepts as $sd): ?>
                                        <option value="<?= $sd['id'] ?>" <?= (!empty($selectedUnitId) && (int)$selectedUnitId === (int)$sd['id']) ? 'selected' : '' ?>>
                                            <?= esc($sd['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            <?php endif; ?>
                        </select>

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
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= !empty($isChairScope) ? 'Department Headcount' : 'College Headcount' ?></span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-info-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= number_format($totalPersonnel) ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Personnel</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22] text-xs text-slate-500 dark:text-slate-400 truncate">
                    <span><?= esc($supervisorCollegeName ?? (!empty($isChairScope) ? 'Department roster' : 'College roster')) ?></span>
                </div>
            </div>

            <!-- Card 2: Targets Submitted & Approved -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Target Commitments</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= $pipeline['target']['approved'] ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">/ <?= $totalPersonnel ?> Approved (<?= $targetComplianceRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: <?= min(100, $targetComplianceRate) ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Missing Target Submissions (Draft) -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border <?= ($pipeline['target']['draft'] > 0) ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20 dark:bg-[#0c1510]' : 'border-slate-200 dark:border-[#1a2b22]' ?> shadow-xs flex flex-col justify-between">
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
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22] text-xs font-semibold truncate">
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
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Final Evaluations</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?= $pipeline['evaluation']['completed'] ?></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">/ <?= $totalPersonnel ?> Finalized (<?= $evalCompletionRate ?>%)</span>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-[#1a2b22]">
                    <div class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: <?= min(100, $evalCompletionRate) ?>%;"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. PHASE SUBMISSION BREAKDOWN (TARGETS VS ACCOMPLISHMENTS) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Phase 1 Card -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">1</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Target Commitment Phase</h3>
                            <span class="text-xs text-slate-400">Submission & Approval status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300">
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
            <div class="p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#1a2b22] mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-black">2</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Accomplishment Report Phase</h3>
                            <span class="text-xs text-slate-400">Evaluation & Grading status</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300">
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

        <!-- 3. "WHO HAS SUBMITTED & WHO HAS NOT" COMPLIANCE ROSTER TABLE -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white"><?= !empty($isChairScope) ? 'Department Personnel Submission Roster' : 'College Personnel Submission Roster' ?></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Track who has submitted their targets/accomplishments and who is still missing or in draft.
                    </p>
                </div>

                <!-- Search & Department Filter Controls -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '')) ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-[#0c4a33] dark:hover:bg-emerald-700 border border-emerald-600/30 transition-all shadow-xs cursor-pointer"
                       title="Download CSC SLIR Excel Sheet for this unit">
                        <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Master List (.xlsx)</span>
                    </a>

                    <?php if (!empty($collegeDepartments) && count($collegeDepartments) > 1 && empty($isChairScope)): ?>
                        <select id="roster-dept-filter" onchange="filterRoster()"
                                class="text-xs font-semibold px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All Sub-Departments</option>
                            <?php foreach ($collegeDepartments as $cd): ?>
                                <option value="<?= esc($cd) ?>"><?= esc($cd) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div class="relative w-64 max-w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="roster-search" onkeyup="filterRoster()"
                               placeholder="Search faculty name or position..."
                               class="w-full text-xs font-medium py-2 pl-9 pr-3 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition-all shadow-2xs" />
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pill Tabs -->
            <div class="flex items-center gap-2 pb-4 overflow-x-auto custom-scrollbar border-b border-slate-100 dark:border-[#1a2b22]">
                <button type="button" onclick="setRosterFilter('all', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-500 text-white shadow-2xs">
                    All Personnel (<?= count($cycleFolders) ?>)
                </button>
                <button type="button" onclick="setRosterFilter('missing', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Missing Submissions (Draft) (<?= $pipeline['target']['draft'] + ($pipeline['evaluation']['draft'] ?? $pipeline['evaluation']['pending']) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('review', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Submitted (In Review) (<?= $pipeline['target']['pending'] + $pipeline['evaluation']['action'] + $pipeline['evaluation']['submitted'] ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('revision', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Needs Revision (<?= $pipeline['target']['returned'] + ($pipeline['evaluation']['returned'] ?? 0) ?>)</span>
                </button>
                <button type="button" onclick="setRosterFilter('completed', this)"
                        class="roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Completed / Approved (<?= $pipeline['evaluation']['completed'] ?>)</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto mt-4 rounded-xl border border-slate-200 dark:border-[#1a2b22]">
                <table class="w-full text-left border-collapse" id="roster-table">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-[#032316] border-b border-slate-200 dark:border-[#1a2b22] text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                            <th class="py-3 px-4">Faculty / Personnel</th>
                            <th class="py-3 px-4">Department / Unit</th>
                            <th class="py-3 px-4 text-center">Phase 1: Targets</th>
                            <th class="py-3 px-4 text-center">Phase 2: Accomplishments</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#1a2b22] text-xs font-semibold">
                        <?php if (empty($cycleFolders)): ?>
                            <tr>
                                <td colspan="5" class="py-10 px-4 text-center text-slate-400 dark:text-slate-500 italic">
                                    No personnel records found for this evaluation cycle.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cycleFolders as $rf): ?>
                                <tr class="roster-row hover:bg-slate-50/80 dark:hover:bg-white/5 transition-colors"
                                    data-filter="<?= esc($rf['submission_filter'] ?? 'all') ?>"
                                    data-target-state="<?= esc($rf['target_state'] ?? 'draft') ?>"
                                    data-eval-state="<?= esc($rf['eval_state'] ?? 'draft') ?>"
                                    data-name="<?= strtolower(esc($rf['full_name'] . ' ' . $rf['email'] . ' ' . $rf['position'])) ?>"
                                    data-dept="<?= esc($rf['department']) ?>">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <span><?= esc($rf['full_name']) ?></span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider <?= ($rf['is_teaching'] == 1) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400' ?>">
                                                <?= ($rf['is_teaching'] == 1) ? 'Teaching' : 'Non-Teaching' ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 font-normal"><?= esc($rf['position']) ?> &bull; <?= esc($rf['email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
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
                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-50 dark:hover:bg-white/5 transition-colors shadow-2xs">
                                                Inspect
                                            </a>
                                        <?php elseif (in_array($rf['folder_status'] ?? '', [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::DRAFT_TARGET->value])): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-slate-400 dark:text-zinc-500 bg-slate-50 dark:bg-zinc-800/40 border border-slate-200 dark:border-zinc-700/40">
                                                Drafting
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 italic">No Folder</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="roster-empty-message" class="hidden">
                            <td colspan="5" class="py-10 px-4 text-center text-slate-400 dark:text-slate-500 italic">
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
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-semibold text-emerald-600 dark:text-[#00df82] mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-[#00df82] shrink-0"></span>
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
        <div class="p-4 sm:p-6 rounded-2xl shadow-xs spms-lifecycle-container">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">SPMS 4-Stage Performance Lifecycle</h2>
                    <p class="text-[10px] sm:text-xs text-slate-500 dark:text-[#7f998c] mt-0.5">CSC MC No. 6, S. 2012 • Standard University Strategic Calibration Cycle</p>
                </div>
                
                <!-- Stepper Flow (Matches Image 2) -->
                <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 sm:pb-0 text-[11px]">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold shrink-0 bg-emerald-500/15 dark:bg-[#0c442b] border border-emerald-500/30 dark:border-[#176641] text-emerald-700 dark:text-[#00df82]">
                        <span class="w-4 h-4 rounded-full bg-[#00df82] text-[#04170e] text-[9px] font-black flex items-center justify-center shrink-0">1</span>
                        <span>Planning</span>
                    </div>
                    <span class="w-3 sm:w-4 h-px bg-slate-300 dark:bg-[#1e4832] shrink-0"></span>
                    <div class="inline-flex items-center gap-1.5 text-slate-500 dark:text-[#7f998c] font-medium shrink-0">
                        <span class="w-4 h-4 rounded-full border border-slate-300 dark:border-[#2a4d3b] text-[9px] font-bold flex items-center justify-center shrink-0">2</span>
                        <span>Coaching</span>
                    </div>
                    <span class="w-3 sm:w-4 h-px bg-slate-300 dark:bg-[#1e4832] shrink-0"></span>
                    <div class="inline-flex items-center gap-1.5 text-slate-500 dark:text-[#7f998c] font-medium shrink-0">
                        <span class="w-4 h-4 rounded-full border border-slate-300 dark:border-[#2a4d3b] text-[9px] font-bold flex items-center justify-center shrink-0">3</span>
                        <span>Review</span>
                    </div>
                    <span class="w-3 sm:w-4 h-px bg-slate-300 dark:bg-[#1e4832] shrink-0"></span>
                    <div class="inline-flex items-center gap-1.5 text-slate-500 dark:text-[#7f998c] font-medium shrink-0">
                        <span class="w-4 h-4 rounded-full border border-slate-300 dark:border-[#2a4d3b] text-[9px] font-bold flex items-center justify-center shrink-0">4</span>
                        <span>Rewarding</span>
                    </div>
                </div>
            </div>

            <!-- 4 Stage Cards Grid on PC (4 Columns) / Stacked on Mobile -->
            <div class="spms-lifecycle-grid">
                
                <!-- STAGE 1: Target Commitment (Expanded / Current) -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box-active flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-[#00df82] border border-emerald-500/20">STAGE 1</span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-[#00df82]">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-[#00df82] animate-pulse"></span>
                                CURRENT
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Target Commitment</h3>
                        <p class="text-[11px] text-slate-500 dark:text-[#7f998c] mt-0.5">Target Setting Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                    <?= $pipeline['stage1']['approved'] ?>
                                    <span class="text-slate-400 dark:text-[#7f998c] font-normal">/ <?= $totalPersonnel ?> Approved</span>
                                </span>
                                <span class="text-xs font-bold text-emerald-600 dark:text-[#00df82]">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-[#0d2a1d] rounded-full h-1 overflow-hidden">
                                <div class="bg-emerald-500 dark:bg-[#00df82] h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage1']['approved'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200/70 dark:border-[#14261d] space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Approved Targets</span>
                            <span class="font-bold text-slate-900 dark:text-[#00df82]"><?= $pipeline['stage1']['approved'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>In Review</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage1']['pending'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Needs Revision</span>
                            <span class="font-bold <?= $pipeline['stage1']['returned'] > 0 ? 'text-amber-500 dark:text-[#f59e0b]' : 'text-slate-900 dark:text-white' ?>"><?= $pipeline['stage1']['returned'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Draft Mode</span>
                            <span class="font-bold text-amber-600 dark:text-[#f59e0b]"><?= $pipeline['stage1']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: Monitoring & Coaching -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between transition-all hover:border-emerald-500/30">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(2)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-[#0d2317] text-slate-500 dark:text-[#7f998c] border border-slate-200 dark:border-[#173826]">STAGE 2</span>
                                <svg id="stage-2-chevron" class="w-4 h-4 text-slate-400 dark:text-[#7f998c] transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1.5">Monitoring &amp; Coaching</h3>
                            <p class="text-[11px] text-slate-500 dark:text-[#7f998c] mt-0.5">Execution &amp; Evidence</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-2-content" class="hidden pt-3 border-t border-slate-200/70 dark:border-[#14261d] space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Active Execution</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage2']['active_execution'] ?? $pipeline['stage1']['approved'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>MOV Attachments</span>
                            <span class="font-bold text-slate-900 dark:text-[#00df82]"><?= $pipeline['stage2']['mov_count'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Coaching Feedback</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage2']['coaching_notes'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: Review & Evaluation (Matches Image 2) -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-[#0d2317] text-slate-500 dark:text-[#7f998c] border border-slate-200 dark:border-[#173826]">STAGE 3</span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Review &amp; Evaluation</h3>
                        <p class="text-[11px] text-slate-500 dark:text-[#7f998c] mt-0.5">Accomplishment Phase</p>

                        <div class="mt-3">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                    <?= $pipeline['stage3']['completed'] ?>
                                    <span class="text-slate-400 dark:text-[#7f998c] font-normal">/ <?= $totalPersonnel ?> Evaluated</span>
                                </span>
                                <span class="text-xs font-bold text-slate-400 dark:text-[#7f998c]">
                                    <?= $totalPersonnel > 0 ? round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100) : 0 ?>%
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-[#0d2a1d] rounded-full h-1 overflow-hidden">
                                <div class="bg-emerald-500 dark:bg-[#00df82] h-1 rounded-full transition-all duration-500" style="width: <?= $totalPersonnel > 0 ? min(100, round(($pipeline['stage3']['completed'] / $totalPersonnel) * 100)) : 0 ?>%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200/70 dark:border-[#14261d] space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Approved Ratings</span>
                            <span class="font-bold text-slate-900 dark:text-[#00df82]"><?= $pipeline['stage3']['completed'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Under Evaluation</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage3']['evaluating'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Submitted Awaiting</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage3']['submitted'] ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Draft Accomplishment</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage3']['draft'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- STAGE 4: Rewarding & Dev. -->
                <div class="p-3.5 sm:p-4 rounded-xl spms-stage-box flex flex-col justify-between transition-all hover:border-emerald-500/30">
                    <div>
                        <button type="button" onclick="toggleStageAccordion(4)" class="w-full text-left cursor-pointer lg:cursor-default spms-stage-accordion-btn">
                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-[#0d2317] text-slate-500 dark:text-[#7f998c] border border-slate-200 dark:border-[#173826]">STAGE 4</span>
                                <svg id="stage-4-chevron" class="w-4 h-4 text-slate-400 dark:text-[#7f998c] transition-transform duration-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1.5">Rewarding &amp; Dev.</h3>
                            <p class="text-[11px] text-slate-500 dark:text-[#7f998c] mt-0.5">Incentives &amp; HR Phase</p>
                        </button>
                    </div>
                    
                    <!-- Collapsible details on mobile only -->
                    <div id="stage-4-content" class="hidden pt-3 border-t border-slate-200/70 dark:border-[#14261d] space-y-1.5 mt-3 text-xs">
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>PBB / Bonus Eligible</span>
                            <span class="font-bold text-slate-900 dark:text-[#00df82]"><?= $pipeline['stage4']['pbb_eligible'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-[#7f998c]">
                            <span>Development Needed</span>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $pipeline['stage4']['dev_needed'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

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
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-[#0c3924] dark:text-[#00df82] dark:border-[#145334]">
                            CSC MC NO. 6, S. 2012
                        </span>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-[#7f998c]">
                            <?= esc($selectedUnitName ?? 'University-Wide Roster') ?>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                            Master List
                        </h2>
                        <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-emerald-700 dark:text-[#00df82] bg-emerald-50 hover:bg-emerald-100 dark:bg-[#072e1e] dark:hover:bg-[#0c442b] border border-emerald-200 dark:border-[#155237] transition-all shadow-xs cursor-pointer"
                           title="Export official CSC Excel workbook">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-[#00df82] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Export (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- 2. 2x2 QUICK METRICS CARDS -->
                <div class="grid grid-cols-2 gap-2">
                    <!-- Card 1: TOTAL PERSONNEL -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c]">
                            TOTAL PERSONNEL
                        </div>
                        <div class="text-xl font-black text-slate-900 dark:text-white mt-0.5">
                            <?= number_format(count($cycleFolders)) ?>
                        </div>
                    </div>

                    <!-- Card 2: PERMANENT -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c]">
                            PERMANENT
                        </div>
                        <div class="text-xl font-black text-emerald-600 dark:text-[#00df82] mt-0.5">
                            <?= number_format($empStatusCounts['permanent'] ?? 0) ?>
                        </div>
                    </div>

                    <!-- Card 3: TEMPORARY -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c]">
                            TEMPORARY
                        </div>
                        <div class="text-xl font-black text-amber-500 dark:text-[#f59e0b] mt-0.5">
                            <?= number_format($empStatusCounts['temporary'] ?? 0) ?>
                        </div>
                    </div>

                    <!-- Card 4: CASUAL & CONTR. -->
                    <div class="py-2.5 px-3 rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] shadow-xs">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c]">
                            CASUAL &amp; CONTR.
                        </div>
                        <div class="text-xl font-black text-sky-500 dark:text-[#38bdf8] mt-0.5">
                            <?= number_format(($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0)) ?>
                        </div>
                    </div>
                </div>

                <!-- 3. SEARCH INPUT (Mobile) -->
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-[#4e6b5c]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="masterlist-search-mobile" onkeyup="filterMasterlistMobile()"
                           placeholder="Search name, dept, or position..."
                           style="padding-left: 36px !important;"
                           class="w-full text-xs font-medium py-2 pr-3 rounded-xl bg-white dark:bg-[#061e14] border border-slate-200 dark:border-[#123d27] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-[#4e6b5c] focus:outline-none focus:ring-1 focus:ring-emerald-500/50 shadow-2xs" />
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
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-[#7f998c] pt-0.5">
                    PERSONNEL RECORDS (<span id="masterlist-mobile-records-count"><?= count($cycleFolders) ?></span>)
                </div>

                <!-- 6. PERSONNEL CARDS LIST (Mobile) -->
                <div class="space-y-2" id="masterlist-mobile-cards-list">
                    <?php if (empty($cycleFolders)): ?>
                        <div class="py-10 text-center text-slate-400 dark:text-slate-500 italic text-xs">
                            No personnel records discovered for this evaluation period.
                        </div>
                    <?php else: ?>
                        <?php foreach ($cycleFolders as $idx => $f): ?>
                            <?php
                            $st = strtolower($f['employment_status'] ?? 'permanent');
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-[#0c3924] dark:text-[#00df82] dark:border-[#145334]';
                            if ($st === 'temporary') {
                                $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-[#241a08] dark:text-[#f59e0b] dark:border-[#47340f]';
                            } elseif ($st === 'casual' || $st === 'contractual') {
                                $badgeClass = 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-[#082230] dark:text-[#38bdf8] dark:border-[#0f435c]';
                            }
                            ?>
                            <div class="masterlist-row-card py-2.5 px-3 rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] shadow-xs space-y-1.5 transition-all"
                                 data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                 data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                 data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                 data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                 data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>">
                                
                                <!-- Top Row: Avatar + Name + Email | Status Badge -->
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#0b291c] border border-slate-200 dark:border-[#144730] flex items-center justify-center font-bold text-xs text-slate-700 dark:text-[#00df82] shrink-0">
                                            <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white truncate">
                                                <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                            </div>
                                            <div class="text-[10px] text-slate-500 dark:text-[#7f998c] truncate">
                                                <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider shrink-0 border <?= $badgeClass ?>">
                                        <?= esc(strtoupper($f['employment_status'] ?? 'Permanent')) ?>
                                    </span>
                                </div>

                                <!-- Bottom Row: Department (left) | Position (right) -->
                                <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-slate-100 dark:border-[#123022] text-slate-500 dark:text-[#7f998c]">
                                    <div class="truncate min-w-0 flex-1 pr-2 text-[11px] font-medium">
                                        <?= esc($f['department'] ?? '—') ?>
                                    </div>
                                    <div class="font-bold text-[11px] text-slate-900 dark:text-white shrink-0 text-right">
                                        <?= esc($f['position'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Empty State (Mobile) -->
                    <div id="masterlist-mobile-empty" class="hidden py-10 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="text-xs font-semibold">No personnel records found matching your filter criteria.</p>
                        </div>
                    </div>
                </div>

                <!-- 7. COMPACT CHEVRON PAGINATION BAR (Mobile) -->
                <div class="rounded-xl bg-white dark:bg-[#061810] border border-slate-200 dark:border-[#123d27] px-3.5 py-2 flex items-center justify-between shadow-xs">
                    <button type="button" id="masterlist-mobile-prev-btn" onclick="goToMasterlistPage(masterlistCurrentPage - 1)"
                            class="text-[#00df82] hover:text-emerald-400 p-1.5 rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="text-xs text-slate-500 dark:text-[#7f998c] select-none">
                        Showing <span id="masterlist-mobile-range" class="font-bold text-slate-900 dark:text-white">1 – 6</span> of <span id="masterlist-mobile-total" class="font-bold text-slate-900 dark:text-white"><?= count($cycleFolders) ?></span> records
                    </div>
                    <button type="button" id="masterlist-mobile-next-btn" onclick="goToMasterlistPage(masterlistCurrentPage + 1)"
                            class="text-[#00df82] hover:text-emerald-400 p-1.5 rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
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
                <div class="p-4 sm:p-5 lg:p-6 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                                    CSC MC No. 6, s. 2012 Prescribed
                                </span>
                                <span class="text-xs font-semibold text-slate-400">|</span>
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <?= esc($selectedUnitName ?? 'University-Wide Roster') ?>
                                </span>
                            </div>
                            <h2 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                                Master List
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-3xl">
                                Consolidated institutional master list of all active plantilla faculty and staff for <?= esc($activeCycle['title'] ?? 'this evaluation period') ?>. Formatted in accordance with Civil Service Commission Strategic Performance Management System guidelines.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <a href="<?= site_url('dashboard/export-masterlist/' . ($activeCycle['id'] ?? '') . (!empty($selectedUnitId) ? '?unit_id=' . $selectedUnitId : '')) ?>"
                               class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs font-black text-white bg-emerald-700 hover:bg-emerald-800 dark:bg-[#0c4a33] dark:hover:bg-emerald-700 border border-emerald-600/30 transition-all shadow-md hover:shadow-lg hover:scale-[1.01] active:scale-[0.99] cursor-pointer"
                               title="Generate and download official CSC landscape Excel workbook">
                                <svg class="w-4 h-4 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Export Master List (.xlsx)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Masterlist Stats Row (Desktop) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 sm:pt-5 mt-4 sm:mt-5 border-t border-slate-100 dark:border-[#1a2b22]">
                        <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate">Total Personnel</div>
                            <div class="text-base font-black text-slate-900 dark:text-white mt-0.5"><?= number_format(count($cycleFolders)) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 truncate">Permanent</div>
                            <div class="text-base font-black text-emerald-700 dark:text-emerald-400 mt-0.5"><?= number_format($empStatusCounts['permanent'] ?? 0) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 truncate">Temporary</div>
                            <div class="text-base font-black text-amber-600 dark:text-amber-400 mt-0.5"><?= number_format($empStatusCounts['temporary'] ?? 0) ?></div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400 truncate" title="Casual &amp; Contractual">Casual &amp; Contr.</div>
                            <div class="text-base font-black text-sky-600 dark:text-sky-400 mt-0.5"><?= number_format(($empStatusCounts['casual'] ?? 0) + ($empStatusCounts['contractual'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>

                <!-- SEARCH & FILTER TOOLBAR (Desktop) -->
                <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-lg">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" id="masterlist-search" onkeyup="filterMasterlist()"
                                   placeholder="Search personnel name, email, department, or position..."
                                   class="w-full text-xs font-medium py-2.5 pl-10 pr-4 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 shadow-2xs" />
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <select id="masterlist-status-filter" onchange="filterMasterlist()"
                                    class="text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-[#032316] border border-slate-200 dark:border-[#0c4a33] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer shadow-2xs [color-scheme:light] dark:[color-scheme:dark]">
                                <option value="">All Appointments</option>
                                <option value="permanent">Permanent</option>
                                <option value="temporary">Temporary</option>
                                <option value="casual">Casual</option>
                                <option value="contractual">Contractual</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Filter Pill Buttons (Desktop) -->
                    <div class="flex items-center gap-2 pb-1 overflow-x-auto custom-scrollbar pt-2 border-t border-slate-100 dark:border-[#1a2b22]">
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
                <div id="masterlist-container" class="rounded-2xl bg-white dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] shadow-xs overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse" id="masterlist-table">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-[#032316] border-b border-slate-200 dark:border-[#1a2b22] text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">
                                    <th class="py-3 px-3 w-10 text-center">#</th>
                                    <th class="py-3 px-3">Personnel / Ratee</th>
                                    <th class="py-3 px-3">College / Division</th>
                                    <th class="py-3 px-3">Position</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Employment Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-[#1a2b22] text-xs">
                                <?php if (empty($cycleFolders)): ?>
                                    <tr>
                                        <td colspan="5" class="py-12 px-4 text-center text-slate-400 dark:text-slate-500 italic">
                                            No personnel records discovered for this evaluation period.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cycleFolders as $idx => $f): ?>
                                        <tr class="masterlist-row hover:bg-slate-50 dark:hover:bg-white/5 transition-colors"
                                            data-name="<?= esc(strtolower($f['ratee_name'] ?? $f['full_name'] ?? '')) ?>"
                                            data-email="<?= esc(strtolower($f['ratee_email'] ?? $f['email'] ?? '')) ?>"
                                            data-dept="<?= esc(strtolower($f['department'] ?? '')) ?>"
                                            data-pos="<?= esc(strtolower($f['position'] ?? '')) ?>"
                                            data-emp-status="<?= esc(strtolower($f['employment_status'] ?? 'permanent')) ?>">
                                            
                                            <!-- Index -->
                                            <td class="py-3 px-3 text-center font-bold text-slate-400 dark:text-slate-500 w-10">
                                                <?= $idx + 1 ?>
                                            </td>

                                            <!-- Personnel Name & Email -->
                                            <td class="py-3 px-3">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-[#0b291c] border border-slate-200 dark:border-[#144730] flex items-center justify-center font-black text-[11px] text-slate-700 dark:text-[#00df82] shrink-0">
                                                        <?= esc(strtoupper(substr($f['ratee_name'] ?? $f['full_name'] ?? 'U', 0, 1))) ?>
                                                    </div>
                                                    <div class="min-w-0 max-w-[130px] sm:max-w-[170px] xl:max-w-none">
                                                        <div class="font-bold text-slate-900 dark:text-white truncate">
                                                            <?= esc($f['ratee_name'] ?? $f['full_name'] ?? 'Personnel') ?>
                                                        </div>
                                                        <div class="text-[11px] text-slate-400 truncate">
                                                            <?= esc($f['ratee_email'] ?? $f['email'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- College / Department -->
                                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300 font-medium">
                                                <span class="truncate block max-w-[130px] sm:max-w-[160px] xl:max-w-[220px]" title="<?= esc($f['department'] ?? '—') ?>">
                                                    <?= esc($f['department'] ?? '—') ?>
                                                </span>
                                            </td>

                                            <!-- Plantilla Position -->
                                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 font-medium">
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
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <tr id="masterlist-empty-row" class="hidden">
                                    <td colspan="5" class="py-12 px-4 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
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
                    <div class="px-4 sm:px-6 py-3.5 border-t border-slate-100 dark:border-[#1a2b22] bg-slate-50/50 dark:bg-[#04170e] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                            <span class="text-slate-500 dark:text-[#7f998c]">
                                Showing <span id="masterlist-visible-range" class="font-bold text-slate-800 dark:text-white">0</span> of <span id="masterlist-visible-total" class="font-bold text-slate-800 dark:text-white"><?= count($cycleFolders) ?></span> personnel
                            </span>
                            
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-[#7f998c]">
                                <span class="hidden sm:inline text-[11px] font-medium">Rows:</span>
                                <select id="masterlist-per-page" onchange="changeMasterlistPerPage(this.value)"
                                        class="text-[11px] font-bold px-2 py-1 rounded-lg bg-white dark:bg-[#061e14] border border-slate-200 dark:border-[#123d27] text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer shadow-2xs">
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
function switchDashboardView(view) {
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
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.add('hidden');

        btnsAnalytics.forEach(b => {
            if (b) {
                b.className = b.className.replace('spms-tab-active', 'spms-tab-inactive');
            }
        });
        btnsMasterlist.forEach(b => {
            if (b) {
                b.className = b.className.replace('spms-tab-inactive', 'spms-tab-active');
            }
        });
        localStorage.setItem('spms_dash_view', 'masterlist');
        filterMasterlist(false);
    } else {
        masterlistView.classList.add('hidden');
        analyticsView.classList.remove('hidden');
        if (mobileAnalyticsHeader) mobileAnalyticsHeader.classList.remove('hidden');

        btnsMasterlist.forEach(b => {
            if (b) {
                b.className = b.className.replace('spms-tab-active', 'spms-tab-inactive');
            }
        });
        btnsAnalytics.forEach(b => {
            if (b) {
                b.className = b.className.replace('spms-tab-inactive', 'spms-tab-active');
            }
        });
        localStorage.setItem('spms_dash_view', 'analytics');
    }
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
        const matchesSearch = !query || name.includes(query) || email.includes(query) || dept.includes(query) || pos.includes(query);

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
    html += `<span class="sm:hidden px-2 text-xs font-bold text-slate-600 dark:text-[#7f998c]">
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
            html += `<span class="hidden sm:inline-flex px-1.5 py-1 text-xs text-slate-400 dark:text-slate-600 font-bold">...</span>`;
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
    const savedView = localStorage.getItem('spms_dash_view');
    if (savedView === 'masterlist') {
        switchDashboardView('masterlist');
    }

    const isMobile = window.innerWidth < 768;
    masterlistPerPage = isMobile ? 6 : 50;
    const perPageSelect = document.getElementById('masterlist-per-page');
    if (perPageSelect && !isMobile) {
        perPageSelect.value = "50";
    }
    filterMasterlist(true);
});

// --- ROSTER / DEAN FILTERING ---
let currentRosterFilter = 'all';

function setRosterFilter(filterType, btn) {
    currentRosterFilter = filterType;
    document.querySelectorAll('.roster-tab-btn').forEach(b => {
        b.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700';
    });
    if (btn) {
        btn.className = 'roster-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-500 text-white shadow-2xs';
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

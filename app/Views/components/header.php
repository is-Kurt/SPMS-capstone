<?php
    $currentUri = uri_string();
    $role = session()->get('role');

    $navItems = [];

    // Executive Analytics Dashboard (Admin) / College Submission Monitor (Supervisor)
    if (in_array($role, ['Admin', 'Supervisor'])) {
        $navItems['dashboard'] = 'Dashboard';
    }

    // TWG only sees Ratings, they don't see Folders.
    if ($role !== 'TWG') {
        $navItems['folders'] = 'Folders';
    }

    // Roles that evaluate others or verify get the Ratings tab
    $isEvaluator = false;
    $sessUserId = session()->get('user_id');
    if ($sessUserId) {
        $isEvaluator = (new \App\Models\EvaluationRoutingModel())->where('evaluator_id', $sessUserId)->countAllResults() > 0;
    }
    if (in_array($role, ['Admin', 'Supervisor', 'HR', 'TWG']) || $isEvaluator) {
        $navItems['ratings'] = 'Ratings';
    }

    // Admins, Supervisors, and Department Chairs create Distribution Lists (Teams)
    $userPos = strtolower(session()->get('position') ?? '');
    $canManageTeams = in_array($role, ['Admin', 'Supervisor']) || str_contains($userPos, 'chair') || str_contains($userPos, 'head');
    if ($canManageTeams) {
        $navItems['teams'] = 'My Teams';
    }

    if ($role === 'Admin') {
        $navItems['accounts'] = 'Accounts';
        $navItems['audit-logs'] = 'Audit Trail';
    }

    // Check if we need a hamburger menu (more than 1 tab available)
    $showHamburger = count($navItems) > 1;
?>

<?php
    $displayName = session()->get('username');
    if (empty($displayName) || $displayName === 'Null username') {
        $fName = session()->get('first_name');
        $lName = session()->get('last_name');
        if ($fName || $lName) {
            $displayName = trim($fName . ' ' . ($lName ?: ''));
        } else {
            $displayName = 'User';
        }
    }
    $avatarLetter = session('avatar_letter') ?? (substr($displayName, 0, 1) ?: 'U');
?>

<!-- Main institutional navigation bar -->
<nav class="antialiased relative z-[110] select-none print-hide bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 transition-colors">
    <div class="mx-auto max-w-[100rem] px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 sm:gap-4 h-16">
            
            <!-- Left Side: GOVPH + Divider + Twin Official Seals + Gold SPMS Badge + Brand Text -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0 min-w-0" style="height: 100%;">
                
                <!-- Official GOVPH & Twin Seals (MC 24, s. 2023 / RA 10535 Compliance) - Visible on sm and up -->
                <div class="hidden sm:flex items-center gap-2 sm:gap-2.5 shrink-0">
                    <!-- GOVPH Link -->
                    <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" 
                       class="font-black tracking-wider uppercase underline underline-offset-2 shrink-0 transition-colors text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white text-[11px]"
                       title="Official Gazette of the Republic of the Philippines">
                        GOVPH
                    </a>

                    <!-- Subtle Vertical Divider -->
                    <span class="text-zinc-300 dark:text-zinc-700 text-xs font-light select-none shrink-0">|</span>

                    <!-- Dual Official Seals: Bagong Pilipinas & Benguet State University -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Bagong Pilipinas Official Logo -->
                        <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" title="Bagong Pilipinas - Republic of the Philippines" class="flex items-center">
                            <img src="<?= base_url('assets/images/bagong_pilipinas.png') ?>" alt="Bagong Pilipinas Logo" 
                                 class="w-[22px] h-[22px] min-w-[22px] min-h-[22px] max-w-[22px] max-h-[22px] object-contain block" />
                        </a>
                        <!-- BSU Official Seal -->
                        <a href="http://www.bsu.edu.ph" target="_blank" rel="noopener noreferrer" title="Benguet State University" class="flex items-center">
                            <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" 
                                 class="w-[22px] h-[22px] min-w-[22px] min-h-[22px] max-w-[22px] max-h-[22px] rounded-full object-contain block" />
                        </a>
                    </div>
                    <!-- Subtle Vertical Divider -->
                    <span class="text-zinc-300 dark:text-zinc-700 text-xs font-light select-none shrink-0">|</span>
                </div>

                <!-- SPMS Institutional Brand Identity -->
                <a href="<?= site_url(array_key_first($navItems) ?? 'folders') ?>" class="shrink-0 flex items-center hover:opacity-90 transition-opacity group min-w-0">
                    <div class="flex flex-col min-w-0 leading-tight">
                        <div class="flex items-center gap-1.5 leading-none">
                            <span class="font-heading font-black tracking-tight text-base sm:text-lg uppercase text-zinc-900 dark:text-white">SPMS</span>
                            <span class="font-bold text-sm text-emerald-600 dark:text-emerald-400">&bull;</span>
                            <span class="font-heading font-black tracking-tight text-xs sm:text-sm uppercase text-emerald-600 dark:text-emerald-400">BSU</span>
                        </div>
                        <span class="hidden sm:block font-bold uppercase tracking-wider truncate text-[8px] text-zinc-500 dark:text-zinc-400">STRATEGIC PERFORMANCE MANAGEMENT SYSTEM</span>
                    </div>
                </a>

            </div>

            <!-- Center: Navigation Pills in Enclosed Capsule Container -->
            <div class="hidden md:flex items-center p-1 rounded-xl bg-zinc-100 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <?php foreach ($navItems as $uri => $label):
                    $isActive = ($currentUri === $uri) || ($uri !== '' && strpos($currentUri, $uri) === 0);
                ?>
                    <a href="<?= site_url($uri) ?>"
                       class="px-3.5 py-1.5 transition-all text-xs rounded-lg font-bold <?= $isActive ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs border border-zinc-200/80 dark:border-zinc-700/80' : 'text-zinc-600 hover:text-zinc-900 hover:bg-white/60 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-800/60 font-semibold' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Right: Theme Toggle, Notification Bell, User Profile Capsule, Mobile Menu -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">


                <!-- Theme Toggle Button (Desktop & Tablet) -->
                <button type="button" id="theme-toggle" 
                        class="hidden sm:flex relative w-8 h-8 rounded-xl text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 shadow-xs items-center justify-center transition-all cursor-pointer shrink-0"
                        title="Toggle Light / Dark Mode">
                    <!-- Sun icon: visible in dark mode, click to switch to light -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden dark:block text-amber-400 hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon: visible in light mode, click to switch to dark -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 block dark:hidden text-zinc-600 hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Notification Bell Direct Link -->
                <a href="<?= site_url('notifications') ?>" id="notification-btn"
                   class="relative w-8 h-8 rounded-xl text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0 <?= (isset($currentUri) && strpos($currentUri, 'notifications') === 0) ? 'ring-2 ring-zinc-400 dark:ring-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs' : '' ?>"
                   title="Notifications"
                   aria-label="View Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Dynamic Notification Badge -->
                    <span id="notification-badge" 
                          class="hidden absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-rose-500 text-white font-black text-[9px] rounded-full flex items-center justify-center shadow-xs border border-white dark:border-zinc-900">
                        0
                    </span>
                </a>

                <!-- Profile Dropdown Button Capsule (Matching GovHeader4 with Fully Visible Name on sm+) -->
                <div class="relative">
                    <button id="profile-btn-mobile" 
                            class="relative flex items-center gap-1.5 sm:gap-2.5 text-zinc-800 dark:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 shadow-xs p-1 sm:pl-1.5 sm:pr-3 sm:py-1 cursor-pointer transition-all rounded-xl">
                        <?php if (session('avatar_image')): ?>
                            <img src="<?= base_url('uploads/avatars/' . session('avatar_image')) ?>" alt="User" 
                                 class="w-[26px] h-[26px] min-w-[26px] min-h-[26px] max-w-[26px] max-h-[26px] rounded-lg object-cover block" />
                        <?php else: ?>
                            <div class="flex items-center justify-center font-black text-xs shrink-0 w-[26px] h-[26px] min-w-[26px] min-h-[26px] max-w-[26px] max-h-[26px] rounded-lg bg-amber-400 text-zinc-950">
                                <?= esc($avatarLetter) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Fully Visible Name & Role on sm and up -->
                        <div class="hidden sm:flex flex-col text-left leading-tight shrink-0">
                            <span class="text-xs font-bold text-zinc-900 dark:text-white whitespace-nowrap"><?= esc($displayName) ?></span>
                            <span class="font-bold tracking-wider uppercase whitespace-nowrap text-[8.5px] text-emerald-600 dark:text-emerald-400"><?= esc($role ?? 'User') ?></span>
                        </div>

                        <!-- Dropdown Chevron on sm and up -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-400 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-3 w-56 origin-top-right rounded-2xl bg-surface p-2 shadow-2xl ring-1 ring-surface-border z-[100]">
                        <div class="flex items-center gap-3 px-3 py-2 mb-1">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-black font-black text-sm bg-amber-400 shrink-0">
                                <?= esc($avatarLetter) ?>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <p class="text-sm font-bold text-text truncate break-all">
                                    <?= esc($displayName) ?>
                                </p>
                                <p class="text-[11px] font-bold text-text-muted tracking-widest break-all"><?= esc(session()->get('email') ?? 'User@email') ?></p>
                            </div>
                        </div>

                        <hr class="my-1.5 border-surface-border">

                        <a href="<?= site_url('profile') ?>" class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-text-muted hover:bg-accent/10 hover:text-accent rounded-xl transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>


                        <?php if ($showHamburger): ?>
                            <!-- For users with a mobile sidebar, hide Sign Out in dropdown on mobile (kept for desktop md+) -->
                            <hr class="my-1.5 border-surface-border hidden md:block">

                            <?= form_open('login', ['class' => 'hidden md:block m-0']) ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-xl transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign out
                                </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <!-- For single-tab users (employees with only Folders), keep Sign Out accessible on mobile -->
                            <hr class="my-1.5 border-surface-border">

                            <?= form_open('login', ['class' => 'm-0']) ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-xl transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign out
                                </button>
                            <?= form_close() ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($showHamburger): ?>
                    <!-- Mobile Navigation Hamburger Toggle Button -->
                    <button type="button" id="mobile-menu-btn" 
                            class="md:hidden w-8 h-8 rounded-xl text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0"
                            title="Open Navigation Menu">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <!-- Telegram-Style Mobile Navigation Side Drawer & Overlay -->
    <?php if ($showHamburger): ?>
        <style>
            #mobile-drawer-overlay {
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.65);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                z-index: 998;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.28s ease;
            }
            #mobile-drawer-overlay.overlay-open {
                opacity: 1 !important;
                pointer-events: auto !important;
            }

            #mobile-drawer {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: 295px;
                max-width: 82vw;
                background-color: #ffffff;
                z-index: 999;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
                display: flex;
                flex-direction: column;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                border-right: 1px solid #e2e8f0;
                overflow: hidden;
            }
            .dark #mobile-drawer {
                background-color: #18181b !important;
                border-right: 1px solid #27272a !important;
            }
            #mobile-drawer.drawer-open {
                transform: translateX(0) !important;
            }

            .spms-drawer-header {
                padding: 24px 20px 16px 20px;
                background-color: #f4f4f5;
                border-bottom: 1px solid #e4e4e7;
                position: relative;
                overflow: hidden;
                flex-shrink: 0;
            }
            .dark .spms-drawer-header {
                background-color: #09090b !important;
                border-bottom: 1px solid #27272a !important;
            }

            .spms-drawer-body {
                flex: 1;
                overflow-y: auto;
                padding: 10px 12px;
                background-color: #ffffff;
            }
            .dark .spms-drawer-body {
                background-color: #18181b !important;
            }

            .spms-drawer-item {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px 14px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 700;
                color: #27272a;
                text-decoration: none;
                transition: all 0.15s ease;
                cursor: pointer;
                width: 100%;
            }
            .spms-drawer-item:hover {
                background-color: #f4f4f5;
                color: #09090b;
            }
            .dark .spms-drawer-item {
                color: #fafafa !important;
            }
            .dark .spms-drawer-item:hover {
                background-color: #27272a !important;
                color: #ffffff !important;
            }

            .spms-drawer-item.active {
                background-color: #f4f4f5;
                color: #09090b;
                font-weight: 800;
                border: 1px solid #e4e4e7;
            }
            .dark .spms-drawer-item.active {
                background-color: #27272a !important;
                color: #ffffff !important;
                border: 1px solid #3f3f46 !important;
            }

            .spms-drawer-icon {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                color: #64748b;
            }
            .dark .spms-drawer-icon {
                color: #94a3b8;
            }
            .spms-drawer-item.active .spms-drawer-icon {
                color: #059669;
            }
            .dark .spms-drawer-item.active .spms-drawer-icon {
                color: #34d399;
            }

            .spms-drawer-divider {
                height: 1px;
                background-color: #f1f5f9;
                margin: 8px 0;
            }
            .dark .spms-drawer-divider {
                background-color: #1e293b !important;
            }
        </style>

        <!-- Semi-transparent backdrop blur overlay -->
        <div id="mobile-drawer-overlay" class="md:hidden"></div>

        <!-- Off-canvas Side Drawer -->
        <div id="mobile-drawer" class="md:hidden select-none">
            
            <!-- Telegram-Style Top Banner -->
            <div class="spms-drawer-header select-none">
                
                <!-- Ambient background glow -->
                <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-emerald-400/10 blur-xl pointer-events-none"></div>

                <div class="flex items-start justify-between relative z-10">
                    <!-- User Avatar -->
                    <a href="<?= site_url('profile') ?>" class="block group">
                        <?php if (session('avatar_image')): ?>
                            <img src="<?= base_url('uploads/avatars/' . session('avatar_image')) ?>" alt="<?= esc($displayName) ?>" 
                                 class="w-14 h-14 rounded-full object-cover border-2 border-white/60 shadow-md group-hover:scale-105 transition-transform" />
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-full bg-amber-500 text-black font-black text-xl flex items-center justify-center border-2 border-white/60 shadow-md group-hover:scale-105 transition-transform">
                                <?= esc($avatarLetter) ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <!-- Theme Toggle Button (Telegram-Style Moon / Sun Icon) -->
                    <button type="button" id="drawer-theme-toggle" 
                            class="w-9 h-9 rounded-full bg-black/25 hover:bg-black/45 text-amber-300 flex items-center justify-center transition-all cursor-pointer shadow-xs"
                            title="Toggle Light / Dark Mode">
                        <!-- Sun icon: visible in dark mode -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden dark:block text-amber-400 hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon icon: visible in light mode -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 block dark:hidden text-amber-300 hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>

                <!-- User Name & Role/Email (Clean header info, no redundant dropdown chevron) -->
                <div class="mt-3.5 block">
                    <h3 class="text-base font-extrabold text-white tracking-tight leading-tight truncate">
                        <?= esc($displayName) ?>
                    </h3>
                    <p class="text-xs text-emerald-200/80 font-medium truncate mt-0.5">
                        <?= esc(session('email') ?: ($role . ' • BSU SPMS')) ?>
                    </p>
                </div>
            </div>

            <!-- Telegram-Style Menu List -->
            <div class="spms-drawer-body custom-scrollbar">
                
                <?php foreach ($navItems as $uri => $label):
                    $isActive = ($currentUri === $uri) || ($uri !== '' && strpos($currentUri, $uri) === 0);
                ?>
                    <a href="<?= site_url($uri) ?>"
                       class="spms-drawer-item <?= $isActive ? 'active' : '' ?>">
                        
                        <span class="spms-drawer-icon">
                            <?php if ($uri === 'dashboard'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            <?php elseif ($uri === 'folders'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            <?php elseif ($uri === 'ratings'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php elseif ($uri === 'teams'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            <?php elseif ($uri === 'accounts'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            <?php elseif ($uri === 'audit-logs'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            <?php endif; ?>
                        </span>
                        
                        <span>
                            <?php 
                                echo match($uri) {
                                    'folders' => 'Evaluation Folders',
                                    'ratings' => 'Performance Ratings',
                                    'accounts' => 'User Accounts',
                                    'audit-logs' => 'Audit Trail',
                                    default => $label
                                };
                            ?>
                        </span>
                    </a>
                <?php endforeach; ?>

                <div class="spms-drawer-divider"></div>

                <!-- Notifications Link -->
                <a href="<?= site_url('notifications') ?>" class="spms-drawer-item <?= (isset($currentUri) && strpos($currentUri, 'notifications') === 0) ? 'active' : '' ?>">
                    <span class="spms-drawer-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                    <span class="flex-1">Notifications</span>
                    <span id="drawer-notif-badge" class="hidden px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black shadow-xs">0</span>
                </a>

                <!-- Secondary Links: Profile & User Guide -->
                <a href="<?= site_url('profile') ?>" class="spms-drawer-item">
                    <span class="spms-drawer-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <span>My Profile</span>
                </a>


                <div class="spms-drawer-divider"></div>

                <!-- Sign Out -->
                <?= form_open('login', ['class' => 'm-0']) ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" 
                            class="spms-drawer-item text-left" style="color: #ef4444 !important;">
                        <span class="spms-drawer-icon" style="color: #ef4444 !important;">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        <span>Sign Out</span>
                    </button>
                <?= form_close() ?>

            </div>
        </div>
    <?php endif; ?>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profile-btn-mobile');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const drawerOverlay = document.getElementById('mobile-drawer-overlay');
        const drawer = document.getElementById('mobile-drawer');
        const drawerThemeToggle = document.getElementById('drawer-theme-toggle');
        const themeToggleBtn = document.getElementById('theme-toggle');

        // Drawer Controllers
        window.openMobileDrawer = function() {
            if (!drawer || !drawerOverlay) return;
            profileMenu?.classList.add('hidden');
            drawerOverlay.classList.add('overlay-open');
            drawer.classList.add('drawer-open');
            document.body.style.overflow = 'hidden';
        };

        window.closeMobileDrawer = function() {
            if (!drawer || !drawerOverlay) return;
            drawerOverlay.classList.remove('overlay-open');
            drawer.classList.remove('drawer-open');
            document.body.style.overflow = '';
        };

        // Hamburger button click opens Telegram-style side drawer
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                window.openMobileDrawer();
            });
        }

        // Overlay click closes side drawer
        drawerOverlay?.addEventListener('click', (e) => {
            e.stopPropagation();
            window.closeMobileDrawer();
        });

        // Universal Theme Toggle Handler
        function handleThemeToggle(btn) {
            if (typeof window.handleThemeToggle === 'function') {
                window.handleThemeToggle(btn);
            }
        }

        // Theme toggle inside Telegram drawer
        if (drawerThemeToggle) {
            drawerThemeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                handleThemeToggle(drawerThemeToggle);
            });
        }

        // Top Header Theme Toggle Button
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                handleThemeToggle(themeToggleBtn);
                profileMenu?.classList.add('hidden');
            });
        }

        // Toggle Profile Dropdown
        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                window.closeMobileDrawer();
            });
        }

        // Close dropdowns & drawer when clicking anywhere outside of them or on Escape key
        document.addEventListener('click', (e) => {
            if (profileMenu && !profileMenu.classList.contains('hidden') && !profileMenu.contains(e.target) && !profileBtn?.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.closeMobileDrawer();
                profileMenu?.classList.add('hidden');
            }
        });

        // --- In-App Notifications Background Syncer ---
        (function() {
            const notifBadge = document.getElementById('notification-badge');
            const drawerBadge = document.getElementById('drawer-notif-badge');
            let isFetching = false;

            async function fetchUnreadCount() {
                if (isFetching) return;
                isFetching = true;
                try {
                    const res = await fetch('<?= site_url("notifications") ?>', {
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'success') {
                            const count = data.unread_count || 0;
                            updateBadge(count);
                        }
                    }
                } catch (e) {
                    console.warn('Failed to sync notification count', e);
                } finally {
                    isFetching = false;
                }
            }

            function updateBadge(count) {
                if (notifBadge) {
                    if (count > 0) {
                        notifBadge.textContent = count > 99 ? '99+' : count;
                        notifBadge.classList.remove('hidden');
                    } else {
                        notifBadge.classList.add('hidden');
                    }
                }
                if (drawerBadge) {
                    if (count > 0) {
                        drawerBadge.textContent = count > 99 ? '99+' : count;
                        drawerBadge.classList.remove('hidden');
                    } else {
                        drawerBadge.classList.add('hidden');
                    }
                }
            }

            // Expose globally so other pages/scripts can trigger badge refresh if needed
            window.syncNotificationBadge = updateBadge;
            window.refreshNotificationCount = fetchUnreadCount;

            // Initial fetch & polling every 45s
            fetchUnreadCount();
            setInterval(fetchUnreadCount, 45000);
        })();
    });
</script>
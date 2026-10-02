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
        $navItems['templates'] = 'Templates';
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

<!-- GovHeader4: Ultra-Clean Unified Government & SPMS Workspace Navbar -->
<nav class="antialiased relative z-[110] select-none print-hide"
     style="background-color: #061a10; border-bottom: 1px solid #0f3d29; box-sizing: border-box;">
    <div class="mx-auto max-w-[100rem] px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 sm:gap-4" style="height: 64px;">
            
            <!-- Left Side: GOVPH + Divider + Twin Official Seals + Gold SPMS Badge + Brand Text -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0 min-w-0" style="height: 100%;">
                
                <!-- Official GOVPH & Twin Seals (MC 24, s. 2023 / RA 10535 Compliance) - Visible on sm and up -->
                <div class="hidden sm:flex items-center gap-2 sm:gap-2.5 shrink-0">
                    <!-- GOVPH Link -->
                    <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" 
                       class="font-black tracking-wider uppercase underline underline-offset-2 shrink-0 transition-colors hover:text-amber-300"
                       style="font-size: 11px; color: #ffffff;"
                       title="Official Gazette of the Republic of the Philippines">
                        GOVPH
                    </a>

                    <!-- Subtle Vertical Divider -->
                    <span style="color: #15452d; font-size: 13px; line-height: 1;" class="select-none font-light shrink-0">|</span>

                    <!-- Dual Official Seals: Bagong Pilipinas & Benguet State University -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Bagong Pilipinas Official Logo -->
                        <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" title="Bagong Pilipinas - Republic of the Philippines" class="flex items-center">
                            <img src="<?= base_url('assets/images/bagong_pilipinas.png') ?>" alt="Bagong Pilipinas Logo" 
                                 style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px; object-fit: contain; display: block;" />
                        </a>
                        <!-- BSU Official Seal -->
                        <a href="http://www.bsu.edu.ph" target="_blank" rel="noopener noreferrer" title="Benguet State University" class="flex items-center">
                            <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" 
                                 style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px; border-radius: 9999px; object-fit: contain; display: block;" />
                        </a>
                    </div>
                    <!-- Subtle Vertical Divider -->
                    <span style="color: #15452d; font-size: 13px; line-height: 1;" class="select-none font-light shrink-0">|</span>
                </div>

                <!-- SPMS Institutional Brand Identity -->
                <a href="<?= site_url(array_key_first($navItems) ?? 'folders') ?>" class="flex-shrink-0 flex items-center text-white hover:opacity-95 transition-opacity group min-w-0">
                    <div class="flex flex-col min-w-0 leading-tight">
                        <div class="flex items-center gap-1.5 leading-none">
                            <span class="font-heading font-black tracking-tight text-base sm:text-lg uppercase text-white">SPMS</span>
                            <span class="font-bold text-sm" style="color: #34d399;">&bull;</span>
                            <span class="font-heading font-black tracking-tight text-xs sm:text-sm uppercase" style="color: #34d399;">BSU</span>
                        </div>
                        <span class="hidden sm:block font-bold uppercase tracking-wider truncate" style="font-size: 8px; color: rgba(209, 250, 229, 0.7); letter-spacing: 0.05em;">STRATEGIC PERFORMANCE MANAGEMENT SYSTEM</span>
                    </div>
                </a>

            </div>

            <!-- Center: Navigation Pills in Enclosed Capsule Container -->
            <div class="hidden md:flex items-center p-1 rounded-2xl" 
                 style="background-color: rgba(0, 0, 0, 0.28); border: 1px solid rgba(16, 185, 129, 0.25);">
                <?php foreach ($navItems as $uri => $label):
                    $isActive = ($currentUri === $uri) || ($uri !== '' && strpos($currentUri, $uri) === 0);
                ?>
                    <a href="<?= site_url($uri) ?>"
                       class="px-4 py-1.5 transition-all text-xs <?= $isActive ? 'shadow-xs' : 'hover:text-white hover:bg-white/5' ?>"
                       style="<?= $isActive ? 'background-color: #14532d; border: 1px solid rgba(52, 211, 153, 0.4); color: #ffffff; font-weight: 700; border-radius: 10px;' : 'color: rgba(209, 250, 229, 0.75); font-weight: 600; border-radius: 10px;' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Right: Theme Toggle, Notification Bell, User Profile Capsule, Mobile Menu -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">

                <!-- SPMS User Guide Button (Desktop / Tablet) -->
                <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal()" 
                        class="hidden sm:flex relative w-8 h-8 rounded-xl text-amber-300 hover:text-white shadow-xs items-center justify-center transition-all cursor-pointer shrink-0"
                        style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(245, 158, 11, 0.35);"
                        title="SPMS User Guide & Performance Cycle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400 hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </button>

                <!-- Theme Toggle Button -->
                <button type="button" id="theme-toggle" 
                        class="relative w-8 h-8 rounded-xl text-slate-300 hover:text-white shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0"
                        style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(16, 185, 129, 0.25);"
                        title="Toggle Light / Dark Mode">
                    <!-- Sun icon: visible in dark mode, click to switch to light -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden dark:block text-amber-400 hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon: visible in light mode, click to switch to dark -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 block dark:hidden text-slate-300 hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Notification Bell Dropdown Container -->
                <div class="relative" id="notification-container">
                    <button type="button" id="notification-btn"
                            class="relative w-8 h-8 rounded-xl text-slate-300 hover:text-white shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0"
                            style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(16, 185, 129, 0.25);"
                            title="Notifications"
                            aria-label="View Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Dynamic Notification Badge -->
                        <span id="notification-badge" 
                              class="hidden absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-rose-500 text-white font-black text-[9px] rounded-full flex items-center justify-center shadow-xs border border-[#061a10] animate-pulse">
                            0
                        </span>
                    </button>

                    <!-- Notifications Dropdown Tray -->
                    <div id="notification-menu" 
                         class="hidden absolute right-0 mt-3 w-80 sm:w-96 origin-top-right rounded-2xl bg-white dark:bg-[#0c1510] p-0 shadow-2xl border border-slate-200 dark:border-[#1a2b22] ring-1 ring-black/5 z-[120] overflow-hidden">
                        
                        <!-- Header -->
                        <div class="px-4 pt-3 pb-2 border-b border-slate-200 dark:border-[#1a2b22] flex flex-col gap-2.5 bg-slate-50/80 dark:bg-[#122019]/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-xs text-slate-900 dark:text-white uppercase tracking-wider">Notifications</span>
                                    <span id="notification-header-count" class="hidden px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[10px] font-black">
                                        0 New
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-mark-all-read"
                                            class="text-[11px] font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-colors cursor-pointer flex items-center gap-1"
                                            title="Mark all as read">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Mark read</span>
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-700 text-xs">|</span>
                                    <button type="button" id="btn-clear-all-notifs"
                                            class="text-[11px] font-bold text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 transition-colors cursor-pointer flex items-center gap-1"
                                            title="Clear all notifications">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Clear</span>
                                    </button>
                                </div>
                            </div>
                            <!-- Filter Tabs (Unread vs All) -->
                            <div class="flex items-center gap-1 bg-slate-200/70 dark:bg-[#0c1510] p-0.5 rounded-xl text-[11px] font-bold">
                                <button type="button" id="notif-tab-unread" 
                                        class="flex-1 py-1 px-2.5 rounded-lg text-center transition-all bg-emerald-600 text-white font-bold shadow-xs cursor-pointer">
                                    Unread (<span id="notif-tab-unread-count">0</span>)
                                </button>
                                <button type="button" id="notif-tab-all" 
                                        class="flex-1 py-1 px-2.5 rounded-lg text-center transition-all text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer">
                                    All (<span id="notif-tab-all-count">0</span>)
                                </button>
                            </div>
                        </div>

                        <!-- Notification List (Scrollable) -->
                        <div id="notification-list" class="max-h-[380px] overflow-y-auto custom-scrollbar divide-y divide-slate-100 dark:divide-[#15271d]">
                            <!-- Loading / Empty / Dynamic Items injected here -->
                            <div id="notification-loading" class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Loading notifications...
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2 bg-slate-50/50 dark:bg-[#080e0b] border-t border-slate-200/60 dark:border-[#1a2b22] text-center">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">BSU-SPMS Activity Center</span>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown Button Capsule (Matching GovHeader4 with Fully Visible Name on sm+) -->
                <div class="relative">
                    <button id="profile-btn-mobile" 
                            class="relative flex items-center gap-1.5 sm:gap-2.5 text-white shadow-xs p-1 sm:pl-1.5 sm:pr-3 sm:py-1 cursor-pointer transition-all hover:bg-white/5"
                            style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px;">
                        <?php if (session('avatar_image')): ?>
                            <img src="<?= base_url('uploads/avatars/' . session('avatar_image')) ?>" alt="User" 
                                 style="width: 26px; height: 26px; min-width: 26px; min-height: 26px; max-width: 26px; max-height: 26px; border-radius: 8px; object-fit: cover; display: block;" />
                        <?php else: ?>
                            <div class="flex items-center justify-center font-black text-xs shrink-0"
                                 style="width: 26px; height: 26px; min-width: 26px; min-height: 26px; max-width: 26px; max-height: 26px; border-radius: 8px; background-color: #f59e0b; color: #000000;">
                                <?= esc($avatarLetter) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Fully Visible Name & Role on sm and up -->
                        <div class="hidden sm:flex flex-col text-left leading-tight shrink-0">
                            <span class="text-xs font-bold text-white whitespace-nowrap" style="font-size: 12px; font-weight: 700; color: #ffffff; white-space: nowrap;"><?= esc($displayName) ?></span>
                            <span class="font-bold tracking-wider uppercase whitespace-nowrap" style="font-size: 8.5px; color: rgba(110, 231, 183, 0.85); letter-spacing: 0.05em; white-space: nowrap;"><?= esc($role ?? 'User') ?></span>
                        </div>

                        <!-- Dropdown Chevron on sm and up -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-300/70 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

                        <button type="button" onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal(); document.getElementById('profile-dropdown-menu')?.classList.add('hidden');" 
                                class="w-full flex items-center gap-3 px-3 py-2 text-sm font-semibold text-text-muted hover:bg-amber-500/10 hover:text-amber-500 rounded-xl transition-colors cursor-pointer text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-amber-500 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            SPMS User Guide
                        </button>

                        <hr class="my-1.5 border-surface-border">

                        <?= form_open('login') ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-xl transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign out
                            </button>
                        <?= form_close() ?>
                    </div>
                </div>

                <?php if ($showHamburger): ?>
                    <!-- Mobile Navigation Hamburger Toggle Button -->
                    <button type="button" id="mobile-menu-btn" 
                            class="md:hidden w-8 h-8 rounded-xl text-slate-300 hover:text-white shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0"
                            style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(16, 185, 129, 0.25);"
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
                background-color: #032115 !important;
                border-right: 1px solid #0d4a32 !important;
            }
            #mobile-drawer.drawer-open {
                transform: translateX(0) !important;
            }

            .spms-drawer-header {
                padding: 24px 20px 16px 20px;
                background: linear-gradient(145deg, #064e3b 0%, #022c1e 100%);
                position: relative;
                overflow: hidden;
                flex-shrink: 0;
            }
            .dark .spms-drawer-header {
                background: linear-gradient(145deg, #042a1b 0%, #011910 100%) !important;
            }

            .spms-drawer-body {
                flex: 1;
                overflow-y: auto;
                padding: 10px 12px;
                background-color: #ffffff;
            }
            .dark .spms-drawer-body {
                background-color: #032115 !important;
            }

            .spms-drawer-item {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px 14px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 700;
                color: #1e293b;
                text-decoration: none;
                transition: all 0.15s ease;
                cursor: pointer;
                width: 100%;
            }
            .spms-drawer-item:hover {
                background-color: #f1f5f9;
                color: #0f172a;
            }
            .dark .spms-drawer-item {
                color: #e2e8f0 !important;
            }
            .dark .spms-drawer-item:hover {
                background-color: #073824 !important;
                color: #ffffff !important;
            }

            .spms-drawer-item.active {
                background-color: #ecfdf5;
                color: #047857;
                font-weight: 800;
            }
            .dark .spms-drawer-item.active {
                background-color: #083b27 !important;
                color: #34d399 !important;
            }

            .spms-drawer-icon {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                color: #64748b;
            }
            .dark .spms-drawer-icon {
                color: #5a8b73;
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
                background-color: #0d4a32 !important;
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

                <!-- User Name & Role/Email with Chevron -->
                <a href="<?= site_url('profile') ?>" class="mt-3.5 block group">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <h3 class="text-base font-extrabold text-white tracking-tight leading-tight group-hover:text-emerald-200 transition-colors truncate">
                                <?= esc($displayName) ?>
                            </h3>
                            <p class="text-xs text-emerald-200/80 font-medium truncate mt-0.5">
                                <?= esc(session('email') ?: ($role . ' • BSU SPMS')) ?>
                            </p>
                        </div>
                        <!-- Chevron icon to Profile -->
                        <div class="text-emerald-300/80 group-hover:text-white transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </a>
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
                            <?php elseif ($uri === 'templates'): ?>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                                    'templates' => 'Document Templates',
                                    'audit-logs' => 'Audit Trail',
                                    default => $label
                                };
                            ?>
                        </span>
                    </a>
                <?php endforeach; ?>

                <div class="spms-drawer-divider"></div>

                <!-- Secondary Links: Profile & User Guide -->
                <a href="<?= site_url('profile') ?>" class="spms-drawer-item">
                    <span class="spms-drawer-icon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <span>My Profile</span>
                </a>

                <button type="button" 
                        onclick="if(typeof openUserGuideModal === 'function') openUserGuideModal(); if(typeof window.closeMobileDrawer === 'function') window.closeMobileDrawer();"
                        class="spms-drawer-item text-left">
                    <span class="spms-drawer-icon" style="color: #f59e0b !important;">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <span>SPMS User Guide</span>
                </button>

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
            document.getElementById('notification-menu')?.classList.add('hidden');
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

        // Theme toggle inside Telegram drawer
        if (drawerThemeToggle) {
            drawerThemeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                if (typeof initEditor === 'function') {
                    initEditor();
                }
            });
        }

        // Top Header Theme Toggle Button
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                if (typeof initEditor === 'function') {
                    initEditor();
                }
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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.closeMobileDrawer();
                profileMenu?.classList.add('hidden');
            }
        });

        // --- In-App Notifications Client ---
        (function() {
            const notifBtn = document.getElementById('notification-btn');
            const notifMenu = document.getElementById('notification-menu');
            const notifBadge = document.getElementById('notification-badge');
            const notifHeaderCount = document.getElementById('notification-header-count');
            const notifList = document.getElementById('notification-list');
            const markAllBtn = document.getElementById('btn-mark-all-read');
            const clearAllBtn = document.getElementById('btn-clear-all-notifs');
            const notifTabUnread = document.getElementById('notif-tab-unread');
            const notifTabAll = document.getElementById('notif-tab-all');
            const notifTabUnreadCount = document.getElementById('notif-tab-unread-count');
            const notifTabAllCount = document.getElementById('notif-tab-all-count');

            let notificationsCache = [];
            let currentFilter = 'unread';
            let isFetching = false;

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token-hash"]')?.content || '';
            }

            function getCsrfName() {
                return document.querySelector('meta[name="csrf-token-name"]')?.content || 'csrf_test_name';
            }

            async function fetchNotifications() {
                if (isFetching) return;
                isFetching = true;
                try {
                    const res = await fetch('<?= site_url("notifications") ?>', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'success') {
                            notificationsCache = data.notifications || [];
                            updateBadge(data.unread_count || 0);
                            renderNotificationList();
                        }
                    }
                } catch (e) {
                    console.warn('Failed to fetch notifications', e);
                } finally {
                    isFetching = false;
                }
            }

            function updateBadge(count) {
                if (!notifBadge) return;
                if (count > 0) {
                    notifBadge.textContent = count > 99 ? '99+' : count;
                    notifBadge.classList.remove('hidden');
                    if (notifHeaderCount) {
                        notifHeaderCount.textContent = `${count} New`;
                        notifHeaderCount.classList.remove('hidden');
                    }
                } else {
                    notifBadge.classList.add('hidden');
                    if (notifHeaderCount) {
                        notifHeaderCount.classList.add('hidden');
                    }
                }
            }

            function getIconSvg(type) {
                switch (type) {
                    case 'target_approved':
                    case 'eval_approved':
                        return `<svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
                    case 'target_returned':
                    case 'eval_returned':
                    case 'twg_disapproved':
                    case 'target_unapproved':
                    case 'target_unsubmitted':
                    case 'eval_unsubmitted':
                    case 'target_revoked':
                        return `<svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                    case 'target_submitted':
                    case 'eval_submitted':
                    case 'target_released':
                    case 'target_assigned':
                        return `<svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                    case 'twg_approved':
                        return `<svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>`;
                    default:
                        return `<svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`;
                }
            }

            function getIconBgClass(type) {
                switch (type) {
                    case 'target_approved':
                    case 'eval_approved':
                        return 'bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900';
                    case 'target_returned':
                    case 'eval_returned':
                    case 'twg_disapproved':
                    case 'target_unapproved':
                    case 'target_unsubmitted':
                    case 'eval_unsubmitted':
                    case 'target_revoked':
                        return 'bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900';
                    case 'target_submitted':
                    case 'eval_submitted':
                    case 'target_released':
                    case 'target_assigned':
                        return 'bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900';
                    case 'twg_approved':
                        return 'bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-900';
                    default:
                        return 'bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700';
                }
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }

            const activeTabClass = 'flex-1 py-1 px-2.5 rounded-lg text-center transition-all bg-emerald-600 text-white font-bold shadow-xs cursor-pointer';
            const inactiveTabClass = 'flex-1 py-1 px-2.5 rounded-lg text-center transition-all text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-300/40 dark:hover:bg-white/5 cursor-pointer';

            function updateTabUI() {
                const unreadCount = notificationsCache.filter(n => !n.is_read).length;
                const allCount = notificationsCache.length;

                if (notifTabUnreadCount) notifTabUnreadCount.textContent = unreadCount;
                if (notifTabAllCount) notifTabAllCount.textContent = allCount;

                if (currentFilter === 'unread') {
                    if (notifTabUnread) notifTabUnread.className = activeTabClass;
                    if (notifTabAll) notifTabAll.className = inactiveTabClass;
                } else {
                    if (notifTabAll) notifTabAll.className = activeTabClass;
                    if (notifTabUnread) notifTabUnread.className = inactiveTabClass;
                }
            }

            function renderNotificationList() {
                if (!notifList) return;
                updateTabUI();

                const unreadItems = notificationsCache.filter(n => !n.is_read);
                const displayItems = (currentFilter === 'unread') ? unreadItems : notificationsCache;

                if (displayItems.length === 0) {
                    if (currentFilter === 'unread' && notificationsCache.length > 0) {
                        notifList.innerHTML = `
                            <div class="p-8 text-center">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center mb-2.5">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-800 dark:text-white">All caught up!</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">No unread notifications.</p>
                                <button type="button" onclick="document.getElementById('notif-tab-all').click()" class="mt-2.5 inline-block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                                    View notification history (${notificationsCache.length})
                                </button>
                            </div>
                        `;
                    } else {
                        notifList.innerHTML = `
                            <div class="p-8 text-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-[#13271b] text-slate-400 dark:text-emerald-400 mx-auto flex items-center justify-center mb-2.5">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-800 dark:text-white">Inbox is empty</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">No notifications at this time.</p>
                            </div>
                        `;
                    }
                    return;
                }

                notifList.innerHTML = displayItems.map(item => {
                    const isUnread = !item.is_read;
                    const bgHover = isUnread 
                        ? 'bg-emerald-50/50 dark:bg-[#10241a]/60 hover:bg-emerald-50/80 dark:hover:bg-[#132b20]' 
                        : 'hover:bg-slate-50 dark:hover:bg-white/5';
                    const iconSvg = getIconSvg(item.type);
                    const iconBg = getIconBgClass(item.type);

                    return `
                        <div class="notification-item flex items-start gap-3 p-3.5 transition-colors cursor-pointer relative group ${bgHover}"
                             data-id="${item.id}"
                             data-link="${item.link || ''}">
                            
                            <!-- Icon Tile -->
                            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center ${iconBg}">
                                ${iconSvg}
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0 pr-1">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h5 class="text-xs text-slate-900 dark:text-white truncate ${isUnread ? 'font-black text-emerald-950 dark:text-emerald-300' : 'font-bold'}">${escapeHtml(item.title)}</h5>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0 whitespace-nowrap">${item.time_ago}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug line-clamp-2">${escapeHtml(item.message)}</p>
                            </div>

                            <!-- Right Action Area (Dot & Dismiss) -->
                            <div class="flex items-center gap-1.5 shrink-0 pt-0.5">
                                ${isUnread ? '<span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 shadow-xs"></span>' : ''}
                                <button type="button" 
                                        class="btn-delete-notif opacity-0 group-hover:opacity-100 p-1 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all cursor-pointer"
                                        title="Dismiss notification"
                                        data-id="${item.id}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                // Attach click listeners on each row (open and mark as read)
                notifList.querySelectorAll('.notification-item').forEach(el => {
                    el.addEventListener('click', async (e) => {
                        // Ignore click if the user clicked the delete button
                        if (e.target.closest('.btn-delete-notif')) return;
                        e.preventDefault();

                        const notifId = el.getAttribute('data-id');
                        const targetLink = el.getAttribute('data-link');

                        try {
                            const formData = new FormData();
                            formData.append(getCsrfName(), getCsrfToken());
                            fetch(`<?= site_url('notifications/read/') ?>${notifId}`, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        } catch (err) {
                            console.warn(err);
                        }

                        if (targetLink) {
                            window.location.href = targetLink;
                        } else {
                            fetchNotifications();
                        }
                    });
                });

                // Attach click listeners on individual delete buttons
                notifList.querySelectorAll('.btn-delete-notif').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const notifId = btn.getAttribute('data-id');

                        try {
                            const formData = new FormData();
                            formData.append(getCsrfName(), getCsrfToken());
                            fetch(`<?= site_url('notifications/delete/') ?>${notifId}`, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        } catch (err) {
                            console.warn(err);
                        }

                        notificationsCache = notificationsCache.filter(n => n.id != notifId);
                        const unreadCount = notificationsCache.filter(n => !n.is_read).length;
                        updateBadge(unreadCount);
                        renderNotificationList();
                    });
                });
            }

            // Tab Switching Listeners
            if (notifTabUnread) {
                notifTabUnread.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentFilter = 'unread';
                    renderNotificationList();
                });
            }

            if (notifTabAll) {
                notifTabAll.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentFilter = 'all';
                    renderNotificationList();
                });
            }

            // Toggle Notification Tray
            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const willOpen = notifMenu.classList.contains('hidden');
                    notifMenu.classList.toggle('hidden');

                    // Close profile menu if open
                    if (profileMenu && !profileMenu.classList.contains('hidden')) {
                        profileMenu.classList.add('hidden');
                    }
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }

                    if (willOpen) {
                        fetchNotifications();
                    }
                });
            }

            // Mark All Read button
            if (markAllBtn) {
                markAllBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    try {
                        const formData = new FormData();
                        formData.append(getCsrfName(), getCsrfToken());
                        const res = await fetch('<?= site_url("notifications/read-all") ?>', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            updateBadge(0);
                            notificationsCache.forEach(n => n.is_read = true);
                            renderNotificationList();
                        }
                    } catch (err) {
                        console.warn(err);
                    }
                });
            }

            // Clear All Notifications button
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (notificationsCache.length === 0) return;
                    try {
                        const formData = new FormData();
                        formData.append(getCsrfName(), getCsrfToken());
                        const res = await fetch('<?= site_url("notifications/clear-all") ?>', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            notificationsCache = [];
                            updateBadge(0);
                            renderNotificationList();
                        }
                    } catch (err) {
                        console.warn(err);
                    }
                });
            }

            // Close notification tray on outside click
            document.addEventListener('click', (e) => {
                if (notifMenu && !notifMenu.classList.contains('hidden') && !notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
                    notifMenu.classList.add('hidden');
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (notifMenu && !notifMenu.classList.contains('hidden')) {
                        notifMenu.classList.add('hidden');
                    }
                }
            });

            // Initial fetch & polling every 45s
            fetchNotifications();
            setInterval(fetchNotifications, 45000);
        })();
    });
</script>
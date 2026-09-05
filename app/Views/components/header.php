<?php
    $currentUri = uri_string();
    $role = session()->get('role');

    $navItems = [];

    // Executive Performance Dashboard
    $navItems['dashboard'] = 'Dashboard';

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

    // Only Admins and Supervisors create Distribution Lists (Teams)
    if (in_array($role, ['Admin', 'Supervisor'])) {
        $navItems['teams'] = 'My Teams';
    }

    if ($role === 'Admin') {
        $navItems['accounts'] = 'Accounts';
        $navItems['templates'] = 'Templates';
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
                
                <!-- Official GOVPH & Twin Seals (MC 24, s. 2023 / RA 10535 Compliance) -->
                <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
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
                </div>

                <!-- SPMS Institutional Brand Identity with Official Logo -->
                <a href="<?= site_url(array_key_first($navItems) ?? 'folders') ?>" class="flex-shrink-0 flex items-center gap-2.5 text-white hover:opacity-95 transition-opacity group min-w-0">
                    <!-- SPMS Official Logo -->
                    <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo"
                         class="w-9 h-9 rounded-full object-contain shrink-0 shadow-xs group-hover:scale-105 transition-transform"
                         style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; max-width: 36px; max-height: 36px; display: block;" />
                    <div class="flex flex-col min-w-0 leading-tight">
                        <div class="flex items-center gap-1.5 leading-none">
                            <span class="font-heading font-black tracking-tight text-base sm:text-lg uppercase text-white">SPMS</span>
                            <span class="font-bold text-sm" style="color: #34d399;">-</span>
                            <span class="font-heading font-black tracking-tight text-xs sm:text-sm uppercase" style="color: #34d399;">BSU</span>
                        </div>
                        <span class="font-bold uppercase tracking-wider truncate" style="font-size: 8px; color: rgba(209, 250, 229, 0.7); letter-spacing: 0.05em;">STRATEGIC PERFORMANCE MANAGEMENT SYSTEM</span>
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
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

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
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-[#1a2b22] flex items-center justify-between bg-slate-50/80 dark:bg-[#122019]/80">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-xs text-slate-900 dark:text-white uppercase tracking-wider">Notifications</span>
                                <span id="notification-header-count" class="hidden px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[10px] font-black">
                                    0 New
                                </span>
                            </div>
                            <button type="button" id="btn-mark-all-read"
                                    class="text-[11px] font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-colors cursor-pointer flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Mark all read</span>
                            </button>
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

                <!-- Profile Dropdown Button Capsule (Matching GovHeader4 with Fully Visible Name) -->
                <div class="relative">
                    <button id="profile-btn-mobile" 
                            class="relative flex items-center gap-2.5 text-white shadow-xs pl-1.5 pr-3 py-1 cursor-pointer transition-all hover:bg-white/5"
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
                        
                        <!-- Fully Visible Name & Role -->
                        <div class="flex flex-col text-left leading-tight shrink-0" style="display: flex;">
                            <span class="text-xs font-bold text-white whitespace-nowrap" style="font-size: 12px; font-weight: 700; color: #ffffff; white-space: nowrap;"><?= esc($displayName) ?></span>
                            <span class="font-bold tracking-wider uppercase whitespace-nowrap" style="font-size: 8.5px; color: rgba(110, 231, 183, 0.85); letter-spacing: 0.05em; white-space: nowrap;"><?= esc($role ?? 'User') ?></span>
                        </div>

                        <!-- Dropdown Chevron -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-300/70 shrink-0" style="display: block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

    <?php if ($showHamburger): ?>
        <div class="md:hidden hidden absolute left-0 right-0 bg-accent shadow-2xl z-[90] border-t border-white/10" id="mobile-menu">
            <div class="space-y-1 px-4 pb-4 pt-2">
                <?php foreach ($navItems as $uri => $label):
                    $isActive = ($currentUri === $uri) || ($uri !== '' && strpos($currentUri, $uri) === 0);
                ?>
                    <a href="<?= site_url($uri) ?>"
                        class="block px-4 py-3 text-base font-bold text-white transition-all duration-200 <?= $isActive ?
                        'rounded-xl bg-black/15 shadow-inner' : 'rounded-xl hover:bg-black/10'?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profile-btn-mobile');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const themeToggleBtn = document.getElementById('theme-toggle');

        // Toggle Theme
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
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden'); // Auto-close mobile menu if open
                }
            });
        }

        // Toggle Mobile Hamburger Menu
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
                if (profileMenu && !profileMenu.classList.contains('hidden')) {
                    profileMenu.classList.add('hidden'); // Auto-close profile menu if open
                }
            });
        }

        // Close dropdowns when clicking anywhere outside of them
        document.addEventListener('click', (e) => {
            if (profileMenu && !profileMenu.classList.contains('hidden') && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
            if (mobileMenu && !mobileMenu.classList.contains('hidden') && !mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.add('hidden');
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

            let notificationsCache = [];
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
                            renderNotificationList(notificationsCache);
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
                        return `<svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                    case 'target_submitted':
                    case 'eval_submitted':
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
                        return 'bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900';
                    case 'target_submitted':
                    case 'eval_submitted':
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

            function renderNotificationList(items) {
                if (!notifList) return;
                if (!items || items.length === 0) {
                    notifList.innerHTML = `
                        <div class="p-8 text-center">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-[#13271b] text-slate-400 dark:text-emerald-400 mx-auto flex items-center justify-center mb-2.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-slate-800 dark:text-white">All caught up!</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">No new notifications at this time.</p>
                        </div>
                    `;
                    return;
                }

                notifList.innerHTML = items.map(item => {
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

                            <!-- Unread Indicator Dot -->
                            ${isUnread ? '<span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 mt-1.5 shadow-xs"></span>' : ''}
                        </div>
                    `;
                }).join('');

                // Attach click listeners on each row
                notifList.querySelectorAll('.notification-item').forEach(el => {
                    el.addEventListener('click', async (e) => {
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
                            renderNotificationList(notificationsCache);
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
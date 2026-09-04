<?php
    $currentUri = uri_string();
    $role = session()->get('role');

    $navItems = [];

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

                <!-- SPMS Institutional Brand Identity with Gold Monitor Badge -->
                <a href="<?= site_url(array_key_first($navItems) ?? 'folders') ?>" class="flex-shrink-0 flex items-center gap-2.5 text-white hover:opacity-95 transition-opacity group min-w-0">
                    <!-- Warm Gold Rounded Square Badge with Dark Monitor Icon -->
                    <div class="flex items-center justify-center shadow-xs shrink-0 group-hover:scale-105 transition-transform"
                         style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; max-width: 36px; max-height: 36px; background-color: #f59e0b; border-radius: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: #061a10;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                            <line x1="6" y1="8" x2="18" y2="8" stroke-width="1.75"></line>
                            <line x1="6" y1="12" x2="14" y2="12" stroke-width="1.75"></line>
                        </svg>
                    </div>
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

                <!-- Notification Bell Button -->
                <button type="button" 
                        class="relative w-8 h-8 rounded-xl text-slate-300 hover:text-white shadow-xs flex items-center justify-center transition-all cursor-pointer shrink-0"
                        style="background-color: rgba(0, 0, 0, 0.25); border: 1px solid rgba(16, 185, 129, 0.25);"
                        title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Notification Badge Dot -->
                    <span class="w-2 h-2 rounded-full bg-amber-400 absolute top-1.5 right-1.5" style="border: 1px solid #061a10;"></span>
                </button>

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
    });
</script>
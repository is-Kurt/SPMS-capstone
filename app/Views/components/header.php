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
            $displayName = trim($fName . ' ' . ($lName ? substr($lName, 0, 1) . '.' : ''));
        } else {
            $displayName = 'Sarah P.';
        }
    }
    $avatarLetter = session('avatar_letter') ?? substr($displayName, 0, 1) ?: 'S';
?>

<nav class="bg-[#042819] dark:bg-[#0c1410] border-b border-[#0c4a33] dark:border-[#1a2b22] antialiased relative z-[110]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            
        <div class="flex items-center gap-3">

            <!-- Golden Hamburger Button (matches mockup) -->
            <button type="button" id="mobile-menu-btn" class="w-9 h-9 rounded-xl bg-[#f59e0b] text-black flex items-center justify-center font-bold shadow-sm shrink-0 hover:bg-[#d97706] transition-colors cursor-pointer" title="Menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="flex items-center">
                <!-- SPMS brand mark with BSU badge and university subline -->
                <a href="<?= site_url('folders') ?>" class="flex-shrink-0 flex items-center gap-2 text-white hover:opacity-95 transition-opacity">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5 leading-none">
                            <span class="font-heading font-black tracking-tight text-xl uppercase text-white">SPMS</span>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-1.5 py-0.5 rounded">BSU</span>
                        </div>
                        <span class="text-[10px] text-emerald-200/60 dark:text-[#8ea396] font-medium leading-tight">Benguet State University</span>
                    </div>
                </a>

                <div class="hidden md:flex ml-8 items-center space-x-2 text-white text-xs">
                    <?php foreach ($navItems as $uri => $label):
                        $isActive = ($currentUri === $uri) || ($uri !== '' && strpos($currentUri, $uri) === 0);
                    ?>
                        <a href="<?= site_url($uri) ?>"
                            class="px-4 py-2 transition-all rounded-xl <?= $isActive ?
                            'bg-[#073824] dark:bg-[#12241b] text-white shadow-xs border border-emerald-700/30 dark:border-[#1d3d2e] font-bold' : 'text-emerald-100/70 dark:text-[#8ea396] hover:text-white hover:bg-white/5 font-medium'?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
            
            <!-- Right: Theme Toggle, Notifications Bell & Capsule User Pill -->
            <div class="flex items-center gap-2.5">
                <!-- Theme Toggle Button (Outside beside Notification Bell) -->
                <button type="button" id="theme-toggle" class="relative w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 shadow-xs flex items-center justify-center transition-all cursor-pointer" title="Toggle Light / Dark Mode">
                    <!-- Sun icon: visible in dark mode, click to switch to light -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden dark:block text-amber-400 hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon: visible in light mode, click to switch to dark -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 block dark:hidden text-slate-300 hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Notification Bell -->
                <button type="button" class="relative w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 shadow-xs flex items-center justify-center transition-all cursor-pointer" title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Notification Badge Dot -->
                    <span class="w-2 h-2 rounded-full bg-amber-400 border border-[#042819] absolute top-1.5 right-1.5"></span>
                </button>

                <!-- Profile Dropdown Button Pill -->
                <div class="relative">
                    <button id="profile-btn-mobile" class="relative flex items-center gap-2 rounded-full bg-white/5 hover:bg-white/10 text-white border border-white/10 shadow-xs pl-1.5 pr-3.5 py-1 cursor-pointer transition-all">
                        <?php if (session('avatar_image')): ?>
                            <img src="<?= base_url('uploads/avatars/' . session('avatar_image')) ?>" alt="User" class="w-6 h-6 rounded-full object-cover shrink-0" />
                        <?php else: ?>
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-black font-black text-xs bg-[#f59e0b] shrink-0">
                                <?= esc($avatarLetter) ?>
                            </div>
                        <?php endif; ?>
                        <span class="text-xs font-bold text-white max-w-[130px] truncate"><?= esc($displayName) ?></span>
                    </button>

                    <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-3 w-56 origin-top-right rounded-2xl bg-surface p-2 shadow-2xl ring-1 ring-surface-border z-[100]">
                        <div class="flex items-center gap-3 px-3 py-2 mb-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-black text-sm bg-purple-600 shrink-0">
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
                            <!-- Person/profile icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>

                        <hr class="my-1.5 border-surface-border">

                        <?= form_open('login') ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-xl transition-colors cursor-pointer">
                                <!-- Door/exit icon (sign out) -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign out
                            </button>
                        <?= form_close() ?>
                    </div>
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
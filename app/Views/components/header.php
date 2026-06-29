<?php 
    $current_uri = uri_string();
    $role = session()->get('role'); 
    
    $nav_items = [
        'folders' => 'Folders',
    ];

    // Roles that evaluate others get the Ratings tab
    if (in_array($role, ['Admin', 'Supervisor', 'HR'])) {
        $nav_items['ratings'] = 'Ratings'; 
    }

    // Only Admins and Supervisors create Distribution Lists (Teams)
    if (in_array($role, ['Admin', 'Supervisor'])) {
        $nav_items['teams'] = 'My Teams';
    }

    if ($role === 'Admin') {
        $nav_items['accounts'] = 'Accounts'; 
        $nav_items['templates'] = 'Templates';
    }

    // Check if we need a hamburger menu (more than 1 tab available)
    $showHamburger = count($nav_items) > 1;
?>

<nav class="bg-accent shadow-lg shadow-accent/10 antialiased relative z-[110]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            
        <div class="flex items-center gap-3">

            <?php if (!$showHamburger): ?>
                <span class="md:hidden text-white text-sm font-bold opacity-90 mr-2">Folders</span>
            <?php endif; ?>

            <?php if ($showHamburger): ?>
                <button type="button" id="mobile-menu-btn" class="md:hidden inline-flex items-center justify-center rounded-lg bg-black/10 p-2.5 text-white hover:bg-black/20 focus:outline-none transition-colors">
                    <svg class="block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            <?php endif; ?>

            <div class="flex items-center">
                <a href="<?= site_url('folders') ?>" class="flex-shrink-0 flex items-center gap-1 md:mr-6 text-white hover:opacity-90 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-black tracking-tighter text-xl uppercase">SPMS</span>
                </a>

                <div class="hidden md:flex ml-10 items-baseline space-x-2 text-white text-sm font-bold">
                    <?php foreach ($nav_items as $uri => $label):
                        $is_active = ($current_uri === $uri) || ($uri !== '' && strpos($current_uri, $uri) === 0);
                    ?>
                        <a href="<?= site_url($uri) ?>" 
                            class="px-4 py-2 transition-all duration-200 <?= $is_active ? 
                            'rounded-xl bg-black/15 text-white shadow-inner' : 'rounded-xl hover:bg-black/10 hover:text-white'?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
            
                <div class="relative">
                    <button id="profile-btn-mobile" class="relative flex items-center rounded-full cursor-pointer border-2 border-transparent hover:border-white/20 transition-all">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                alt="User" class="size-10 md:size-12 rounded-full" />
                    </button>

                    <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-3 w-56 origin-top-right rounded-2xl bg-surface p-2 shadow-2xl ring-1 ring-surface-border z-[100]">
                        <div class="flex items-center gap-3 px-3 py-2 mb-1">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                alt="User" class="size-10 rounded-full object-cover ring-1 ring-surface-border" />
                            <div class="flex flex-col min-w-0">
                                <p class="text-sm font-bold text-text truncate break-all">
                                    <?= esc(session()->get('username') ?? 'Null username') ?>
                                </p>
                                <p class="text-[11px] font-bold text-text-muted tracking-widest break-all"><?= esc(session()->get('email') ?? 'User@email') ?></p>
                            </div>
                        </div>

                        <hr class="my-1.5 border-surface-border">

                        <a href="<?= site_url('profile') ?>"  class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-text-muted hover:bg-accent/10 hover:text-accent rounded-xl transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>
                        
                        <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-text-muted hover:bg-accent/10 hover:text-accent rounded-xl transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            Archive
                        </a>

                        <hr class="my-1.5 border-surface-border">

                        <?= form_open('login') ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-colors cursor-pointer">
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
                <?php foreach ($nav_items as $uri => $label):
                    $is_active = ($current_uri === $uri) || ($uri !== '' && strpos($current_uri, $uri) === 0);
                ?>
                    <a href="<?= site_url($uri) ?>" 
                        class="block px-4 py-3 text-base font-bold text-white transition-all duration-200 <?= $is_active ? 
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
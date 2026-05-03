<nav class="bg-accent shadow-lg shadow-accent/10 antialiased">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            
            <div class="hidden md:flex items-center">
                <div class="flex-shrink-0 flex items-center gap-1 mr-6 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-black tracking-tighter text-xl uppercase">IPCR</span>
                </div>

                <div class="ml-10 flex items-baseline space-x-2 text-white text-sm font-bold">
                    <?php 
                        $current_uri = uri_string();
                        
                        $role = session()->get('role'); 
                        
                        $nav_items = [
                            'documents' => 'Documents',
                            'submissions' => 'Submissions'
                        ];

                        if ($role !== 'user') {
                            $nav_items = ['ratings' => 'Ratings'] + $nav_items; 
                        }

                        foreach ($nav_items as $uri => $label):
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

            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <div class="relative ml-3">
                        <button class="profile-btn relative flex max-w-xs items-center rounded-full cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                 alt="User" class="size-12 rounded-full" />
                        </button>

                        <div class="profile-dropdown hidden absolute right-0 mt-3 w-56 origin-top-right rounded-2xl bg-surface p-2 shadow-2xl ring-1 ring-surface-border z-50">
                            <div class="flex items-center gap-3 px-3 py-2 mb-1">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                    alt="User" class="size-10 rounded-full object-cover ring-1 ring-surface-border" />
                                <div class="flex flex-col min-w-0">
                                    <p class="text-sm font-bold text-text truncate">
                                        <?= esc(session()->get('username') ?? 'Null username') ?>
                                    </p>
                                    <p class="text-[11px] font-bold text-text-muted tracking-widest"><?= esc(session()->get('email') ?? 'User@email') ?></p>
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
        </div>
    </div>
</nav>
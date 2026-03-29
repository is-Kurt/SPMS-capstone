<nav class="bg-highlight">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="hidden md:flex items-center">
                <div class="ml-10 flex items-baseline space-x-4 text-md font-bold">
                    <a href="<?= site_url('/') ?>" class="px-3 py-2 <?= (uri_string() === '') ?
                        'rounded-md bg-page-bg ' : 'rounded-md hover:bg-highlight-hover'?>
                        focus-visible:rounded-md focus-visible:bg-highlight-hover focus-visible:text-main-text">Home</a>
                    <a href="<?= site_url('documents?docs=all') ?>" class="px-3 py-2 <?= (uri_string() === 'documents') ?
                        'rounded-md bg-page-bg text-main-text' : 'rounded-md hover:bg-highlight-hover'?>
                        focus-visible:rounded-md focus-visible:bg-highlight-hover focus-visible:text-main-text">Documents</a>
                    <a href="<?= site_url('submissions?docs=all') ?>" class="px-3 py-2 <?= (uri_string() === 'submissions') ? 
                        'rounded-md bg-page-bg text-main-text' : 'rounded-md hover:bg-highlight-hover'?>
                        focus-visible:rounded-md focus-visible:bg-highlight-hover focus-visible:text-main-text">Submissions</a>
                </div>
            </div>
            
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <!-- Profile dropdown -->
                    <div class="relative ml-3">
                        <button class="profile-btn relative flex max-w-xs items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-main-border">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">Open user menu</span>
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                 alt="" class="size-12 rounded-full" />
                        </button>

                        <div class="profile-dropdown hidden absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-card-bg py-1 shadow-lg ring-1 ring-main-border">
                            <a href="#" class="block px-4 py-2 text-sm hover:bg-white/5 hover:text-main-text">Your profile</a>
                            <a href="#" class="block px-4 py-2 text-sm hover:bg-white/5 hover:text-main-text">Settings</a>
                            <?= form_open('login') ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="block px-4 py-2 text-sm hover:bg-white/5 hover:text-main-text">Logout</button>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu buttons -->
            <div class="-ml-2 flex flex-row items-center md:hidden space-x-3">
                <button class="mobile-nav-btn relative inline-flex items-center justify-center rounded-md p-2  hover:bg-white/5 hover:text-main-text">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open navigation menu</span>
                    <!-- Hamburger icon -->
                    <svg class="menu-icon size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <!-- Close icon -->
                    <svg class="close-icon hidden size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="px-3 py-2 font-bold rounded-md bg-card-bg">
                    <?= empty(uri_string()) ? 'Home' : ucwords(uri_string()) ?>
                </div>
            </div>

            <div class="-mr-2 md:hidden">
                <button class="mobile-profile-btn relative inline-flex items-center justify-center rounded-md p-2  hover:bg-white/5 hover:text-main-text">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open profile menu</span>
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                 alt="" class="size-12 rounded-full" />
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile navigation and profile menus -->
    <div class="mobile-nav-menu hidden bg-highlight border-b border-main-border md:hidden font-bold">
        <div class="space-y-1 px-2 pt-2 pb-3">
            <a href="/" class="block px-3 py-2 text-sm <?= (uri_string() === '') ? 
                'rounded-md bg-card-bg text-main-text' : 'rounded-md  hover:bg-highlight-hover hover:text-main-text'?>">Home</a>
            <a href="templates" class="block px-3 py-2 text-sm <?= (uri_string() === 'templates') ? 
                'rounded-md bg-card-bg text-main-text' : 'rounded-md  hover:bg-highlight-hover hover:text-main-text'?>">Templates</a>
            <a href="create" class="block px-3 py-2 text-sm <?= (uri_string() === 'create') ? 
                'rounded-md bg-card-bg text-main-text' : 'rounded-md  hover:bg-highlight-hover hover:text-main-text'?>">Create</a>
        </div>
    </div>

    <div class="mobile-profile-menu hidden bg-highlight border-b border-main-border md:hidden font-bold">
        <div class="mt-3 space-y-1 px-2 pb-3">
            <a href="#" class="block rounded-md px-3 py-2 text-base hover:bg-white/5 hover:text-main-text">Your profile</a>
            <a href="#" class="block rounded-md px-3 py-2 text-base hover:bg-white/5 hover:text-main-text">Settings</a>
            <a href="#" class="block rounded-md px-3 py-2 text-base hover:bg-white/5 hover:text-main-text">Sign out</a>
        </div>
    </div>
</nav>
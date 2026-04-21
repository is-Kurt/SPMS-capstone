<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-4xl mx-auto flex flex-col gap-8 pb-20 h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar">
    
    <div class="shrink-0">
        <h1 class="text-3xl font-black tracking-tight text-text">Account Settings</h1>
        <p class="text-sm text-text-muted mt-1 font-medium italic">Manage your profile information and security preferences.</p>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0">
        <div class="px-8 py-5 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">General Information</h2>
        </div>
        
        <div class="p-8 flex flex-col gap-8">
            <div class="flex items-center gap-6">
                <div class="relative group cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                         alt="Profile Picture" 
                         class="size-24 rounded-full object-cover ring-4 ring-surface-border group-hover:ring-accent/50 transition-all">
                    
                    <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex gap-3">
                        <button type="button" class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-text text-xs font-bold py-2 px-4 rounded-lg transition-all active:scale-[0.98] cursor-pointer">
                            Upload New
                        </button>
                        <button type="button" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 text-xs font-bold py-2 px-4 rounded-lg transition-all cursor-pointer">
                            Remove
                        </button>
                    </div>
                    <p class="text-[10px] font-semibold text-text-muted uppercase tracking-widest">JPG, GIF or PNG. Max size of 2MB.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">First Name</label>
                    <input type="text" placeholder="e.g. Juan" 
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold placeholder:text-text-muted/50">
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Last Name</label>
                    <input type="text" placeholder="e.g. Dela Cruz" 
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold placeholder:text-text-muted/50">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Email Address</label>
                    <input type="email" placeholder="e.g. user@example.com" value="<?= esc(session()->get('email') ?? '') ?>"
                        class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold placeholder:text-text-muted/50">
                </div>
            </div>
        </div>

        <div class="px-8 py-4 bg-zinc-50 dark:bg-zinc-800/30 border-t border-surface-border flex justify-end">
            <button type="button" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-lg shadow-accent/20 transition-all active:scale-[0.98] cursor-pointer">
                Save Profile
            </button>
        </div>
    </div>

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden shrink-0">
        <div class="px-8 py-5 border-b border-surface-border bg-zinc-50 dark:bg-zinc-800/30">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-text">Security & Authentication</h2>
        </div>
        
        <div class="p-8 flex flex-col gap-8">
            
            <div class="flex flex-col gap-6">
                <div>
                    <h3 class="text-sm font-bold text-text">Change Password</h3>
                    <p class="text-xs text-text-muted mt-1 font-medium italic">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Current Password</label>
                        <input type="password" placeholder="••••••••" 
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">New Password</label>
                        <input type="password" placeholder="••••••••" 
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Confirm Password</label>
                        <input type="password" placeholder="••••••••" 
                            class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold">
                    </div>
                </div>

                <div class="flex justify-start mt-2">
                    <button type="button" class="bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:opacity-90 text-xs font-bold py-2.5 px-6 rounded-lg transition-all active:scale-[0.98] cursor-pointer">
                        Update Password
                    </button>
                </div>
            </div>

            <hr class="border-surface-border">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        Two-Factor Authentication (2FA)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[9px] uppercase tracking-widest">Recommended</span>
                    </h3>
                    <p class="text-xs text-text-muted mt-1 font-medium">Add an extra layer of security to your account. When enabled, you will be prompted for a secure code during login.</p>
                </div>
                
                <button type="button" id="toggle-2fa" class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full bg-zinc-300 dark:bg-zinc-700 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 dark:focus:ring-offset-zinc-900" role="switch" aria-checked="false">
                    <span class="sr-only">Enable 2FA</span>
                    <span id="toggle-circle" class="pointer-events-none inline-block h-5 w-5 translate-x-1 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('toggle-2fa').addEventListener('click', function() {
        const isEnabled = this.getAttribute('aria-checked') === 'true';
        this.setAttribute('aria-checked', !isEnabled);
        
        const circle = document.getElementById('toggle-circle');
        
        if (!isEnabled) {
            this.classList.remove('bg-zinc-300', 'dark:bg-zinc-700');
            this.classList.add('bg-accent');
            circle.classList.remove('translate-x-1');
            circle.classList.add('translate-x-6');
        } else {
            this.classList.add('bg-zinc-300', 'dark:bg-zinc-700');
            this.classList.remove('bg-accent');
            circle.classList.remove('translate-x-6');
            circle.classList.add('translate-x-1');
        }
    });
</script>

<?= $this->endSection() ?>
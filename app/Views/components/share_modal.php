<div id="share-modal" 
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-[400px] p-8 border border-zinc-200 dark:border-zinc-800">
        
        <div class="text-center mb-8">
            <h2 class="text-xl font-black tracking-tight text-text">Share Document</h2>
        </div>

        <div class="space-y-6">
            <div class="space-y-2">
                <label for="share-email-input" class="block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                    Collaborator Email
                </label>
                <div class="flex gap-2">
                    <input type="email" id="share-email-input" placeholder="Enter user email..."
                        class="flex-1 bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all placeholder:text-text-muted/50 text-text">
                    
                    <button id="btn-share-search"
                        class="bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-text-muted px-4 rounded-xl transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div id="share-search-result" class="hidden items-center justify-between p-4 bg-accent/5 border border-accent/20 rounded-xl animate-in fade-in zoom-in duration-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="bg-accent/20 p-2 rounded-full text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span id="share-user-label" class="text-xs font-bold text-text"></span>
                </div>
                <button id="btn-share-confirm" class="text-[10px] uppercase tracking-widest font-bold bg-accent text-white px-4 py-2.5 rounded-lg hover:bg-accent-hover shadow-md shadow-accent/20 transition-all cursor-pointer active:scale-[0.98]">
                    Confirm
                </button>
            </div>

            <p id="share-error" class="hidden mt-4 text-xs font-semibold text-red-500 dark:text-red-400 text-center animate-in fade-in duration-200">
                </p>

        </div>
    </div>
</div>
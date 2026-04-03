<div id="delete-modal" 
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-[380px] p-8 border border-zinc-200 dark:border-zinc-800">
        
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-full mb-4 ring-4 ring-red-50 dark:ring-red-500/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h2 class="text-xl font-black tracking-tight text-text">Delete Document</h2>
            <p class="text-xs text-text-muted mt-2 px-4 leading-relaxed font-medium">
                Are you sure you want to delete <span id="delete-doc-title" class="font-bold text-text"></span>?
            </p>
        </div>

        <div class="flex flex-col gap-2 mt-8">
            <button id="btn-confirm-delete" 
                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-red-500/20 transition-all text-sm cursor-pointer active:scale-[0.98]">
                Delete Permanently
            </button>
            <button id="btn-close-delete" 
                class="w-full text-xs font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 py-2 transition-colors cursor-pointer">
                Cancel, keep it
            </button>
        </div>
    </div>
</div>
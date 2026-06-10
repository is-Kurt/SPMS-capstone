<div id="send-all-modal" 
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-[380px] p-8 border border-zinc-200 dark:border-zinc-800">
        
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-50 dark:bg-blue-500/10 text-blue-500 rounded-full mb-4 ring-4 ring-blue-50 dark:ring-blue-500/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </div>
            <h2 class="text-xl font-black tracking-tight text-text">Send to All Users</h2>
            <p class="text-xs text-text-muted mt-2 px-4 leading-relaxed font-medium">
                Are you sure you want to distribute <span id="send-all-doc-title" class="font-bold text-text"></span> to every user in the system?
            </p>
        </div>
        
        <div class="flex flex-col gap-2 mt-8">
            <button id="btn-confirm-send-all" 
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all text-sm cursor-pointer active:scale-[0.98]">
                Yes, Send to Everyone
            </button>
            <button id="btn-close-send-all" 
                class="w-full text-xs font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 py-2 transition-colors cursor-pointer">
                Cancel
            </button>
        </div>
    </div>
</div>
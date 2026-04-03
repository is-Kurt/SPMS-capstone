<div id="create-modal" 
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all">
    
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-[400px] p-8 border border-zinc-200 dark:border-zinc-800">
        
        <div class="text-center mb-8">
            <h2 class="text-xl font-black tracking-tight text-text">New Document</h2>
        </div>

        <div class="space-y-6">
            <div class="space-y-2">
                <label for="new-doc-title" class="block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                    Document Name
                </label>
                <input type="text" id="new-doc-title" placeholder="Untitled Document"
                    class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none placeholder:text-text-muted/50 text-text transition-all">
            </div>

            <div class="space-y-2">
                <label for="new-doc-template" class="block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                    Template
                </label>
                <div class="relative group">
                    <select id="new-doc-template" 
                        class="w-full appearance-none bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none cursor-pointer text-text transition-all">
                        <option value="" selected>Blank Canvas</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-text-muted group-hover:text-accent transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <button id="btn-create"
                class="w-full bg-accent text-white font-bold py-3.5 rounded-xl cursor-pointer hover:bg-accent-hover transition-all text-sm shadow-lg shadow-accent/20 active:scale-[0.98]">
                Create Document
            </button>
        </div>
    </div>
</div>
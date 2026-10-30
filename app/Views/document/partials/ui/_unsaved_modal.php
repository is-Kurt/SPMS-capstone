<!-- UNSAVED CHANGES EXIT CONFIRMATION MODAL -->
<div id="unsaved-changes-modal" class="hidden fixed inset-0 z-[250] flex items-center justify-center bg-zinc-950/40 backdrop-blur-sm transition-all p-4" aria-modal="true" role="dialog">
    <div class="bg-surface rounded-2xl shadow-2xl w-full p-8 border border-surface-border transform transition-all duration-150 scale-95 opacity-0" style="max-width: 380px; width: 100%;" id="unsaved-changes-card">
        
        <!-- Icon & Header matching confirm_modal -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4 ring-4 bg-warning-50 dark:bg-warning-500/10 text-warning-500 ring-warning-50 dark:ring-warning-500/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h2 class="text-xl font-black tracking-tight text-text">Unsaved Changes</h2>
            <p class="text-xs text-text-muted mt-2 px-2 leading-relaxed font-medium">
                You have recent edits that have not finished saving yet. Please save your changes before exiting.
            </p>
        </div>

        <!-- Action Buttons matching confirm_modal layout -->
        <div class="flex flex-col gap-2 mt-6">
            <!-- 1. Save and Exit -->
            <button type="button" id="btn-save-and-exit" onclick="handleSaveAndExit()"
                    class="w-full text-white font-bold py-3.5 rounded-xl shadow-lg transition-all text-sm cursor-pointer active:scale-[0.98] bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20 inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span id="btn-save-exit-text">Save and Exit</span>
            </button>

            <!-- 2. Stay and Keep Editing -->
            <button type="button" onclick="closeUnsavedChangesModal()"
                    class="w-full text-xs font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 py-2 transition-colors cursor-pointer mt-1">
                Stay &amp; Keep Editing
            </button>
        </div>

    </div>
</div>

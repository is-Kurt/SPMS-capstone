
<!-- Mobile backdrop for spreadsheet context menu -->
<div id="spms-context-backdrop" class="hidden fixed inset-0 bg-black/25 backdrop-blur-xs transition-opacity" style="z-index: 99999;" onclick="closeContextMenu()"></div>

<!-- Custom Context Menu for Document Grid -->
<div id="spms-context-menu" class="hidden fixed bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-2xl py-1 text-xs text-zinc-700 dark:text-zinc-200" style="z-index: 100000; min-width: 190px;">
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors" data-action="comment">
        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
        <span class="flex-1 font-semibold">View Comment</span>
        <span class="text-[10px] text-zinc-400">Alt+M</span>
    </button>
    <div id="spms-ctx-div-1" class="h-px bg-zinc-200 dark:bg-zinc-800 my-1"></div>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="cut">
        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242 3 3 0 014.242 0z"/></svg>
        <span class="flex-1">Cut</span>
        <span class="text-[10px] text-zinc-400">Ctrl+X</span>
    </button>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="copy">
        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <span class="flex-1">Copy</span>
        <span class="text-[10px] text-zinc-400">Ctrl+C</span>
    </button>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="paste">
        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="flex-1">Paste</span>
        <span class="text-[10px] text-zinc-400">Ctrl+V</span>
    </button>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="clear">
        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span class="flex-1">Clear Cell</span>
        <span class="text-[10px] text-zinc-400">Del</span>
    </button>
    <div id="spms-ctx-div-2" class="h-px bg-zinc-200 dark:bg-zinc-800 my-1"></div>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="insert-above">
        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/></svg>
        <span class="flex-1">Insert Row Above</span>
    </button>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" data-action="insert-below">
        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/></svg>
        <span class="flex-1">Insert Row Below</span>
    </button>
    <button type="button" class="spms-ctx-item flex items-center gap-2.5 px-3 py-1.5 w-full text-left hover:bg-red-50 dark:hover:bg-red-950/40 text-red-600 dark:text-red-400 transition-colors" data-action="delete-row">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span class="flex-1 font-semibold">Delete Row</span>
    </button>
</div>

<!-- Threaded Cell Comment Card Overlay -->
<div id="spms-comment-overlay" class="hidden fixed bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl shadow-2xl overflow-hidden animate-in fade-in duration-150 flex flex-col text-left" style="z-index: 100001; width: 340px; max-height: 480px; text-align: left !important;">
    <!-- Header -->
    <div class="px-4 py-2.5 bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-left" style="text-align: left !important;">
        <div class="flex items-center gap-2 text-left" style="text-align: left !important;">
            <div class="w-2 h-2 rounded-full bg-amber-500" id="comment-status-dot"></div>
            <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100" id="comment-overlay-title">Cell Comment</span>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" id="btn-toggle-resolve-thread" onclick="toggleResolveCurrentThread()" class="text-[10px] font-bold px-2 py-0.5 rounded border transition-colors cursor-pointer text-emerald-700 bg-emerald-50 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-100" title="Mark this comment thread as resolved">
                Resolve
            </button>
            <button type="button" onclick="closeCommentOverlay()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-0.5 rounded transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <!-- Thread Items Body -->
    <div id="spms-comment-thread-body" class="p-3 overflow-y-auto space-y-2.5 flex-1 text-xs text-left" style="text-align: left !important;">
        <!-- Rendered dynamically -->
    </div>
    <!-- Reply Composer -->
    <div class="p-3 bg-zinc-50/70 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800">
        <div class="relative">
            <textarea id="comment-reply-input" rows="2" placeholder="Write a comment or reply..." class="w-full text-xs p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 placeholder-zinc-400 focus:outline-emerald-500 resize-none"></textarea>
            <div class="flex justify-between items-center mt-1.5">
                <span class="text-[10px] text-zinc-400">Ctrl+Enter to post</span>
                <button type="button" id="btn-post-comment-reply" onclick="postCurrentCommentOrReply()" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-md shadow-xs transition-colors cursor-pointer flex items-center gap-1">
                    <span>Send</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

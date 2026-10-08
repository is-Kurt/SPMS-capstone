<?php
    $activeTitle = $activeFolder['title'] ?? ($activeCycle['title'] ?? 'Evaluation Period');
    $activeId = $selectedFolderId ?? ($activeFolder['id'] ?? ($activeCycle['id'] ?? null));
    $isArchived = !empty($activeFolder['deleted_at']) || !empty($activeCycle['deleted_at']);
    $containerClass = $containerClass ?? 'mb-3';
?>

<!-- Mobile Unified Folder Sidebar Trigger -->
<div class="lg:hidden <?= esc($containerClass) ?>">
    <button type="button" 
            onclick="typeof toggleAppSidebar === 'function' ? toggleAppSidebar() : (window.toggleAppSidebar && window.toggleAppSidebar())"
            aria-label="Open evaluation folders sidebar"
            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white hover:border-amber-500/50 dark:hover:border-amber-500/50 active:scale-[0.98] transition-all shadow-xs cursor-pointer text-left group">
        <div class="flex items-center gap-2.5 min-w-0">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-white truncate tracking-tight">
                <?= esc($activeTitle) ?>
            </span>
            <?php if ($isArchived): ?>
                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shrink-0">
                    Archived
                </span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800/80 text-[11px] font-bold text-zinc-600 dark:text-zinc-300 group-hover:text-amber-600 dark:group-hover:text-amber-400 group-hover:bg-amber-500/10 transition-colors shrink-0 ml-2">
            <span>Folders</span>
            <svg class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-500 group-hover:text-amber-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </button>
</div>

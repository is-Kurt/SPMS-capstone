<?php
    $activeTitle = $activeFolder['title'] ?? ($activeCycle['title'] ?? 'Evaluation Period');
    $activeId = $selectedFolderId ?? ($activeFolder['id'] ?? ($activeCycle['id'] ?? null));
    $dropdownFolders = $folders ?? ($sidebarFolders ?? ($rootFolders ?? []));
    
    // Route resolution
    $isArchivedRoute = $isArchivedRoute ?? (service('uri')->getSegment(2) === 'archived');
    $firstSeg = service('uri')->getSegment(1);
    $resolvedBaseUrl = $baseUrl ?? (
        ($firstSeg === 'ratings') ? 'ratings' : 
        (($firstSeg === 'dashboard') ? 'dashboard' : 
        ($isArchivedRoute ? 'folders/archived' : 'folders'))
    );
    $containerClass = $containerClass ?? 'mb-3';
?>

<style>
    .spms-folder-card {
        background-color: #061810 !important;
        border-color: #14422b !important;
    }
    .dark .spms-folder-card,
    .dark [class*="dark:border-[#14422b]"] {
        border-color: #14422b !important;
    }
    .spms-folder-dropdown {
        background-color: #04170e !important;
        border-color: #14422b !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.65), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
    }
    .dark .spms-folder-dropdown {
        background-color: #04170e !important;
        border-color: #14422b !important;
    }
</style>

<!-- MOBILE FOLDER SELECTOR DROPDOWN (PULL-DOWN MENU SPEC) -->
<div class="lg:hidden relative js-mobile-folder-dropdown-container <?= esc($containerClass) ?>">
    <!-- TRIGGER CAPSULE BUTTON -->
    <button type="button" 
            onclick="toggleSPMSFolderDropdown(this, event)"
            aria-haspopup="true"
            aria-expanded="false"
            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl spms-folder-card border border-[#14422b] text-white hover:border-emerald-500/50 transition-all shadow-xs cursor-pointer text-left group">
        <div class="flex items-center gap-2.5 min-w-0">
            <svg class="w-5 h-5 text-[#00df82] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span class="text-xs sm:text-sm font-bold text-white truncate tracking-tight">
                <?= esc($activeTitle) ?>
            </span>
            <?php if (!empty($activeFolder['deleted_at']) || !empty($activeCycle['deleted_at'])): ?>
                <span class="ml-1.5 px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30 shrink-0">
                    Archived
                </span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-1 text-[11px] font-bold text-[#7f998c] group-hover:text-[#00df82] transition-colors shrink-0 ml-2">
            <span>Folders</span>
            <svg class="w-3.5 h-3.5 transition-transform duration-200 js-folder-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <!-- SLEEK FLOATING DROPDOWN PANEL -->
    <div class="hidden js-mobile-folder-dropdown-menu absolute left-0 right-0 top-full mt-1.5 z-[150] rounded-xl spms-folder-dropdown border border-[#14422b] shadow-2xl overflow-hidden backdrop-blur-md">
        <!-- HEADER -->
        <div class="px-3.5 py-2.5 border-b border-[#0f3d28] flex items-center justify-between bg-black/25">
            <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#00df82]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#7f998c]">
                    Evaluation Periods
                </span>
            </div>
            <span class="text-[10px] font-extrabold text-[#00df82] bg-[#00df82]/10 px-2 py-0.5 rounded-full border border-[#00df82]/20">
                <?= count($dropdownFolders) ?> <?= count($dropdownFolders) === 1 ? 'Period' : 'Periods' ?>
            </span>
        </div>

        <!-- FOLDERS LIST -->
        <div class="max-h-64 overflow-y-auto custom-scrollbar p-1.5 space-y-1 bg-[#04170e]">
            <?php if (empty($dropdownFolders)): ?>
                <div class="p-3 text-center text-xs text-[#7f998c] italic">
                    No evaluation periods found.
                </div>
            <?php else: ?>
                <?php foreach ($dropdownFolders as $f): ?>
                    <?php 
                        $fId = $f['id'] ?? null;
                        $fTitle = $f['title'] ?? 'Evaluation Period';
                        $isCurrent = ($activeId == $fId);
                        $targetUrl = site_url($resolvedBaseUrl . '/' . $fId);
                    ?>
                    <a href="<?= $targetUrl ?>" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-bold transition-all <?= $isCurrent ? 'bg-[#0b2b1d] text-white border border-[#165a39] shadow-xs' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0 <?= $isCurrent ? 'text-[#00df82]' : 'text-[#7f998c]' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <span class="truncate <?= $isCurrent ? 'font-black text-white' : '' ?>">
                                <?= esc($fTitle) ?>
                            </span>
                            <?php if (!empty($f['deleted_at'])): ?>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30 shrink-0">
                                    Archived
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isCurrent): ?>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#00df82] shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00df82]"></span>
                                Active
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- FOOTER (Switch between Active and Archived if Admin on Folders) -->
        <?php if (session()->get('role') === 'Admin' && in_array($firstSeg, ['folders', 'document'])): ?>
            <div class="p-2 border-t border-[#0f3d28] bg-black/20">
                <a href="<?= site_url($isArchivedRoute ? 'folders' : 'folders/archived') ?>" 
                   class="flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-bold text-[#7f998c] hover:text-[#00df82] hover:bg-white/5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <span><?= $isArchivedRoute ? 'View Active Folders' : 'View Archived Folders' ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
if (typeof window.toggleSPMSFolderDropdown === 'undefined') {
    window.toggleSPMSFolderDropdown = function(btn, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const container = btn.closest('.js-mobile-folder-dropdown-container');
        if (!container) return;
        const menu = container.querySelector('.js-mobile-folder-dropdown-menu');
        const chevron = container.querySelector('.js-folder-chevron');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        
        // Close any other open folder dropdowns
        document.querySelectorAll('.js-mobile-folder-dropdown-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
        });
        document.querySelectorAll('.js-folder-chevron').forEach(c => {
            if (c !== chevron) c.classList.remove('rotate-180');
        });

        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            btn.setAttribute('aria-expanded', 'false');
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.js-mobile-folder-dropdown-container')) {
            document.querySelectorAll('.js-mobile-folder-dropdown-menu').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('.js-folder-chevron').forEach(c => c.classList.remove('rotate-180'));
            document.querySelectorAll('.js-mobile-folder-dropdown-container button').forEach(b => b.setAttribute('aria-expanded', 'false'));
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.js-mobile-folder-dropdown-menu').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('.js-folder-chevron').forEach(c => c.classList.remove('rotate-180'));
            document.querySelectorAll('.js-mobile-folder-dropdown-container button').forEach(b => b.setAttribute('aria-expanded', 'false'));
        }
    });
}
</script>

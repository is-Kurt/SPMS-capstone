<nav id="sidebar-nav" class="flex flex-col gap-1.5">
    <?php if (empty($presets)): ?>
        <p class="px-4 text-xs text-text-muted italic">No teams saved yet.</p>
    <?php else: ?>
        <?php foreach ($presets as $preset): ?>
            <?php $isActive = ($selectedTeamId == $preset['id']); ?>
            
            <div class="relative group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all <?= $isActive ? 'bg-accent text-white shadow-lg shadow-accent/20' : 'text-text-muted hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-text' ?>" data-preset-id="<?= $preset['id'] ?>" data-active="<?= $isActive ? '1' : '0' ?>">

                <a href="<?= site_url('teams?team_id=' . $preset['id']) ?>" class="absolute inset-0 z-10 rounded-xl"></a>

                <div class="flex items-center gap-3 truncate min-w-0 pr-4">
                    <span class="truncate"><?= esc($preset['name']) ?></span>
                </div>

                <div class="flex items-center gap-2 shrink-0 relative z-20 pr-2">
                    <span class="text-[10px] <?= $isActive ? 'text-white/80' : 'text-text-muted/60' ?> font-black">
                        <?= $preset['member_count'] ?>
                    </span>
                </div>
            </div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</nav>
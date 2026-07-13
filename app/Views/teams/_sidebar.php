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

                <div class="flex items-center gap-2 shrink-0 relative z-20">
                    <span class="text-[10px] <?= $isActive ? 'text-white/80' : 'text-text-muted/60' ?> font-black">
                        <?= $preset['member_count'] ?>
                    </span>

                    <?php
                        $confirmMessage = $preset['in_use']
                            ? "This team is still cascaded to at least one evaluation folder. Are you sure you want to delete this team?"
                            : 'Are you sure you want to delete this team?';
                    ?>
                    <?= form_open('teams/delete', [
                        'class' => 'flex items-center',
                        'data-ajax' => 'delete-team',
                        'data-confirm' => $confirmMessage,
                        'data-confirm-title' => 'Delete Team',
                        'data-confirm-text' => 'Delete Team'
                    ]) ?>
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="preset_id" value="<?= $preset['id'] ?>">
                        <!-- Trash icon (icon-only button, see title=): archives this team (soft delete) - safe even while actively cascaded, see data-confirm above -->
                        <button type="submit" class="p-1 rounded-md <?= $isActive ? 'text-white/60 hover:text-white hover:bg-white/20' : 'text-zinc-400 opacity-0 group-hover:opacity-100 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10' ?> transition-all cursor-pointer" title="Delete Team">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    <?= form_close() ?>
                </div>
            </div>
            
        <?php endforeach; ?>
    <?php endif; ?>
</nav>
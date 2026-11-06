<?php
/**
 * <select> of positions in tree order, for choosing a parent position.
 * Expects $positionTree (AccountManagement::flattenPositionTree), $name, $id, optional $selected.
 */
$selected = $selected ?? '';
?>
<select name="<?= esc($name) ?>" id="<?= esc($id) ?>" class="w-full bg-zinc-50 dark:bg-zinc-800/50 border border-transparent dark:border-zinc-800 rounded-xl px-4 py-3 text-sm focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all text-text font-bold cursor-pointer">
    <option value="">None (top level)</option>
    <?php foreach ($positionTree as $p): ?>
        <option value="<?= $p['id'] ?>" <?= (string) $selected === (string) $p['id'] ? 'selected' : '' ?>><?= str_repeat('&nbsp;&nbsp;&nbsp;', $p['level']) . esc($p['title']) . ($p['is_general_root'] ? ' (General group)' : '') ?></option>
    <?php endforeach; ?>
</select>

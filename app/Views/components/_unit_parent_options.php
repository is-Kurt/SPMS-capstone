<?php
/**
 * Parent department / unit choices as a collapsible tree (rows wired by TreeList in customSelect.js).
 * Expects $units (id, name, parent_id), $optionClass (row class the modal script looks for) and $topLabel.
 */
$tree = [];
$ids = array_column($units, 'id');
foreach ($units as $u) $tree[in_array($u['parent_id'], $ids) ? (int) $u['parent_id'] : 0][] = $u;
$rowClass = 'flex items-center gap-1 py-2 text-sm text-text hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg cursor-pointer transition-colors';
?>
<li class="<?= $optionClass ?> px-3 py-2 text-sm rounded-lg cursor-pointer transition-colors bg-accent/10 text-accent-ink font-bold" data-value=""><span class="truncate"><?= esc($topLabel) ?></span></li>
<?php
$render = function ($parent, $level) use (&$render, $tree, $optionClass, $rowClass) {
    foreach ($tree[$parent] ?? [] as $u): ?>
<li class="<?= $optionClass ?> <?= $rowClass ?>" data-value="<?= (int) $u['id'] ?>" data-tl-index="<?= (int) $u['id'] ?>" data-tl-parent="<?= $parent ?: '' ?>" style="padding-left: calc(0.25rem + <?= $level * 1.1 ?>rem); padding-right: 0.75rem;<?= $level ? ' display: none;' : '' ?>"><span class="js-tl-toggle shrink-0 flex items-center justify-center w-6 h-6 rounded-md text-text-muted hover:text-text cursor-pointer" role="button" aria-label="Show or hide the units under <?= esc($u['name'], 'attr') ?>"><svg class="h-4 w-4 transition-transform duration-200" style="transform: rotate(-90deg);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span><span class="truncate"><?= esc($u['name']) ?></span></li>
<?php   $render((int) $u['id'], $level + 1);
    endforeach;
};
$render(0, 0);

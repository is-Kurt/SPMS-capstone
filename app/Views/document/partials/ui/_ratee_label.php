<?php
/**
 * Whose document this is (the ratee), shown to reviewers in the document toolbar so they can confirm it
 * before returning or approving, even if the form's own name field is blank or wrong. The name comes from
 * the owner's account, not the form. _navbar.php renders it in three places, one visible at a time: in the
 * Return/Approve bar from lg up, in a slim row under the bar from md to lg, and in the mobile toolstrip
 * below md (the bar has no room for it below lg).
 *
 * @var string $rateeName
 * @var string $rateePosition  optional, shown in the tooltip
 * @var string $rateeClass     visibility/size classes for this placement
 * @var string $rateeStyle     optional inline style (a max-width the compiled CSS has no utility for)
 */
$rateeStyle = $rateeStyle ?? '';
?>
<span class="<?= esc($rateeClass, 'attr') ?> items-center gap-1.5 min-w-0 px-2.5 py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20"
      <?= $rateeStyle !== '' ? 'style="' . esc($rateeStyle, 'attr') . '"' : '' ?>
      title="<?= esc('Ratee: ' . $rateeName . ($rateePosition !== '' ? ' (' . $rateePosition . ')' : ''), 'attr') ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
    <span class="sr-only">Ratee: </span>
    <span class="hidden sm:inline font-normal" aria-hidden="true">Ratee:</span>
    <span class="truncate min-w-0 font-bold"><?= esc($rateeName) ?></span>
</span>

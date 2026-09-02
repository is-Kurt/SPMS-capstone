<?php
/**
 * Shared CTA button partial for email templates.
 * Expects: $link (url), $label (text), $color (optional hex, defaults to accent green).
 */
$color = $color ?? '#10b981';
?>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:4px;">
    <tr>
        <td style="border-radius:10px; background-color:<?= esc($color, 'attr') ?>;">
            <a href="<?= esc($link, 'attr') ?>" style="display:inline-block; padding:12px 28px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none; font-family: Arial, Helvetica, sans-serif;"><?= esc($label) ?></a>
        </td>
    </tr>
</table>

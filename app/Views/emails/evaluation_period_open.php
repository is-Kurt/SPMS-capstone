<p style="margin:0 0 16px; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#3b82f6; font-family: Arial, Helvetica, sans-serif;">Action Required</p>
<h2 style="margin:0 0 12px; font-size:21px; font-weight:800; color:#0f172a; font-family: Arial, Helvetica, sans-serif;">Evaluation Period Open</h2>
<p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#475569; font-family: Arial, Helvetica, sans-serif;">Hello <?= esc($firstName) ?>, the official evaluation window for <strong style="color:#0f172a;"><?= esc($title) ?></strong> has now opened. You may access your folder to conduct and lock in your self-evaluation.</p>
<?= view('emails/_button', ['link' => $link, 'label' => 'Open Your Folder', 'color' => '#3b82f6']) ?>

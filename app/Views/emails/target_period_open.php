<p style="margin:0 0 16px; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#10b981; font-family: Arial, Helvetica, sans-serif;">New Assignment</p>
<h2 style="margin:0 0 12px; font-size:21px; font-weight:800; color:#0f172a; font-family: Arial, Helvetica, sans-serif;">Target Setting Period Open</h2>
<p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#475569; font-family: Arial, Helvetica, sans-serif;">Hello <?= esc($firstName) ?>, the target setting period for your performance evaluation folder has officially started. You may now begin drafting your entries and submitting your targets.</p>
<?= view('emails/_button', ['link' => $link, 'label' => 'Open Your Folder']) ?>

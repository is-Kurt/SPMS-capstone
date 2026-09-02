<?php
// Inspect image dimensions and details
$imgPath = 'C:\Users\Deus\.gemini\antigravity-ide\brain\26826ccf-ae9c-4452-9337-4d2d3aa7237b\.user_uploaded\media_1788355747168.png';
$info = getimagesize($imgPath);
echo "Image dimensions: " . $info[0] . "x" . $info[1] . "\n";

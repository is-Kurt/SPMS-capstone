<?php
$imgPath = 'C:\Users\Deus\.gemini\antigravity-ide\brain\26826ccf-ae9c-4452-9337-4d2d3aa7237b\.user_uploaded\media_1788355747168.png';
$im = imagecreatefrompng($imgPath);
$crop = imagecrop($im, ['x' => 900, 'y' => 90, 'width' => 124, 'height' => 90]);
imagepng($crop, 'scratch/crop_arrow.png');
echo "Cropped successfully.\n";

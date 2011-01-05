<?php
$breite = round($_GET['km']/4);
if ($breite < 0) $breite = 0;
elseif ($breite > 330) $breite = 330;
header ("Content-type: image/png");
$bild = ImageCreateFromPng('../../img/schuhbalken.png');
$transp = ImageColorAllocate ($bild,255,255,255);
ImageFilledRectangle($bild,$breite,0,330,2,$transp);
ImageColorTransparent($bild, $transp);
ImagePng($bild);
ImageDestroy($bild);
?>
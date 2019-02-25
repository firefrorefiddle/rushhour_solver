<?php
header('Content-Type: image/gif');
/* RGB of your inside color */
$rgb = array(0,0,255);
/* Your file */
$file=$_GET["size"] == 2 ? "car2.gif" : "car3.gif";
       
$im = imagecreatefromgif($file);

$blue = imagecolorclosest($im, 0, 0, 255);
imagecolorset($im, $blue, $_GET["R"], $_GET["G"], $_GET["B"]);
       
imagegif($im);
imagedestroy($im);
?>

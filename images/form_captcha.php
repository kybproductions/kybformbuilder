<?php
	putenv('GDFONTPATH=' . realpath('.'));
	$basedir = $_SERVER['DOCUMENT_ROOT'] . "/mywiz";
    $captchanumber = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
	$captchanumber = substr(str_shuffle($captchanumber), 0, 6); // Getting first 6 word after shuffle.
	$_SESSION["code"] = $captchanumber; // Initializing session variable with above generated sub-string
	$captchaImg = $basedir . "/wp-content/plugins/kybformbuilder/images/captcha_bg.png";
	$im = imagecreatefrompng($captchaImg);
    
    $white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	$font = "arial";
	$font_size = 20;
	$angle = 45;
    imagettftext($im, $font_size, $angle, 48, 25, $black, $font, $captchanumber);
	header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);
?>
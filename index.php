<?php
preg_match('/\/.*\/(.+)\?/', $_SERVER['REQUEST_URI'], $matches);
$filename = $matches[1];

$width = $_GET['w'];
if (!$width || $width > 1000) { $width = 1000; }

$path =  "/tmp/imgrepo_{$filename}_{$width}";

if (!file_exists($path))
{
	$image = new Imagick($filename);
	$image->resizeImage($width, $image->getImageHeight(), false, 1, true);

	// add watermark if exists
	if (file_exists('watermark.png'))
	{
		$watermark = new Imagick('watermark.png');		
		
		$watermark->resizeImage($image->getImageWidth(), $image->getImageHeight(), false, 1, true);
		$left = ($image->getImageWidth() - $watermark->getImageWidth()) / 2;
		$top = ($image->getImageHeight() - $watermark->getImageHeight()) / 2;
		$image->compositeImage($watermark, $watermark->getImageCompose(), $left, $top);
		
		/*$watermark->resizeImage(100, 100, false, 1, true);
		$image = $image->textureImage($watermark);*/
	}
	
	$image->writeImage($path);
	$image->destroy(); 
}

header("Content-type: image/jpeg");
readfile($path);
exit();

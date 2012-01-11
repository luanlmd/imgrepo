<?php
preg_match('/\/.*\/(.+)\?/', $_SERVER['REQUEST_URI'], $matches);
$filename = $matches[1];

$width = isset($_GET['w'])? $_GET['w'] : 1000;
if ($width > 1000) { $width = 1000; }

$qs = $_SERVER['QUERY_STRING'];
$path =  "/tmp/imgrepo_{$filename}?{$qs}";

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
		$watermark->destroy();
		
		/*$watermark->resizeImage(100, 100, false, 1, true);
		$image = $image->textureImage($watermark);*/
	}
	
	$image->writeImage($path);
}
else
{
	$image = new Imagick($path);
}


header("Content-type: image/{$image->getImageFormat()}");
echo $image->getImageBlob();
$image->destroy();
exit();

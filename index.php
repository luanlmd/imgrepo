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
	$image->destroy();
}

$size = getimagesize($path);
header("Content-type: {$size['mime']}");
header('Cache-Control: private, pre-check=0, post-check=0, max-age=1080');
header('Expires: ' . gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time() + 1080)); //60*3
header('Last-Modified: ' . gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time() - 20)); //60*60*3

readfile($path);
exit();

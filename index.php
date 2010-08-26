<?php

$file = $_SERVER['REQUEST_URI'];
$file = explode('/', $file);
$file = $file[count($file)-1];

list($file, $ext) = explode('.',$file);

$exploded = explode('_',$file);
$count = count($exploded);

$width = 1000;
$q = false;
if ($count == 1)
{
	$id = $exploded[0];
}

if ($count == 2)
{
	list($id, $width) = $exploded;
}
else if ($count == 3)
{
	list($id, $width, $q) = $exploded;
}

if ($width > 1000) $width = 1000;

$path = "{$id}";

if (!$width) { $newPath = $path; }
else { $newPath =  "/tmp/imgrepo_{$id}_{$width}" . (($q)? "_q" : ""); }

if (!file_exists($newPath))
{
	list($w, $h) = getimagesize($path);
	$original = imagecreatefromjpeg($path);

	if ($q)
	{
		$s = $w;
		if ($w > $h)
			$s = $h;

		$offW = ($w - $s) / 2;
		$offH = ($h - $s) / 2;

		$new = imagecreatetruecolor($s, $s);
		imagecopyresampled($new, $original, -$offW, -$offH, 0, 0, $w, $h, $w, $h);
		imagejpeg($new, $newPath, 72);

		list($w, $h) = getimagesize($newPath);
		$original = imagecreatefromjpeg($newPath);
	}

	$a = $h * $width / $w;
	$new = imagecreatetruecolor($width, $a);

	// resize
	imagecopyresampled($new, $original, 0, 0, 0, 0, $width, $a, $w, $h);

	// add watermark if exists
	if (file_exists('watermark.png'))
	{
		$mark = imagecreatefrompng('watermark.png');
		imagecopymerge($new, $mark , 0, 0, 0, 0, imagesx($mark), imagesy($mark), 20);
		imagesavealpha($new, true);
	}

	imagejpeg($new, $newPath, 75);
	imagedestroy($new);
}

header("Content-type: image/jpeg");
readfile($newPath);
exit();

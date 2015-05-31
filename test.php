<?php

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'imagewrapper' . DIRECTORY_SEPARATOR . 'image.php');

use \ImageWrapper\Image as Image;

define('FILES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'files');

Image::useLib(Image::GD);
$file = FILES_DIR . '/test.jpg';
$thumbs = [
	[300, 100, true, FILES_DIR . '/res1.jpg'],
	[100, 300, true, FILES_DIR . '/res2.jpg'],
];
$gravity = Image::SouthWest;

echo '<img src="'.str_replace(FILES_DIR, 'files', $file).'" style="float: left; margin-right: 20px;">';

foreach ($thumbs as $thumb)
{
	$img = Image::create($file);
	$img->setCompressionQuality(50);
	$img->setGravity($gravity);
	$img->resize($thumb[0], $thumb[1], $thumb[2]);
	$img->save($thumb[3]);

	echo '<img src="'.str_replace(FILES_DIR, 'files', $thumb[3]).'" style="float: left; margin-right: 20px;">';
}


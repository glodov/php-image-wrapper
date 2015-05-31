<?php

namespace ImageWrapper;

class Imagick extends \ImageWrapper\Image
{

	protected $img;

	/**
	 * @see \ImageWrapper\Image::__construct()
	 */
	public function __construct($file = null)
	{
		$this->img = new \Imagick();
		if ($file)
		{
			$this->load($file);
		}
	}

	/**
	 * @see \ImageWrapper\Image::load()
	 */
	public function load($file)
	{
		$fp = fopen($file, 'r');
		$this->img->readImageFile($fp);
		fclose($fp);
		$this->img->setImageFilename($file);
	}

	/**
	 * @see \ImageWrapper\Image::save()
	 */
	public function save($file = null)
	{
		$this->img->setCompressionQuality($this->getCompressionQuality());
		$this->img->writeImage($file);
	}

	/**
	 * @see \ImageWrapper\Image::resize()
	 */
	public function resize($width, $height, $adaptive = false)
	{
		if (!$width || !$height)
		{
			return false;
		}
		$filter = \Imagick::FILTER_CATROM;
		$blur = 1;
		if ($adaptive)
		{
			if (self::Fit === $this->getGravity())
			{
				$this->img->resizeImage($width, $height, $filter, $blur, true);
			}
			else
			{
				$geo = $this->getAdaptiveGeometry($width, $height);
				$this->img->resizeImage($geo['width'], $geo['height'], $filter, $blur);
				$this->img->cropImage($width, $height, $geo['left'], $geo['top']);
			}
		}
		else
		{
			$this->img->resizeImage($width, $height, $filter, $blur);
		}
		return true;
	}

	/**
	 * @see \ImageWrapper\Image::getGeometry()
	 */
	public function getGeometry()
	{
		return $this->img->getImageGeometry();
	}

	/**
	 * @see \ImageWrapper\Image::getWidth()
	 */
	public function getWidth()
	{
		return $this->img->getImageWidth();
	}

	/**
	 * @see \ImageWrapper\Image::getHeight()
	 */
	public function getHeight()
	{
		return $this->img->getImageHeight();
	}

}
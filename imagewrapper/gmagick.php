<?php

namespace ImageWrapper;

class Gmagick extends \ImageWrapper\Image
{

	protected $img;

	/**
	 * @see \ImageWrapper\Image::__construct()
	 */
	public function __construct($file = null)
	{
		$this->img = new \Gmagick();
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
		$this->img->ReadImage($file);
	}

	/**
	 * @see \ImageWrapper\Image::save()
	 */
	public function save($file = null)
	{
		if (preg_match('/\.(\w{2,4})$/', $file, $res))
		{
			$this->img->SetImageFormat(strtolower($res[1]));
		}
		$this->img->WriteImage($file);
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
		$filter = \Gmagick::FILTER_CATROM;
		$blur = 1;
		if ($adaptive)
		{
			if (self::Fit === $this->getGravity())
			{
				$this->img->ResizeImage($width, $height, $filter, $blur, true);
			}
			else
			{
				$geo = $this->getAdaptiveGeometry($width, $height);
				$this->img->ResizeImage($geo['width'], $geo['height'], $filter, $blur);
				$this->img->CropImage($width, $height, $geo['left'], $geo['top']);

				// @todo GIF images empty space not removed
				// $this->img->setImagePage($width, $height, 0, 0);
			}
		}
		else
		{
			$this->img->ResizeImage($width, $height, $filter, $blur);
		}
		return true;
	}

	/**
	 * @see \ImageWrapper\Image::getGeometry()
	 */
	public function getGeometry()
	{
		return [
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
		];
	}

	/**
	 * @see \ImageWrapper\Image::getWidth()
	 */
	public function getWidth()
	{
		return $this->img->GetImageWidth();
	}

	/**
	 * @see \ImageWrapper\Image::getHeight()
	 */
	public function getHeight()
	{
		return $this->img->GetImageHeight();
	}

}
<?php

namespace ImageWrapper;

class GD extends \ImageWrapper\Image
{

	protected $img, $file, $type;

	private $width, $height;

	/**
	 * @see \ImageWrapper\Image::__construct()
	 */
	public function __construct($file = null)
	{
		$this->img = imagecreatetruecolor(100, 100);
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
		$this->file = $file;
		list($this->width, $this->height, $this->type, $attr) = getimagesize($this->file);
		switch ($this->type)
		{
			case IMAGETYPE_GIF:
				return $this->img = imagecreatefromgif($file);

			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				return $this->img = imagecreatefromjpeg($file);

			case IMAGETYPE_PNG:
				return $this->img = imagecreatefrompng($file);

			case IMAGETYPE_WBMP:
				return $this->img = imagecreatefromwbmp($file);

			case IMAGETYPE_XBM:
				return $this->img = imagecreatefromxbm($file);
		}
		$this->file = null;
		return false;
	}

	/**
	 * @see \ImageWrapper\Image::save()
	 */
	public function save($file = null)
	{
		if (null === $file)
		{
			$file = $this->file;
		}
		switch ($this->getImageFormat($file))
		{
			case IMAGETYPE_GIF:
				return imagegif($this->img, $file);

			case IMAGETYPE_JPEG:
				return imagejpeg($this->img, $file, $this->getCompressionQuality());

			case IMAGETYPE_PNG:
				return imagepng($this->img, $file);

			case IMAGETYPE_WBMP:
				return imagewbmp($this->img, $file);

			case IMAGETYPE_XBM:
				return imagexbm($this->img, $file);
		}
		return false;		
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
		if ($adaptive)
		{
			$geo = $this->getAdaptiveGeometry($width, $height);
			if (self::Fit === $this->getGravity())
			{
				$img = imagecreatetruecolor($geo['width'], $geo['height']);
				imagecopyresized($img, $this->img, 0, 0, 0, 0, 
					$geo['width'], $geo['height'], $this->width, $this->height);
				$this->img = $img;
				$this->width = $geo['width'];
				$this->height = $geo['height'];
			}
			else
			{
				$img = imagecreatetruecolor($geo['width'], $geo['height']);
				imagecopyresized($img, $this->img, 0, 0, 0, 0, 
					$geo['width'], $geo['height'], $this->width, $this->height);
				$this->img = imagecrop($img, [
					'width' => $width, 
					'height' => $height, 
					'x' => $geo['left'], 
					'y' => $geo['top']
					]);
				$this->width = $width;
				$this->height = $height;
			}
		}
		else
		{
			$img = imagecreatetruecolor($width, $height);
			imagecopyresized($img, $this->img, 0, 0, 0, 0, 
				$width, $height, $this->width, $this->height);
			$this->img = $img;
			$this->width = $width;
			$this->height = $height;
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
		return $this->width;
	}

	/**
	 * @see \ImageWrapper\Image::getHeight()
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Returns image type format based on image file extension.
	 * 
	 * @access private
	 * @param string $file The filename or extension.
	 * @return integer The image type format.
	 */
	private function getImageFormat($file)
	{
		$type = null;
		if (strlen($file) <= 4)
		{
			$type = strtolower($file);
		}
		else if (preg_match('/\.(\w{3,4})$/', $file, $res))
		{
			$type = strtolower($res[1]);
		}
		$types = [
			'gif'	=> IMAGETYPE_GIF,
			'jpg'	=> IMAGETYPE_JPEG,
			'jpeg'	=> IMAGETYPE_JPEG,
			'png'	=> IMAGETYPE_PNG,
			'wbmp'	=> IMAGETYPE_WBMP,
			'xbm'	=> IMAGETYPE_XBM
		];
		return isset($types[$type]) ? $types[$type] : $this->type;
	}

}
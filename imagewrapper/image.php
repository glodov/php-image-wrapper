<?php

namespace ImageWrapper;

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'gd.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'gmagick.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'imagick.php');

abstract class Image
{

	/**
	 * Creates an object instance and reads image file if it is defined.
	 * 
	 * @abstract
	 * @access protected
	 * @param string $file The image file path to read.
	 */
	abstract protected function __construct($file = null);

	/**
	 * Reads image file into object.
	 * 
	 * @abstract
	 * @access public
	 * @param string $file The file path.
	 */
	abstract public function load($file);

	/**
	 * Saves image into $file, if not defined saves into file where it has been loaded.
	 * 
	 * @abstract
	 * @access public
	 * @param string $file The file path.
	 */
	abstract public function save($file = null);

	/**
	 * Resizes image to defined resolution.
	 * 
	 * @abstract
	 * @access public
	 * @param integer $width The width of resized image.
	 * @param integer $height The height of resized image.
	 * @param boolean $adaptive If TRUE uses current gravity to crop or fit image, 
	 * 	if FALSE just resizes image as it is without propotions.
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	abstract public function resize($width, $height, $adaptive = false);

	/**
	 * Returns image geometry [width, height] in associated array.
	 * 
	 * @abstract
	 * @access public
	 * @return array The geometry.
	 */
	abstract public function getGeometry();

	/**
	 * Returns image width.
	 * 
	 * @abstract
	 * @access public
	 * @return integer The width in pixels.
	 */
	abstract public function getWidth();

	/**
	 * Returns image height.
	 * 
	 * @abstract
	 * @access public
	 * @return integer The height in pixels.
	 */
	abstract public function getHeight();

	const NorthWest = 2, 
		North = 1, 
		NorthEast = 11, 
		West = 9, 
		Center = 0, 
		East = 3, 
		SouthWest = 7, 
		South = 6, 
		SouthEast = 5,
		Fit = 20;

	const GD = 1, Imagick = 2, Gmagick = 3, None = 0;

	private static $lib;

	private $gravity = self::Fit, $compressionQuality = 75;

	/**
	 * Returns gravity option.
	 * 
	 * @access public
	 * @return integer The gravity option.
	 */
	public function getGravity()
	{
		return $this->gravity;
	}

	/**
	 * Sets gravity option.
	 * 
	 * @access public
	 * @param integer $gravity The new gravity option.
	 */
	public function setGravity($gravity)
	{
		return $this->gravity = $gravity;
	}

	/**
	 * Returns image compression quality from 0 to 100.
	 * 
	 * @access public
	 * @return integer The quality value.
	 */
	public function getCompressionQuality()
	{
		return $this->compressionQuality;
	}

	/**
	 * Sets image compression quality from 0 to 100.
	 * 
	 * @access public
	 * @param integer $quality The quality value.
	 */
	public function setCompressionQuality($quality)
	{
		$this->compressionQuality = $quality;
	}

	/**
	 * Returns width, heigt and position of new image which 
	 * 	must be resized into defined $width:$height.
	 * 
	 * @access protected
	 * @param integer $width The width of new image.
	 * @param integer $height The height of new image.
	 * @return array The associatred array [width, height, left, top].
	 */
	protected function getAdaptiveGeometry($width, $height)
	{
		$result = [
			'width' => $width,
			'height' => $height,
			'left' => 0,
			'top' => 0,
		];
		if (!$width || !$height)
		{
			throw new Exception("Incorrect new resoltion [{$width}x{$height}]");
		}
		$w = $this->getWidth();
		$h = $this->getHeight();
		$scale = $w / $width < $h / $height ? $w / $width : $h / $height;
		if (self::Fit === $this->gravity)
		{
			$scale = $w / $width < $h / $height ? $h / $height : $w / $width;
		}
		if (0 == $scale)
		{
			return $result;
		}
		$w = intval($w / $scale);
		$h = intval($h / $scale);
		$left = 0;
		$top = 0;

		switch ($this->gravity)
		{
			case self::Fit:
				break;

			case self::South:
				$top = $h - $height;
			case self::North:
				$left = intval(($w - $width) / 2);
				break;

			case self::East:
				$left = $w - $width;
			case self::West:
				$top = intval(($h - $height) / 2);
				break;

			case self::Center:
				$left = intval(($w - $width) / 2);
				$top = intval(($h - $height) / 2);
				break;

			case self::NorthEast:
				$left = $w - $width;
				break;

			case self::SouthEast:
				$left = $w - $width;
			case self::SouthWest:
				$top = $h - $height;
				break;
		}
		$result = [
			'width' => $w,
			'height' => $h,
			'left' => $left,
			'top' => $top
		];
		return $result;
	}

	/**
	 * Sets default library without detecting.
	 * 
	 * @static
	 * @access public
	 * @param integer $lib The lib constant.
	 */
	public static function useLib($lib = null)
	{
		self::$lib = $lib;
	}

	/**
	 * Detects image library to use.
	 * 
	 * @static
	 * @access protected
	 */
	protected static function detectLib()
	{
		if (null !== self::$lib)
		{
			return false;
		}
		if (class_exists("\\Imagick"))
		{
			self::$lib = self::Imagick;
		}
		else if (class_exists("\\Gmagick"))
		{
			self::$lib = self::Gmagick;
		}
		else if (function_exists('gd_info'))
		{
			self::$lib = self::GD;
		}
		else
		{
			self::$lib = self::None;
		}
		return true;
	}

	/**
	 * Creates an instance of image object depending on used library.
	 * 
	 * @static
	 * @access public
	 * @param string $file The image 
	 */
	public static function create($file = null)
	{
		self::detectLib();
		switch (self::$lib)
		{
			case self::Imagick:
				return new \ImageWrapper\Imagick($file);

			case self::Gmagick:
				return new \ImageWrapper\Gmagick($file);

			case self::GD:
				return new \ImageWrapper\GD($file);
		}

		throw new Exception('Cannot find any supported Image processing library on server');
	}

}
<?php

class rpImageThumb {
	var $image;
	var $type;
	var $width;
	var $height;
	var $name;
	var $transparent_color;
	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name){
		$this->loadImage($name);
	}
	/**
	 * Load an image
	 *
	 * @param string $name
	 */
	function loadImage($name, $minsize = 128) {
		$this->name = $name;
		if (file_exists($name) && filesize($name) >= $minsize) {
			$info = getimagesize($name);
			$this->width = $info[0];
			$this->height = $info[1];
			$this->type = rpImageToolkit::getImageType($name);
			switch ($this->type) {
				case "jpeg":
					$this->image = @imagecreatefromjpeg($name);
					break;
				case "gif":
					$this->image = @imagecreatefromgif($name);
					if (is_resource($this->image)) $this->transparent_color = imagecolortransparent($this->image);
					break;
				case "png":
					$this->image = @imagecreatefrompng($name);
					if (is_resource($this->image)) $this->transparent_color = imagecolortransparent($this->image);
					break;
			}
		}
	}
	/**
	 * Save the image
	 *
	 * @param string $name
	 * @param float $quality
	 */
	function save($name, $quality = 100) {
		switch ($this->type) {
			case "jpeg":
				@imagejpeg($this->image, $name, $quality);
				break;
			case "gif":
				imagecolortransparent($this->image, $this->transparent_color);
				@imagegif($this->image, $name);
				break;
			case "png":
				$pngquality = floor(($quality - 10) / 10);
				imagecolortransparent($this->image, $this->transparent_color);
				imagepng($this->image, $name, $pngquality);
				break;
		}
	}
	/**
	 * Show the image (output)
	 *
	 */
	function show($quality = 100) {
		switch ($this->type) {
			case "jpeg":
				if ($quality == 100)
				imagejpeg($this->image);
				else
				imagejpeg($this->image, null, $quality);
				break;
			case "gif":
				imagegif($this->image, null);
				break;
			case "png":
				$pngquality = floor(($quality - 10) / 10);
				if ($quality == 100)
				imagepng($this->image);
				else
				imagepng($this->image, null, $pngquality);
				break;
		}
	}
	/**
	 * Resize the image
	 *
	 * @param integer $value
	 * @param string $prop
	 */
	function resize($value, $prop = 'width') {
		$prop_value = ($prop == 'width') ? $this->width : $this->height;
		$prop_versus = ($prop == 'width') ? $this->height : $this->width;
		$pcent = $value / $prop_value;
		$value_versus = $prop_versus * $pcent;
		$image = ($prop == 'width') ? imagecreatetruecolor($value, $value_versus) : imagecreatetruecolor($value_versus, $value);
		switch ($prop) {
			case 'width':
				if (is_resource($this->image)) imagecopyresampled($image, $this->image, 0, 0, 0, 0, $value, $value_versus, $this->width, $this->height);
				break;
			case 'height':
				if (is_resource($this->image)) imagecopyresampled($image, $this->image, 0, 0, 0, 0, $value_versus, $value, $this->width, $this->height);
				break;
		}
		$info = getimagesize($this->name);
		$this->width = imagesx($image);
		$this->height = imagesy($image);
		$this->image = $image;
	}
	/**
	 * Crop the image
	 *
	 * @param integer $cwidth
	 * @param integer $cheight
	 * @param string $pos
	 */
	function crop($cwidth = -1, $cheight = -1, $pos = 'center') {
		if ($cwidth < 1) $cwidth = $this->width;
		if ($cheight < 1) $cheight = $this->height;
			
		if ($cwidth > $cheight) $this->resize($cwidth, 'width');
		else $this->resize($cheight, 'height');
		$image = imagecreatetruecolor($cwidth, $cheight);
		switch ($pos) {
			case 'center':
				imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
				break;
			case 'left':
				imagecopyresampled($image, $this->image, 0, 0, 0, abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
				break;
			case 'right':
				imagecopyresampled($image, $this->image, 0, 0, $this->width - $cwidth, abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight);
				break;
			case 'top':
				imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), 0, $cwidth, $cheight, $cwidth, $cheight);
				break;
			case 'bottom':
				imagecopyresampled($image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), $this->height - $cheight, $cwidth, $cheight, $cwidth, $cheight);
				break;
		}
		$this->image = $image;
	}
}
// End of file

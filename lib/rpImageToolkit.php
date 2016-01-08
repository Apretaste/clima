<?php

class rpImageTookit{

	/**
	 * Get resized image
	 *
	 * @param string $path
	 * @param integer $w
	 * @param integer $h
	 * @return rpImageThumb
	 */
	static function getResizedImage($path, $w, $h){

		$t = self::getImage($path);
		if (!is_null($t)){

			if ($h != -1 && $w != -1) {
				if ($t->height > $t->width) $t->resize($h, 'height');
				else $t->resize($w, 'width');
			} else {
				if ($h != -1) $t->resize($h, 'height');
				if ($w != -1) $t->resize($w, 'width');
			}

			return $t;
		}

		return null;
	}

	/**
	 * Return a crop of image
	 *
	 * @param string $path
	 * @param integer $w
	 * @param integer $h
	 * @param string $pos
	 * @return rpImageThumb
	 */
	static function getCropImage($path, $w, $h, $pos = 'center'){

		$t = self::getImage($path);

		if (!is_null($t)){

			if ($h != -1 && $w != -1) $t->crop($w, $h, $pos);

			return $t;
		}

		return null;
	}

	/**
	 * Get the type of image
	 *
	 * @param string $path
	 * @return string
	 */
	static function getImageType($path){
		if (file_exists($path)){
			$info = getimagesize($path);
			$mime = strtolower($info['mime']);
			$mime = str_replace("image/","", $mime);
			return $mime;
		}
		return null;
	}

	/**
	 * Return iamge
	 *
	 * @param string $path
	 * @param float $quality
	 */
	static function getImage($path){
		$ext = strtolower(rpFileSystem::getFileExtension($path));
		if ($ext == "png" || $ext == "gif" || $ext == "jpg"|| $ext == "jpeg") {
			$t = new rpImageThumb($path);
			return $t;
		}
		return null;
	}

	/**
	 * Get a resized image as base64 encoded
	 *
	 * @param string $path
	 * @param integer $w
	 * @param integer $h
	 * @return string
	 */
	static function getResizedBase64Image($path, $w = -1, $h = -1){
		if (file_exists($path)){
			$t = self::getResizedImage($path, $w, $h);
			echo $path;
			ob_start();
			$t->show();
			$data = ob_get_contents();
			ob_end_clean();
			return base64_encode($data);
		}
		return "";
	}
}
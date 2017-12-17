<?php
/**
 * @deprecated
 */
class Filesystem {
	/**
	 * Write a file
	 * @param string $fileName relative to FRONTEND_PATH
	 * @param string $fileContent
	 */
	public static function writeFile($fileName, $fileContent) {
		$file = fopen(FRONTEND_PATH.$fileName, "w");

		if ($file !== false) {
			fwrite($file, $fileContent);
			fclose($file);
		} else
			\Runalyze\Error::getInstance()->addError('Die Datei "'.$fileName.'" konnte zum Schreiben nicht erstellt/ge&ouml;ffnet werden.');
	}

	/**
	 * Get maximum filesize
	 * @return string
	 */
	public static function getMaximumFilesize() {
		$UploadSize = ini_get('upload_max_filesize');
		$PostSize = ini_get('post_max_size');

		return min(self::stringToBytes($UploadSize), self::stringToBytes($PostSize));
	}

	/**
	 * String to bytes
	 * @param string $string e.g. "8M"
	 * @return int
	 */
	public static function stringToBytes($string) {
		$value = trim($string);

		if ($string == '-1')
			return PHP_INT_MAX;

		if (strlen($value) == 0)
			return 0;

		$unit  = strtolower($value[strlen($value)-1]);

		switch ($unit) {
			case 'g': $value *= 1024;
			case 'm': $value *= 1024;
			case 'k': $value *= 1024;
		}

		return $value;
	}
}

<?php
/**
 * This file contains class::Filesystem
 * @package Runalyze\System
 */
/**
 * Class for handling files and co
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class Filesystem {
	/**
	 * Get extension of filename
	 * @param string $PathToFile
	 * @return string
	 */
	public static function extensionOfFile($PathToFile) {
		if (strlen(trim($PathToFile)) == 0)
			return '';

		$PathInfo = pathinfo($PathToFile);

		if (isset($PathInfo['extension']))
			return $PathInfo['extension'];

		return '';
	}

	/**
	 * Get all file names from a path
	 * @param string $Path
	 * @return array
	 */
	public static function getFileNamesFromPath($Path) {
		$Files = array();
		$handle = opendir(FRONTEND_PATH.$Path);

		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					$Files[] = $file;
				}
			}

			closedir($handle);
		}

		return $Files;
	}

	/**
	 * Get content from extern url
	 * @param string $url
	 * @return string
	 */
	public static function getExternUrlContent($url) {
		if (self::isCurlInstalled())
			return self::getFileContentsWithCurl($url);

		if (self::canOpenExternUrl())
			return file_get_contents($url);

		throw new RuntimeException('The server does not allow opening external pages. (allow_url_fopen=0)');
	}

	/**
	 * Get contents with CUrl
	 * @param string $url 
	 * @return string
	 */
	private static function getFileContentsWithCurl($url) {
		$url = str_replace (' ', '%20', $url);
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_REFERER, 'http://user.runalyze.de/');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

		$response = curl_exec($curl);

		if (!$response) {
			if (curl_errno($curl) == 0)
				\Runalyze\Error::getInstance()->addError('CUrl response was empty (url: '.$url.')');
			else
				\Runalyze\Error::getInstance()->addError('CUrl-Error: '.curl_errno($curl).' '.curl_error($curl).' (url: '.$url.')');
		}

		curl_close($curl);

		return $response;
	}

	/**
	 * Is CUrl enabled?
	 * @return boolean 
	 */
	private static function isCurlInstalled() {
		if (in_array('curl', get_loaded_extensions()))
			return true;

		return false;
	}

	/**
	 * Are fopen-wrapper allowed for using file_get_contents on extern sources
	 * @return bool
	 */
	private static function canOpenExternUrl() {
		return ini_get('allow_url_fopen');
	}

	/**
	 * Throw an error for a bad XML
	 * @param string $XML 
	 */
	public static function throwErrorForBadXml($XML) {
		if (empty($XML) || defined('RUNALYZE_TEST'))
			return;

		$FileName = 'data/log/corrupt.xml.'.time().'.xml';
		self::writeFile('../'.$FileName, $XML);

		\Runalyze\Error::getInstance()->addError('Die XML-Datei scheint fehlerhaft zu sein und konnte nicht erfolgreich geladen werden.
			Zur Analyse kannst du die Datei <a href="'.$FileName.'">'.$FileName.'</a> an einen Administrator schicken.');
	}

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
	 * Get file content and delete it afterwards
	 * @param string $fileName relative to FRONTEND_PATH
	 * @return string
	 */
	public static function openFileAndDelete($fileName) {
		$content = self::openFile($fileName);
		unlink(FRONTEND_PATH.$fileName);

		return $content;
	}

	/**
	 * Get file content
	 * @param string $fileName relative to FRONTEND_PATH
	 * @return string
	 */
	public static function openFile($fileName) {
		return file_get_contents(FRONTEND_PATH.$fileName);
	}

	/**
	 * Delete file
	 * @param string $fileName relative to FRONTEND_PATH
	 */
	public static function deleteFile($fileName) {
		unlink(FRONTEND_PATH.$fileName);
	}

	/**
	 * Get filesize
	 * @param string $file
	 * @return string 
	 */
	public static function getFilesize($file) {
		$size = ($file && @is_file($file)) ? filesize($file) : null;

		return self::bytesToString($size);
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
	 * Get maximum filesize
	 * @return string
	 */
	public static function getMaximumFilesizeAsString() {
		$Max = self::getMaximumFilesize();

		if ($Max == PHP_INT_MAX)
			return 'unlimitiert';

		return self::bytesToString($Max);
	}

	/**
	 * Bytes to string
	 * @param int $bytes
	 * @return string
	 */
	public static function bytesToString($bytes) {
		if ($bytes == 0)
			return '0 B';

		$FS = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");

		return number_format($bytes / pow(1024, $I=floor(log($bytes, 1024))), ($I >= 1) ? 2 : 0, '.', '').' '.$FS[$I];
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

	/**
	 * Check write permissions
	 * 
	 * Will show an error if folder is not writable
	 * 
	 * @param string $folder path relative to runalyze/
	 */
	public static function checkWritePermissions($folder) {
		$realfolder = FRONTEND_PATH.'../'.$folder;

		if (!is_writable($realfolder))
			echo HTML::error( sprintf( __('The directory <strong>%s</strong> is not writable. <em>(chmod = %s)</em>'), $folder, substr(decoct(fileperms($realfolder)),1)) );
	}
}

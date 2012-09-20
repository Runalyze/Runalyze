<?php
/**
 * Class for handling files and co
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class Filesystem {
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

		Error::getInstance()->addError('Der Server erlaubt keine externen Seitenzugriffe. (allow_url_fopen=0)');

		return '';
	}

	/**
	 * Get contents with CUrl
	 * @param string $url 
	 * @return string
	 */
	private static function getFileContentsWithCurl($url) {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_REFERER, 'http://user.runalyze.de/');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

		$response = curl_exec($curl);

		if (!$response)
			Error::getInstance()->addError('CUrl-Error: '.curl_error($curl));

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
	 * Write a file
	 * @param string $fileName relative to FRONTEND_PATH
	 * @param string $fileContent 
	 */
	static public function writeFile($fileName, $fileContent) {
		$file = fopen(FRONTEND_PATH.$fileName, "w");

		if ($file !== false) {
			fwrite($file, $fileContent);
			fclose($file);
		} else
			Error::getInstance()->addError('Die Datei "'.$fileName.'" konnte zum Schreiben nicht erstellt/ge&ouml;ffnet werden.');
	}

	/**
	 * Get file content and delete it afterwards
	 * @param string $fileName relative to FRONTEND_PATH
	 * @return string
	 */
	static public function openFileAndDelete($fileName) {
		$content = self::openFile($fileName);
		unlink(FRONTEND_PATH.$fileName);

		return $content;
	}

	/**
	 * Get file content
	 * @param string $fileName relative to FRONTEND_PATH
	 * @return string
	 */
	static public function openFile($fileName) {
		return file_get_contents(FRONTEND_PATH.$fileName);
	}
}
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
		if (self::canOpenExternUrl())
			return file_get_contents($url);

		Error::getInstance()->addError('Der Server erlaubt keine externen Seitenzugriffe. (allow_url_fopen=0)');
		// TODO: use curl()
		return '';
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
?>

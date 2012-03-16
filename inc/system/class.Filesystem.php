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
}
?>

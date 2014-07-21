<?php
/**
 * This file contains class::ImporterFiletypeKML
 * @package Runalyze\Import\Filetype
 */
ImporterWindowTabUpload::addInfo( __('kml-files from TomTom are supported.') );
/**
 * Importer: *.xml
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeKML extends ImporterFiletypeAbstract {
	/**
	 * Allowed producer of XML files
	 * @var string
	 */
	static private $ALLOWED_PRODUCER = 'TomTom';

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		if ($this->isFromTomTom($String))
			$this->Parser = new ParserKMLtomtomMultiple($String);
		else
			$this->throwErrorForUnknownFormat();
	}

	/**
	 * Is this string from Polar?
	 * @param string $String
	 * @return bool
	 */
	private function isFromTomTom(&$String) {
		return strpos($String, '<gx:Track') !== false;
	}

	/**
	 * Throw error for unknown format
	 */
	private function throwErrorForUnknownFormat() {
		$this->Errors[] = __('This file is not supported. Supported producers of kml-files: '.self::$ALLOWED_PRODUCER.'.');
	}
}
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
	static private $ALLOWED_PRODUCER = 'Google, TomTom';

	/**
	 * Set parser
	 * @param string $string string to parse
	 */
	protected function setParserFor($string) {
		if ($this->isFromTomTom($string)) {
			$this->Parser = new ParserKMLtomtomMultiple($string);
		} elseif ($this->isDefaultKML($string)) {
			$this->Parser = new ParserKMLSingle($string);
		} else {
			$this->throwErrorForUnknownFormat();
		}
	}

	/**
	 * Is this file from TomTom?
	 * @param string $string
	 * @return bool
	 */
	private function isFromTomTom($string) {
		return strpos($string, '<gx:Track') !== false;
	}

	/**
	 * Is this a standard kml file?
	 * @param type $string
	 * @return type
	 */
	private function isDefaultKML($string) {
		return strpos($string, '<coordinates') !== false;
	}

	/**
	 * Throw error for unknown format
	 */
	private function throwErrorForUnknownFormat() {
		$this->Errors[] = __('This file is not supported. Supported producers of kml-files: '.self::$ALLOWED_PRODUCER.'.');
	}
}
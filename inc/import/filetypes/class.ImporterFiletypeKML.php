<?php
/**
 * This file contains class::ImporterFiletypeKML
 * @package Runalyze\Import\Filetype
 */
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
	const ALLOWED_PRODUCER = 'Google, TomTom';

	/**
	 * Set parser
	 * @param string $string string to parse
	 */
	protected function setParserFor($string) {
		if ($this->isFromTomTom($string)) {
			$this->Parser = new ParserKMLtomtomMultiple($string);
		} elseif ($this->isDefaultKML($string)) {
			$this->Parser = new ParserKMLSingle($string);
		} elseif ($this->isNamespacedKml($string)) {
			$this->Parser = new ParserKMLSingle($string);
			$this->Parser->setNamespace('kml');
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
		return (strpos($string, '<coordinates') !== false);
	}

	/**
	 * Is this a namespaced kml file?
	 * @param type $string
	 * @return type
	 */
	private function isNamespacedKml($string) {
		return (strpos($string, '<kml:coordinates') !== false);
	}

	/**
	 * Throw error for unknown format
	 */
	private function throwErrorForUnknownFormat() {
		$this->Errors[] = sprintf(
			__('This file is not supported. Supported producers of %s-files: %s.'),
			'kml', self::ALLOWED_PRODUCER
		);
	}
}

ImporterWindowTabUpload::addInfo( sprintf(__('%s-files are supported from: %s'), 'kml', ImporterFiletypeKML::ALLOWED_PRODUCER) );
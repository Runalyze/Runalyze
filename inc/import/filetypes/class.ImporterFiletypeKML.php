<?php
/**
 * This file contains class::ImporterFiletypeKML
 * @package Runalyze\Import\Filetype
 */
ImporterWindowTabUpload::addInfo('KML-Dateien werden von TomTom unterst&uuml;tzt.');
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
		$this->Errors[] = 'Das XML-Format wird nicht unterst&uuml;tzt. Es k&ouml;nnen nur KML-Dateien von '.self::$ALLOWED_PRODUCER.' importiert werden.';
	}
}
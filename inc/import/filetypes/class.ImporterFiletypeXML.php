<?php
/**
 * This file contains class::ImporterFiletypeXML
 * @package Runalyze\Importer\Filetype
 */
ImporterWindowTabUpload::addInfo('XML-Dateien werden von Polar und RunningAHEAD unterst&uuml;tzt.');
/**
 * Importer: *.xml
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeXML extends ImporterFiletypeAbstract {
	/**
	 * Allowed producer of XML files
	 * @var string
	 */
	static private $ALLOWED_PRODUCER = 'Polar, RunningAHEAD';

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		if ($this->isFromPolar($String))
			$this->Parser = new ParserXMLpolarMultiple($String);
		elseif ($this->isFromRunningAHEAD($String))
			$this->Parser = new ParserXMLrunningAHEADMultiple($String);
		else
			$this->throwErrorForUnknownFormat();
	}

	/**
	 * Is this string from Polar?
	 * @param string $String
	 * @return bool
	 */
	private function isFromPolar(&$String) {
		return strpos($String, '<polar-exercise-data') !== false;
	}

	/**
	 * Is this string from RunningAHEAD?
	 * @param string $String
	 * @return bool
	 */
	private function isFromRunningAHEAD(&$String) {
		return strpos($String, '<RunningAHEADLog') !== false;
	}

	private function throwErrorForUnknownFormat() {
		$this->Errors[] = 'Das XML-Format wird nicht unterst&uuml;tzt. Es k&ouml;nnen nur XML-Dateien von '.self::$ALLOWED_PRODUCER.' importiert werden.';
	}
}
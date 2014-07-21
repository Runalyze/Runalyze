<?php
/**
 * This file contains class::ImporterFiletypeXML
 * @package Runalyze\Import\Filetype
 */
ImporterWindowTabUpload::addInfo( __('xml-files from Polar, Suunto and RunningAHEAD are supported.') );
/**
 * Importer: *.xml
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeXML extends ImporterFiletypeAbstract {
	/**
	 * Allowed producer of XML files
	 * @var string
	 */
	static private $ALLOWED_PRODUCER = 'Polar, Suunto, RunningAHEAD';

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		if ($this->isFromPolar($String))
			$this->Parser = new ParserXMLpolarMultiple($String);
		elseif ($this->isFromRunningAHEAD($String))
			$this->Parser = new ParserXMLrunningAHEADMultiple($String);
		elseif ($this->isFromSuunto($String))
			$this->Parser = new ParserXMLsuuntoMultiple($String);
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

	/**
	 * Is this string from Suunto?
	 * @param string $String
	 * @return bool
	 */
	private function isFromSuunto(&$String) {
		return strpos($String, '<header>') !== false;
	}

	/**
	 * Throw error for unknown format
	 */
	private function throwErrorForUnknownFormat() {
		$this->Errors[] = __('This file is not supported. Supported producers of kml-files: '.self::$ALLOWED_PRODUCER.'.');
	}
}
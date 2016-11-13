<?php
/**
 * This file contains class::ImporterFiletypeXML
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.xml
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeXML extends ImporterFiletypeAbstract {
	/** @var string */
	const ALLOWED_PRODUCER = 'Polar, Suunto, RunningAHEAD';

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
			$this->throwErrorForUnknownFormat('xml', self::ALLOWED_PRODUCER);
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
}

ImporterWindowTabUpload::addInfo( sprintf(__('%s-files are supported from: %s'), 'xml', ImporterFiletypeXML::ALLOWED_PRODUCER) );

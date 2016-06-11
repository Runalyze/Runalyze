<?php
/**
 * This file contains class::ImporterFiletypeSLF
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.slf
 * 
 * Files of *.slf are from Sigma
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeSLF extends ImporterFiletypeAbstract {
	/** @var string */
	const ALLOWED_PRODUCER = 'Sigma Data Center 3 & 4';
    
	/**
	 * Set parser
	 * @param string $string string to parse
	 */
	protected function setParserFor($string) {
		if ($this->isFromSigmaDataCenter3($string))
			$this->Parser = new ParserSLF3Multiple($string);
		elseif ($this->isFromSigmaDataCenter4($string))
			$this->Parser = new ParserSLF4Multiple($string);
		else
			$this->throwErrorForUnknownFormat('slf', self::ALLOWED_PRODUCER);
	}

	/**
	 * Is this string from Sigma DataCenter => V4?
	 * @param string $string
	 * @return bool
	 */
	private function isFromSigmaDataCenter4($string) {
		return strpos($string, '<Entries') !== false;
	}   
        
	/**
	 * Is this string from Sigma DataCenter <= V3?
	 * @param string $string
	 * @return bool
	 */
	private function isFromSigmaDataCenter3($string) {
		return strpos($string, '<LogEntries') !== false;
	}
}

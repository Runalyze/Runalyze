<?php
/**
 * This file contains class::ImporterFiletypeSML
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.sml
 * 
 * Files of *.sml are from Suunto/Movescount
 *
 * @author Michael Pohl
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeSML extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserSMLsuuntoMultiple($String);
	}
}
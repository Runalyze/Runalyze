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
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserSLFMultiple($String);
	}
}
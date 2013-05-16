<?php
/**
 * This file contains class::ImporterFiletypeGPX
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.gpx
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeGPX extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserGPXMultiple($String);
	}
}
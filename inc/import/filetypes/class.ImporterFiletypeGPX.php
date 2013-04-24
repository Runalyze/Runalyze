<?php
/**
 * This file contains class::ImporterFiletypeGPX
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.gpx
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
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
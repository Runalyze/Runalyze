<?php
/**
 * This file contains class::ImporterFiletypeTRK
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.trk
 * 
 * Files of *.trk are from TwoNav or O-Synce
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeTRK extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserTRKSingle($String);
	}
}
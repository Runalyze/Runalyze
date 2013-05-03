<?php
/**
 * This file contains class::ImporterFiletypeTCX
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.tcx
 * 
 * Files of *.tcx have to be Garmin tcx-files.
 * This importer only runs the tcx parser
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeTCX extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserTCXMultiple($String);
	}
}
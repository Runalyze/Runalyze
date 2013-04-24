<?php
/**
 * This file contains class::ImporterFiletypeLOGBOOK3
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.logbook3
 * 
 * Files of *.logbook3 have to be from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeLOGBOOK3 extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserLOGBOOKMultiple($String);
	}
}
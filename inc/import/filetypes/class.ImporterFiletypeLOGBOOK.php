<?php
/**
 * This file contains class::ImporterFiletypeLOGBOOK
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.logbook
 * 
 * Files of *.logbook have to be from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeLOGBOOK extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserLOGBOOKMultiple($String);
	}
}
<?php
/**
 * This file contains class::ImporterFiletypeFITLOG
 * @package Runalyze\Importer\Filetype
 */
/**
 * Importer: *.fitlog
 * 
 * Files of *.fitlog have to be from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Importer\Filetype
 */
class ImporterFiletypeFITLOG extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserFITLOGMultiple($String);
	}
}
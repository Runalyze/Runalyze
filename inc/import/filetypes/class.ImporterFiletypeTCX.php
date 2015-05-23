<?php
/**
 * This file contains class::ImporterFiletypeTCX
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.tcx
 * 
 * Files of *.tcx have to be Garmin tcx-files.
 * This importer only runs the tcx parser
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeTCX extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
            if($this->isFromRuntastic($String))
		$this->Parser = new ParserTCXruntasticMultiple($String);
            else 
                $this->Parser = new ParserTCXMultiple($String);
	}
        
	/**
	 * Is this string from Sigma DataCenter => V4?
	 * @param string $String
	 * @return bool
	 */
	private function isFromRuntastic(&$String) {
		return strpos($String, 'runtastic') !== false;
	} 
}
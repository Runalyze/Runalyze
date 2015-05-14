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
            if($this->isFromSigmaDataCenter3($String))
		$this->Parser = new ParserSLF3Multiple($String);
            elseif ($this->isFromSigmaDataCenter4($String))
                $this->Parser = new ParserSLF4Multiple($String);
            else 
                $this->throwErrorForUnknownFormat();
                        
	}
        
	/**
	 * Is this string from Sigma DataCenter => V4?
	 * @param string $String
	 * @return bool
	 */
	private function isFromSigmaDataCenter4(&$String) {
		return strpos($String, '<Entries') !== false;
	}   
        
	/**
	 * Is this string from Sigma DataCenter <= V3?
	 * @param string $String
	 * @return bool
	 */
	private function isFromSigmaDataCenter3(&$String) {
		return strpos($String, '<LogEntries') !== false;
	}         
        
}

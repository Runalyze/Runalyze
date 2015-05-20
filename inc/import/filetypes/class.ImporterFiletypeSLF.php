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
	 * Allowed producer of SLF files
	 * @var string
	 */
	static private $ALLOWED_PRODUCER = 'Sigma Data Center 3 & 4';
    
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
	/**
	 * Throw error for unknown format
	 */
	private function throwErrorForUnknownFormat() {
		$this->Errors[] = __('This file is not supported. Supported producers of slf-files: '.self::$ALLOWED_PRODUCER.'.');
	}
        
}

<?php
/**
 * This file contains class::ImporterFiletypePWX
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.pwx
 * 
 * Files of *.pwx are from Peaksware/Trainingpeaks
 * 
 * @see http://www.peaksware.com/PWX/1/0/pwx.xsd
 * @see http://support.trainingpeaks.com/api/easy-file-upload.aspx
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypePWX extends ImporterFiletypeAbstract {
	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		$this->Parser = new ParserPWXMultiple($String);
	}
}
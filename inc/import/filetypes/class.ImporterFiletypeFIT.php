<?php
/**
 * This file contains class::ImporterFiletypeFIT
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.fit
 *
 * Files of *.fit are from Garmin
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeFIT extends ImporterFiletypeAbstract {
	/**
	 * Name of output file
	 * @var string
	 */
	protected $Filename = '';

	/**
	 * Handle
	 * @var resource
	 */
	protected $Handle = null;

	/**
	 * Current values
	 * @var array
	 */
	protected $CurrentValues = array();

	/**
	 * Parse file
	 * @param string $Filename relative path (from FRONTEND_PATH) to file
	 */
	public function parseFile($Filename) {
		$File = FRONTEND_PATH.$Filename;
		$this->Filename = FRONTEND_PATH.$Filename.'.temp';

		$Command = new PerlCommand();
		$Command->setScript('fittorunalyze.pl', '"'.$File.'" 1>"'.$this->Filename.'"');

		$Shell = new Shell();
		$Shell->runCommand($Command);

		$this->readFile();
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		throw new RuntimeException('ImporterFiletypeFIT does not use any parser, parseFile() has to be used instead of setParserFor().');
	}

	/**
	 * Read file
	 *
	 * WARNING: Don't use this method with a FIT-file.
	 * FIT-files have to be parsed first with parseFile($Filename).
	 *
	 * For unittesting, this method accepts a filename of the output of fittorunalyze.pl
	 *
	 * @param string $filename [optional] absolute path
	 */
	public function readFile($filename = '') {
		if (!empty($filename))
			$this->Filename = $filename;

		$this->Parser = new ParserFITMultiple('');
		$this->Parser->setFilename($this->Filename);
		$this->Parser->parse();

        unlink($this->Filename);
	}
}

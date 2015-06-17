<?php
/**
 * This file contains class::ImporterFiletypeTTBIN
 * @package Runalyze\Import\Filetype
 */
/**
 * Importer: *.ttbin
 * 
 * Files of *.ttbin are from TomTomWatches
 *
 * @author Michael Pohl
 * @package Runalyze\Import\Filetype
 */
class ImporterFiletypeTTBIN extends ImporterFiletypeAbstract {
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
		$this->Filename = $Filename.'.temp.tcx';

		$Command = new ShellCommand('ttbincnv -t < '.FRONTEND_PATH.$Filename.'  > '.FRONTEND_PATH.$this->Filename.'');
		$Command->run();     

		$this->readFile();
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
		throw new RuntimeException('ImporterFiletypeTTBIN does not use any parser, parseFile() has to be used instead of setParserFor().');
	}

	/**
	 * Read file
	 * 
	 * WARNING: Don't use this method with a TTBIN-file.
	 * TTBIN-files have to be parsed first with parseFile($Filename).
	 * 
	 * For unittesting, this method accepts a filename of the output of ttbincnv
	 * 
	 * @param string $filename [optional] absolute path
	 */
	public function readFile($filename = '') {
		if (!empty($filename))
			$this->Filename = $filename;

		$this->Parser = new ParserTCXMultiple(Filesystem::openFile($this->Filename));
		$this->Parser->parse();

		Filesystem::deleteFile($this->Filename);
	}
}
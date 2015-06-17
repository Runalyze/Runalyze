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
		$File = FRONTEND_PATH.$Filename;
		$this->Filename = FRONTEND_PATH.$Filename.'.temp.tcx';
                
		$Command = new ShellCommand('ttbincnv -t < '.$File.'  > '.$this->Filename.'');
                $Command->run();
                   
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 */
	protected function setParserFor($String) {
                $read = $this->parseFile($this->Filename);
		$this->Parser = new ParserTCXMultiple($read);
	}


	/**
	 * Make sure perl script worked
	 * @throws RuntimeException
	 */
	protected function readFirstLine() {
		$FirstLine = stream_get_line($this->Handle, 4096, PHP_EOL);

		if (strpos($FirstLine,'version') !== true) {
			fclose($this->Handle);
			unlink($this->Filename);

			throw new RuntimeException('Reading converted ttbin - tcx-file failed. First line was "'.$FirstLine.'".');
		}
	}
}
<?php
/**
 * This file contains class::ImporterFiletypeTTBIN
 * @package Runalyze\Import\Filetype
 */

use Runalyze\Import;

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
		$Command = new ShellCommand(TTBIN_PATH.' -t -E < "'.FRONTEND_PATH.$Filename.'"  > "'.FRONTEND_PATH.$this->Filename.'"');
		$Command->run();

		$this->readFile();
	}

	/**
	 * Set parser
	 * @param string $String string to parse
	 * @throws \RuntimeException
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
	 * @throws \Runalyze\Import\Exception\ParserException
	 */
	public function readFile($filename = '') {
		if (!empty($filename))
			$this->Filename = $filename;

		$Handle = @fopen(FRONTEND_PATH.$this->Filename, "r");
		if ($Handle) {
			$firstLine = stream_get_line($Handle, 4096, PHP_EOL);

			if (strpos($firstLine, 'ttbincnv') !== FALSE) {
				$message = 'Executing ttbincnv did not work: '.$firstLine;
				$message .= NL.NL.'You may need to compile ttbincnv for your environment.';

				throw new Import\Exception\InstallationSpecificException($message);
			} elseif (substr($firstLine, 0, 1) != '<') {
				throw new Import\Exception\UnexpectedContentException('Parsing your *.ttbin-file failed: '.$firstLine);
			} else {
				$Filecontent = Filesystem::openFile($this->Filename);

				$this->Parser = new ParserTCXMultiple($Filecontent);
				$this->Parser->parse();
			}

			fclose($Handle);
		}

		Filesystem::deleteFile($this->Filename);
	}
}

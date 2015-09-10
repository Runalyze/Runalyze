<?php
/**
 * This file contains class::ParserFITMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Abstract parser for multiple activities in *.fit-file
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITMultiple extends ParserAbstractMultiple {
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
	 * Set filename
	 * @param string $filename
	 */
	public function setFilename($filename) {
		$this->Filename = $filename;
	}

	/**
	 * Parse
	 */
	public function parse() {
		$firstActivityStarted = false;
		$SingleParser = new ParserFITSingle('');

		$this->Handle = @fopen($this->Filename, "r");
		if ($this->Handle) {
			$this->readFirstLine();

			while (($line = stream_get_line($this->Handle, 4096, PHP_EOL)) !== false && !feof($this->Handle)) {
				if (substr($line, -20) == 'NAME=sport NUMBER=12') {
					if ($firstActivityStarted) {
						$SingleParser->finishParsing();
						$this->addObject($SingleParser->object());

						$SingleParser->startNewActivity();
					} else {
						$firstActivityStarted = true;
					}
				}

				$SingleParser->parseLine($line);
			}

			$SingleParser->finishParsing();
			$this->addObject($SingleParser->object());

			fclose($this->Handle);
		}
	}

	/**
	 * Make sure perl script worked
	 * @throws RuntimeException
	 */
	protected function readFirstLine() {
		$FirstLine = stream_get_line($this->Handle, 4096, PHP_EOL);

		if (trim($FirstLine) != 'SUCCESS') {
			fclose($this->Handle);
			unlink($this->Filename);

			throw new RuntimeException('Reading *.fit-file failed. First line was "'.$FirstLine.'".');
		}
	}
}
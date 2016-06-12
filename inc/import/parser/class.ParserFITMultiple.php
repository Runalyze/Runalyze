<?php
/**
 * This file contains class::ParserFITMultiple
 * @package Runalyze\Import\Parser
 */

use Runalyze\Import\Exception\InstallationSpecificException;
use Runalyze\Import\Exception\ParserException;
use Runalyze\Import\Exception\UnexpectedContentException;

/**
 * Abstract parser for multiple activities in *.fit-file
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITMultiple extends ParserAbstractMultiple {
	/** @var string */
	const PERL_FIT_ERROR_MESSAGE_START = 'main::Garmin::FIT';

	/** @var string */
	const PERL_GENERAL_MESSAGE_START = 'perl: warning:';

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

			$this->throwErrorForFirstLine($FirstLine);
		}
	}

	/**
	 * @param string $firstLine
	 * @throws \Runalyze\Import\Exception\ParserException
	 */
	protected function throwErrorForFirstLine($firstLine) {
		$message = 'Reading *.fit-file failed. First line was "'.$firstLine.'".';

		if (substr($firstLine, 0, strlen(self::PERL_FIT_ERROR_MESSAGE_START)) == self::PERL_FIT_ERROR_MESSAGE_START) {
			throw new UnexpectedContentException($message);
		}

		if (substr($firstLine, 0, strlen(self::PERL_GENERAL_MESSAGE_START)) == self::PERL_GENERAL_MESSAGE_START) {
			$message .= NL.NL.'See https://github.com/Runalyze/Runalyze/issues/1701';

			throw new InstallationSpecificException($message);
		}

		throw new ParserException($message);
	}
}
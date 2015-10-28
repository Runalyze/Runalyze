<?php
/**
 * This file contains class::GZipWriter
 * @package Runalyze\Util\File
 */

namespace Runalyze\Util\File;

/**
 * Class for writing big gzipped files
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Util\File
 */
class GZipWriter {
	/**
	 * Resource for file
	 * @var resource
	 */
	protected $Resource = null;

	/**
	 * Constructor
	 * @param string $fileName
	 * @throws \RuntimeException
	 */
	public function __construct($fileName) {
		if (!is_writable($fileName)) {
			$this->Resource = gzopen($fileName, "wb");
		}

		if (!$this->Resource) {
			throw new \RuntimeException('The file "'.$fileName.'" couldn\'t be opened for writing.');
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->finish();
	}

	/**
	 * Add string to file
	 * @param string $string
	 */
	public function addToFile($string) {
		if ($this->isOpen()) {
			gzwrite($this->Resource, $string);
		}
	}

	/**
	 * Is file open?
	 * @return bool
	 */
	protected function isOpen() {
		return !is_null($this->Resource);
	}

	/**
	 * Finish: close file
	 */
	public function finish() {
		if ($this->isOpen()) {
			gzclose($this->Resource);
			$this->Resource = null;
		}
	}
}
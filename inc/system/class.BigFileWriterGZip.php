<?php
/**
 * This file contains class::BigFileWriterGZip
 * @package Runalyze\System
 */
/**
 * Class for writing big files
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class BigFileWriterGZip {
	/**
	 * Resource for file
	 * @var resource
	 */
	protected $resource = null;

	/**
	 * Constructor
	 * @param string $fileName relative to FRONTEND_PATH
	 */
	public function __construct($fileName) {
		$this->resource = gzopen(FRONTEND_PATH.$fileName, "wb");

		if (!$this->resource) {
			$this->resource = null;
			Error::getInstance()->addError('The file "'.$fileName.'" couldn\'t be opened for writing.');
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
		gzwrite($this->resource, $string);
	}

	/**
	 * Is file open?
	 * @return boolean
	 */
	protected function isOpen() {
		return is_null($this->resource);
	}

	/**
	 * Finish: close file
	 */
	public function finish() {
		if ($this->isOpen()) {
			gzclose($this->resource);
			$this->resource = null;
		}
	}
}
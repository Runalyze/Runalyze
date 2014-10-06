<?php
/**
 * This file contains class::BigFileReaderGZip
 * @package Runalyze\System
 */
/**
 * Class for reading big files
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class BigFileReaderGZip {
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
		if (is_readable(FRONTEND_PATH.$fileName))
			$this->resource = gzopen(FRONTEND_PATH.$fileName, "r");

		if (!$this->resource) {
			throw new RuntimeException('The file "'.$fileName.'" couldn\'t be opened for reading.');
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		if ($this->isOpen())
			$this->close();
	}

	/**
	 * Read one line
	 */
	public function readLine() {
		return gzgets($this->resource);
	}

	/**
	 * End of file?
	 * @return bool
	 */
	public function eof() {
		return gzeof($this->resource);
	}

	/**
	 * Is file open?
	 * @return boolean
	 */
	protected function isOpen() {
		return !is_null($this->resource);
	}

	/**
	 * Close
	 */
	public function close() {;
		gzclose($this->resource);

		$this->resource = null;
	}
}
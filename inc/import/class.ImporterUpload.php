<?php
/**
 * This file contains class::ImporterUpload
 * @package Runalyze\Import
 */
/**
 * Uploader
 * 
 * Constructing a new instance of this class will check for an uploaded file
 * and try to move the temporary file to the internal import-directory.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterUpload {
	/**
	 * Path to files, after construction with absolute path
	 * @var string 
	 */
	static private $pathToFiles = '/files/';

	/**
	 * JSON-response
	 * @var string
	 */
	private $Response = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		if (isset($_GET['json']))
			$this->tryToUploadFile();
	}

	/**
	 * Was there a file upload?
	 * @return bool
	 */
	public function thereWasAFile() {
		return isset($_GET['json']);
	}

	/**
	 * Get response
	 * @return string
	 */
	public function getResponse() {
		return $this->Response;
	}

	/**
	 * Upload succeeded?
	 * @return bool
	 */
	public function succeeded() {
		return $this->Response == 'success';
	}

	/**
	 * Set to succeeded
	 */
	private function setSucceeded() {
		$this->Response = 'success';
	}

	/**
	 * Try to upload file
	 */
	private function tryToUploadFile() {
		$extension = Filesystem::extensionOfFile($_FILES['userfile']['name']);

		if (!ImporterFactory::canImportExtension($extension))
			$this->throwUnknownExtension($extension);
		elseif ($this->uploadedFileWasTooBig())
			$this->throwTooBigFile();
		elseif ($this->tryToMoveFile())
			$this->setSucceeded();
		else
			$this->throwUploadFailed();
	}

	/**
	 * Set response for unknown extension
	 * @param string $format extension
	 */
	private function throwUnknownExtension($format) {
		$this->Response = 'Unknown input format "'.$format.'".';
	}

	/**
	 * Set response for too big file
	 */
	private function throwTooBigFile() {
		$this->Response = 'Uploaded file was too big.';
	}

	/**
	 * Try to move uploaded file
	 * @return bool
	 */
	private function tryToMoveFile() {
		return move_uploaded_file($_FILES['userfile']['tmp_name'], self::absolutePath($_FILES['userfile']['name']));
	}

	/**
	 * Set response for upload failed
	 */
	private function throwUploadFailed() {
		$this->Response = 'Can\'t move uploaded file '.$_FILES['userfile']['name'].'.<br />
					The following paths need chmod 777 (write permissions):<br />
						/log/<br />
						/inc/export/files/<br />
						/inc/import/files/<br />
						/plugin/RunalyzePluginTool_DbBackup/backup/<br />
						/plugin/RunalyzePluginTool_DbBackup/import/';
	}

	/**
	 * Check whether the uploaded file was too big to handle
	 */
	private function uploadedFileWasTooBig() {
		$Max    = ini_get('post_max_size');
		$unit   = substr($Max, -1);
		$factor = ($unit == 'M' ? 1048576
				: ($unit == 'K' ? 1024
				: ($unit == 'G' ? 1073741824
				: 1)));

		if ($Max && $_SERVER['CONTENT_LENGTH'] > $factor*(int)$Max)
			return true;

		return false;
	}

	/**
	 * Get absolute path
	 * @param string $File
	 * @return string
	 */
	static public function absolutePath($File) {
		return realpath(dirname(__FILE__)).self::$pathToFiles.$File;
	}

	/**
	 * Get relative path
	 * @param string $File
	 * @return string
	 */
	static public function relativePath($File) {
		return 'import'.self::$pathToFiles.$File;
	}
}
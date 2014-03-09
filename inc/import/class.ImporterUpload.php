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
		if ($this->thereWasAFile())
			$this->tryToUploadFile();
	}

	/**
	 * Was there a file upload?
	 * @return bool
	 */
	public function thereWasAFile() {
		return isset($_GET['json']) && isset($_FILES['qqfile']);
	}

	/**
	 * Get response
	 * @return string
	 */
	public function getResponse() {
		if ($this->succeeded())
			return '{"success":true}';

		return '{"error":"'.$this->Response.'"}';
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
		$extension = Filesystem::extensionOfFile($_FILES['qqfile']['name']);

		if (!ImporterFactory::canImportExtension($extension))
			$this->throwUnknownExtension($extension);
		elseif ($this->uploadErrorIsPresent())
			$this->throwUploadError();
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
	 * Check for upload error
	 * @return boolean
	 */
	private function uploadErrorIsPresent() {
		if (isset($_FILES['qqfile']) && isset($_FILES['qqfile']['error']) && $_FILES['qqfile']['error'] != 0)
			return true;

		return false;
	}

	/**
	 * Set response for upload error
	 */
	private function throwUploadError() {
		switch ($_FILES['qqfile']['error']) {
			case 1:
			case 2:
				$this->Response = 'Die Datei ist zu gro&szlig;.';
				break;
			case 3:
			case 4:
			default:
				$this->Response = 'There was a problem with your upload.';
				break;
	   }
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
		return move_uploaded_file($_FILES['qqfile']['tmp_name'], self::absolutePath($_FILES['qqfile']['name']));
	}

	/**
	 * Set response for upload failed
	 */
	private function throwUploadFailed() {
		// TODO: Use folder from /system/define.chmod.php
		$this->Response = 'Can\'t move uploaded file '.$_FILES['qqfile']['name'].'.<br>
					The following paths need chmod 777 (write permissions):<br>
						/log/<br>
						/inc/export/files/<br>
						/inc/import/files/<br>
						/plugin/RunalyzePluginTool_DbBackup/backup/<br>
						/plugin/RunalyzePluginTool_DbBackup/import/';
	}

	/**
	 * Check whether the uploaded file was too big to handle
	 */
	private function uploadedFileWasTooBig() {
		$Max    = ini_get('upload_max_filesize');
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
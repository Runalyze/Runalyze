<?php
/**
 * This file contains class::ImporterFactory
 * @package Runalyze\Import
 */
/**
 * Importer factory
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterFactory {
	/**
	 * Creator: manually via form
	 * @var string
	 */
	public static $CREATOR_FORM = 'form';

	/**
	 * Creator: file upload
	 * @var string
	 */
	public static $CREATOR_FILE = 'file-upload';

	/**
	 * Creator: Garmin Communicator API
	 * @var string
	 */
	public static $CREATOR_GARMIN_COMMUNICATOR = 'garmin-communicator';
	/**
	 * Boolean flag: log all filecontents?
	 * @var bool
	 */
	static private $logFileContents = false;

	/**
	 * Import from garmin communicator
	 * @var enum
	 */
	static public $FROM_COMMUNICATOR = 'NO_FILENAME_IMPORT_FROM_GARMIN_COMMUNICATOR';

	/**
	 * Filename
	 * @var string
	 */
	protected $Filename = '';

	/**
	 * Training objects
	 * @var array
	 */
	protected $TrainingObjects = array();

	/**
	 * Constructor
	 * @param string $Filename filename
	 */
	public function __construct($FilenameOrGarminFlag) {
		if ($FilenameOrGarminFlag == self::$FROM_COMMUNICATOR)
			$this->constructForGarminCommunicator();
		else
			$this->constructForFilename($FilenameOrGarminFlag);
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		if (!empty($this->Filename))
			unlink(FRONTEND_PATH.$this->Filename);
	}

	/**
	 * Get training objects
	 * @return array
	 */
	public function trainingObjects() {
		return $this->TrainingObjects;
	}

	/**
	 * Construct for garmin communicator
	 */
	private function constructForGarminCommunicator() {
		if (isset($_POST['activityIds']) && $_POST['data'] == 'FINISHED')
			$this->constructForMultipleFilesFromGarminCommunicator();
		else
			$this->constructForSingleFileFromGarminCommunicator();
	}
 
	/**
	 * Construct for multiple files from garmin communicator
	 */
	private function constructForMultipleFilesFromGarminCommunicator() {
		if (!is_array($_POST['activityIds']))
			return;

		$this->readMultipleFilesFromGarminCommunicator();
		$this->deleteMultipleFilesFromGarminCommunicator();
	}

	/**
	 * Read multiple files from garmin communicator
	 */
	private function readMultipleFilesFromGarminCommunicator() {
		$Importer = new ImporterFiletypeTCX();
		foreach ($_POST['activityIds'] as $ID) {
			$Importer->parseCompressedFile( ImporterUpload::relativePath($ID.'.tcx') );

			$this->TrainingObjects = array_merge($this->TrainingObjects, $Importer->objects());
		}
	}

	/**
	 * Delete multiple files from garmin communicator
	 */
	private function deleteMultipleFilesFromGarminCommunicator() {
		foreach ($_POST['activityIds'] as $ID)
			Filesystem::deleteFile( ImporterUpload::relativePath($ID.'.tcx') );
	}

	/**
	 * Construct for single file from garmin communicator
	 */
	private function constructForSingleFileFromGarminCommunicator() {
		$Importer = new ImporterFiletypeTCX();
		$Importer->parseCompressedString( $_POST['data'] );

		$this->TrainingObjects = $Importer->objects();
	}

	/**
	 * Construct for filename
	 * @param string $Filename filename
	 */
	private function constructForFilename($Filename) {
		$this->Filename = ImporterUpload::relativePath($Filename);
		$extension      = Filesystem::extensionOfFile($this->Filename);

		if (self::canImportExtension($extension))
			$this->importWithClass( self::classFor($extension) );
		else
			$this->throwUnknownExtension($Filename, $extension);
	}

	/**
	 * Import given file with special class
	 * @param string $Classname class of ImporterFiletypeAbstract
	 */
	private function importWithClass($Classname) {
		$Importer = new $Classname();
		$Importer->parseFile($this->Filename);

		$this->TrainingObjects = $Importer->objects();
	}

	/**
	 * Throw error for unknown extension
	 * @param string $filename
	 * @param string $extension
	 */
	private function throwUnknownExtension($filename, $extension) {
		Error::getInstance()->addError('Can\'t importer '.$filename.', there is no importer for *.'.$extension);
	}

	/**
	 * Classname for extension
	 * @param string $extension filetype
	 * @return string
	 */
	static private function classFor($extension) {
		return 'ImporterFiletype'.mb_strtoupper($extension);
	}

	/**
	 * Is this extension known?
	 * @param string $extension file extension
	 * @return boolean
	 */
	static public function canImportExtension($extension) {
		return class_exists(self::classFor($extension));
	}
}
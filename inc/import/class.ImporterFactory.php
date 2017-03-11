<?php
/**
 * This file contains class::ImporterFactory
 * @package Runalyze\Import
 */

use Runalyze\Import;

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
	 * Import from garmin communicator
	 * @var string
	 */
	const FROM_COMMUNICATOR = 'NO_FILENAME_IMPORT_FROM_GARMIN_COMMUNICATOR';

	/**
	 * Filename
	 * @var string
	 */
	protected $Filename = '';

	/**
	 * Training objects
	 * @var array
	 */
	private $TrainingObjects = array();

	/**
	 * Errors
	 * @var array
	 */
	private $Errors = array();

	/**
	 * Constructor
	 * @param string|array $filenameOrGarminFlag filename(s) or self::FROM_COMMUNICATOR
	 */
	public function __construct($filenameOrGarminFlag) {
		try {
			$this->tryToConstructImporterFor($filenameOrGarminFlag);
		} catch (Import\Exception\ParserException $e) {
			$this->handleParserException($e);
		}
	}

	/**
	 * @param string|array $filenameOrGarminFlag
	 */
	protected function tryToConstructImporterFor($filenameOrGarminFlag) {
		if ($filenameOrGarminFlag == self::FROM_COMMUNICATOR) {
			$this->constructForGarminCommunicator();
		} else if (is_array($filenameOrGarminFlag)) {
			$this->constructForFilenames($filenameOrGarminFlag);
		} else {
			$this->constructForFilename($filenameOrGarminFlag);
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->deleteCurrentFile();
	}

	/**
	 * Get errors
	 * @return array
	 */
	public function getErrors() {
		return $this->Errors;
	}

	/**
	 * Delete current file
	 */
	protected function deleteCurrentFile() {
		if (!empty($this->Filename) && file_exists(FRONTEND_PATH.$this->Filename))
			unlink(FRONTEND_PATH.$this->Filename);
	}

	/**
	 * Get training objects
	 * @return TrainingObject[]
	 */
	public function trainingObjects() {
		return $this->TrainingObjects;
	}

	/**
	 * Add errors
	 * @param array $Errors
	 */
	protected function addErrors(array $Errors) {
		$this->Errors = array_merge($this->Errors, $Errors);
	}

	/**
	 * Add objects
	 * @param TrainingObject[] $TrainingObjects
	 */
	protected function addObjects(array $TrainingObjects) {
		$this->TrainingObjects = array_merge($this->TrainingObjects, $TrainingObjects);
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

			$this->addObjects($Importer->objects());
		}

		$this->addErrors($Importer->getErrors());
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

		$this->addObjects($Importer->objects());
		$this->addErrors($Importer->getErrors());
	}

	/**
	 * Construct for filenames
	 * @param array $Filenames filenames
	 */
	private function constructForFilenames(array $Filenames) {
		$Files = $this->parseFilenames($Filenames);

		$this->extractAndImportHRMandGPXfrom($Files);
		$this->importFiles($Files);
	}

	/**
	 * Extract hrm+gpx from array and import
	 * @param array $Files
	 */
	private function extractAndImportHRMandGPXfrom(array &$Files) {
		if (isset($Files['gpx']) && isset($Files['hrm'])) {
			foreach ($Files['gpx'] as $g => $gpx) {
				foreach ($Files['hrm'] as $h => $hrm) {
					if (substr($gpx,0,-4) == substr($hrm,0,-4)) {
						$this->importHRMandGPX(substr($gpx,0,-4));

						unset($Files['gpx'][$g]);
						unset($Files['hrm'][$h]);
					}
				}
			}
		}
	}

	/**
	 * Import hrm and gpx
	 * @param string $filename relative path
	 */
	private function importHRMandGPX($filename) {
		$HRMImporter = new ImporterFiletypeHRM();
		$HRMImporter->parseFile($filename.'.hrm');

		$GPXImporter = new ImporterFiletypeGPX();
		$GPXImporter->parseFile($filename.'.gpx');

		$Importer = new ImporterHRMandGPX($HRMImporter, $GPXImporter);
		$this->TrainingObjects[] = $Importer->object();

		$this->addErrors($HRMImporter->getErrors());
		$this->addErrors($GPXImporter->getErrors());

		unlink(FRONTEND_PATH.$filename.'.hrm');
		unlink(FRONTEND_PATH.$filename.'.gpx');
	}

	/**
	 * Import files
	 * @param array $Files
	 */
	private function importFiles(array &$Files) {
		foreach ($Files as $extension => $filenames) {
			foreach ($filenames as $file) {
				$this->Filename = $file;

				$this->importWithClass(self::classFor($extension));
				$this->deleteCurrentFile();
			}
		}
	}

	/**
	 * Parse filenames and return array
	 * @param array $Filenames
	 * @return array key: extensions, value: array with relative filenames
	 */
	private function parseFilenames(array $Filenames) {
		$Files = array();

		foreach ($Filenames as $file) {
			$filename  = ImporterUpload::relativePath($file);
			$extension = Filesystem::extensionOfFile($filename);

			if (self::canImportExtension($extension)) {
				if (!isset($Files[$extension]))
					$Files[$extension] = array();

				$Files[$extension][] = $filename;
			} else {
				$this->throwUnknownExtension($file, $extension);
			}
		}

		return $Files;
	}

	/**
	 * Construct for filename
	 * @param string $filename filename
	 */
	private function constructForFilename($filename) {
		$this->Filename = ImporterUpload::relativePath($filename);
		$extension      = Filesystem::extensionOfFile($this->Filename);

		if (!file_exists(FRONTEND_PATH.$this->Filename)) {
			$this->throwNonExistingFile($filename);
		} elseif (self::canImportExtension($extension)) {
			$this->importWithClass(self::classFor($extension));
		} else {
			$this->throwUnknownExtension($filename, $extension);
		}
	}

	/**
	 * Import given file with special class
	 * @param string $Classname class of ImporterFiletypeAbstract
	 */
	private function importWithClass($Classname) {
		try {
			/** @var ImporterFiletypeAbstract $Importer */
			$Importer = new $Classname();
			$Importer->parseFile($this->Filename);

			$this->addObjects($Importer->objects());
			$this->addErrors($Importer->getErrors());
		} catch (Import\Exception\ParserException $exception) {
			$this->handleParserException($exception, basename($this->Filename).': ');
		}
	}

	/**
	 * Throw error for unknown extension
	 * @param string $filename
	 * @param string $extension
	 */
	protected function throwUnknownExtension($filename, $extension) {
		// This must not happen as the file uploader should catch unsupported extensions.
		$this->Errors[] = $filename.': '.__('This file format is not supported.');
	}

	/**
	 * @param string $filename
	 */
	protected function throwNonExistingFile($filename) {
		$this->Errors[] = $filename.': '.__('The file could not be saved.');
	}

	/**
	 * @TODO use monolog
	 * @param \Runalyze\Import\Exception\ParserException $e
	 * @param string $messagePrefix
	 */
	protected function handleParserException(Import\Exception\ParserException $e, $messagePrefix = '') {
		$message = __('There was a problem while importing the file.');
		$message .= ' ('.$e->getMessage().')';
		$addErrorMessage = false;

		if ($e instanceof Import\Exception\UnexpectedContentException) {
			$message = __('There are some unexpected contents in the file.');
			$message .= ' '.sprintf(__('Please mail the file to %s.'), 'support@runalyze.com');
			$addErrorMessage = true;
		} elseif ($e instanceof Import\Exception\InstallationSpecificException) {
			$message = __('There was an installation specific problem. Please contact the administrator.');
			$addErrorMessage = true;
		} elseif ($e instanceof Import\Exception\UnsupportedFileException) {
			$message = __('This file format is not supported.');
		}

		if ($addErrorMessage) {
			// TODO: users should see as little as necessary of the exact error messages
			$message .= '<br><br>'.__('Please add the following information').':<br><code style="height:auto;color:#000;">'.nl2br($e->getMessage()).'</code>';
		}

		$this->Errors[] = $messagePrefix.$message;
	}

	/**
	 * Classname for extension
	 * @param string $extension filetype
	 * @return string
	 */
	private static function classFor($extension) {
		return 'ImporterFiletype'.mb_strtoupper($extension);
	}

	/**
	 * Is this extension known?
	 * @param string $extension file extension
	 * @return boolean
	 */
	public static function canImportExtension($extension) {
		return class_exists(self::classFor($extension));
	}
}

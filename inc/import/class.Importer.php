<?php
/**
 * Class: Importer
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class Importer {
	/**
	 * Path to files, after construction with absolute path
	 * @var string 
	 */
	protected $pathToFiles = '/files/';

	/**
	 * Boolean flag: log all filecontents?
	 * @var bool
	 */
	protected $logFileContents = false;

	/**
	 * Boolean flag: has the try to insert training to database failed?
	 * @var mixed
	 */
	protected $insertFailed = -1;

	/**
	 * Array for input formats
	 * @var array
	 */
	private static $formats = array();

	/**
	 * File name
	 * @var string
	 */
	protected $fileName;

	/**
	 * File content
	 * @var string
	 */
	protected $fileContent;

	/**
	 * Training data for creating
	 * @var array
	 */
	protected $TrainingData = array();

	/**
	 * Which keys are allowed to be directly set as training data?
	 * @var array
	 */
	protected $allowedKeysForSet = array();

	/**
	 * Which keys are accessible via get-function?
	 * @var array
	 */
	protected $allowedKeysForGet = array();

	/**
	 * Additional information for importer
	 * @var array
	 */
	static protected $additionalImporterInfo = array();

	/**
	 * Array with all internal errors
	 * @var array
	 */
	private $Errors = array();

	/**
	 * Set values for training from file or post-data
	 */
	abstract protected function setTrainingValues();

	/**
	 * Constructor
	 * @param string $fileName
	 */
	protected function __construct($fileName = '') {
		$this->fileName = $fileName;
		$this->pathToFiles = realpath(dirname(__FILE__)).$this->pathToFiles;

		$this->setAllowedKeys();
		$this->setTrainingValues();
	}

	/**
	 * Set all allowed keys
	 */
	private function setAllowedKeys() {
		$this->allowedKeysForSet[] = 'sportid';
		$this->allowedKeysForSet[] = 'time';
		$this->allowedKeysForSet[] = 'datum';
		$this->allowedKeysForSet[] = 'zeit';
		$this->allowedKeysForSet[] = 's';
		$this->allowedKeysForSet[] = 'kcal';
		$this->allowedKeysForSet[] = 'comment';
		$this->allowedKeysForSet[] = 'partner';
		$this->allowedKeysForSet[] = 'typeid';
		$this->allowedKeysForSet[] = 'shoeid';
		$this->allowedKeysForSet[] = 'abc';
		$this->allowedKeysForSet[] = 'distance';
		$this->allowedKeysForSet[] = 'is_track';
		$this->allowedKeysForSet[] = 'elevation';
		$this->allowedKeysForSet[] = 'pulse_avg';
		$this->allowedKeysForSet[] = 'pulse_max';
		$this->allowedKeysForSet[] = 'route';
		$this->allowedKeysForSet[] = 'weatherid';
		$this->allowedKeysForSet[] = 'temperature';
		$this->allowedKeysForSet[] = 'splits';

		$this->allowedKeysForGet   = $this->allowedKeysForSet;
		$this->allowedKeysForGet[] = 'pace';
		$this->allowedKeysForGet[] = 'kmh';
		$this->allowedKeysForGet[] = 'arr_time';
		$this->allowedKeysForGet[] = 'arr_lat';
		$this->allowedKeysForGet[] = 'arr_lon';
		$this->allowedKeysForGet[] = 'arr_alt';
		$this->allowedKeysForGet[] = 'arr_dist';
		$this->allowedKeysForGet[] = 'arr_heart';
		$this->allowedKeysForGet[] = 'arr_pace';
	}

	/**
	 * Get instance for one special importer
	 * @param string $fileName
	 */
	static public function getInstance($fileName = '') {
		$format = mb_strtoupper(self::getExtensionFrom($fileName));

		if ($format == '') {
			if (isset($_POST['data']))
				$format = 'TCX';
			else
				return new ImporterFormular();
		}

		if (self::isKnownFormat($format))
			return new self::$formats[$format]($fileName);

		Error::getInstance()->addError('Importer: unknown input format "'.$format.'".');

		return new ImporterFormular();
	}

	/**
	 * Is this format known?
	 * @param string $format
	 * @return boolean
	 */
	static public function isKnownFormat($format) {
		$format = mb_strtoupper($format);

		return (isset(self::$formats[$format]) && class_exists(self::$formats[$format]));
	}

	/**
	 * Register a new importer
	 * @param string $format
	 * @param string $className
	 */
	static public function registerImporter($format, $className) {
		$classFileName = 'import/class.'.$className.'.php';
		if (file_exists(FRONTEND_PATH.$classFileName)) {
			self::$formats[$format] = $className;

			require_once FRONTEND_PATH.$classFileName;
		} else {
			Error::getInstance()->addError('Importer: Can\'t find "'.$classFileName.'" to register format "'.$format.'".');
		}
	}

	/**
	 * Add an information to importer formular
	 * @param string $string
	 */
	static public function addAdditionalInfo($string) {
		self::$additionalImporterInfo[] = $string;
	}

	/**
	 * Get extension from a given filename
	 * @param string $fileName
	 * @return string
	 */
	static public function getExtensionFrom($fileName) {
		if (strlen(trim($fileName)) == 0)
			return '';

		$PathInfo = pathinfo($fileName);

		if (!isset($PathInfo['extension'])) {
			Error::getInstance()->addError('Die hochgeladene Datei hat keine Endung. Leerzeichen d&uuml;rfen nicht enthalten sein.');
			return '';
		}

		return $PathInfo['extension'];
	}

	/**
	 * Upload file temporary if a file has been sent
	 * @return bool
	 */
	public function tryToUploadFileHasSuccess() {
		if (isset($_GET['json'])) { // TODO: Fehlermeldungen klappen nicht so recht
			$responseCode = $this->uploadFile();
			if ($responseCode !== true) {
				echo $responseCode;
				return true;
			}
		
			Error::getInstance()->footer_sent = true;
			echo 'success';
			return true;
		}

		return false;
	}

	/**
	 * Upload file
	 * @return boolean
	 */
	private function uploadFile() {
		$format = self::getExtensionFrom($_FILES['userfile']['name']);
		if (!self::isKnownFormat($format))
			return 'Unknown input format "'.$format.'".';

		if (self::uploadedFileWasTooBig())
			return 'Uploaded file was too big.';

		if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $this->absolutePathTo($_FILES['userfile']['name'])))
			return 'Can\'t move uploaded file '.$_FILES['userfile']['name'].'. Check permissions and filesystem.';

		return true;
	}

	/**
	 * Absolute path to uploaded files
	 * @param string $fileName
	 * @return string 
	 */
	private function absolutePathTo($fileName) {
		return $this->pathToFiles.$fileName;
	}

	/**
	 * Check whether the uploaded file was too big to handle
	 */
	static public function uploadedFileWasTooBig() {
		$Max = ini_get('post_max_size');
		$factor = substr($Max, -1);
		$factor = ($factor == 'M' ? 1048576
				: ($factor == 'K' ? 1024
				: ($factor == 'G' ? 1073741824 : 1)));

		if ($Max && $_SERVER['CONTENT_LENGTH'] > $factor*(int)$Max)
			return true;

		return false;
	}

	/**
	 * Has the training been created with success?
	 * @return bool
	 */
	public function tryToCreateTrainingHasSuccess() {
		return ($this->insertFailed === false);
	}

	/**
	 * Get string from file
	 * @return string
	 */
	protected function getFileContentAsString() {
		if (empty($this->fileName) && isset($_POST['data'])) {
			$string = ImporterTCX::decodeCompressedData($_POST['data']);
			$this->logFileContent($string);
			return $string;
		}

		$file = $this->absolutePathTo($this->fileName);

		if (!file_exists($file)) {
			$this->addError('class::Importer: Uploaded file "'.$this->fileName.'" can\'t be found.');
			return;
		}

		$Content = utf8_encode(file_get_contents($file));
		$this->logFileContent($Content);
		unlink($file);

		return $Content;
	}

	/**
	 * Log file content
	 * @param string $fileContent
	 */
	protected function logFileContent($fileContent) {
		if ($this->logFileContents)
			Error::getInstance()->addDebug('Importer::fileContent(file="'.$this->fileName.'"): '.$fileContent);
	}

	/**
	 * Add error message to debugger
	 * @param string $message
	 */
	protected function addError($message) {
		$this->Errors[] = $message;

		Error::getInstance()->addError($message);
	}

	/**
	 * Include template for displaying formular for uploading a file
	 */
	public function displayUploadFormular() {
		$Formats = array();
		foreach (self::$formats as $Format => $ClassName)
			$Formats[] = mb_strtolower($Format);

		$AllowedFormatsForJS = "'".implode("', '", $Formats)."'";
		$AllowedFormats      = '*.'.implode(', *.', $Formats);

		include 'tpl/tpl.Importer.upload.php';
	}

	/**
	 * Include template for displaying formular for uploading a file
	 */
	public function displayGarminCommunicator() {
		include 'tpl/tpl.Importer.garminCommunicator.php';
	}

	/**
	 * Include template for displaying standard formular, can be overwritten from subclass
	 */
	public function displayHTMLformular() {
		$Mysql = Mysql::getInstance();
		
		$this->makeTrainingDataReadable();
		$this->setDefaultTrainingDataForCreation();
		$this->transformTrainingDataToPostData();

		$this->displayErrors();

		include 'tpl/tpl.Importer.formular.php';
	}

	/**
	 * Display error-messages
	 */
	private function displayErrors() {
		if (empty($this->Errors))
			return;

		echo '<h1>Probleme beim Import</h1>';
		echo HTML::em('Beim Importieren ist ein Fehler aufgetreten.');
		echo HTML::clearBreak();
		echo HTML::clearBreak();

		foreach ($this->Errors as $Error)
			echo HTML::error($Error);

		echo HTML::clearBreak();
	}

	/**
	 * Set a value for training data
	 * @param string $key
	 * @param mixed $value
	 */
	protected function set($key, $value) {
		if (in_array($key, $this->allowedKeysForSet))
			$this->TrainingData[$key] = $value;
		else
			Error::getInstance()->addError('Importer: Can\'t set "'.$key.'" to "'.$value.'" - not allowed.');
	}

	/**
	 * Set a value for training data
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if (in_array($key, $this->allowedKeysForGet)) {
			if (!isset($this->TrainingData[$key]))
				return '';
			return $this->TrainingData[$key];
		}

		Error::getInstance()->addError('Importer: Can\'t get "'.$key.'" - not allowed.');

		return '';
	}

	/**
	 * Transform internal training data to post data
	 */
	public function transformTrainingDataToPostData() {
		$_POST = array_merge($_POST, $this->TrainingData);
	}

	/**
	 * Make some values readable
	 */
	private function makeTrainingDataReadable() {
		if ($this->get('s') > 0) {
			$this->TrainingData['pace'] = Helper::Pace($this->get('distance'), $this->get('s'));
			$this->TrainingData['kmh']  = Helper::Kmh($this->get('distance'), $this->get('s'));
			$this->TrainingData['s']    = Helper::Time($this->get('s'), false, true);
		}
	}

	/**
	 * Set default post-variables if not set
	 */
	private function setDefaultTrainingDataForCreation() {
		$this->setDefaultValue('s', '0:00:00');
		$this->setDefaultValue('kcal', '0');
		$this->setDefaultValue('distance', '0.00');
		$this->setDefaultValue('pace', '0:00');
		$this->setDefaultValue('kmh', '0,00');
		$this->setDefaultValue('elevation', '0');
		$this->setDefaultValue('splits', '');

		if ($this->trainingWasToday()) {
			$Weather = Weather::Forecaster();
			$Weather->setPostDataIfEmpty();
		}
	}

	/**
	 * Is the training less than 24h old?
	 * @return bool
	 */
	private function trainingWasToday() {
		return empty($_POST) || ($this->get('time') > 0 && (time() - $this->get('time')) < 24*3600) || ($this->get('datum') == date("d.m.Y"));
	}

	/**
	 * Set default value as training data
	 * @param string $key
	 * @param string $default
	 */
	private function setDefaultValue($key, $default = '') {
		if (!isset($this->TrainingData[$key]) || $this->TrainingData[$key] == '')
			$this->TrainingData[$key] = isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	/**
	 * Implode array and set as training data
	 * @param string $key
	 * @param array $array
	 */
	private function setArrayFor($key, $array) {
		$this->TrainingData[$key] = implode(Training::$ARR_SEP, $array);
	}

	/**
	 * Set array for training data: time
	 * @param array $array
	 */
	protected function setArrayForTime($array) {
		$this->setArrayFor('arr_time', $array);
	}

	/**
	 * Set array for training data: latitude
	 * @param array $array
	 */
	protected function setArrayForLatitude($array) {
		$this->setArrayFor('arr_lat', $array);
	}

	/**
	 * Set array for training data: longitude
	 * @param array $array
	 */
	protected function setArrayForLongitude($array) {
		$this->setArrayFor('arr_lon', $array);
	}

	/**
	 * Set array for training data: elevation
	 * @param array $array
	 */
	protected function setArrayForElevation($array) {
		$this->setArrayFor('arr_alt', $array);
	}

	/**
	 * Set array for training data: distance
	 * @param array $array
	 */
	protected function setArrayForDistance($array) {
		$this->setArrayFor('arr_dist', $array);
	}

	/**
	 * Set array for training data: heartrate
	 * @param array $array
	 */
	protected function setArrayForHeartrate($array) {
		$this->setArrayFor('arr_heart', $array);
	}

	/**
	 * Set array for training data: pace
	 * @param array $array
	 */
	protected function setArrayForPace($array) {
		$this->setArrayFor('arr_pace', $array);
	}
}
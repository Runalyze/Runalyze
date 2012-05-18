<?php
/**
 * Class: Exporter
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class Exporter {
	/**
	 * Array for input formats
	 * @var array
	 */
	private static $formats = array();

	/**
	 * Training data for creating
	 * @var Training
	 */
	protected $Training = null;

	/**
	 * File content to write
	 * @var string
	 */
	protected $FileContent = '';

	/**
	 * Internal array with errors to display
	 * @var array
	 */
	private $errors = array();

	/**
	 * Get all formats
	 * @return array
	 */
	static public function getFormats() {
		return self::$formats;
	}

	/**
	 * Get instance for one special exporter
	 * @param string $format
	 */
	static public function getInstance($format) {
		if (isset(self::$formats[$format]) && class_exists(self::$formats[$format]))
			return new self::$formats[$format]();

		Error::getInstance()->addError('Exporter: unknown input format "'.$format.'".');

		return null;
	}

	/**
	 * Register a new exporter
	 * @param string $format
	 * @param string $className
	 */
	static public function registerExporter($format, $className) {
		$fileName = 'export/class.'.$className.'.php';
		if (file_exists(FRONTEND_PATH.$fileName)) {
			self::$formats[$format] = $className;

			require_once FRONTEND_PATH.$fileName;
		} else {
			Error::getInstance()->addError('Exporter: Can\'t find "'.$fileName.'" to register format "'.$format.'".');
		}
	}

	/**
	 * Default constructor 
	 */
	public function __construct() {
		
	}

	/**
	 * Default destructor 
	 */
	public function __destruct() {
		
	}

	/**
	 * Get extension
	 * @return string 
	 */
	abstract protected function getExtension();

	/**
	 * Set file content
	 */
	abstract protected function setFileContent();

	/**
	 * Export a given training
	 * @param int $trainingId 
	 */
	final public function export($trainingId) {
		$this->setTraining($trainingId);
		$this->setFileContent();
		$this->writeFile();
	}

	/**
	 * Write file 
	 */
	final protected function writeFile() {
		if (empty($this->errors) && !empty($this->FileContent))
			Filesystem::writeFile('export/files/'.$this->getFilename(), $this->getFileContent());
	}

	/**
	 * Add indents to file content 
	 */
	final protected function formatFileContentAsXML() {
		$XML = new DOMDocument('1.0');
		$XML->preserveWhiteSpace = false;
		$XML->loadXML( $this->FileContent );
		$XML->formatOutput = true;

		$this->FileContent = $XML->saveXML();
	}

	/**
	 * Get file content
	 * @return string
	 */
	final public function getFileContent() {
		return $this->FileContent;
	}

	/**
	 * Add error message to display to user
	 * @param string $message 
	 */
	final protected function addError($message) {
		$this->errors[] = $message;
	}

	/**
	 * Get all errors
	 * @return array
	 */
	final public function getAllErrors() {
		return $this->errors;
	}

	/**
	 * Set training
	 * @param int $id 
	 */
	private function setTraining($id) {
		$this->Training = new Training($id);
	}

	/**
	 * Get filename
	 * @return string 
	 */
	final public function getFilename() {
		if (is_null($this->Training))
			return 'undefined.'.$this->getExtension();
	
		return date('Y-m-d_H-i', $this->Training->get('time')).'_Training_'.$this->Training->id().'.'.$this->getExtension();
	}
}
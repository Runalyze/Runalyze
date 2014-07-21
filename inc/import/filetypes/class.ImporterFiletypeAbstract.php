<?php
/**
 * This file contains class::ImporterFiletypeAbstract
 * @package Runalyze\Import\Filetype
 */
/**
 * Abstract importer for a given filetype
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Filetype
 */
abstract class ImporterFiletypeAbstract {
	/**
	 * Parser
	 * @var ParserAbstract
	 */
	protected $Parser = null;

	/**
	 * Errors
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		
	}

	/**
	 * Parse string
	 * @param string $String string to parse
	 */
	final public function parseString($String) {
		$this->setParserFor($String);
		$this->runParser();
	}

	/**
	 * Parse compressed data (base64, gzip)
	 * @param string $String string to parse
	 */
	final public function parseCompressedString($String) {
		$this->setParserFor( self::decodeCompressedData($String) );
		$this->runParser();
	}

	/**
	 * Load file
	 * @param string $Filename relative path (from FRONTEND_PATH) to file
	 */
	public function parseFile($Filename) {
		$this->parseString( Filesystem::openFile($Filename) );
	}

	/**
	 * Load compressed file (base64, gzip)
	 * @param string $Filename relative path (from FRONTEND_PATH) to file
	 */
	final public function parseCompressedFile($Filename) {
		$this->parseCompressedString( Filesystem::openFile($Filename) );
	}

	/**
	 * Run parser
	 */
	private function runParser() {
		if (is_null($this->Parser))
			return;

		$this->Parser->parse();

		if ($this->Parser->failed())
			$this->Errors = array_merge($this->Errors, $this->Parser->getErrors());

		if ($this->numberOfTrainings() == 0)
			$this->Errors[] = __('No activities could be found.');
	}

	/**
	 * Analyze string and set correct parser
	 * @param string $String string
	 */
	abstract protected function setParserFor($String);

	/**
	 * Did the parser fail?
	 * @return boolean
	 */
	final public function failed() {
		return count($this->getErrors()) > 0;
	}

	/**
	 * Get errors
	 * @return array
	 */
	final public function getErrors() {
		if (is_null($this->Parser))
			return array( __('There is no parser in ImporterFiletype. Maybe this filetype is not supported.') );

		return array_merge($this->Errors, $this->Parser->getErrors());
	}

	/**
	 * Get training objects
	 * @return array array of TrainingObject
	 */
	final public function objects() {
		if (is_null($this->Parser))
			return array();

		return $this->Parser->objects();
	}

	/**
	 * Get training objects
	 * @param int $index optional index
	 * @return array array of TrainingObject
	 */
	final public function object($index = 0) {
		if (is_null($this->Parser)) {
			Error::getInstance()->addError('Parser of Importer is empty. Returned default TrainingObject.');
			return new TrainingObject( DataObject::$DEFAULT_ID );
		}

		return $this->Parser->object($index);
	}

	/**
	 * Number of trainings
	 * @return int
	 */
	final public function numberOfTrainings() {
		return count($this->objects());
	}

	/**
	 * Has more than one training?
	 * @return bool
	 */
	final public function hasMultipleTrainings() {
		return $this->numberOfTrainings() > 1;
	}

	/**
	 * Decode from Garmin-Communicator compressed data (base64, gzip)
	 * @param string $string
	 * @return string
	 */
	static public function decodeCompressedData($string) {
		$string = mb_substr($string, mb_strpos($string, "\n") + 1);
		return gzinflate(substr(base64_decode($string),10,-8));
	}
}
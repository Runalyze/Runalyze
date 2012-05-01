<?php
class Parser {
	/**
	 * Training data that have been parsed
	 * @var array
	 */
	protected $data = array();

	/**
	 * Array with all internal errors
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Construct a new parser
	 */
	public function __construct() {
		
	}

	/**
	 * Parsed without errors?
	 * @return boolean
	 */
	final public function worked() {
		return empty($this->Errors);
	}

	/**
	 * Get all errors
	 * @return array
	 */
	final public function getErrors() {
		return $this->Errors;
	}

	/**
	 * Get parameter of training if set, otherwise returns null
	 * @param string $key
	 * @return mixed
	 */
	final public function get($key) {
		if (isset($this->data[$key]))
			return $this->data[$key];

		return null;
	}

	/**
	 * Get full array with training data
	 * @return array
	 */
	final public function getFullData() {
		return $this->data;
	}

	/**
	 * Set a special training value
	 * @param string $key
	 * @param mixed $value 
	 */
	final protected function set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Add an error to internal array
	 * @param string $message 
	 */
	final protected function addError($message) {
		$this->Errors[] = $message;
	}

	/**
	 * Implode array and set as training data
	 * @param string $key
	 * @param array $array
	 */
	private function setArrayFor($key, $array) {
		$this->data[$key] = implode(Training::$ARR_SEP, $array);
	}

	/**
	 * Set array for training data: time
	 * @param array $array
	 */
	final protected function setArrayForTime($array) {
		$this->setArrayFor('arr_time', $array);
	}

	/**
	 * Set array for training data: latitude
	 * @param array $array
	 */
	final protected function setArrayForLatitude($array) {
		$this->setArrayFor('arr_lat', $array);
	}

	/**
	 * Set array for training data: longitude
	 * @param array $array
	 */
	final protected function setArrayForLongitude($array) {
		$this->setArrayFor('arr_lon', $array);
	}

	/**
	 * Set array for training data: elevation
	 * @param array $array
	 */
	final protected function setArrayForElevation($array) {
		$this->setArrayFor('arr_alt', $array);
	}

	/**
	 * Set array for training data: distance
	 * @param array $array
	 */
	final protected function setArrayForDistance($array) {
		$this->setArrayFor('arr_dist', $array);
	}

	/**
	 * Set array for training data: heartrate
	 * @param array $array
	 */
	final protected function setArrayForHeartrate($array) {
		$this->setArrayFor('arr_heart', $array);
	}

	/**
	 * Set array for training data: pace
	 * @param array $array
	 */
	final protected function setArrayForPace($array) {
		$this->setArrayFor('arr_pace', $array);
	}
}
<?php
/**
 * This file contains the class to handle errors.
 * set_error_handler() is needed to get all normal errors from php.
 * Before handling the errors of a script a new object of class::Error has to be created.
 */
/**
 * Class: Error
 * This class handles all errors 
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses error_handler()
 *
 * Last modified 2011/03/05 13:00 by Hannes Christiansen
 */
class Error {
	/**
	 * Internatl instance pointer
	 * @var Error
	 */
	private static $instance = NULL;

	/**
	 * Array of strings with all errors
	 * @var array
	 */
	private $errors = array();

	/**
	 * Filename creating these errors
	 * @var string
	 */
	private $file = '';

	/**
	 * Boolean flag: tracking errors
	 * @var bool
	 */
	private $log = false;

	/**
	 * Name for the log-file
	 * @var string
	 */
	private $log_file = '';

	/**
	 * Static getter for the singleton instnace
	 * @return class::Error static instance
	 */
	public static function getInstance() {
		if (self::$instance == NULL)
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Prohibit creating an object from outside
	 */
	private function __construct() {}

	/**
	 * Destructor for this class
	 * Prints error messages to logfile if wanted
	 */
	function __destruct() {
		if ($this->log) {
			$this->display();
		}
	}

	/**
	 * Prohibit cloning
	 */
	private function __clone() {}

	/**
	 * To initialise for this class.
	 * @param string $file       filename
	 * @param bool   $log        Logging errors?
	 * @param string $log_path   Path for logging errors
	 */
	public static function init($file = __FILE__, $log = false, $log_file = '') {
		if ($log_file == '')
			$log_file = 'log/'.$file.'.log.'.date("Ymd.Hi").'.html';

		self::getInstance()->setLogVars($log, $log_file);
	}

	/**
	 * Set private variables from self::init()
	 */
	public function setLogVars($log, $log_file) {
		$this->log = $log;
		$this->log_file = $log_file;
	}

	/**
	 * Prints all errors to screen or into the log-file
	 */
	public function display() {
		if (!$this->log)
			print implode('<br />', $this->errors);
		else {
			$handle = fopen($this->log_file, 'w+');
			fwrite($handle, implode('<br />', $this->errors));
			fclose($handle);
		}
	}

	/**
	 * Adds a new message to the array of errors
	 * @param const  $type      type of error (ERROR | WARNING | NOTICE | Unknown error type)
	 * @param string $message   error message
	 * @param string $file      file containing the error
	 * @param int    $line      line number containing the error
	 */
	public function add($type, $message, $file = '', $line = -1) {
		if ($file != '') {
			$message .= ' (in '.$file;
			if ($line != -1)
				$message .= '::'.$line;
			$message .= ')';
		}
		
		$this->errors[] = '<strong>'.$type.'</strong> '.$message;
	}

	/**
	 * Add an error to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addError($message, $file = '', $line = -1) {
		$this->add('ERROR', $message, $file, $line);
	}

	/**
	 * Add a warning to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addWarning($message, $file = '', $line = -1) {
		$this->add('WARNING', $message, $file, $line);
	}

	/**
	 * Add a todo to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addTodo($message, $file = '', $line = -1) {
		$this->add('TODO', $message, $file, $line);
	}
}

/**
 * Own function to handle the errors using class::Error.
 * @param $type      type of error (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)
 * @param $message   error message
 * @param $file      filename
 * @param $line      line number
 * @return bool      returning true to not execute PHP internal error handler
 */
function error_handler($type, $message, $file, $line) {
	switch($type) {
		case E_ERROR:
			$type = 'ERROR';
			break;
		case E_WARNING:
			$type = 'WARNING';
			break;
		case E_NOTICE:
			$type = 'NOTICE';
			break;
		default:
			$type = 'Unknown error type';
			break;
	}

	Error::getInstance()->add($type, $message, $file, $line);

    // Don't execute PHP internal error handler
    return true;
}
?>
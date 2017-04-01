<?php
/**
 * Class: Error - This class handles all errors
 * @author Hannes Christiansen
 * @package Runalyze
 */

namespace Runalyze;

/**
 * This file contains the class to handle errors.
 * set_error_handler() is needed to get all normal errors from php.
 * Before handling the errors of a script a new object of class::Error has to be created.
 */
class Error {
	/**
	 * Force log file to be written
	 * @var boolean
	 */
	private static $FORCE_LOG_FILE = false;

	/**
	 * Maximum number of errors to stop
	 * @var int
	 */
	public static $MAX_NUM_OF_ERRORS = 100;

	/**
	 * Internatl instance pointer
	 * @var Error
	 */
	private static $instance = null;

	/**
	 * Array of strings with all errors
	 * @var array
	 */
	private $errors = array();

	/**
	 * Number of arrays
	 * @var array
	 */
	private $numErrors = 0;

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
	 * Boolean flag: Has the footer been sent?
	 * @var bool
	 */
	public $footer_sent = false;

	/**
	 * Boolean flag: Has the debug been displayed?
	 * @var bool
	 */
	public $debug_displayed = false;

	/**
	 * Static getter for the singleton instnace
	 * @return Error
	 */
	public static function getInstance() {
		if (self::$instance === null)
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
	public function __destruct() {
		$this->display();
	}

	/**
	 * Prohibit cloning
	 */
	private function __clone() {}

	/**
	 * To initialise for this class.
	 * @param string $file       filename
	 * @param bool   $log        Logging errors?
	 * @param string $log_file   File for logging errors
	 */
	public static function init($file = __FILE__, $log = true, $log_file = '') {
		self::getInstance()->setLogVars($log, $log_file, $file);
	}

	/**
	 * Set private variables from self::init()
	 * @param bool   $log        Logging errors?
	 * @param string $log_file   File for logging errors
	 * @param string $file       filename
	 */
	public function setLogVars($log, $log_file = '', $file = '') {
		if ($file != '' && $this->file == '')
			$this->file = $file;

		if ($log_file == '') {
			$log_file = 'data/log/'.self::getFilenameFromPath($this->file).'.log.'.date("Ymd.Hi").'.html';
			$log_file = str_replace(array('?', '&'), array('-', '-'), $log_file);
		}

		$this->log = $log;
		$this->log_file = $log_file;
	}

	/**
	 * Get only filename from path
	 * @param string $path
	 * @return string
	 */
	private function getFilenameFromPath($path) {
		$split = explode('/', $path);
		$split = explode('\\', end($split));
		return end($split);
	}

	/**
	 * Prints all errors to screen or into the log-file
	 */
	public function display() {
		if ($this->debug_displayed || !$this->hasErrors())
			return;

		if (defined('RUNALYZE_TEST'))
			$this->displayErrorsForUnitTest();
		elseif ($this->log || self::$FORCE_LOG_FILE)
			\Filesystem::writeFile('../'.$this->log_file, $this->getErrorTable());

		$this->debug_displayed = true;
	}

	/**
	 * Are errors reported?
	 * @return bool
	 */
	public function hasErrors() {
		return !empty($this->errors);
	}

	/**
	 * Display error messages for unit test
	 */
	private function displayErrorsForUnitTest() {
		echo $this->getErrorTable();
	}

	/**
	 * Get table for displaying all errors
	 * @return string
	 */
	private function getErrorTable() {
		$string = NL.NL.'===== '.count($this->errors).' ERROR MESSAGES: ====='.NL;

		foreach ($this->errors as $error) {
			$string .= '=== '.$error['type'].': '.$error['message'].NL.NL;
		}

		return $string;
	}

	/**
	 * Adds a new message to the array of errors
	 * @param string  $type      type of error (ERROR | WARNING | NOTICE | Unknown error type)
	 * @param string $message   error message
	 * @param string $file      file containing the error
	 * @param int    $line      line number containing the error
	 */
	public function add($type, $message, $file = '', $line = -1) {
		$this->numErrors++;

		if ($file != '') {
			$message .= ' (in '.$file;
			if ($line != -1)
				$message .= '::'.$line;
			$message .= ')';
		}

		if ($this->numErrors >= self::$MAX_NUM_OF_ERRORS && !defined('RUNALYZE_TEST')) {
			$this->errors[] = array('type' => 'ERROR', 'message' => 'FATAL ERROR: TOO MANY ERRORS.');
			$this->display();
			exit();
		}

		$this->errors[] = array('type' => $type, 'message' => $message);
	}

	/**
	 * Add an error to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addError($message, $file = '', $line = -1) {
		$this->add('ERROR', self::formErrorMessage($message, debug_backtrace()), $file, $line);
	}

	/**
	 * Add a warning to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addWarning($message, $file = '', $line = -1) {
		$this->add('WARNING', self::formErrorMessage($message, debug_backtrace()), $file, $line);
	}

	/**
	 * Add a debug-info to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addDebug($message, $file = '', $line = -1) {
		$this->add('DEBUG', $message, $file, $line);
	}

	/**
	 * Form an error-message with backtrace-info
	 * @param string $message
	 * @param array $backtrace
	 * @return string
	 */
	private function formErrorMessage($message, $backtrace) {
		$trace = '';
		foreach ($backtrace as $i => $part) {
			if (!isset($part['args']))
				$args = '';
			elseif (is_array($part['args']))
				$args = self::r_implode(', ', $part['args']);

			$class = isset($part['class']) ? $part['class'].'::' : '';
			$func  = isset($part['function']) ? $part['function'] : '';
			if ($i != 0) {
				if (isset($part['file'])) {
					$trace .= $part['file'];
					if (isset($part['line']))
						$trace .= '::'.$part['line'].NL;
				}
				$trace .= $class.$func.' ('.$args.')'.NL.NL;
			}
		}

		return $message.NL.$trace;
	}

	/**
	 * Implode for a multidimensional array
	 * @param string $glue
	 * @param array $pieces
	 * @return string
	 */
	public static function r_implode($glue, $pieces) {
		$retVal = array();

		foreach ($pieces as $r_pieces) {
			if (is_object($r_pieces)) {
				$retVal[] = 'Object';
				break;
			}

			$retVal[] = is_array($r_pieces) ? self::r_implode($glue, $r_pieces) : $r_pieces;
		}

	  	return implode($glue, $retVal);
	}
}

if (defined('RUNALYZE'))
	set_error_handler('Runalyze\error_handler');

/**
 * Own function to handle the errors using class::Error.
 * @param string $type      type of error (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)
 * @param string $message   error message
 * @param string $file      filename
 * @param int $line      line number
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

    return true;
}

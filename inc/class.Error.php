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
	 * Boolean flag: Has the header been sent?
	 * @var bool
	 */
	public $header_sent = false;

	/**
	 * Boolean flag: Has the footer been sent?
	 * @var bool
	 */
	public $footer_sent = false;

	/**
	 * Static getter for the singleton instnace
	 * @return Error
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
	 * @param string $log_file   File for logging errors
	 */
	public static function init($file = __FILE__, $log = false, $log_file = '') {
		if ($log_file == '')
			$log_file = 'log/'.$file.'.log.'.date("Ymd.Hi").'.html';

		self::getInstance()->setLogVars($log, $log_file);
	}

	/**
	 * Set private variables from self::init()
	 * @param bool   $log        Logging errors?
	 * @param string $log_file   File for logging errors
	 */
	public function setLogVars($log, $log_file) {
		$this->log = $log;
		$this->log_file = $log_file;
	}

	/**
	 * Prints all errors to screen or into the log-file
	 */
	public function display() {
		if (!$this->log) {
			echo $this->getErrorTable();
		} else {
			$handle = fopen($this->log_file, 'w+');
			fwrite($handle, $this->getErrorTable());
			fclose($handle);
		}
	}

	/**
	 * Get table for displaying all errors
	 * @return string
	 */
	private function getErrorTable() {
		$table = '<table style="width:90%;margin:0;">';
		foreach ($this->errors as $error)
			$table .= '<tr><td class="b">'.$error['type'].'</td><td>'.$error['message'].'</td></tr>';

		$table .= '</table>';

		return $table;
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
	 * Add a warning to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addNotice($message, $file = '', $line = -1) {
		$this->add('NOTICE', $message, $file, $line);
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

	/**
	 * Add a debug-info to error list
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function addDebug($message, $file = '', $line = -1) {
		$this->add('debug', $message, $file, $line);
	}

	/**
	 * Form an error-message with backtrace-info
	 * @param string $message
	 * @param array $backtrace
	 * @return string
	 */
	private function formErrorMessage($message, $backtrace) {
		$id = md5($message);
		$trace = '';
		foreach ($backtrace as $i => $part) {
			if (!isset($part['args']))
				$args = '';
			if (is_array($part['args']))
				$args = self::r_implode(', ', $part['args']);

			$class = isset($part['class']) ? $part['class'].'::' : '';
			if ($i != 0) {
				$trace .= $part['file'].'<small>::'.$part['line'].'</small><br />';
				$trace .= '<strong>'.$class.$part['function'].'</strong>';
				$trace .= '<small>('.$args.')</small><br /><br />';
			}
		}

		if (class_exists('Ajax'))
			$message = Ajax::toggle('<a class="error" href="#errorInfo">&raquo;</a>', $id).' '.$message;
		$message .= '<div id="'.$id.'" class="hide"><br />'.$trace.'</div>';

		return $message;
	}

	/**
	 * Display an error message causing a fatal error
	 * @param string $message
	 */
	public function displayFatalErrorMessage($message) {
		if (!$this->header_sent)
			include('tpl/tpl.Frontend.header.php');

		echo '<div class="panel">';
		echo '<h1>Fataler Fehler</h1>';
		echo $message;
		echo '</div>';
		
		if (!$this->footer_sent)
			include('tpl/tpl.Frontend.footer.php');

		exit();
	}

	/**
	 * Implode for a multidimensional array
	 * @param string $glue
	 * @param array $pieces
	 * @return string
	 */
	public static function r_implode($glue, $pieces) {
		$retVal = array();

		foreach ($pieces as $r_pieces)
			$retVal[] = is_array($r_pieces) ? self::r_implode($glue, $r_pieces) : $r_pieces;

	  	return implode($glue, $retVal);
	}
}

set_error_handler("error_handler");
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
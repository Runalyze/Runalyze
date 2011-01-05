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
 * Last modified 2010/08/08 21:34 by Hannes Christiansen
 */
class Error {
	private $errors = array(),
		$file = '',
		$log = false,
		$log_file = '';

	/**
	 * Constructor for this class.
	 * @param string $file       filename
	 * @param bool   $log        Logging errors?
	 * @param string $log_path   Path for logging errors
	 */
	function __construct($file = __FILE__, $log = false, $log_file = '') {
		if ($log_file == '')
			$log_file = 'log/'.$file.'.log.'.date("Ymd.Hi").'.html';

		$this->log = $log;
		$this->log_file = $log_file;
	}

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
	 * Adds a new message to the array of errors
	 * @param const  $type      type of error (ERROR | WARNING | NOTICE | Unknown error type)
	 * @param string $message   error message including file and line number
	 * @param string $file      file containing the error
	 * @param int    $line      line number containing the error
	 */
	function add($type, $message, $file = '', $line = '') {
		if ($file != '') {
			$message .= ' (in '.$file;
			if ($line != '')
				$message .= '::'.$line;
			$message .= ')';
		}
		
		$this->errors[] = '<strong>'.$type.'</strong> '.$message;
	}

	/**
	 * Prints all errors to screen or into the log-file
	 */
	function display() {
		if (!$this->log)
			print implode('<br />', $this->errors);
		else {
			$handle = fopen($this->log_file, 'w+');
			fwrite($handle, implode('<br />', $this->errors));
			fclose($handle);
		}
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
	global $error;

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

	$error->add($type, $message, $file, $line);

    // Don't execute PHP internal error handler
    return true;
}
?>
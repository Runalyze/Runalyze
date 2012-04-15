<?php
class System {
	/**
	 * Is a connection to database possible?
	 * @return boolean
	 */
	static public function hasDatabaseConnection() {
		// TODO
		return mysql_ping();
	}

	/**
	 * Send an email via smtp
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @return boolean 
	 */
	static public function sendMail($to, $subject, $message) {
		//ini_set('SMTP', "smtp.runalyze.de");
		$header = "From: Runalyze <mail@runalyze.de>\nMIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\n";

		return mail($to, $subject, $message, $header);
	}

	/**
	 * Set memory- and time-limit as high as possible 
	 */
	static public function setMaximalLimits() {
		ini_set('memory_limit', '-1');
		set_time_limit(0);
	}

	/**
	 * Get domain where Runalyze is running
	 * @return string
	 */
	static public function getDomain() {
		if (!isset($_SERVER['HTTP_HOST']))
			return '';

		return 'http://'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Get full domain
	 * @return string
	 */
	static public function getFullDomain() {
		return self::getDomain().substr($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], "/"))."/";
	}

	/**
	 * Is this script running on localhost?
	 * @return boolean
	 */
	static public function isAtLocalhost() {
		if (!isset($_SERVER['SERVER_NAME']))
			return false;

		return $_SERVER['SERVER_NAME'] == 'localhost';
	}
}
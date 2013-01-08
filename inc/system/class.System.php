<?php
class System {
	/**
	 * Get code to include all JS-files
	 * @return string 
	 */
	static public function getCodeForAllJSFiles() {
		return '<script type="text/javascript" src="'.Request::getProtocol().'://maps.google.com/maps/api/js?sensor=false"></script>
	<script type="text/javascript" src="lib/min/g=js"></script>';
	}

	/**
	 * Get code to include all CSS-files
	 * @return string 
	 */
	static public function getCodeForAllCSSFiles() {
		return '<link rel="stylesheet" type="text/css" href="lib/min/g=css" />';
	}

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
		$header = "From: Runalyze <mail@runalyze.de>\nMIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\n";

		return mail($to, $subject, $message, $header);
	}

	/**
	 * Set memory- and time-limit as high as possible 
	 */
	static public function setMaximalLimits() {
		@ini_set('memory_limit', '-1');

		if (!ini_get('safe_mode'))
			set_time_limit(0);
	}

	/**
	 * Get domain where Runalyze is running
	 * @return string
	 */
	static public function getDomain() {
		if (!isset($_SERVER['HTTP_HOST']))
			return '';

		return Request::getProtocol().'://'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Get full domain
	 * @param boolean $onlyToRunalyze
	 * @return string
	 */
	static public function getFullDomain($onlyToRunalyze = true) {
		$path = self::getDomain().substr($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], "/"))."/";

		if ($onlyToRunalyze) {
			$path = str_replace(array('call/', 'inc/', 'tpl/'), array('', '', ''), $path);
		}

		return $path;
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
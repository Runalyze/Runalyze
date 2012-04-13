<?php
class System {
	/**
	 * Is a connection to database possible?
	 * @return boolean
	 */
	static public function hasDatabaseConnection() {
		// TODO
		return true;
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
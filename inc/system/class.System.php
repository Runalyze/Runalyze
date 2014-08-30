<?php
/**
 * This file contains class::System
 * @package Runalyze\System
 */
/**
 * System
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class System {
	/**
	 * Get code to include all local JS-files
	 * @return string 
	 */
	static public function getCodeForLocalJSFiles() {
		//if (self::isAtLocalhost())
		//	return '<script src="build/scripts.js"></script>';
		//else
		//	return '<script src="build/scripts.min.js"></script>';

		return '<script src="lib/min/?g=js"></script>';
	}

	/**
	 * Get code to include all external JS-files
	 * @return string 
	 */
	static public function getCodeForExternalJSFiles() {
		return '';
	}

	/**
	 * Get code to include all CSS-files
	 * @return string 
	 */
	static public function getCodeForAllCSSFiles() {
		return '<link rel="stylesheet" href="lib/less/runalyze-style.css">';
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
		$sender = defined('MAIL_SENDER') ? MAIL_SENDER : 'Runalyze <mail@runalyze.de>';
		$header = "From: ".$sender."\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";

		return mail($to, $subject, $message, $header);
	}

	/**
	 * Set memory- and time-limit as high as possible 
	 */
	static public function setMaximalLimits() {
		@ini_set('memory_limit', '-1');

		if (!ini_get('safe_mode'))
			set_time_limit(0);

		DB::getInstance()->stopAddingAccountID();
		DB::getInstance()->exec('SET GLOBAL max_allowed_packet=536870912;');
		DB::getInstance()->exec('SET GLOBAL key_buffer_size=536870912;');
		DB::getInstance()->startAddingAccountID();
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

	/**
	 * Clear complete cache 
	 */
	static public function clearCache() {
		self::clearTrainingCache();
	}

	/**
	 * Clear training cache 
	 */
	static public function clearTrainingCache() {
		DB::getInstance()->exec('UPDATE '.PREFIX.'training SET gps_cache_object=""');
	}
}
<?php
/**
 * This file contains class::Request
 * @package Runalyze\System
 */
/**
 * Request
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class Request {
	/**
	 * Get requested URI
	 * @return string
	 */
	public static function Uri() {
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	}

	/**
	 * Get requested script name
	 * @return string
	 */
	public static function ScriptName() {
		return isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
	}

	/**
	 * Get requested filename
	 * @return string
	 */
	public static function Basename() {
		return basename(self::Uri());
	}

	/**
	 * Get current folder of request
	 * @return string
	 */
	public static function CurrentFolder() {
		return basename(dirname(self::Uri()));
	}

	/**
	 * Is the user on a shared page?
	 * @return boolean
	 */
	public static function isOnSharedPage() {
		return SharedLinker::isOnSharedPage();
	}

	/**
	 * Was the request an AJAX-request?
	 * Be careful: Does not work if a file is sent via jQuery!
	 * @return boolean
	 */
	public static function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/**
	 * Is request HTTPS?
	 * @return boolean
	 */
	public static function isHttps() {
		return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
	}

	/**
	 * Get protocol (http/https)
	 * @return string
	 */
	public static function getProtocol() {
		if (self::isHttps())
			return 'https';

		return 'http';
	}

	/**
	 * Get ID send as post or get
	 * @return mixed
	 */
	public static function sendId() {
		if (isset($_GET['id']))
			return $_GET['id'];
		if (isset($_POST['id']))
			return $_POST['id'];

		return false;
	}

	/**
	 * Get parameter send via GET or POST
	 * @param string $key
	 * @return string 
	 */
	public static function param($key) {
		if (isset($_GET[$key]))
			return $_GET[$key];
		if (isset($_POST[$key]))
			return $_POST[$key];

		return '';
	}
}

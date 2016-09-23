<?php
/**
 * This file contains class::System
 * @package Runalyze\System
 */

use Runalyze\Error;

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
	public static function getCodeForLocalJSFiles() {
		if (self::isAtLocalhost()) {
			return '<script>document.addEventListener("touchstart", function(){}, true);</script>'.
				'<script src="assets/js/scripts.js?v='.RUNALYZE_VERSION.'"></script>';
		}

		return '<script>document.addEventListener("touchstart", function(){}, true);</script>'.
			'<script src="assets/js/scripts.min.js?v='.RUNALYZE_VERSION.'"></script>';
	}

	/**
	 * Get code to include all external JS-files
	 * @return string
	 */
	public static function getCodeForExternalJSFiles() {
		return '';
	}

	/**
	 * Get code to include all CSS-files
	 * @return string
	 */
	public static function getCodeForAllCSSFiles() {
		return '<link rel="stylesheet" href="assets/css/runalyze-style.css?v='.RUNALYZE_VERSION.'">';
	}

	/**
	 * Set memory- and time-limit as high as possible
	 */
	public static function setMaximalLimits() {
		@ini_set('memory_limit', '-1');

		if (!ini_get('safe_mode')) {
			set_time_limit(0);
		}
	}

	/**
	 * Get domain where Runalyze is running
	 * @return string
	 */
	public static function getDomain() {
		if (!isset($_SERVER['HTTP_HOST'])) {
			return '';
		}

		return '//'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Get full domain
	 * @param boolean $onlyToRunalyze
	 * @return string
	 */
	public static function getFullDomain($onlyToRunalyze = true) {
		// TODO: correct handling of /web

		$path = self::getDomain().substr($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], "/"))."/";

		if ($onlyToRunalyze) {
			$path = str_replace(array('call/', 'inc/', 'tpl/'), array('', '', ''), $path);
		}

		return $path;
	}

	/**
	 * Get full domain with protocol
	 * @return string
	 */
	public static function getFullDomainWithProtocol() {
		return Request::getProtocol().':'.self::getFullDomain();
	}

	/**
	 * Is this script running on localhost?
	 * @return boolean
	 */
	public static function isAtLocalhost() {
		if (!isset($_SERVER['SERVER_NAME'])) {
			return false;
		}

		return $_SERVER['SERVER_NAME'] == 'localhost';
	}
}

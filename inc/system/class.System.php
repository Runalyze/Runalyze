<?php
/**
 * This file contains class::System
 * @package Runalyze\System
 */

/**
 * System
 * @author Hannes Christiansen
 * @package Runalyze\System
 * @deprecated since v3.1
 */
class System {
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

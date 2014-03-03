<?php
/**
 * This file contains class::Language
 * @package Runalyze\System
 */
/**
 * Language class
 * @author Michael Pohl
 * @package Runalyze\System
 */
class Language {
	/**
	 * Locale dir
	 * @var string
	 */
	static private $localedir = './inc/locale';

	/**
	 * Constructor
	 */
	public function __construct() {
		putenv("LANG=$language"); 
		setlocale(LC_ALL, $language);
		$domain = 'runalyze';
		bindtextdomain('runalyze', $this->localdir); 
		textdomain('runalyze');
	}

	/**
	 * Available languages
	 * @return string
	 */
	public function availableLanguages() {
		$languages = array(
			'de'	=> 'German'
		);

		return $languages;
	}

	/**
	 * Add text domain
	 * @param string $domainname
	 * @param string $dir
	 */
	static public function addTextDomain($domainname, $dir) {
		bindtextdomain($domainname, $dir);
	}

	/**
	 * Set Language for user
	 * @return boolean
	 */
	public function setLanguage() {
		// TODO

		return true;        
	}

	/**
	 * Get all available languages
	 * @return array
	 */
	public function getLanguages() {
		// TODO

		return array();
	}

	/**
	 * Returns the translation for a textstring
	 * @param string $text
         * @param string domain
	 */
	static public function __($text, $domain) {
	   return gettext($text);
	}

	/**
	 * Echo the translation for a textstring
	 * @param string $text
         * @param string domain
	 */
	static public function _e($text, $domain) {
	   return gettext($text);
	}

	/**
	 * Return singular/plural translation for a textstring
	 * @param string $text
         * @param string domain
	 */
	static public function _n($msg1, $msg2, $n, $domain) {
	   return ngettext($msg1, $msg2, $n);
	}

	/**
	 * Echo singular/plural translation for a textstring
	 * @param string $text
         * @param string domain
	 */
	static public function _ne($msg1, $msg2, $n, $domain) {
	   return ngettext($msg1, $msg2, $n);
	}

	/**
	 * get browser language
	 * @return type 
	 */
	private function getBrowserLanguage() {
		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
}
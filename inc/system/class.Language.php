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
	static private $LOCALE_DIR = './inc/locale';

	/**
	 * Constructor
	 * @param string $language [optional]
	 * @param string $domain [optional]
	 */
	public function __construct($language = '', $domain = 'runalyze') {
		if (empty($language))
			$language = !empty($_GET['lang']) ? $_GET['lang'] : 'en_US.UTF8';

		putenv("LANG=$language"); 
		setlocale(LC_ALL, $language);
		setlocale(LC_NUMERIC, 'en_US');

		bind_textdomain_codeset($domain, 'UTF-8');

		self::addTextDomain($domain, self::$LOCALE_DIR); 
		textdomain($domain);
	}

	/**
	 * Available languages
	 * @return string
	 */
	static public function availableLanguages() {
		$languages = array(
			'de'	=> 'German',
			'en'	=> 'English'
		);

		return $languages;
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
	 * Add text domain
	 * @param string $domainname
	 * @param string $dir
	 */
	static public function addTextDomain($domainname, $dir) {
		bindtextdomain($domainname, $dir);
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

/**
 * Returns the translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 */
function __($text, $domain = 'runalyze') {
    return Language::__($text, $domain);
}

/**
 * Echo the translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 */
function _e($text, $domain = 'runalyze') {
    echo Language::_e($text, $domain);
}

/**
 * Return singular/plural translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 */
function _n($msg1, $msg2, $n, $domain = 'runalyze') {
    return Language::_n($msg1, $msg2, $n, $domain);
}

/**
 * Echo singular/plural translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 */
function _ne($msg1, $msg2, $n, $domain = 'runalyze') {
    echo Language::_ne($msg1, $msg2, $n, $domain);
}
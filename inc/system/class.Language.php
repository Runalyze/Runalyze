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

    static private $CURRENT_LANG = 'en';

	/**
	 * Constructor
	 * @param string $language [optional]
	 * @param string $domain [optional]
	 */
	public function __construct($language = '', $domain = 'runalyze') {
		self::$LOCALE_DIR = __DIR__.'/../locale';

		if (empty($language)) {
			$language = $this->guessLanguage();
		}

        $languagesSupported=Language::availableLanguages();
        $locale = $languagesSupported[$language][1];

        self::$CURRENT_LANG=$language;

		putenv("LANG=$locale");
		setlocale(LC_ALL, $locale);
		setlocale(LC_NUMERIC, 'C');

		bind_textdomain_codeset($domain, 'UTF-8');

		self::addTextDomain($domain, self::$LOCALE_DIR); 
		textdomain($domain);
	}

    /**
     * Return currently selected language
     * @return string
     */

    public static function getCurrentLanguage()
    {
        return self::$CURRENT_LANG;
    }


    /**
	 * Guess language
	 * Based on get parameter or language accepted by the browser
	 * @return string
	 */
	protected function guessLanguage() {
        $languagesSupported = Language::availableLanguages();

		if (isset($_GET['lang'])) {     //try to set language from GET if supported
            $preferredLanguage=$_GET['lang'];
            if (isset($languagesSupported[$preferredLanguage])){
                setcookie('lang', $preferredLanguage);
                return $preferredLanguage;
            }
		}

        if (isset($_COOKIE['lang'])){   //try to set language from COOKIE
            $preferredLanguage=$_COOKIE['lang'];
            return $preferredLanguage;
        }


		$preferredLanguage = $this->getBrowserLanguage($languagesSupported);   //set language from browser
        setcookie('lang', $preferredLanguage);
		return $preferredLanguage;

	}

    function getBrowserLanguage($supported, $default = 'en')
    {
        $supp = array();
        foreach ($supported as $lang => $isSupported) {
            if ($isSupported) {
                $supp[strtolower($lang)] = $lang;
            }
        }
        if (!count($supp)) {
            return $default;
        }
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $match = $this->matchAccept(
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $supp
            );
            if (!is_null($match)) {
                return $match;
            }
        }
        return $default;
    }
    /**
     * Parses a weighed "Accept" HTTP header and matches it against a list
     * of supported options
     *
     * @param string $header The HTTP "Accept" header to parse
     * @param array $supported A list of supported values
     *
     * @return string|NULL a matched option, or NULL if no match
     */

    function matchAccept($header, $supported)
    {
        $matches = $this->sortAccept($header);
        foreach ($matches as $key => $q) {
            if (isset($supported[$key])) {
                return $supported[$key];
            }
        }
// If any (i.e. "*") is acceptable, return the first supported format
        if (isset($matches['*'])) {
            return array_shift($supported);
        }
        return null;
    }
	/**
     * Parses and sorts a weighed "Accept" HTTP header
     *
     * @param string $header The HTTP "Accept" header to parse
     *
     * @return array Sorted list of "accept" options
     */

    function sortAccept($header)
    {
        $matches = array();
        foreach (explode(',', $header) as $option) {
            $option = array_map('trim', explode(';', $option));
            $l = strtolower($option[0]);
            if (isset($option[1])) {
                $q = (float) str_replace('q=', '', $option[1]);
            } else {
                $q = null;
// Assign default low weight for generic values
                if ($l == '*/*') {
                    $q = 0.01;
                } elseif (substr($l, -1) == '*') {
                    $q = 0.02;
                }
            }
// Unweighted values, get high weight by their position in the
// list
            $matches[$l] = isset($q) ? $q : 1000 - count($matches);
        }
        foreach ($matches as $k => $v){
            if (strlen($k)>2){
                $gen_lang=substr($k, 0, 2);
                if (!isset ($matches[$gen_lang])){
                    $matches[$gen_lang]=$v-0.001;
                }
            }
        }
        arsort($matches, SORT_NUMERIC);

        return $matches;
    }

    /**
	 * Available languages
	 * @return string
	 */
	static public function availableLanguages() {
        $supportedLanguages=array();
        require __DIR__.'/../../config_lang.php';
		return $supportedLanguages;
	}

	/**
	 * Set Language for user
	 * @return boolean
	 */
	static public function setLanguage($language) {
		setcookie('lang', $language);
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
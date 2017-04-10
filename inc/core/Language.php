<?php
/**
 * This file contains class::Language
 * @package Runalyze
 */

namespace Runalyze;
use Runalyze\Util\InterfaceChoosable;
use Symfony\Component\Yaml\Yaml;

/**
 * Language class
 * @author Michael Pohl
 * @package Runalyze
 */
class Language implements InterfaceChoosable
{
    /** @var string */
    const GET_KEY = 'lang';

    /** @var string */
    const COOKIE_KEY = 'lang';

    /**
     * Locale dir
     * @var string
     */
    private static $LOCALE_DIR = './inc/locale';

    /**
     * Current language
     * @var string
     */
    private static $CURRENT_LANG = 'en';

    /**
     * Available languages
     * @var array|null
     */
    private static $AVAILABLE_LANGUAGES = null;

    /**
     * Constructor
     * @param string $language [optional]
     * @param string $domain [optional]
     */
    public function __construct($language = '', $domain = 'runalyze')
    {
        $this->setLocaleDir();

        self::readAvailableLanguages();

        if (empty($language)) {
            $language = $this->guessLanguage();
        }

        self::setLanguage($language);

        $this->setDomain($domain);
    }

    /**
     * @return array
     */
    public static function getChoices() {
        $languages = [];

        foreach (self::availableLanguages() as $name => $lang) {
            $languages[$lang[0]] = $name;
        }

        return $languages;
    }

    /**
     * Set locale dir
     */
    protected function setLocaleDir()
    {
        self::$LOCALE_DIR = __DIR__.'/../locale';
    }

    /**
     * Set Language for user
     * @param string $language
     * @param boolean $overwriteGetParameter [optional]
     * @return boolean
     */
    public static function setLanguage($language, $overwriteGetParameter = true)
    {
        $supportedLanguages = self::availableLanguages();

        if (!isset($supportedLanguages[$language])) {
            return false;
        }

        if (!$overwriteGetParameter && isset($_GET[self::GET_KEY]) && isset($supportedLanguages[$_GET[self::GET_KEY]])) {
            return false;
        }

        $locale = $supportedLanguages[$language][1];

        self::$CURRENT_LANG = $language;

        putenv("LANG=$locale");
        setlocale(LC_ALL, $locale);
        setlocale(LC_NUMERIC, 'C');

        if (!headers_sent() && (!isset($_COOKIE[self::COOKIE_KEY]) || $_COOKIE[self::COOKIE_KEY] != $language)) {
            setcookie(self::COOKIE_KEY, $language);
        }

        return true;
    }

    /**
     * Set domain
     * @param string $domain
     */
    protected function setDomain($domain)
    {
        bind_textdomain_codeset($domain, 'UTF-8');

        bindtextdomain($domain, self::$LOCALE_DIR);
        textdomain($domain);
    }

    /**
     * Read available languages
     */
    private static function readAvailableLanguages()
    {
        $languages = [];
        $config = Yaml::parse(file_get_contents(__DIR__.'/../../app/config/languages.yml'));

        if (is_array($config) && isset($config['parameters']) && isset($config['parameters']['locales'])) {
            foreach ($config['parameters']['locales'] as $short => $lang) {
                $languages[$short] = array($lang['name'], $lang['locale']);
            }
        }

        self::$AVAILABLE_LANGUAGES = $languages;
    }

    /**
     * Available languages
     * @return array
     */
    public static function availableLanguages()
    {
        if (null === self::$AVAILABLE_LANGUAGES) {
            self::readAvailableLanguages();
        }

        return self::$AVAILABLE_LANGUAGES;
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
    protected function guessLanguage()
    {
        if (isset($_GET[self::GET_KEY])) {
            if (isset(self::$AVAILABLE_LANGUAGES[$_GET[self::GET_KEY]])) {
                return $_GET[self::GET_KEY];
            }
        }

        if (isset($_COOKIE[self::COOKIE_KEY])) {
            return $_COOKIE[self::COOKIE_KEY];
        }

        return $this->getBrowserLanguage(self::$AVAILABLE_LANGUAGES);
    }

    /**
     * Get browser language
     * @param array $supported
     * @param string $default
     * @return string
     */
    protected function getBrowserLanguage(array $supported, $default = 'en')
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
     * @return string|null a matched option, or null if no match
     */
    protected function matchAccept($header, $supported)
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
    protected function sortAccept($header)
    {
        $matches = array();

        foreach (explode(',', $header) as $option) {
            $option = array_map('trim', explode(';', $option));
            $l = strtolower($option[0]);

            if (isset($option[1])) {
                $q = (float)str_replace('q=', '', $option[1]);
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

        foreach ($matches as $k => $v) {
            if (strlen($k) > 2) {
                $gen_lang = substr($k, 0, 2);

                if (!isset ($matches[$gen_lang])) {
                    $matches[$gen_lang] = $v - 0.001;
                }
            }
        }
        arsort($matches, SORT_NUMERIC);

        return $matches;
    }
}

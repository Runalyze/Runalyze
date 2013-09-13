<?php
/**
 * This file contains class::Helper
 * @package Runalyze
 */
/**
 * Maximal heart-frequence of the user
 * @var HF_MAX
 */
define('HF_MAX', Helper::getHFmax());

/**
 * Heart-frequence in rest of the user
 * @var HF_REST
 */
define('HF_REST', Helper::getHFrest());

/**
 * Timestamp of the first training
 * @var START_TIME
 */
define('START_TIME', Helper::getStartTime());

/**
 * Year of the first training
 * @var START_YEAR
 */
define('START_YEAR', date("Y", START_TIME));

require_once FRONTEND_PATH.'calculate/class.JD.php';

/**
 * Class for all helper-functions previously done by functions.php
 * @author Hannes Christiansen
 * @package Runalyze
 */
class Helper {
	/**
	 * Private constructor
	 */
	private function __construct() {}
	/**
	 * Private destructor
	 */
	private function __destruct() {}

	/**
	 * Trim all values of an array
	 * @param array $array
	 * @return array 
	 */
	public static function arrayTrim($array) {
		array_walk($array, 'trimValuesForArray');

		return $array;
	}

	/**
	 * Round to factor of $roundForInt
	 * @param double $numberToRound
	 * @param int $roundForInt
	 * @return int
	 */
	public static function roundFor($numberToRound, $roundForInt) {
		return $roundForInt * round($numberToRound / $roundForInt);
	}

	/**
	 * Round to the next lowest factor of $roundForInt
	 * @param double $numberToRound
	 * @param int $roundForInt
	 * @return int
	 */
	public static function floorFor($numberToRound, $roundForInt) {
		return $roundForInt * floor($numberToRound / $roundForInt);
	}

	/**
	 * Round to the next highest factor of $roundForInt
	 * @param double $numberToRound
	 * @param int $roundForInt
	 * @return int
	 */
	public static function ceilFor($numberToRound, $roundForInt) {
		return $roundForInt * ceil($numberToRound / $roundForInt);
	}

	/**
	 * Get a leading 0 if $int is lower than 10
	 * @param int $int
	 */
	public static function TwoNumbers($int) {
		return ($int < 10) ? '0'.$int : $int;
	}

	/**
	 * Get a special $string if $var is not set
	 * @param mixed $var
	 * @param string $string string to be displayed instead, default: ?
	 */
	public static function Unknown($var, $string = '?') {
		if ($var == NULL || !isset($var))
			return $string;

		if ((is_numeric($var) && $var != 0) || (!is_numeric($var) && $var != '') )
			return $var;

		return $string;
	}

	/**
	 * Cut a string if it is longer than $cut (default CUT_LENGTH)
	 * @param string $text
	 * @param int $cut [optional]
	 */
	public static function Cut($text, $cut = 0) {
		if ($cut == 0)
			$cut = CUT_LENGTH;

		if (mb_strlen($text) >= $cut)
			return Ajax::tooltip(mb_substr($text, 0, $cut-3).'...', $text);

		return $text;
	}

	/**
	 * Replace every comma with a point
	 * @param string $string
	 */
	public static function CommaToPoint($string) {
		return str_replace(",", ".", $string);
	}

	/**
	 * Get const for START_TIME
	 * @return int
	 */
	public static function getStartTime() {
		if (!defined('CONF_START_TIME')) {
			Error::getInstance()->addError('Constant CONF_START_TIME has to be set!');
			define('CONF_START_TIME', 0);
		}

		if (defined('START_TIME'))
			return START_TIME;

		if (CONF_START_TIME == 0)
			return self::recalculateStartTime();

		return CONF_START_TIME;
	}

	/**
	 * Recalculate START_TIME
	 */
	public static function recalculateStartTime() {
		$START_TIME = self::calculateStartTime();

		ConfigValue::update('START_TIME', $START_TIME);

		if ($START_TIME == 0)
			return time();

		return $START_TIME;
	}

	/**
	 * Get timestamp of first training
	 * @return int   Timestamp
	 */
	private static function calculateStartTime() {
		$data = Mysql::getInstance()->fetch('SELECT MIN(`time`) as `time` FROM `'.PREFIX.'training`');

		if (isset($data['time']) && $data['time'] == 0) {
			$data = Mysql::getInstance()->fetch('SELECT MIN(`time`) as `time` FROM `'.PREFIX.'training` WHERE `time` != 0');
			Error::getInstance()->addWarning('Du hast ein Training ohne Zeitstempel, also mit dem Datum 01.01.1970.');
		}

		if ($data === false || $data['time'] == null)
			return 0;

		return $data['time'];
	}

	/**
	 * Recalculate HF_MAX and HF_REST
	 */
	public static function recalculateHFmaxAndHFrest() {
		self::recalculateHFmax();
		self::recalculateHFrest();
	}

	/**
	 * Get const for HF_MAX
	 * @return int
	 */
	public static function getHFmax() {
		if (!defined('CONF_HF_MAX')) {
			Error::getInstance()->addError('Constant CONF_HF_MAX has to be set!');
			define('CONF_HF_MAX', 200);
		}

		if (defined('HF_MAX'))
			return HF_MAX;

		return CONF_HF_MAX;
	}

	/**
	 * Recalculate HF_MAX
	 */
	public static function recalculateHFmax() {
		$HF_MAX = self::calculateHFmax();

		ConfigValue::update('HF_MAX', $HF_MAX);

		return $HF_MAX;
	}

	/**
	 * Get the HFmax from user-table
	 * @return int   HFmax
	 */
	private static function calculateHFmax() {
		// TODO: Move to class::UserData - possible problem in loading order?
		if (SharedLinker::isOnSharedPage()) {
			$userdata = Mysql::getInstance()->fetchSingle('SELECT `pulse_max` FROM `'.PREFIX.'user` WHERE `accountid`="'.SharedLinker::getUserId().'" ORDER BY `time` DESC');
		} else {
			$userdata = Mysql::getInstance()->fetchSingle('SELECT `pulse_max` FROM `'.PREFIX.'user` ORDER BY `time` DESC');
		}

		if ($userdata === false || $userdata['pulse_max'] == 0)
			return 200;

		return $userdata['pulse_max'];
	}

	/**
	 * Get const for HF_REST
	 * @return int
	 */
	public static function getHFrest() {
		if (!defined('CONF_HF_REST')) {
			Error::getInstance()->addError('Constant CONF_HF_REST has to be set!');
			define('CONF_HF_REST', 200);
		}

		if (defined('HF_REST'))
			return HF_REST;

		return CONF_HF_REST;
	}

	/**
	 * Recalculate HF_REST
	 */
	public static function recalculateHFrest() {
		$HF_REST = self::calculateHFrest();

		ConfigValue::update('HF_REST', $HF_REST);

		return $HF_REST;
	}

	/**
	 * Get the HFrest from user-table
	 * @return int   HFrest
	 */
	private static function calculateHFrest() {
		// TODO: Move to class::UserData - possible problem in loading order?
		if (SharedLinker::isOnSharedPage()) {
			$userdata = Mysql::getInstance()->fetchSingle('SELECT `pulse_rest` FROM `'.PREFIX.'user` WHERE `accountid`="'.SharedLinker::getUserId().'" ORDER BY `time` DESC');
		} else {
			$userdata = Mysql::getInstance()->fetchSingle('SELECT `pulse_rest` FROM `'.PREFIX.'user` ORDER BY `time` DESC');
		}

		if ($userdata === false)
			return 60;

		return $userdata['pulse_rest'];
	}
}

/**
 * Load a given XML-string with simplexml, correcting encoding
 * @param string $Xml
 * @return SimpleXMLElement
 */
function simplexml_load_string_utf8($Xml) {
	return simplexml_load_string(simplexml_correct_ns($Xml));
}

/**
 * Correct namespace for using xpath in simplexml
 * @param string $string
 * @return string
 */
function simplexml_correct_ns($string) {
	return str_replace('xmlns=', 'ns=', removeBOMfromString($string));
}

/**
 * Remove leading BOM from string
 * @param string $string
 * @return string
 */
function removeBOMfromString($string) {
	return mb_substr($string, mb_strpos($string, "<"));
}

/**
 * Trimmer function for array_walk
 * @param array $value 
 */
function trimValuesForArray(&$value) {
	$value = trim($value);
}

/**
 * Reverse use of strstr (same as strstr($haystack, $needle, true) for PHP > 5.3.0)
 * @param string $haystack
 * @param string $needle
 * @return string 
 */
function rstrstr($haystack, $needle) {
	return substr($haystack, 0,strpos($haystack, $needle));
}
<?php
/**
 * This file contains class::FormularValueParser
 * @package Runalyze\HTML\Formular\Validation
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\Energy;
use Runalyze\Activity\Weight;
use Runalyze\Activity\Temperature;
use Runalyze\Data\Weather\WindSpeed;

/**
 * @deprecated since v4.3
 */
class FormularValueParser {
	/**
	 * Parser: timestamp <=> date-string
	 * @var string
	 */
	public static $PARSER_DATE = 'date';

	/**
	 * Parser: time in seconds <=> time-string
	 * @var string
	 */
	public static $PARSER_TIME = 'time';

	/**
	 * Parser: check for boolean
	 * @var string
	 */
	public static $PARSER_BOOL = 'bool';

	/**
	 * Validate post-value for a given key with a given parser
	 * @param string $key
	 * @param string $parser
	 * @param array $parserOptions
	 * @return boolean
	 */
	public static function validatePost($key, $parser, $parserOptions = array()) {
		if (is_null($parser))
			return true;

		if (isset($parserOptions['null']) && $parserOptions['null']) {
			if (isset($_POST[$key]) && !is_array($_POST[$key]) && strlen($_POST[$key]) == 0) {
				$_POST[$key] = null;
				return true;
			}
		}

		switch ($parser) {
			case self::$PARSER_DATE:
				return self::validateDate($key);
			case self::$PARSER_TIME:
				return self::validateTime($key, $parserOptions);
			case self::$PARSER_BOOL:
				return self::validateBool($key);

			default:
				return true;
		}
	}

	/**
	 * Transform value with a given parser
	 * @param mixed $value
	 * @param string $parser
	 * @param array $parserOptions
	 */
	public static function parse(&$value, $parser, $parserOptions = array()) {
		if (is_null($parser))
			return;

		switch ($parser) {
			case self::$PARSER_DATE:
				self::parseDate($value);
				break;
			case self::$PARSER_TIME:
				self::parseTime($value, $parserOptions);
				break;
		}
	}

	/**
	 * Validator: boolean
	 * @param string $key
	 * @return boolean
	 */
	private static function validateBool($key) {
		$_POST[$key] = isset($_POST[$key]) ? '1' : '0';

		return true;
	}

	/**
	 * Validator: date-string => timestamp
	 * @param string $key
	 * @return boolean
	 */
	private static function validateDate($key) {
		$dateParts = self::removeEmptyValues(explode('.', $_POST[$key]));
		$numParts  = count($dateParts);

		if ($numParts == 3) {
			$_POST[$key] = mktime(0, 0, 0, (int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2]);
		} elseif ($numParts == 2) {
			$_POST[$key] = mktime(0, 0, 0, (int)$dateParts[1], (int)$dateParts[0], date('Y'));
		} else {
			return __('The date could not be parsed.');
		}

		if ($_POST[$key] > time()) {
			return __('The date must not be in the future.');
		}

		return true;
	}

	/**
	 * Parse: timestamp => date-string
	 * @param string $value
	 */
	private static function parseDate(&$value) {
		if (is_numeric($value))
			$value = date('d.m.Y', $value);
	}

	/**
	 * Validator: time-string => time in seconds
	 * @param string $key
	 * @param array $options
	 * @return boolean
	 */
	private static function validateTime($key, $options) {
		$Time = new Duration($_POST[$key]);

		$_POST[$key] = $Time->seconds();

		if ($_POST[$key] == 0 && (isset($options['required']) || isset($options['notempty'])))
			return __('You have to enter a time.');

		return true;
	}

	/**
	 * Parse: time in seconds => time-string
	 * @param mixed $value
	 * @param array $options
	 */
	private static function parseTime(&$value, $options) {
		if ($value == 0) {
			if (isset($options['hide-empty'])) {
				$value = '';
			} else {
				$value = '0:00:00';
			}
		} else {
			$value = Duration::format($value);
		}
	}

	/**
	 * Remove all empty values from array
	 * @param array $array
	 * @return array
	 */
	private static function removeEmptyValues($array) {
		foreach ($array as $key => $value)
			if (empty($value))
				unset($array[$key]);

		return $array;
	}
}

<?php
/**
 * This file contains class::FormularValueParser
 * @package Runalyze\HTML\Formular\Validation
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\Weight;
use Runalyze\Activity\Temperature;

/**
 * Library with parsers for formular values, default behavior: from user input to database value
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular\Validation
 */
class FormularValueParser {
	/**
	 * Parser: timestamp <=> date-string
	 * @var string 
	 */
	public static $PARSER_DATE = 'date';

	/**
	 * Parser: timestamp <=> daytime-string
	 * @var string 
	 */
	public static $PARSER_DAYTIME = 'daytime';

	/**
	 * Parser: time in seconds <=> time-string
	 * @var string
	 */
	public static $PARSER_TIME = 'time';

	/**
	 * Parser: time in minutes <=> time-string
	 * @var string
	 */
	public static $PARSER_TIME_MINUTES = 'time-minutes';

	/**
	 * Parser: encoded string <=> string
	 * @var string 
	 */
	public static $PARSER_STRING = 'string';

	/**
	 * Parser: check for integer
	 * @var string 
	 */
	public static $PARSER_INT = 'int';

	/**
	 * Parser: check for decimal
	 * @var string 
	 */
	public static $PARSER_DECIMAL = 'decimal';

	/**
	 * Parser: check for boolean
	 * @var string 
	 */
	public static $PARSER_BOOL = 'bool';

	/**
	 * Parser: comma separated string <=> checkboxes
	 * @var string 
	 */
	public static $PARSER_ARRAY_CHECKBOXES = 'array-checkboxes';

	/**
	 * Parser: array with splits <=> string for splits
	 * @var string
	 */
	public static $PARSER_SPLITS = 'splits';

	/**
	 * Parser: weight in kg <=> weight in preferred unit
	 * @var string 
	 */
	public static $PARSER_WEIGHT = 'weight';
        
	/**
	 * Parser: temperature in c <=> temperature in preferred unit
	 * @var string 
	 */
	public static $PARSER_TEMPERATURE = 'temperature';

	/**
	 * Parser: elevation in m <=> elevation in preferred unit
	 * @var string 
	 */
	public static $PARSER_ELEVATION = 'elevation';

	/**
	 * Parser: distance in km <=> distance in preferred unit
	 * @var string 
	 */
	public static $PARSER_DISTANCE = 'distance';

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
			case self::$PARSER_DAYTIME:
				return self::validateDaytime($key);
			case self::$PARSER_TIME:
				return self::validateTime($key, $parserOptions);
			case self::$PARSER_TIME_MINUTES:
				return self::validateTimeMinutes($key, $parserOptions);
			case self::$PARSER_STRING:
				return self::validateString($key, $parserOptions);
			case self::$PARSER_INT:
				return self::validateInt($key, $parserOptions);
			case self::$PARSER_DECIMAL:
				return self::validateDecimal($key, $parserOptions);
			case self::$PARSER_BOOL:
				return self::validateBool($key);
			case self::$PARSER_ARRAY_CHECKBOXES:
				return self::validateArrayCheckboxes($key, $parserOptions);
			case self::$PARSER_SPLITS:
				return self::validateSplits($key, $parserOptions);
			case self::$PARSER_WEIGHT:
				return self::validateWeight($key);
                        case self::$PARSER_TEMPERATURE:
                                return self::validateTemperature($key);
			case self::$PARSER_ELEVATION:
				return self::validateElevation($key);
			case self::$PARSER_DISTANCE:
				return self::validateDistance($key, $parserOptions);
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
			case self::$PARSER_DAYTIME:
				self::parseDaytime($value);
				break;
			case self::$PARSER_TIME:
				self::parseTime($value, $parserOptions);
				break;
			case self::$PARSER_TIME_MINUTES:
				self::parseTimeMinutes($value);
				break;
			case self::$PARSER_ARRAY_CHECKBOXES:
				self::parseArrayCheckboxes($value);
				break;
			case self::$PARSER_SPLITS:
				self::parseSplits($value, $parserOptions);
				break;
			case self::$PARSER_WEIGHT:
				self::parseWeight($value);
				break;
                        case self::$PARSER_TEMPERATURE:
				self::parseTemperature($value);
				break;
			case self::$PARSER_ELEVATION:
				self::parseElevation($value);
				break;
			case self::$PARSER_DISTANCE:
				self::parseDistance($value, $parserOptions);
				break;
		}
	}

	/**
	 * Validator: string => encoded string
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	private static function validateString($key, $options = array()) {
		if (isset($options['notempty']) && $options['notempty'] && strlen($_POST[$key]) == 0)
			return __('The field can not be left empty.');

		return true;
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
	 * Validator: integer
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	private static function validateInt($key, $options) {
		if (!is_numeric($_POST[$key]) || ($_POST[$key]) != (int)$_POST[$key])
			return __('Please enter a number.');

		$_POST[$key] = (int)$_POST[$key];

		if (!self::precisionIsOkay($_POST[$key], $options))
			return __('The value is too large.');

		return true;
	}

	/**
	 * Validator: decimal
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	private static function validateDecimal($key, $options) {
		$_POST[$key] = Helper::CommaToPoint($_POST[$key]);

		if (!is_numeric($_POST[$key]))
			return false;

		$_POST[$key] = (float)$_POST[$key];

		if (!self::precisionIsOkay($_POST[$key], $options))
			return __('The value is too large.');

		return true;
	}

	/**
	 * Check precision of a given value, ignores too much decimal values
	 * @param mixed $value as int/float/double
	 * @param array $options array with key 'precision': as int or string for decimal
	 * @return boolean
	 */
	private static function precisionIsOkay($value, $options) {
		if (!isset($options['precision']))
			return true;

		$precision = explode(',', $options['precision']);

		if (isset($precision[1]))
			$precision[0] -= $precision[1];

		return ($value <= pow(10, $precision[0]));
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
			$_POST[$key] = mktime(0, 0, 0, $dateParts[1], $dateParts[0], $dateParts[2]);
		} elseif ($numParts == 2) {
			$_POST[$key] = mktime(0, 0, 0, $dateParts[1], $dateParts[0], date('Y'));
		} else {
			//$_POST[$key] = time();
			return __('The date could not be parsed.');
		}

		return true;
	}

	/**
	 * Parse: timestamp => date-string
	 * @param type $value 
	 */
	private static function parseDate(&$value) {
		if (is_numeric($value))
			$value = date('d.m.Y', $value);
	}

	/**
	 * Validator: daytime-string => timestamp
	 * @param string $key
	 * @return boolean 
	 */
	private static function validateDaytime($key) {
		$timeParts = self::removeEmptyValues(explode(':', $_POST[$key]));
		$numParts  = count($timeParts);

		if ($numParts == 3) {
			$_POST[$key] = 60*60*$timeParts[0] + 60*$timeParts[1] + $timeParts[2];
		} elseif ($numParts == 2) {
			$_POST[$key] = 60*60*$timeParts[0] + 60*$timeParts[1];
		} else {
			$_POST[$key] = 0;
		}

		if ($numParts == 1 || $numParts > 3 || $_POST[$key] > DAY_IN_S)
			return __('The time could not be read.');

		return true;
	}

	/**
	 * Parse: timestamp => date-string
	 * @param type $value 
	 */
	private static function parseDaytime(&$value) {
		if (is_numeric($value))
			$value = date('H:i', $value);

		if ($value == '00:00')
			$value = '';
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
	 * Validator: time-string => time in minutes
	 * This will transform 6:23 to 6*60 + 23
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	private static function validateTimeMinutes($key, $options) {
		$Time = new Duration($_POST[$key]);

		$_POST[$key] = $Time->seconds();

		if ($_POST[$key] == 0 && (isset($options['required']) || isset($options['notempty'])))
			return __('You have to enter a time.');

		return true;
	}

	/**
	 * Parse: time in seconds => time-string
	 * @param mixed $value 
	 */
	private static function parseTimeMinutes(&$value) {
		if ($value == 0) {
			$value = '0:00';
		} else {
			$duration = new Duration($value*60);
			$value = $duration->string('G:i');
		}
	}

	/**
	 * Validator: checkbox array => comma separated string
	 * @param string $key
	 * @param array $options
	 * @return boolean
	 */
	private static function validateArrayCheckboxes($key, $options) {
		if (!isset($_POST[$key]))
			return true;

		$_POST[$key] = is_array($_POST[$key]) ? implode(',', array_keys($_POST[$key])) : $_POST[$key];

		return true;
	}

	/**
	 * Parse: comma separated string => checkbox array
	 * @param string $value
	 */
	private static function parseArrayCheckboxes(&$value) {
		if (is_array($value))
			return;

		$Array = array();
		$IDs   = explode(',', $value);

		foreach ($IDs as $ID)
			$Array[trim($ID)] = 'on';

		$value = $Array;
	}

	/**
	 * Validator: splits array => splits string
	 * @param string $key
	 * @param array $options
	 * @return boolean
	 */
	private static function validateSplits($key, $options) {
		if (!isset($_POST[$key])) {
			$_POST[$key] = array();
		}

		$Splits = new Splits($_POST[$key], $options);
		$_POST[$key] = $Splits->asString();

		return true;
	}

	/**
	 * Parse: splits string => splits array
	 * @param string $value
	 * @param array $options
	 */
	private static function parseSplits(&$value, $options) {
		$Splits = new Splits($value, $options);
		$value = $Splits->asArray();
	}

	/**
	 * Validator: weight in preferred unit => weight in kg
	 * @param string $key
	 * @return bool
	 */
	private static function validateWeight($key) {
		$_POST[$key] = round((new Weight())->setInPreferredUnit($_POST[$key])->kg(), 2);

		return true;
	}

	/**
	 * Parse: weight in kg => weight in preferred unit
	 * @param mixed $value 
	 */
	private static function parseWeight(&$value) {
		$value = round((new Weight($value))->valueInPreferredUnit(), 2);
	}
        
	/**
	 * Validator: temperature in preferred unit => temperature in °C
	 * @param string $key
	 * @return bool
	 */
	private static function validateTemperature($key) {
		if (trim($_POST[$key]) == '') {
			$_POST[$key] = null;
		} else {
			$_POST[$key] = round((new Temperature())->setInPreferredUnit($_POST[$key])->celsius(), 2);
		}

		return true;
	}

	/**
	 * Parse: temperature in °C => temperature in preferred unit
	 * @param mixed $value 
	 */
	private static function parseTemperature(&$value) {
		$value = (new Temperature($value))->valueInPreferredUnit();
	}
      

	/**
	 * Validator: elevation in preferred unit => elevation in m
	 * @param string $key
	 * @return bool
	 */
	private static function validateElevation($key) {
		$_POST[$key] = round((new Elevation())->setInPreferredUnit($_POST[$key])->meter(), 2);

		return true;
	}

	/**
	 * Parse: elevation in m => elevation in preferred unit
	 * @param mixed $value 
	 */
	private static function parseElevation(&$value) {
		$value = round((new Elevation($value))->valueInPreferredUnit(), 2);
	}

	/**
	 * Validator: distance in preferred unit => distance in km
	 * @param string $key
	 * @param array $parserOptions
	 * @return bool
	 */
	private static function validateDistance($key, array $parserOptions) {
		$decimals = isset($parserOptions['decimals']) ? $parserOptions['decimals'] : 3;
		$decimalPoint = isset($parserOptions['decimal-point']) ? $parserOptions['decimal-point'] : '.';
		$thousandsPoint = isset($parserOptions['thousans-point']) ? $parserOptions['thousans-point'] : '';

		if ($thousandsPoint != '') {
			$_POST[$key] = str_replace($thousandsPoint, '', $_POST[$key]);
			$_POST[$key] = str_replace($decimalPoint, '.', $_POST[$key]);
		} else {
			$_POST[$key] = str_replace(',', '.', $_POST[$key]);
			$_POST[$key] = str_replace($decimalPoint, '.', $_POST[$key]);
		}

		$_POST[$key] = round((new Distance())->setInPreferredUnit($_POST[$key])->kilometer(), $decimals);

		return true;
	}

	/**
	 * Parse: distance in km => distance in preferred unit
	 * @param mixed $value 
	 * @param array $parserOptions
	 */
	private static function parseDistance(&$value, array $parserOptions) {
		$decimals = isset($parserOptions['decimals']) ? $parserOptions['decimals'] : 3;
		$decimalPoint = isset($parserOptions['decimal-point']) ? $parserOptions['decimal-point'] : '.';
		$thousandsPoint = isset($parserOptions['thousans-point']) ? $parserOptions['thousans-point'] : '';

		$value = number_format((new Distance($value))->valueInPreferredUnit(), $decimals, $decimalPoint, $thousandsPoint);
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
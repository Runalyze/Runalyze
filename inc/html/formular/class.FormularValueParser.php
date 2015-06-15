<?php
/**
 * This file contains class::FormularValueParser
 * @package Runalyze\HTML\Formular\Validation
 */

use Runalyze\Activity\Duration;

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
	static public $PARSER_DATE = 'date';

	/**
	 * Parser: timestamp <=> daytime-string
	 * @var string 
	 */
	static public $PARSER_DAYTIME = 'daytime';

	/**
	 * Parser: time in seconds <=> time-string
	 * @var string
	 */
	static public $PARSER_TIME = 'time';

	/**
	 * Parser: time in minutes <=> time-string
	 * @var string
	 */
	static public $PARSER_TIME_MINUTES = 'time-minutes';

	/**
	 * Parser: encoded string <=> string
	 * @var string 
	 */
	static public $PARSER_STRING = 'string';

	/**
	 * Parser: check for integer
	 * @var string 
	 */
	static public $PARSER_INT = 'int';

	/**
	 * Parser: check for decimal
	 * @var string 
	 */
	static public $PARSER_DECIMAL = 'decimal';

	/**
	 * Parser: check for boolean
	 * @var string 
	 */
	static public $PARSER_BOOL = 'bool';

	/**
	 * Parser: comma separated string <=> checkboxes
	 * @var string 
	 */
	static public $PARSER_ARRAY_CHECKBOXES = 'array-checkboxes';

	/**
	 * Parser: array with splits <=> string for splits
	 * @var string
	 */
	static public $PARSER_SPLITS = 'splits';

	/**
	 * Validate post-value for a given key with a given parser
	 * @param string $key
	 * @param enum $parser
	 * @param array $parserOptions
	 * @return boolean 
	 */
	static public function validatePost($key, $parser, $parserOptions = array()) {
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
			default:
				return true;
		}
	}

	/**
	 * Transform value with a given parser
	 * @param mixed $value
	 * @param enum $parser
	 * @param array $parserOptions
	 */
	static public function parse(&$value, $parser, $parserOptions = array()) {
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
				self::parseTime($value);
				break;
			case self::$PARSER_TIME_MINUTES:
				self::parseTimeMinutes($value);
				break;
			case self::$PARSER_ARRAY_CHECKBOXES:
				self::parseArrayCheckboxes($value);
				break;
			case self::$PARSER_SPLITS:
				self::parseSplits($value);
				break;
		}
	}

	/**
	 * Validator: string => encoded string
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	static protected function validateString($key, $options = array()) {
		if (isset($options['notempty']) && $options['notempty'] && strlen($_POST[$key]) == 0)
			return __('The field can not be left empty.');

		return true;
	}

	/**
	 * Validator: boolean
	 * @param string $key
	 * @return boolean 
	 */
	static protected function validateBool($key) {
		$_POST[$key] = isset($_POST[$key]) ? '1' : '0';

		return true;
	}

	/**
	 * Validator: integer
	 * @param string $key
	 * @param array $options
	 * @return boolean 
	 */
	static protected function validateInt($key, $options) {
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
	static protected function validateDecimal($key, $options) {
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
	static protected function precisionIsOkay($value, $options) {
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
	static protected function validateDate($key) {
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
	static protected function parseDate(&$value) {
		if (is_numeric($value))
			$value = date('d.m.Y', $value);
	}

	/**
	 * Validator: daytime-string => timestamp
	 * @param string $key
	 * @return boolean 
	 */
	static protected function validateDaytime($key) {
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
	static protected function parseDaytime(&$value) {
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
	static protected function validateTime($key, $options) {
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
	static protected function parseTime(&$value) {
		if ($value == 0) {
			$value = '0:00:00';
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
	static protected function validateTimeMinutes($key, $options) {
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
	static protected function parseTimeMinutes(&$value) {
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
	static protected function validateArrayCheckboxes($key, $options) {
		if (!isset($_POST[$key]))
			return true;

		$_POST[$key] = is_array($_POST[$key]) ? implode(',', array_keys($_POST[$key])) : $_POST[$key];

		return true;
	}

	/**
	 * Parse: comma separated string => checkbox array
	 * @param string $value
	 */
	static protected function parseArrayCheckboxes(&$value) {
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
	static protected function validateSplits($key, $options) {
		if (!isset($_POST[$key])) {
			$_POST[$key] = array();
		}

		$Splits = new Splits($_POST[$key]);
		$_POST[$key] = $Splits->asString();

		return true;
	}

	/**
	 * Parse: splits string => splits array
	 * @param string $value
	 */
	static protected function parseSplits(&$value) {
		$Splits = new Splits($value);
		$value = $Splits->asArray();
	}

	/**
	 * Remove all empty values from array
	 * @param array $array
	 * @return array
	 */
	static private function removeEmptyValues($array) {
		foreach ($array as $key => $value)
			if (empty($value))
				unset($array[$key]);

		return $array;
	}
}
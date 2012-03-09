<?php
/**
 * Library with parsers for formular values, default behavior: from user input to database value
 */
class FormularValueParser {
	/**
	 * Parser: timestamp <=> date-string
	 * @var string 
	 */
	static public $PARSER_DATE = 'date';

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
	 * Validate post-value for a given key with a given parser
	 * @param string $key
	 * @param enum $parser
	 * @param array $parserOptions
	 * @return boolean 
	 */
	static public function validatePost($key, $parser, $parserOptions = array()) {
		if (is_null($parser))
			return true;

		switch ($parser) {
			case self::$PARSER_DATE:
				return self::validateDate($key);
			case self::$PARSER_STRING:
				return self::validateString($key);
			case self::$PARSER_INT:
				return self::validateInt($key, $parserOptions);
			case self::$PARSER_DECIMAL:
				return self::validateDecimal($key, $parserOptions);
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
		}
	}

	/**
	 * Validator: string => encoded string
	 * @param string $key
	 * @return boolean 
	 */
	static protected function validateString($key) {
		// Nothing to do because of correct encoding

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
			return false;

		$_POST[$key] = (int)$_POST[$key];

		if (!self::precisionIsOkay($_POST[$key], $options))
			return 'Der eingegebene Wert ist zu gro&szlig;.';

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
			return 'Der eingegebene Wert ist zu gro&szlig;.';

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
			$_POST[$key] = time();
			return 'Das Datum konnte nicht gelesen werden.';
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
?>

<?php
/**
 * This file contains the class::Type for handling training-types
 */
/**
 * Class: Type
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class Type {
	/**
	 * Array containing all types-data from database
	 * @var array
	 */
	static private $types = null;

	/**
	 * Internal ID in database
	 * @var int
	 */
	private $id;

	/**
	 * Array with all information from database
	 * @var array
	 */
	private $data;

	/**
	 * Constructor
	 */
	public function __construct($id) {
		self::initTypes();

		if (!isset(self::$types[$id]))
			return false;

		$this->id = $id;
		$this->data = self::$types[$id];
	}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Get name for this type
	 * @return string
	 */
	public function name() {
		return $this->data['name'];
	}

	/**
	 * Get abbreviation for this type
	 * @return string
	 */
	public function abbr() {
		return $this->data['abbr'];
	}

	/**
	 * Get html-formatted abbreviation for this type
	 * @return string
	 */
	public function formattedAbbr() {
		if ($this->hasHighRPE())
			return '<strong>'.$this->data['abbr'].'</strong>';

		return $this->data['abbr'];
	}

	/**
	 * Get boolean flag whether type alouds splits
	 * @return bool
	 */
	public function hasSplits() {
		return ($this->data['splits'] == 1);
	}

	/**
	 * Get boolean flag: Is the RPE of this type higher than 4?
	 * @return bool
	 */
	public function hasHighRPE() {
		return ($this->data['RPE'] > 4);
	}

	/**
	 * Get RPE for this type
	 * @return int
	 */
	public function RPE() {
		return $this->data['RPE'];
	}

	/**
	 * Initialize internal types-array from database
	 */
	static private function initTypes() {
		if (is_null(self::$types)) {
			$types = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'type`');
			foreach ($types as $data)
				self::$types[$data['id']] = $data;
		}
	}

	/**
	 * Get internal array with all types
	 * @return array
	 */
	static private function getTypes() {
		self::initTypes();

		return self::$types;
	}

	/**
	 * Get array with alle names, indizes are IDs
	 * @return array
	 */
	static public function getNamesAsArray() {
		$types = self::getTypes();
		foreach ($types as $id => $type)
			$types[$id] = $type['name'];

		return $types;
	}

	/**
	 * Get array with default values for a type
	 * @return array
	 */
	static public function getDefaultArray() {
		return array('name' => '?', 'abbr' => '?', 'splits' => 0, 'RPE' => 0);
	}

	/**
	 * Get select-box for all types
	 * @param bool $showUnknown Show a first option for a unknown type
	 * @param mixed $selected value to be selected
	 * @return string
	 */
	static public function getSelectBox($showUnknown = true, $selected = -1) {
		$types = self::getNamesAsArray();

		if (empty($types))
			$types[0] = 'Keine Typen vorhanden';
		elseif ($showUnknown)
			$types = array(0 => '?') + $types;

		return HTML::selectBox('typeid', $types, $selected);
	}
}
?>
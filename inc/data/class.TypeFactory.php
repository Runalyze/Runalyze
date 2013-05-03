<?php
/**
 * This file contains class::TypeFactory
 * @package Runalyze\Data\Type
 */
/**
 * Factory serving static methods for types
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Type
 */
class TypeFactory {
	/**
	 * All types as array
	 * @var array
	 */
	static private $AllTypes = null;

	/**
	 * Data for ID
	 * @param int $id typeid
	 * @return array
	 */
	static public function DataFor($id) {
		$Types = self::AllTypes();

		if (isset($Types[$id]))
			return $Types[$id];

		return self::defaultArray();
	}

	/**
	 * Array with default values
	 * 
	 * @todo This method should be useless as soon as a DatabaseScheme is used
	 * @return array
	 */
	static private function defaultArray() {
		return array(
			'name' => '?',
			'abbr' => '?',
			'RPE' => 0
		);
	}

	/**
	 * Get all types
	 * @return array
	 */
	static public function AllTypes() {
		if (is_null(self::$AllTypes))
			self::initAllTypes();

		return self::$AllTypes;
	}

	/**
	 * Init all types
	 */
	static private function initAllTypes() {
		$types = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'type`');
		foreach ($types as $data)
			self::$AllTypes[$data['id']] = $data;
	}

	/**
	 * Get array with all names
	 * @param bool $abbr
	 * @return array keys are ids, values are names
	 */
	static public function NamesAsArray($abbr = false) {
		$types = self::AllTypes();
		foreach ($types as $id => $type) {
			if ($abbr == false) {
				$types[$id] = $type['name'];
			} else {
				$types[$id] = $type['abbr'];
			}
		}

		if (CONF_TRAINING_SORT_TYPES == 'alpha')
			asort($types);
		elseif (CONF_TRAINING_SORT_TYPES == 'id-desc')
			krsort($types);

		return $types;
	}

	/**
	 * Get select-box for all types
	 * @param bool $showUnknown Show a first option for a unknown type
	 * @param mixed $selected value to be selected
	 * @param bool $abbr
	 * @return string
	 */
	static public function SelectBox($showUnknown = true, $selected = -1, $abbr = false) {
		$types = self::NamesAsArray($abbr);

		if (empty($types)) 
			$types[0] = 'Keine Typen vorhanden';
		elseif ($showUnknown)
			$types = array(0 => '?') + $types;

		return HTML::selectBox('typeid', $types, $selected);
	}
}
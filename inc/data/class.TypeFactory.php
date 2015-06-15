<?php
/**
 * This file contains class::TypeFactory
 * @package Runalyze\Data\Type
 */

use Runalyze\Configuration;

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
			'hr_avg' => 0,
			'quality_session' => 0
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
	 * 
	 * IDs will be set as string as indices for correct order
	 */
	static private function initAllTypes() {
		self::$AllTypes = array();
		$types = Cache::get('types');

		if (is_null($types)) {
			$types = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'type` WHERE `accountid` = '.SessionAccountHandler::getId())->fetchAll();
			Cache::set('types', $types, '3600');
		}

		foreach ($types as $data) {
			self::$AllTypes[(string)$data['id']] = $data;
		}

		Configuration::ActivityForm()->orderTypes()->sort(self::$AllTypes);
	}

	/**
	 * Reinit all types
	 *
	 * Use this method after updating types table
	 */
	static public function reInitAllTypes() {
		Cache::delete('types');

		self::initAllTypes();
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
			$types[0] = __('No types available');
		elseif ($showUnknown)
			$types = array(0 => '?') + $types;

		return HTML::selectBox('typeid', $types, $selected);
	}
}
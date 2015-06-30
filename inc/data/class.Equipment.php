<?php
/**
 * This file contains class::Equipment
 * @package Runalyze\Data\Equipment
 */
/**
 * Class: Equipment
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Data\Equipment
 */
class Equipment {
	/**
	 * Internal ID-array
	 * @var array
	 */
	private $ids = array();

	/**
	 * Constructor
	 * @param string $id_string comma-separated string with IDs
	 */
	public function __construct($id_string) {
		$this->ids = EquipmentFactory::idStringToArray($id_string);
	}

	/**
	 * Are no equipment given?
	 * @return bool
	 */
	public function areEmpty() {
		return empty($this->ids);
	}

	/**
	 * Get equipment as string
	 * @return string
	 */
	public function asString() {
		$usedEquipment = array();
		$equipment     = EquipmentFactory::AllEquipment();

		foreach ($this->ids as $id) {
			$id = (int)trim($id);

			if (isset($equipment[$id]))
				$usedEquipment[] = $equipment[$id]['name'];
			else
				Error::getInstance()->addWarning('Asked for unknown equipment-ID: "'.$id.'"');
		}

		return implode(', ', $usedEquipment);
	}

	/**
	 * Transform IDs to array for post-data
	 * @return array
	 */
	public function arrayForPostdata() {
		$equipment = array();

		foreach ($this->ids as $id)
			$equipment[$id] = 'on';

		return $equipment;
	}

	/**
	 * Get search links for all given equipment
	 * @return string
	 */
	public function asLinks() {
		$links = array();

		foreach ($this->ids as $id)
			$links[] = EquipmentFactory::getSearchLinkForSingleEquipment($id);

		return implode(', ', $links);
	}
}
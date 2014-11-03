<?php
/**
 * This file contains class::Clothes
 * @package Runalyze\Data\Clothes
 */
/**
 * Class: Clothes
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Clothes
 */
class Clothes {
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
		$this->ids = ClothesFactory::idStringToArray($id_string);
	}

	/**
	 * Are no clothes given?
	 * @return bool
	 */
	public function areEmpty() {
		return empty($this->ids);
	}

	/**
	 * Get clothes as string
	 * @return string
	 */
	public function asString() {
		$usedClothes = array();
		$clothes     = ClothesFactory::AllClothes();

		foreach ($this->ids as $id) {
			$id = (int)trim($id);

			if (isset($clothes[$id]))
				$usedClothes[] = $clothes[$id]['name'];
			else
				Error::getInstance()->addWarning('Asked for unknown clothes-ID: "'.$id.'"');
		}

		return implode(', ', $usedClothes);
	}

	/**
	 * Transform IDs to array for post-data
	 * @return array
	 */
	public function arrayForPostdata() {
		$clothes = array();

		foreach ($this->ids as $id)
			$clothes[$id] = 'on';

		return $clothes;
	}

	/**
	 * Get search links for all given clothes
	 * @return string
	 */
	public function asLinks() {
		$links = array();

		foreach ($this->ids as $id)
			$links[] = ClothesFactory::getSearchLinkForSingleClothes($id);

		return implode(', ', $links);
	}
}
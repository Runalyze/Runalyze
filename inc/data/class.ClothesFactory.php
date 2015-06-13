<?php
/**
 * This file contains class::ClothesFactory
 * @package Runalyze\Data\Clothes
 */
/**
 * Factory serving static methods for clothes
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Clothes
 */
class ClothesFactory {
	/**
	 * @var string
	 */
	const CACHE_KEY = 'clothes';

	/**
	 * All clothes as array
	 * @var array
	 */
	static private $AllClothes = null;

	/**
	 * Get all clothes
	 * @return array
	 */
	static public function AllClothes() {
		if (is_null(self::$AllClothes))
			self::initAllClothes();

		return self::$AllClothes;
	}

	/**
	 * Init all clothes
	 */
	static private function initAllClothes() {
		self::$AllClothes = array();

		$clothes = self::cacheAllClothes();
		foreach ($clothes as $data)
			self::$AllClothes[$data['id']] = $data;
	}

	/**
	 * Reinit all clothes
	 */
	static public function reInitAllClothes() {
		Cache::delete(self::CACHE_KEY);
		self::initAllClothes();
	}
        
	/**
	 * Cache Clothes
	 */
	static private function cacheAllClothes() {
		$clothes = Cache::get(self::CACHE_KEY);
		if (is_null($clothes)) {
			$clothes = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'clothes` WHERE accountid = '.SessionAccountHandler::getId())->fetchAll();
			Cache::set(self::CACHE_KEY, $clothes, '3600');
		}
		return $clothes;
	}

	/**
	 * Get ordered clothes
	 * @return array
	 */
	static public function OrderedClothes() {
		$clothes = self::AllClothes();
		usort($clothes, function($a, $b){
			if ($a['order'] == $b['order']) {
				return 0;
			} else if ($a['order'] < $b['order']) {
				return -1;
			}

			return 1;
		});

		return $clothes;
	}

	/**
	 * Transform string with IDs to array
	 * @param string $id_string
	 * @return array
	 */
	static public function idStringToArray($id_string) {
		if (strlen($id_string) == 0)
			return array();

		return explode(',', $id_string);
	}

	/**
	 * Get checkboxes for all clothes
	 * @return string
	 */
	static public function Checkboxes() {
		$html    = HTML::hiddenInput('clothes_sent', 'true');
		$clothes = self::OrderedClothes();

		foreach ($clothes as $data)
			$html .= self::Checkbox($data);

		return $html;
	}

	/**
	 * Get single checkbox for one clothes
	 * @param array $dataArray
	 * @return string
	 */
	static private function Checkbox($dataArray) {
		$name     = 'clothes['.$dataArray['id'].']';
		$label    = $dataArray['short'];
		$Checkbox = new FormularCheckbox($name, $label);
		$Checkbox->setLayout( FormularFieldset::$LAYOUT_FIELD_SMALL_INLINE );
		$Checkbox->addLayout( 'margin-5' );

		return $Checkbox->getCode();
	}

	/**
	 * Get name for a given ID
	 * @param int $id
	 * @return string
	 */
	static public function NameFor($id) {
		$clothes = self::AllClothes();

		if (isset($clothes[$id]))
			return $clothes[$id]['name'];

		return '?';
	}

	/**
	 * Get search-link for one ID
	 * @param int $id
	 * @return string
	 */
	static public function getSearchLinkForSingleClothes($id) {
		return SearchLink::to('clothes', $id, self::NameFor($id));
	}
}
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
		$clothes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes`');
		foreach ($clothes as $data)
			self::$AllClothes[$data['id']] = $data;
	}

	/**
	 * Get ordered clothes
	 * @return array
	 */
	static public function OrderedClothes() {
		return Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order` ASC');
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
		return DataBrowserLinker::searchLink(self::NameFor($id), 'opt[clothes]=is&val[clothes][0]='.$id);
	}
}
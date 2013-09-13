<?php
/**
 * This file contains class::ShoeFactory
 * @package Runalyze\Data\Shoe
 */
/**
 * Factory serving static methods for shoes
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Shoe
 */
class ShoeFactory {
	/**
	 * Array with all shoes
	 * @var array
	 */
	static private $AllShoes = null;

	/**
	 * Are any shoes in database?
	 * @return bool
	 */
	static public function hasShoes() {
		return count(self::AllShoes()) > 0;
	}

	/**
	 * Are shoes in use?
	 * @return bool
	 */
	static public function hasShoesInUse() {
		return count(self::FullArray()) > 0;
	}

	/**
	 * Initialize internal shoes-array from database
	 */
	static private function initAllShoes() {
		self::$AllShoes = array();
		$shoes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'shoe` '.self::getOrder());
		foreach ($shoes as $shoe)
			self::$AllShoes[(string)$shoe['id']] = $shoe;
	}

	/**
	 * Get order
	 * @see CONF_TRAINING_SORT_SHOES
	 * @return string
	 */
	static private function getOrder() {
		switch (CONF_TRAINING_SORT_SHOES) {
			case 'alpha':
				return 'ORDER BY `name` ASC';
			case 'id-desc':
				return 'ORDER BY `id` DESC';
			case 'id-asc':
			default:
				return 'ORDER BY `id` ASC';
		}
	}

	/**
	 * Get internal array with all shoes
	 * @return array
	 */
	static private function AllShoes() {
		if (is_null(self::$AllShoes))
			self::initAllShoes();

		return self::$AllShoes;
	}

	/**
	 * Get array with all shoe-data
	 * @param bool $onlyInUse [optional] default: true
	 * @return array
	 */
	static public function FullArray($onlyInUse = true) {
		$shoes = self::AllShoes();

		if ($onlyInUse)
			foreach ($shoes as $id => $shoe)
				if ($shoe['inuse'] != 1)
					unset($shoes[$id]);

		return $shoes;
	}

	/**
	 * Get array with alle names, indizes are IDs
	 * @param bool $inUse [optional] default: true
	 * @return array
	 */
	static public function NamesAsArray($inUse = true) {
		$shoes = self::AllShoes();
		foreach ($shoes as $id => $shoe)
			if (!$inUse || $shoe['inuse'] == 1)
				$shoes[$id] = $shoe['name'];
			else
				unset($shoes[$id]);

		return $shoes;
	}

	/**
	 * Get name of a shoe
	 * @param int $id ID for the shoe
	 * @return string
	 */
	static public function NameOf($id) {
		$shoes = self::AllShoes();

		if (isset($shoes[$id]))
			return $shoes[$id]['name'];

		if ($id > 0)
			Error::getInstance()->addWarning('Asked for unknown shoe-ID: "'.$id.'"');

		return '?';
	}

	/**
	 * Get search link for given shoe id
	 * @param int $id
	 * @return string
	 */
	static public function getSearchLink($id) {
		$shoes = self::AllShoes();

		return SearchLink::to('shoeid', $id, $shoes[$id]['name']);
	}

	/**
	 * Get select-box for all shoes
	 * @param bool $inUse Only show shoes beeing in use
	 * @param bool $showUnknown Show a first option for a unknown shoe
	 * @param mixed $selected Value to be selected
	 * @return string
	 */
	static public function SelectBox($inUse = true, $showUnknown = true, $selected = -1) {
		$shoes = self::NamesAsArray($inUse);

		if (empty($shoes))
			$shoes[0] = 'Keine Schuhe vorhanden';
		elseif ($showUnknown)
			$shoes = array(0 => '?') + $shoes;

		return HTML::selectBox('shoeid', $shoes, $selected);
	}

	/**
	 * Recalculate all shoes
	 */
	static public function recalculateAllShoes() {
		$shoes = self::AllShoes();

		foreach (array_keys($shoes) as $id) {
			$data = Mysql::getInstance()->fetchSingle('SELECT SUM(`distance`) as `km`, SUM(`s`) as `s` FROM `'.PREFIX.'training` WHERE `shoeid`="'.$id.'" GROUP BY `shoeid`');

			if ($data === false)
				$data = array('km' => 0, 's' => 0);

			Mysql::getInstance()->update(PREFIX.'shoe', $id, array('km', 'time'), array($data['km'], $data['s']));
		}

		self::initAllShoes();
	}
}
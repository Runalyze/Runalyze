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
	 * Number of shoes
	 * @return int
	 */
	static public function numberOfShoes() {
		return count(self::AllShoes());
	}

	/**
	 * Are any shoes in database?
	 * @return bool
	 */
	static public function hasShoes() {
		return self::numberOfShoes() > 0;
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
                $shoes = Cache::get('shoes');
                if(is_null($shoes)) {
                    $shoes = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'shoe` '.self::getOrder())->fetchAll();
                    Cache::set('shoes', $shoes, '3600');
                }
		foreach ($shoes as $shoe)
			self::$AllShoes[(string)$shoe['id']] = $shoe;
	}

	/**
	 * Clear internal array
	 */
	static private function clearAllShoes() {
		self::$AllShoes = null;
	}

	/**
	 * Get order
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
			$shoes[0] = __('No shoes available');
		elseif ($showUnknown)
			$shoes = array(0 => '?') + $shoes;

		return HTML::selectBox('shoeid', $shoes, $selected);
	}

	/**
	 * Recalculate all shoes
	 * 
	 * Be sure that a complete recalculation is really needed.
	 * This task may take very long.
	 */
	static public function recalculateAllShoes() {
		DB::getInstance()->exec('UPDATE `'.PREFIX.'shoe` SET `km`=0, `time`=0');

		$Statement = DB::getInstance()->query(
			'SELECT `shoeid`, SUM(`distance`) as `km`, SUM(`s`) as `s` '.
			'FROM `'.PREFIX.'training` '.
			'GROUP BY `shoeid`'
		);

		while ($ShoeData = $Statement->fetch()) {
			if ($ShoeData['shoeid'] > 0 && $ShoeData['s'] > 0) {
				DB::getInstance()->update('shoe', $ShoeData['shoeid'], array('km', 'time'), array($ShoeData['km'], $ShoeData['s']));
			}
		}

		self::clearAllShoes();
	}
}

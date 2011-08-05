<?php
/**
 * This file contains the class::Shoe for handling shoes
 */
/**
 * Class: Shoe
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class Shoe {
	/**
	 * Array containing all shoe-data from database
	 * @var array
	 */
	static private $shoes = null;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Initialize internal shoes-array from database
	 */
	static private function initShoes() {
		if (is_null(self::$shoes)) {
			self::$shoes = array();
			$shoes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'shoe`');
			foreach ($shoes as $shoe)
				self::$shoes[$shoe['id']] = $shoe;
		}
	}

	/**
	 * Get internal array with all shoes
	 * @return array
	 */
	static private function getShoes() {
		self::initShoes();

		return self::$shoes;
	}

	/**
	 * Get array with all shoe-data
	 * @return array
	 */
	static public function getFullArray($inUse = true) {
		$shoes = self::getShoes();
		foreach ($shoes as $id => $shoe)
			if ($inUse && $shoe['inuse'] != 1)
				unset($shoes[$id]);

		return $shoes;
	}

	/**
	 * Get array with alle names, indizes are IDs
	 * @return array
	 */
	static public function getNamesAsArray($inUse = true) {
		$shoes = self::getShoes();
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
	static public function getName($id) {
		$shoes = self::getShoes();

		if (isset($shoes[$id]))
			return $shoes[$id]['name'];

		Error::getInstance()->addWarning('Asked for unknown shoe-ID: "'.$id.'"');
		return '?';
	}

	/**
	 * Get search link for given shoe id
	 * @param int $id
	 * @return string
	 */
	static public function getSeachLink($id) {
		$shoes = self::getShoes();

		return DataBrowser::getSearchLink($shoes[$id]['name'], 'opt[shoeid]=is&val[shoeid][0]='.$id);
	}

	/**
	 * Get select-box for all shoes
	 * @param bool $inUse Only show shoes beeing in use
	 * @param bool $showUnknown Show a first option for a unknown shoe
	 * @param mixed $selected Value to be selected
	 * @return string
	 */
	static public function getSelectBox($inUse = true, $showUnknown = true, $selected = -1) {
		$shoes = self::getNamesAsArray($inUse);

		if (empty($shoes))
			$shoes[0] = 'Keine Schuhe vorhanden';
		elseif ($showUnknown)
			$shoes = array_merge(array('?'), $shoes);

		return HTML::selectBox('shoeid', $shoes, $selected);
	}

	/**
	 * Are any shoes in database?
	 * @return bool
	 */
	static public function hasShoes() {
		return count(self::getShoes()) > 0;
	}

	/**
	 * Are shoes in use?
	 * @return bool
	 */
	static public function hasShoesInUse() {
		return count(self::getFullArray()) > 0;
	}
}
?>
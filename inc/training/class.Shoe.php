<?php
/**
 * This file contains the class::Shoe for handling running shoes
 */
/**
 * Class: Shoe
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Shoe extends DataObject {
	/**
	 * Array containing all shoe-data from database
	 * @var array
	 */
	static private $shoes = null;

	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.Shoe.php');
	}
	
	/**
	* Get name
	* @return string
	*/
	public function getName() {
		return $this->get('name');
	}
	
	/**
	* Get brand
	* @return string
	*/
	public function getBrand() {
		return $this->get('brand');
	}
	
	/**
	* Get since
	* @return string
	*/
	public function getSince() {
		return $this->get('since');
	}

	/**
	* Get time
	* @return int
	*/
	public function getTime() {
		return $this->get('time');
	}

	/**
	 * Get string for time
	 * @return string
	 */
	public function getTimeString() {
		return Time::toString($this->getTime());
	}

	/**
	* Get km
	* @return float
	*/
	public function getKm() {
		return $this->getKmInDatabase() + $this->getAdditionalKm();
	}

	/**
	 * Get string for km
	 * @return string
	 */
	public function getKmString() {
		return Running::Km($this->getKm());
	}

	/**
	 * Get icon for usage
	 * @return string
	 */
	public function getKmIcon() {
		return self::getIcon($this->getKm());
	}

	/**
	* Get km from trainings in database
	* @return float
	*/
	public function getKmInDatabase() {
		return $this->get('km');
	}

	/**
	* Get additional km
	* @return float
	*/
	public function getAdditionalKm() {
		return $this->get('additionalKm');
	}

	/**
	* Is this shoe in use?
	* @return boolean
	*/
	public function isInUse() {
		return $this->get('inuse') == 1;
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

	/**
	 * Initialize internal shoes-array from database
	 */
	static private function initShoes() {
		self::$shoes = array();
		$shoes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'shoe`');
		foreach ($shoes as $shoe)
			self::$shoes[$shoe['id']] = $shoe;
	}

	/**
	 * Get internal array with all shoes
	 * @return array
	 */
	static private function getShoes() {
		if (is_null(self::$shoes))
			self::initShoes();

		return self::$shoes;
	}

	/**
	 * Get array with all shoe-data
	 * @param bool $inUse [optional] default: true
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
	 * @param bool $inUse [optional] default: true
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
	static public function getNameOf($id) {
		$shoes = self::getShoes();

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
			$shoes = array(0 => '?') + $shoes;

		return HTML::selectBox('shoeid', $shoes, $selected);
	}

	/**
	 * Get an icon for the abrasion of the shoe
	 * @param double $distance
	 * @return string
	 */
	static public function getIcon($distance) {
		if ($distance > 900)
			return Icon::$BROKEN_5;
		elseif ($distance > 700)
			return Icon::$BROKEN_4;
		elseif ($distance > 500)
			return Icon::$BROKEN_3;
		elseif ($distance > 200)
			return Icon::$BROKEN_2;
		else
			return Icon::$BROKEN_1;
	}
}
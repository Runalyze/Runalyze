<?php
/**
 * Class: Sport
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Sport {
	/**
	 * Array containing all sport-data from database
	 * @var array
	 */
	static private $sports = null;

	/**
	 * ID for this sport in database
	 * @var int
	 */
	private $id;

	/**
	 * Array with all information from database
	 * @var array
	 */
	private $data;

	/**
	 * Constructor
	 * @param int $id
	 */
	public function __construct($id) {
		self::initSports();

		$this->id = $id;

		if (isset(self::$sports[$id]))
			$this->data = self::$sports[$id];
		else
			$this->data = self::getDefaultArray();
	}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Is this sport valid?
	 * @return boolean
	 */
	public function isValid() {
		return !empty($this->data);
	}

	/**
	 * Get name
	 * @return string
	 */
	public function name() {
		return $this->data['name'];
	}

	/**
	 * Get RPE for this sport
	 * @return int
	 */
	public function RPE() {
		return $this->data['RPE'];
	}
	
	/**
	* Get icon for this sport
	* @return string
	*/
	public function Icon() {
		return Icon::getSportIcon($this->id);
	}
	
	/**
	* Is this sport active?
	* @return bool
	*/
	public function isActive() {
		return ($this->data['online'] == 1);
	}
	
	/**
	* Is this sport set to short-mode?
	* @return bool
	*/
	public function isShort() {
		return ($this->data['short'] == 1);
	}
	
	/**
	* Get normal kcal per hour
	* @return int
	*/
	public function kcalPerHour() {
		return $this->data['kcal'];
	}
	
	/**
	* Get normal kcal per hour
	* @return int
	*/
	static public function kcalPerHourFor($SportID) {
		if (isset(self::$sports[$SportID]))
			return self::$sports[$SportID]['kcal'];

		return 0;
	}
	
	/**
	* Get average heartfrequence
	* @return int
	*/
	public function avgHF() {
		return $this->data['HFavg'];
	}

	/**
	 * Has a training of this sport a distance?
	 * @return bool
	 */
	public function usesDistance() {
		return ($this->data['distances'] == 1);
	}

	/**
	 * Does this sport use pulse?
	 * @return bool
	 */
	public function usesPulse() {
		return ($this->data['pulse'] == 1);
	}

	/**
	 * Does this sport use km/h as unit for speed?
	 * @return bool
	 */
	public function usesKmh() {
		return ($this->data['kmh'] == 1);
	}

	/**
	 * Has this sport trainingtypes?
	 * @return bool
	 */
	public function hasTypes() {
		return ($this->data['types'] == 1);
	}

	/**
	 * Has this sport a high RPE?
	 * @return bool
	 */
	public function hasHighRPE() {
		return ($this->data['RPE'] > 4);
	}

	/**
	 * Is this sport outside?
	 * @return bool
	 */
	public function isOutside() {
		return ($this->data['outside'] == 1);
	}

	/**
	 * Checks if this sport is set as "Running"
	 * @return bool
	 */
	public function isRunning() {
		return ($this->id == CONF_RUNNINGSPORT);
	}

	/**
	 * Initialize internal sports-array from database
	 */
	static private function initSports() {
		if (is_null(self::$sports)) {
			$sports = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'sport`');
			foreach ($sports as $sport)
				self::$sports[$sport['id']] = $sport;
		}
	}

	/**
	 * Try to get sport by name
	 * @param string $name
	 * @return int 
	 */
	static public function getIdByName($name) {
		$Sport = Mysql::getInstance()->fetchSingle('SELECT id FROM `'.PREFIX.'sport` WHERE `name`="'.$name.'"');

		if (isset($Sport['id']))
			return $Sport['id'];

		return -1;
	}

	/**
	 * Get internal array with all sports
	 * @return array
	 */
	static public function getSports() {
		self::initSports();

		return self::$sports;
	}

	/**
	 * Get all sports with types
	 * @return array
	 */
	static public function getSportsWithTypes() {
		$Sports = self::getSports();

		foreach ($Sports as $i => $Sport)
			if ($Sport['types'] == 0)
				unset($Sports[$i]);

		return $Sports;
	}

	/**
	 * Get how often the sport is used
	 * @return array or $string
	 */
	static public function getSportsCount($id = false) {
		if($id == false) {
		$CountSport = Mysql::getInstance()->untouchedFetchArray('SELECT sportid, COUNT(sportid) as scount FROM `'.PREFIX.'training` WHERE `accountid`="'.SessionAccountHandler::getId().'" GROUP BY sportid');
		foreach($CountSport as $CS)
			$SportCount[$CS['sportid']] = $CS['scount'];
		return $SportCount;
		} else {
				$CountSport = Mysql::getInstance()->untouchedFetchArray('SELECT sportid, COUNT(sportid) as scount FROM `'.PREFIX.'training` WHERE `accountid`="'.SessionAccountHandler::getId().'" AND sportid="'.$id.'"');
				foreach($CountSport as $CS)
					$SportCount[$CS['sportid']] = $CS['scount'];
				return $SportCount;
		}
	}
	
	/**
	 * Get select-box for all sport-ids
	 * @param mixed $selected [optional] Value to be selected
	 * @return string
	 */
	static public function getSelectBox($selected = -1) {
		if ($selected == -1 && isset($_POST['sportid']))
			$selected = $_POST['sportid'];

		$sport = self::getSports();
		foreach ($sport as $id => $data)
			$sport[$id] = $data['name'];

		return HTML::selectBox('sportid', $sport, $selected);
	}

	/**
	 * Get array with alle names, indizes are IDs
	 * @return array
	 */
	static public function getNamesAsArray() {
		$sports = self::getSports();
		foreach ($sports as $id => $sport)
			$sports[$id] = $sport['name'];

		return $sports;
	}

	/**
	 * Get array with default values for a sport
	 * @return array
	 */
	static public function getDefaultArray() {
		return array('name' => '?', 'img' => '', 'online' => 0, 'short' => 0, 'kcal' => 0,
			'HFavg' => 0, 'RPE' => 0, 'distances' => 0, 'kmh' => 0, 'types' => 0, 'pulse' => 0,
			'outside' => 0);
	}

	/**
	 * Does this sport-id displays speed in km/h?
	 * @param int $id
	 * @return bool
	 */
	static public function usesSpeedInKmh($id) {
		$sports = self::getSports();

		if (isset($sports[$id]))
			return ($sports[$id]['kmh'] == 1);

		return false;
	} 
}
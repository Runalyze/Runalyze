<?php
/**
 * This file contains class::Sport
 * @package Runalyze\Data\Sport
 */
/**
 * Class: Sport
 * @author Hannes Christiansen
 * @package Runalyze\Data\Sport
 */
class Sport {
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
	public function __construct($id = false) {
		if ($id === false)
			$id = CONF_MAINSPORT;

		$this->id   = $id;
		$this->data = SportFactory::DataFor($id);
	}

	/**
	 * ID
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

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
	 * @param string $tooltip optional parameter for tooltip
	* @return string
	*/
	public function Icon($tooltip = '') {
		return Icon::getSportIcon($this->id, '', $tooltip);
	}
	
	/**
	* Get icon for this sport
	* @return string
	*/
	public function IconWithTooltip() {
		return $this->Icon( $this->name() );
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
	 * @todo REMOVE this function
	 * @return bool
	 */
	public function usesKmh() {
		return ($this->data['speed'] == SportSpeed::$KM_PER_H);
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
}
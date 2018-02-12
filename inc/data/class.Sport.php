<?php
/**
 * @deprecated
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
	 * @param int|bool $id
	 */
	public function __construct($id = false) {
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
	 * Get name
	 * @return string
	 */
	public function name() {
		return $this->data['name'];
	}

        /*
	 * Has a training of this sport a distance?
	 * @return bool
	 */
	public function usesDistance() {
		return ($this->data['distances'] == 1);
	}

	/**
	 * Checks if this sport is set as "Running"
	 * @return bool
	 */
	public function isRunning() {
	    return (\Runalyze\Profile\Sport\SportProfile::RUNNING == $this->data['internal_sport_id']);
	}
}

<?php
/**
 * This file contains class::Type
 * @package Runalyze\Data\Type
 */
/**
 * Training types
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Type
 */
class Type {
	/**
	 * Internal ID in database
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
	 * @param int $id type id
	 */
	public function __construct($id) {
		$this->id = $id;
		$this->data = TypeFactory::DataFor($id);
	}

	/**
	 * Get name for this type
	 * @return string
	 */
	public function name() {
		return $this->data['name'];
	}

	/**
	 * Get abbreviation for this type
	 * @return string
	 */
	public function abbr() {
		return $this->data['abbr'];
	}

	/**
	 * Get html-formatted abbreviation for this type
	 * @return string
	 */
	public function formattedAbbr() {
		if ($this->hasHighRPE())
			return '<strong>'.$this->data['abbr'].'</strong>';

		return $this->data['abbr'];
	}

	/**
	 * Get boolean flag whether type alouds splits
	 * @return bool
	 */
	public function hasSplits() {
		return ($this->data['splits'] == 1);
	}

	/**
	 * Get boolean flag: Is the RPE of this type higher than 4?
	 * @return bool
	 */
	public function hasHighRPE() {
		return ($this->data['RPE'] > 4);
	}

	/**
	 * Get RPE for this type
	 * @return int
	 */
	public function RPE() {
		return $this->data['RPE'];
	}

	/**
	 * Is this type unknown? (id=0)
	 * @return bool
	 */
	public function isUnknown() {
		return ($this->id == 0);
	}

	/**
	 * Is this type competition?
	 * @return bool
	 */
	public function isCompetition() {
		return ($this->id == CONF_WK_TYPID);
	}
}
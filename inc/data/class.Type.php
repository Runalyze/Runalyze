<?php
/**
 * This file contains class::Type
 * @package Runalyze\Data\Type
 */

use Runalyze\Configuration;

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
		if ($this->isQualitySession())
			return '<strong>'.$this->data['abbr'].'</strong>';

		return $this->data['abbr'];
	}

	/**
	 * @return boolean
	 */
	public function isShort() {
		return ($this->data['short'] == 1);
	}

	/**
	 * @return int
	 */
	public function hrAvg() {
		return $this->data['hr_avg'];
	}

	/**
	 * @return boolean
	 */
	public function isQualitySession() {
		return ($this->data['quality_session'] == 1);
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
		return ($this->id == Configuration::General()->competitionType());
	}
}
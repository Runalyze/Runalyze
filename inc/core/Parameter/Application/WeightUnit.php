<?php
/**
 * This file contains class::WeightUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Weight Unit
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class WeightUnit extends \Runalyze\Parameter\Select {
	/**
	 * KG	
	 * @var string
	 */
	const KG = 'kg';

	/**
	 * Pound
	 * @var string
	 */
	const POUNDS = 'lbs';
        
	/**
	 * Stone
	 * @var string
	 */
	const STONES = 'st';

	/**
	 * Construct
	 * @param string $default
	 */
	public function __construct($default = self::KG) {
		parent::__construct($default, array(
			'options'		=> array(
				self::KG		=> __('kilograms'),
				self::POUNDS	=> __('pounds'),
				self::STONES	=> __('stones')
			)
		));
	}
	/**
	 * Is kg?
	 * @return bool
	 */
	public function isKG() {
		return ($this->value() == self::KG);
	}

	/**
	 * Is pounds?
	 * @return bool
	 */
	public function isPounds() {
		return ($this->value() == self::POUNDS);
	}
        
	/**
	 * Is stones?
	 * @return bool
	 */
	public function isStones() {
		return ($this->value() == self::STONES);
	}

	/**
	 * Get current user weight unit
	 * @return string
	 */
	public function unit() {
		return $this->value();
	}
}
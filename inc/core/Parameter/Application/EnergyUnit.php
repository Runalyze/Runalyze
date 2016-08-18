<?php
/**
 * This file contains class::EnergyUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Energy Unit
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class EnergyUnit extends \Runalyze\Parameter\Select {
	/**
	 * KCAL	
	 * @var string
	 */
	const KCAL = 'kcal';

	/**
	 * Pound
	 * @var string
	 */
	const KJ = 'kj';
        
	/**
	 * Construct
	 * @param string $default
	 */
	public function __construct($default = self::KCAL) {
		parent::__construct($default, array(
			'options'		=> array(
				self::KCAL		=> __('Kilocalorie'),
				self::KJ	=> __('Kilojule')
			)
		));
	}
	/**
	 * Is kg?
	 * @return bool
	 */
	public function isKCAL() {
		return ($this->value() == self::KCAL);
	}

	/**
	 * Is pounds?
	 * @return bool
	 */
	public function isKJ() {
		return ($this->value() == self::KJ);
	}

	/**
	 * Get current user energy unit
	 * @return string
	 */
	public function unit() {
		return $this->value();
	}
}
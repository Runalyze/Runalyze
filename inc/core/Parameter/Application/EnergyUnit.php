<?php
/**
 * This file contains class::EnergyUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;
use Runalyze\Activity\Energy;

/**
 * Energy Unit
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class EnergyUnit extends \Runalyze\Parameter\Select {
	/**
	 * Kilocalories
	 * @var string
	 */
	const KCAL = 'kcal';

	/**
	 * Kilojoule
	 * @var string
	 */
	const KJ = 'kJ';

	/**
	 * Construct
	 * @param string $default
	 */
	public function __construct($default = self::KCAL) {
		parent::__construct($default, array(
			'options'		=> array(
				self::KCAL	=> __('Kilocalories'),
				self::KJ	=> __('Kilojoules')
			)
		));
	}

	/**
	 * Is kilocalories?
	 * @return bool
	 */
	public function isKCAL() {
		return ($this->value() == self::KCAL);
	}

	/**
	 * Is kilojoules?
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

	/**
	 * @return float
	 */
	public function factorFromKcalToUnit() {
		if ($this->isKJ()) {
			return Energy::KJ_MULTIPLIER;
		}

		return 1.0;
	}
}

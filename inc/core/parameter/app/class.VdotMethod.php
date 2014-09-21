<?php
/**
 * This file contains class::VdotMethod
 * @package Runalyze\Parameter\Application
 */
/**
 * VDOT method
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class VdotMethod extends ParameterSelect {
	/**
	 * Logarithmic
	 * @var string
	 */
	const LOGARITHMIC = 'logarithmic';

	/**
	 * Linear
	 * @var string
	 */
	const LINEAR = 'linear';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::LOGARITHMIC, array(
			'options'		=> array(
				self::LOGARITHMIC	=> __('logarithmic (new method since v1.5)'),
				self::LINEAR		=> __('linear (old method up to v1.4)')
			)
		));
	}

	/**
	 * Uses: Logarithmic
	 * @return bool
	 */
	public function usesLogarithmic() {
		return ($this->value() == self::LOGARITHMIC);
	}

	/**
	 * Uses: Linear
	 * @return bool
	 */
	public function usesLinear() {
		return ($this->value() == self::LINEAR);
	}
}
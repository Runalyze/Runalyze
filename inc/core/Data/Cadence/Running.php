<?php
/**
 * This file contains class::Running
 * @package Runalyze\Data\Cadence
 */

namespace Runalyze\Data\Cadence;

/**
 * Cadence for running in steps per minute
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Cadence
 */
class Running extends AbstractCadence {
	/**
	 * Factor for manipulating value
	 * @var float
	 */
	protected $Factor = 2;

	/**
	 * Label
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label() {
		return __('Cadence (running)');
	}

	/**
	 * Unit as string
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function unitAsString() {
		return 'spm';
	}

	/**
	 * Explanation for unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function unitExplanation() {
		return __('spm = steps per minute');
	}

	/**
	 * Formular unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function formularUnit() {
		return \FormularUnit::$SPM;
	}
}
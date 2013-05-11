<?php
/**
 * This file contains class::CadenceRunning
 * @package Runalyze\Data
 */
/**
 * Cadence for running
 * 
 * This class displays the cadence of a training.
 * Cadence is used here as "steps per minute" for e.g. running.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data
 */
class CadenceRunning extends Cadence {
	/**
	 * Factor for manipulating value
	 * @var float
	 */
	protected $factor = 2;

	/**
	 * Label
	 * @return string
	 */
	public function label() {
		return 'Schrittfrequenz';
	}

	/**
	 * Unit as string
	 * @return string
	 */
	public function unitAsString() {
		return 'spm';
	}

	/**
	 * Explanation for unit
	 * @return string
	 */
	protected function unitExplanation() {
		return 'steps per minute = Schritte pro Minute';
	}

	/**
	 * Formular unit
	 * @return enum
	 */
	public function formularUnit() {
		return FormularUnit::$SPM;
	}
}
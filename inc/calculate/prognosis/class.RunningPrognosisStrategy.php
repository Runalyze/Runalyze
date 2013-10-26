<?php
/**
 * This file contains class::RunningPrognosisStrategy
 * @package Runalyze\Calculations\Prognosis
 */
/**
 * Class: RunningPrognosisStrategy
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
abstract class RunningPrognosisStrategy {
	/**
	 * Running setup from database
	 */
	abstract public function setupFromDatabase();

	/**
	 * Prognosis in seconds
	 */
	abstract public function inSeconds($distance);
}
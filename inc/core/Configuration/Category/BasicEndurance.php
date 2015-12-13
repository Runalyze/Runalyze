<?php
/**
 * This file contains class::BasicEndurance
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Calculation\BasicEndurance as CalculationBasicEndurance;
use Runalyze\Configuration\Messages;
use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Integer;
use Runalyze\Parameter\FloatingPoint;

use FormularUnit;

/**
 * Configuration category: basic endurance
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class BasicEndurance extends \Runalyze\Configuration\Category {
	/**
	 * Flag: recalculation triggered?
	 * @var boolean
	 */
	private static $TRIGGERED = false;

	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'basic-endurance';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('BE_MIN_KM_FOR_LONGJOG', new Integer(13));
		$this->createHandle('BE_DAYS_FOR_LONGJOGS', new Integer(70));
		$this->createHandle('BE_DAYS_FOR_WEEK_KM', new Integer(182));
		$this->createHandle('BE_DAYS_FOR_WEEK_KM_MIN', new Integer(70));
		$this->createHandle('BE_PERCENTAGE_WEEK_KM', new FloatingPoint(0.67));
	}

	/**
	 * Min km for longjog
	 * @return int
	 */
	public function minKmForLongjog() {
		return $this->get('BE_MIN_KM_FOR_LONGJOG');
	}

	/**
	 * Days for longjogs
	 * @return int
	 */
	public function daysForLongjogs() {
		return $this->get('BE_DAYS_FOR_LONGJOGS');
	}

	/**
	 * Days for week km
	 * @return int
	 */
	public function daysForWeekKm() {
		return $this->get('BE_DAYS_FOR_WEEK_KM');
	}

	/**
	 * Days for week km (min)
	 * @return int
	 */
	public function daysForWeekKmMin() {
		return $this->get('BE_DAYS_FOR_WEEK_KM_MIN');
	}

	/**
	 * Percentage week km
	 * @return float
	 */
	public function percentageWeekKm() {
		return $this->get('BE_PERCENTAGE_WEEK_KM');
	}

	/**
	 * Percentage longjogs
	 * @return float
	 */
	public function percentageLongjogs() {
		return 1 - $this->percentageWeekKm();
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Basic endurance') );
		$Fieldset->addHandle( $this->handle('BE_MIN_KM_FOR_LONGJOG'), array(
			'label'		=> __('Minimal distance for longjogs'),
			'tooltip'	=> __('Every run above this distance will be rated.'),
			'unit'		=> FormularUnit::$KM
		));
		$Fieldset->addHandle( $this->handle('BE_DAYS_FOR_LONGJOGS'), array(
			'label'		=> __('Days for longjogs'),
			'tooltip'	=> __('Number of days to look at for longjog goal.')
		));
		$Fieldset->addHandle( $this->handle('BE_DAYS_FOR_WEEK_KM'), array(
			'label'		=> __('Days for week kilometer'),
			'tooltip'	=> __('Number of days to look at for weekly kilometer goal.')
		));
		$Fieldset->addHandle( $this->handle('BE_DAYS_FOR_WEEK_KM_MIN'), array(
			'label'		=> __('Days for week kilometer (minimum)'),
			'tooltip'	=> __('This value will be used if not enough data is available.')
		));
		$Fieldset->addHandle( $this->handle('BE_PERCENTAGE_WEEK_KM'), array(
			'label'		=> __('Factor for week kilometers'),
			'tooltip'	=> __('Percentage as value between 0.00 and 1.00.')
		));

		return $Fieldset;
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('BE_MIN_KM_FOR_LONGJOG')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\BasicEndurance::triggerRecalculation');
		$this->handle('BE_DAYS_FOR_LONGJOGS')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\BasicEndurance::triggerRecalculation');
		$this->handle('BE_DAYS_FOR_WEEK_KM')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\BasicEndurance::triggerRecalculation');
		$this->handle('BE_DAYS_FOR_WEEK_KM_MIN')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\BasicEndurance::triggerRecalculation');
		$this->handle('BE_PERCENTAGE_WEEK_KM')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\BasicEndurance::triggerRecalculation');
	}

	/**
	 * Trigger recalculation
	 */
	public static function triggerRecalculation() {
		if (!self::$TRIGGERED) {
			self::$TRIGGERED = true;

			$oldValue = CalculationBasicEndurance::getConst();
			CalculationBasicEndurance::recalculateValue();
			$newValue = CalculationBasicEndurance::getConst();

			Messages::addValueRecalculated(__('Basic endurance'), $newValue.' &#37;', $oldValue.' &#37;');
		}
	}
}
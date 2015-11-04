<?php
/**
 * This file contains class::WeekStart
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * WeekStart
 * @author Hannes Christiansen
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Parameter\Application
 */
class WeekStart extends \Runalyze\Parameter\Select {
	/**
	 * Monday
	 * @var string
	 */
	const MONDAY = '1';

	/**
	 * Saturday
	 * @var string
	 */
	const SATURDAY = '6';

	/**
	 * Sunday
	 * @var string
	 */
	const SUNDAY = '0';

	/**
	 * Construct
	 */
	public function __construct($default = self::MONDAY) {
		parent::__construct($default, array(
			'options'		=> array(
				self::MONDAY		=>	__('Monday'),
				self::SATURDAY		=>	__('Saturday'),
				self::SUNDAY	=>	__('Sunday')
			)
		));
	}

	/**
	 * Is week start set?
	 * @return bool
	 */
	public function hasWeekStart() {
		return !($this->value() == self::NONE);
	}

	/**
	 * Is monday?
	 * @return bool
	 */
	public function isMonday() {
		return ($this->value() == self::MONDAY);
	} 
        
	/**
	 * Is saturday?
	 * @return bool
	 */
	public function isSaturday() {
		return ($this->value() == self::SATURDAY);
	}

	/**
	 * Is sunday?
	 * @return bool
	 */
	public function isSunday() {
		return ($this->value() == self::SUNDAY);
	}
        
	/**
	 * Get current week start
	 * @return string
	 */
	public function unit() {
		return $this->value();
	}
}
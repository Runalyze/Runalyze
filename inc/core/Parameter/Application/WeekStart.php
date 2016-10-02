<?php
/**
 * This file contains class::WeekStart
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

use Runalyze\Util\LocalTime;

/**
 * Parameter to set first day of week
 *
 * The internal value of this class is the value returned by date('w') which
 * equals numeric representation of the day of the week based on ISO-8601
 * modulo 7, i.e. 1 for Monday and 0 for Sunday.
 *
 * @author Hannes Christiansen
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Parameter\Application
 */
class WeekStart extends \Runalyze\Parameter\Select {
	/**
	 * Monday
	 * @var int
	 */
	const MONDAY = 1;

	/**
	 * Saturday
	 * @var int
	 */
	const SATURDAY = 6;

	/**
	 * Sunday
	 * @var int
	 */
	const SUNDAY = 0;

	/**
	 * Construct
	 * @param int|string
	 */
	public function __construct($default = self::MONDAY) {
		parent::__construct($default, array(
			'options'		=> array(
				self::MONDAY		=>	__('Monday'),
				// Saturday is not supported by MySQL as first day of the week
				//self::SATURDAY		=>	__('Saturday'),
				self::SUNDAY	=>	__('Sunday')
			)
		));
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

	/**
	 * @param int|bool $now
	 * @param bool $transformToServerTime
	 * @return int
	 */
	public function phpWeek($now = false, $transformToServerTime = false) {
		if ($now === false) {
			$now = time();
		} elseif ($transformToServerTime) {
			$now = (new LocalTime($now))->toServerTimestamp();
		}

		if ($this->isSunday() && date('w', $now) == 0) {
			if (date('W', $now) == 53 || date('W', $now) == date('W', mktime(0,0,0,12,28,date('Y', $now))))
				return 1;

			return date('W', $now) + 1;
		}

		return date('W', $now);
	}

	/**
	 * Parameter for `WEEK(date, mode)` in MySQL
	 * @see http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_week
	 * @deprecated 3.1.0 Use `mysqlWeek()` instead as it fixes #1941
	 *
	 * @return int
	 */
	public function mysqlParameter() {
		switch ($this->value()) {
			case self::SUNDAY:
				return 6;
			case self::MONDAY:
			default:
				return 3;
		}
	}

	/**
	 * Fixed version to get correct mysql week
	 * @see https://github.com/Runalyze/Runalyze/issues/1941
	 *
	 * @param string $dateString
	 * @return string Query part to select correct week number
	 */
	public function mysqlWeek($dateString) {
		if ($this->isSunday()) {
			return 'WEEK(DATE_ADD('.$dateString.', INTERVAL 1 DAY), 3)';
		}

		return 'WEEK('.$dateString.', 3)';
	}

	/**
	 * @param string $dateString
	 * @return string
	 */
	public function mysqlYearWeek($dateString) {
		return 'YEAR'.$this->mysqlWeek($dateString);
	}

	/**
	 * @return string
	 */
	public function firstDayOfWeekForStrtotime() {
		switch ($this->value()) {
			case self::SUNDAY:
				return 'sunday';
			case self::SATURDAY:
				return 'saturday';
			case self::MONDAY:
			default:
				return 'monday';
		}
	}

	/**
	 * @return string
	 */
	public function lastDayOfWeekForStrtotime() {
		switch ($this->value()) {
			case self::SUNDAY:
				return 'saturday';
			case self::SATURDAY:
				return 'friday';
			case self::MONDAY:
			default:
				return 'sunday';
		}
	}
}

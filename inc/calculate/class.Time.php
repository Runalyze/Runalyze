<?php
/**
 * Class for standard operations for timestamps
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class Time {
	/**
	 * Strings for months
	 * @var array
	 */
	static private $MONTHS   = array('Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

	/**
	 * Short strings for months
	 * @var array
	 */
	static private $MONTHS_S = array('Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

	/**
	 * Strings for weekdays
	 * @var array
	 */
	static private $WEEKDAYS   = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');

	/**
	 * Short strings for weekdays
	 * @var array
	 */
	static private $WEEKDAYS_S = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');

	/**
	 * Absolute difference in days between two timestamps
	 * @param int $time_1
	 * @param int $time_2 optional
	 * @return int
	 */
	public static function diffInDays($time_1, $time_2 = 0) {
		if ($time_2 == 0)
			$time_2 = time();

		return floor(abs(($time_1 - $time_2)/(3600*24)));
	}

	/**
	 * Is given timestamp from today?
	 * @param int $timestamp
	 * @return boolean
	 */
	public static function isToday($timestamp) {
		return date('d.m.Y') == date('d.m.Y', $timestamp);
	}

	/**
	 * Get the timestamp of the start of the week
	 * @param int $time
	 */
	public static function Weekstart($time) {
		$w = date("w", $time);
		if ($w == 0)
			$w = 7;
		$w -= 1;
		return mktime(0, 0, 0, date("m",$time), date("d",$time)-$w, date("Y",$time));
	}

	/**
	 * Get the timestamp of the end of the week
	 * @param int $time
	 */
	public static function Weekend($time) {
		$start = self::Weekstart($time);
		return mktime(23, 59, 50, date("m",$start), date("d",$start)+6, date("Y",$start));
	}

	/**
	 * Get the name of a day
	 * @param string $w     date('w');
	 * @param bool $short   short version, default: false
	 */
	public static function Weekday($w, $short = false) {
		if ($short)
			return self::$WEEKDAYS_S[$w%7];

		return self::$WEEKDAYS[$w%7];
	}

	/**
	 * Get the name of the month
	 * @param string $m     date('m');
	 * @param bool $short   short version, default: false
	 */
	public static function Month($m, $short = false) {
		if ($short)
			return self::$MONTHS_S[$m-1];

		return self::$MONTHS[$m-1];
	}
}
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
	static public function diffInDays($time_1, $time_2 = 0) {
		if ($time_2 == 0)
			$time_2 = time();

		return floor(abs(($time_1 - $time_2)/(3600*24)));
	}

	/**
	 * Is given timestamp from today?
	 * @param int $timestamp
	 * @return boolean
	 */
	static public function isToday($timestamp) {
		return date('d.m.Y') == date('d.m.Y', $timestamp);
	}

	/**
	 * Get string for daytime if not 0:00
	 * @param int $timestamp 
	 * @return string
	 */
	static public function daytimeString($timestamp) {
		return date('H:i', $timestamp) != '00:00' ? date('H:i', $timestamp).' Uhr' : '';
	}

	/**
	 * Get the timestamp of the start of the week
	 * @param int $time
	 */
	static public function Weekstart($time) {
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
	static public function Weekend($time) {
		$start = self::Weekstart($time);
		return mktime(23, 59, 50, date("m",$start), date("d",$start)+6, date("Y",$start));
	}

	/**
	 * Get the name of a day
	 * @param string $w     date('w');
	 * @param bool $short   short version, default: false
	 */
	static public function Weekday($w, $short = false) {
		if ($short)
			return self::$WEEKDAYS_S[$w%7];

		return self::$WEEKDAYS[$w%7];
	}

	/**
	 * Get the name of the month
	 * @param string $m     date('m');
	 * @param bool $short   short version, default: false
	 */
	static public function Month($m, $short = false) {
		if ($short)
			return self::$MONTHS_S[$m-1];

		return self::$MONTHS[$m-1];
	}

	/**
	 * Transform day and daytime to timestamp
	 * @param string $day
	 * @param string $time
	 * @return int
	 */
	static public function getTimestampFor($day, $time) {
		$post_day    = explode(".", $day);
		$post_time   = explode(":", $time);

		if (count($post_day) < 2) {
			$timestamp   = strtotime($day);

			if ($timestamp > 0)
				return $timestamp;

			$post_day[1] = date("m");
		}

		if (count($post_day) < 3)
			$post_day[2] = isset($_POST['year']) ? $_POST['year'] : date("Y");

		if (count($post_time) < 2)
			$post_time[1] = 0;

		return mktime((int)$post_time[0], (int)$post_time[1], 0, (int)$post_day[1], (int)$post_day[0], (int)$post_day[2]);
	}
}
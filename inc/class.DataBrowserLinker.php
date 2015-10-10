<?php
/**
 * This file contains class::DataBrowserLinker
 * @package Runalyze\DataBrowser
 */

use Runalyze\Util\Time;

/**
 * Linker for DataBrowser
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser
 */
class DataBrowserLinker {
	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param string $title title for the link
	 * @param string $rel
	 * @return string HTML-link
	 */
	public static function link($name, $start, $end, $title = '', $rel = '') {
		if (FrontendShared::$IS_SHOWN)
			return DataBrowserShared::getLink($name, $start, $end, $title = '');

		$href = 'call/call.DataBrowser.display.php?start='.$start.'&end='.$end;

		return Ajax::link($name, DATA_BROWSER_ID, $href, $rel, $title);
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $time Timestamp of the week
	 * @return string HTML-link
	 */
	public static function weekLink($name, $time) {
		return self::link($name, Time::weekstart($time), Time::weekend($time), '', 'week-link');
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $time Timestamp of the month
	 * @return string HTML-link
	 */
	public static function monthLink($name, $time) {
		return self::link($name,
			mktime(0, 0, 0, date("m", $time), 1, date("Y", $time)),
			mktime(23, 59, 50, date("m", $time)+1, 0, date("Y", $time)),
			'', 'month-link');
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $time Timestamp of the year
	 * @return string HTML-link
	 */
	public static function yearLink($name, $time) {
		return self::link($name,
			mktime(0, 0, 0, 1, 1, date("Y", $time)),
			mktime(23, 59, 50, 12, 31, date("Y", $time)),
			'', 'year-link');
	}

	/**
	 * Get previous timestamps depending on current time-interval (just an alias for getNextTimestamps($start, $end, true);)
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @return array Returns an array {'start', 'end'}
	 */
	public static function prevTimestamps($start, $end) {
		return self::nextTimestamps($start, $end, true);
	}

	/**
	 * Get next timestamps depending on current time-interval
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param bool $getPrev optional to get previous timestamps
	 * @return array Returns an array {'start', 'end'}
	 */
	public static function nextTimestamps($start, $end, $getPrev = false) {
		if (!is_numeric($start))
			$start = time();
		if (!is_numeric($end))
			$end = time();

		$date = array();
		$factor = $getPrev ? -1 : 1;
		$diff_in_days = round(($end - $start) / 86400);
		$start_month = date("m", $start);
		$start_day   = date("d", $start);
		$start_year  = date("Y", $start);
		$end_month   = date("m", $end);
		$end_day     = date("d", $end);
		$end_year    = date("Y", $end);

		if (360 < $diff_in_days && $diff_in_days < 370) {
			$start_year  += 1*$factor;
			$end_year    += 1*$factor;
		} elseif (28 <= $diff_in_days && $diff_in_days <= 31) {
			$start_month += 1*$factor;
			$end_month   += 1*$factor;

			if ($start_day == 1 && $end_day != 0) {
				$end_month = $start_month + 1;
				$end_day = 0;
			}
		} else {
			$start_day   += $diff_in_days*$factor;
			$end_day     += $diff_in_days*$factor;
		}

		$date['start'] = mktime(0, 0, 0, $start_month, $start_day, $start_year);
		$date['end'] = mktime(23, 59, 50, $end_month, $end_day, $end_year);

		return $date;
	}
}
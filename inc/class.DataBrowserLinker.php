<?php
/**
 * This file contains class::DataBrowserLinker
 * @package Runalyze\DataBrowser
 */
/**
 * Linker for DataBrowser
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser
 */
class DataBrowserLinker {
	/**
	 * URL for search to be called via jQuery
	 * @var string
	 */
	static public $SEARCH_URL = 'call/window.search.php';

	/**
	 * Get complete HTML-link for the search
	 * @param string $name
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function searchLink($name, $var = '') {
		// TODO: Just get $name, $column, $option, $value (may be arrays)
		return Ajax::window('<a href="'.self::searchLinkUrl($var).'">'.$name.'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function searchLinkUrl($var) {
		if (empty($var))
			return self::$SEARCH_URL;

		$var = str_replace(' ', '+', $var);

		return self::$SEARCH_URL.'?get=true&'.$var;
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param string $title title for the link
	 * @return string HTML-link
	 */
	static function link($name, $start, $end, $title = '') {
		if (FrontendShared::$IS_SHOWN)
			return DataBrowserShared::getLink($name, $start, $end, $title = '');

		$href = 'call/call.DataBrowser.display.php?start='.$start.'&end='.$end;

		return Ajax::link($name, DATA_BROWSER_ID, $href, '', $title);
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the week
	 * @return string HTML-link
	 */
	static function weekLink($name, $time) {
		return self::link($name, Time::Weekstart($time), Time::Weekend($time));
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the month
	 * @return string HTML-link
	 */
	static function monthLink($name, $time) {
		return self::link($name,
			mktime(0, 0, 0, date("m", $time), 1, date("Y", $time)),
			mktime(23, 59, 50, date("m", $time)+1, 0, date("Y", $time)));
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the year
	 * @return string HTML-link
	 */
	static function yearLink($name, $time) {
		return self::link($name,
			mktime(0, 0, 0, 1, 1, date("Y", $time)),
			mktime(23, 59, 50, 12, 31, date("Y", $time)));
	}

	/**
	 * Get previous timestamps depending on current time-interval (just an alias for getNextTimestamps($start, $end, true);)
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @return array Returns an array {'start', 'end'}
	 */
	static function prevTimestamps($start, $end) {
		return self::nextTimestamps($start, $end, true);
	}

	/**
	 * Get next timestamps depending on current time-interval
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param bool $getPrev optional to get previous timestamps
	 * @return array Returns an array {'start', 'end'}
	 */
	static function nextTimestamps($start, $end, $getPrev = false) {
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
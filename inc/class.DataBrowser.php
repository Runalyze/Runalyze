<?php
/**
 * This file contains the class::DataBrowser
 * The class::DataBrowser is is used to handle and display a list of trainings.
 */
/**
 * Tag-ID for the whole databrowser
 * @const DATA_BROWSER_ID
 */
define('DATA_BROWSER_ID', 'daten');

/**
 * Tag-ID for the search
 * @const DATA_BROWSER_SEARCH_ID
 */
define('DATA_BROWSER_SEARCH_ID', 'search');

/**
 * Tag-ID for the resultbrowser of the search
 * @const DATA_BROWSER_SEARCHRESULT_ID
 */
define('DATA_BROWSER_SEARCHRESULT_ID', 'searchResult');

Config::register('Suchfenster', 'RESULTS_AT_PAGE', 'int', 15, 'Ergebnisse pro Seite');
Config::register('Design', 'DB_HIGHLIGHT_TODAY', 'bool', '1', 'Heutigen Tag im Kalender hervorheben');

/**
 * Class: Frontend
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Ajax
 * @uses class::Training
 */
class DataBrowser {
	/**
	 * URL for search to be called via jQuery
	 * @var string
	 */
	static private $SEARCH_URL = 'call/window.search.php';

	/**
	 * CSS-ID for calendar-widget
	 * @var string
	 */
	static public $CALENDAR_ID = 'calendar';

	/**
	 * Timestamp for first day to be displayed
	 * @var int
	 */
	private $timestamp_start;

	/**
	 * Timestamp for last day to be displayed
	 * @var int
	 */
	private $timestamp_end;

	/**
	 * Number of days to be displayed
	 * @var int
	 */
	private $day_count;

	/**
	 * Days to be displayed
	 * @var array
	 */
	private $days;

	/**
	 * Array containing IDs for 'short' sports
	 * @var array
	 */
	private $sports_short;

	/**
	 * Internal MySql-object
	 * @var Mysql
	 */
	private $Mysql;

	/**
	 * Internal Error-object
	 * @var Error
	 */
	private $Error;

	/**
	 * Internal Dataset-object
	 * @var Dataset
	 */
	private $Dataset;

	/**
	 * Default constructor
	 */
	public function __construct() {
		$this->initInternalObjects();
		$this->initTimestamps();
		$this->initDays();
	}

	/**
	 * Init pointer to Mysql/Error-object
	 */
	private function initInternalObjects() {
		$this->Mysql = Mysql::getInstance();
		$this->Error = Error::getInstance();
		$this->Dataset = new Dataset();
	}

	/**
	 * Init private timestamps from request
	 */
	private function initTimestamps() {
		$this->timestamp_start = isset($_GET['start']) ? $_GET['start'] : Helper::Weekstart(time());
		$this->timestamp_end   = isset($_GET['end'])   ? $_GET['end']   : Helper::Weekend(time());

		$this->day_count = round(($this->timestamp_end - $this->timestamp_start) / 86400);
	}

	/**
	 * Init all days for beeing displayed
	 */
	private function initDays() {
		$this->initShortSports();
		$this->initEmptyDays();

		$AllTrainings = $this->Mysql->fetchAsArray('
			SELECT *, DATE(FROM_UNIXTIME(time)) as `date`
			FROM `'.PREFIX.'training`
			WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).'
			ORDER BY `time` ASC');

		foreach ($AllTrainings as $Training) {
			$w = Helper::diffInDays($Training['time'], $this->timestamp_start);

			if (in_array($Training['sportid'], $this->sports_short))
				$this->days[$w]['shorts'][]    = $Training;
			else
				$this->days[$w]['trainings'][] = $Training;
		}
	}

	/**
	 * Init array with empty days
	 */
	private function initEmptyDays() {
		$this->days = array();

		for ($w = 0; $w <= ($this->day_count-1); $w++)
			$this->days[] = array(
				'date' => mktime(0, 0, 0, date("m",$this->timestamp_start), date("d",$this->timestamp_start)+$w, date("Y",$this->timestamp_start)),
				'shorts' => array(),
				'trainings' => array());
	}

	/**
	 * Init $this->sports_short
	 */
	private function initShortSports() {
		$this->sports_short = array();
		$sports = $this->Mysql->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sport` WHERE `short`=1');
		foreach ($sports as $sport)
			$this->sports_short[] = $sport['id'];
	}

	/**
	 * Display the DataBrowser
	 */
	public function display() {
		include 'tpl/tpl.DataBrowser.php';
	}

	/**
	 * Display links to navigate in calendar
	 */
	private function displayNavigationLinks() {
		echo $this->getPrevLink().NL;
		echo $this->getCalenderLink().NL;

		echo self::getMonthLink(Helper::Month(date("m", $this->timestamp_start)), $this->timestamp_start).', ';
		echo self::getYearLink(date("Y", $this->timestamp_start), $this->timestamp_start).', ';
		echo self::getWeekLink(strftime("%W", $this->timestamp_start).'. Woche ', $this->timestamp_start);

		echo $this->getNextLink().NL;	
	}

	/**
	 * Display specific icon-links
	 */
	private function displayIconLinks() {
		echo $this->getRefreshLink();
		echo $this->getMonthKmLink();
		echo $this->getWeekKmLink();
		echo $this->getNaviSearchLink();
		echo $this->getAddLink();
	}

	/**
	 * Get link to navigation back
	 * @return string
	 */
	private function getPrevLink() {
		$icon = Icon::get(Icon::$ARR_BACK, 'zur&uuml;ck');
		$timestamp_array = self::getPrevTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink($icon, $timestamp_array['start'], $timestamp_array['end']);
	}

	/**
	 * Get link to navigation forward
	 * @return string
	 */
	private function getNextLink() {
		$icon = Icon::get(Icon::$ARR_NEXT, 'vorw&auml;rts');
		$timestamp_array = self::getNextTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink($icon, $timestamp_array['start'], $timestamp_array['end']);
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 * @return string
	 */
	private function getRefreshLink() {
		$icon = Icon::get(Icon::$REFRESH, 'Aktuelles Datenblatt neuladen');
		return self::getLink($icon, $this->timestamp_start, $this->timestamp_end);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 * @return string
	 */
	private function getCalenderLink() {
		return '<span id="calendarLink" class="link">'.Icon::get(Icon::$CALENDAR, 'Kalender-Auswahl').'</span>';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	private function getMonthKmLink() {
		$icon = Icon::get(Icon::$MONTH_KM, 'Monatskilometer');
		return Ajax::window('<a href="call/window.monatskilometer.php" title="Monatskilometer anzeigen">'.$icon.'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	private function getWeekKmLink() {
		$icon = Icon::get(Icon::$WEEK_KM, 'Wochenkilometer');
		return Ajax::window('<a href="call/window.wochenkilometer.php" title="Wochenkilometer anzeigen">'.$icon.'</a>');
	}

	/**
	 * Get ajax-link for searching
	 * @return string
	 */
	private function getNaviSearchLink() {
		$icon = Icon::get(Icon::$SEARCH, 'Suche');
		//$href = 'inc/class.DataBrowser.search.php';
		// TODO For displaying search inside the databrowser ...
		// return Ajax::link($icon, DATA_BROWSER_ID, $href);
		return Ajax::window('<a href="call/window.search.php" title="Suche">'.$icon.'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $name
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function getSearchLink($name, $var) {
		// TODO: Just get $name, $column, $option, $value (may be arrays)
		return Ajax::window('<a href="'.self::getSearchLinkUrl($var).'">'.$name.'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function getSearchLinkUrl($var) {
		$var = str_replace(' ', '+', $var);

		return self::$SEARCH_URL.'?get=true&'.$var;
	}

	/**
	 * Get ajax-link for adding a training
	 * @return string
	 */
	private function getAddLink() {
		return Training::getCreateWindowLink();
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @return string HTML-link
	 */
	static function getLink($name, $start, $end) {
		$href = 'call/call.DataBrowser.display.php?start='.$start.'&end='.$end;
		return Ajax::link($name, DATA_BROWSER_ID, $href);
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the week
	 * @return string HTML-link
	 */
	static function getWeekLink($name, $time) {
		return self::getLink($name, Helper::Weekstart($time), Helper::Weekend($time));
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the month
	 * @return string HTML-link
	 */
	static function getMonthLink($name, $time) {
		return self::getLink($name,
			mktime(0, 0, 0, date("m", $time), 1, date("Y", $time)),
			mktime(23, 59, 50, date("m", $time)+1, 0, date("Y", $time)));
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $int Timestamp of the year
	 * @return string HTML-link
	 */
	static function getYearLink($name, $time) {
		return self::getLink($name,
			mktime(0, 0, 0, 1, 1, date("Y", $time)),
			mktime(23, 59, 50, 12, 31, date("Y", $time)));
	}

	/**
	 * Get previous timestamps depending on current time-interval (just an alias for getNextTimestamps($start, $end, true);)
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @return array Returns an array {'start', 'end'}
	 */
	static function getPrevTimestamps($start, $end) {
		return self::getNextTimestamps($start, $end, true);
	}

	/**
	 * Get next timestamps depending on current time-interval
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @return array Returns an array {'start', 'end'}
	 */
	static function getNextTimestamps($start, $end, $getPrev = false) {
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
?>
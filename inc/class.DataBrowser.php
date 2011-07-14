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

/**
 * Class: Frontend
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Ajax
 * @uses class::Training
 *
 * Last modified 2011/04/14 17:30 by Hannes Christiansen
 */
class DataBrowser {
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
		$this->days = array();
		$this->initShortSports();

		for ($w = 0; $w <= ($this->day_count-1); $w++)
			$this->days[] = $this->initDay($w);
	}

	/**
	 * Init array for one day {'date', 'shorts', 'trainings'}
	 * @param int $w Number of day in DataBrowser
	 * @return array
	 */
	private function initDay($w) {
		$shorts     = array();
		$trainings  = array();
		$time       = $this->timestamp_start;
		$time_start = mktime(0, 0, 0, date("m",$time), date("d",$time)+$w,   date("Y",$time));
		$time_end   = mktime(0, 0, 0, date("m",$time), date("d",$time)+$w+1, date("Y",$time));

		$data = $this->Mysql->fetchAsArray('SELECT `id`, `sportid` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($time_start-10).' AND '.($time_end-10).' ORDER BY `time` ASC');
		if (!empty($data)) {
			foreach ($data as $short)
				if (in_array($short['sportid'], $this->sports_short))
					$shorts[]    = $short['id'];
				else
					$trainings[] = $short['id'];
		}

		return array('date' => $time_start, 'shorts' => $shorts, 'trainings' => $trainings);
	}

	/**
	 * Init $this->sports_short
	 */
	private function initShortSports() {
		$this->sports_short = array();
		$sports = $this->Mysql->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sports` WHERE `short`=1');
		foreach ($sports as $sport)
			$this->sports_short[] = $sport['id'];
	}

	/**
	 * Display the DataBrowser
	 */
	public function display() {
		include('tpl/tpl.DataBrowser.php');
	}

	/**
	 * Display links to navigate in calendar
	 */
	private function displayNavigationLinks() {
		echo $this->getPrevLink().NL;
		echo $this->getLink(Helper::Month(date("m", $this->timestamp_start)),
							mktime(0, 0, 0, date("m", $this->timestamp_start), 1, date("Y", $this->timestamp_start)),
							mktime(23, 59, 50, date("m", $this->timestamp_start)+1, 0, date("Y", $this->timestamp_start))).', ';
		echo $this->getLink(date("Y", $this->timestamp_start),
							mktime(0, 0, 0, 1, 1, date("Y", $this->timestamp_start)),
							mktime(0, 0, 0, 12, 31, date("Y", $this->timestamp_start))).', ';
		echo $this->getLink(strftime("%W", $this->timestamp_start).'. Woche ',
							Helper::Weekstart($this->timestamp_start),
							Helper::Weekend($this->timestamp_end));
		echo $this->getNextLink().NL;	
	}

	/**
	 * Display specific icon-links
	 */
	private function displayIconLinks() {
		echo $this->getRefreshLink();
		echo $this->getCalenderLink();
		echo $this->getMonthKmLink();
		echo $this->getWeekKmLink();
		echo $this->getNaviSearchLink();
		echo $this->getAddLink();
	}

	/**
	 * Get link to navigation back
	 */
	private function getPrevLink() {
		$icon = Icon::get(Icon::$ARR_BACK, 'zur&uuml;ck');
		$timestamp_array = self::getPrevTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink($icon, $timestamp_array['start'], $timestamp_array['end']);
	}

	/**
	 * Get link to navigation forward
	 */
	private function getNextLink() {
		$icon = Icon::get(Icon::$ARR_NEXT, 'vorw&auml;rts');
		$timestamp_array = self::getNextTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink($icon, $timestamp_array['start'], $timestamp_array['end']);
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 */
	private function getRefreshLink() {
		$icon = Icon::get(Icon::$REFRESH, 'Aktuelles Datenblatt neuladen');
		return self::getLink($icon, $this->timestamp_start, $this->timestamp_end);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 */
	private function getCalenderLink() {
		$icon = Icon::get(Icon::$CALENDAR, 'Kalender-Auswahl');
		return Ajax::window('<a href="inc/tpl/window.DataBrowser.calendar.php" title="Kalender-Auswahl">'.$icon.'</a>');
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 */
	private function getMonthKmLink() {
		$icon = Icon::get(Icon::$MONTH_KM, 'Monatskilometer');
		return Ajax::window('<a href="inc/plugin/window.monatskilometer.php" title="Monatskilometer anzeigen">'.$icon.'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 */
	private function getWeekKmLink() {
		$icon = Icon::get(Icon::$WEEK_KM, 'Wochenkilometer');
		return Ajax::window('<a href="inc/plugin/window.wochenkilometer.php" title="Wochenkilometer anzeigen">'.$icon.'</a>');
	}

	/**
	 * Get ajax-link for searching
	 */
	private function getNaviSearchLink() {
		$href = 'inc/class.DataBrowser.search.php';
		$icon = Icon::get(Icon::$SEARCH, 'Suche');
		// TODO For displaying search inside the databrowser ...
		// return Ajax::link($icon, DATA_BROWSER_ID, $href);
		return Ajax::window('<a href="inc/tpl/window.search.php" title="Suche">'.$icon.'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $name
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 */
	static function getSearchLink($name, $var) {
		return Ajax::window('<a href="inc/tpl/window.search.php?get=true&'.$var.'">'.$name.'</a>', 'big');
	}

	/**
	 * Get ajax-link for adding a training
	 */
	private function getAddLink() {
		return Training::getCreateWindowLink();
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name
	 * @param int $start
	 * @param int $end
	 * @return string HTML-link
	 */
	static function getLink($name, $start, $end) {
		$href = 'inc/class.DataBrowser.display.php?start='.$start.'&end='.$end;
		return Ajax::link($name, DATA_BROWSER_ID, $href);
	}

	/**
	 * Get previous timestamps depending on current time-interval (just an alias for getNextTimestamps($start, $end, true);)
	 * @param int $start
	 * @param int $end
	 * @return array Returns an array {'start', 'end'}
	 */
	static function getPrevTimestamps($start, $end) {
		return self::getNextTimestamps($start, $end, true);
	}

	/**
	 * Get next timestamps depending on current time-interval
	 * @param int $start
	 * @param int $end
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
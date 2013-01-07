<?php
/**
 * Class: DataBrowser
 * @author Hannes Christiansen <mail@laufhannes.de>
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
	 * CSS-ID for refresh button
	 * @var string
	 */
	static public $REFRESH_BUTTON_ID = 'refreshDataBrowser';

	/**
	 * Timestamp for first day to be displayed
	 * @var int
	 */
	protected $timestamp_start;

	/**
	 * Timestamp for last day to be displayed
	 * @var int
	 */
	protected $timestamp_end;

	/**
	 * Number of days to be displayed
	 * @var int
	 */
	protected $day_count;

	/**
	 * Days to be displayed
	 * @var array
	 */
	protected $days;

	/**
	 * Array containing IDs for 'short' sports
	 * @var array
	 */
	protected $sports_short;

	/**
	 * Internal MySql-object
	 * @var Mysql
	 */
	protected $Mysql;

	/**
	 * Internal Error-object
	 * @var Error
	 */
	protected $Error;

	/**
	 * Internal Dataset-object
	 * @var Dataset
	 */
	protected $Dataset;

	/**
	 * Number of additional columns
	 * @var int
	 */
	protected $additionalColumns = 2;

	/**
	 * Boolean flag: show public link for trainings
	 * @var boolean
	 */
	protected $showPublicLink = false;

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
	protected function initInternalObjects() {
		$this->Mysql = Mysql::getInstance();
		$this->Error = Error::getInstance();
		$this->Dataset = new Dataset();
	}

	/**
	 * Init private timestamps from request
	 */
	protected function initTimestamps() {
		if (!isset($_GET['start']) || !isset($_GET['end'])) {
			switch (CONF_DB_DISPLAY_MODE) {
				case 'month':
					$this->timestamp_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
					$this->timestamp_end   = mktime(23, 59, 50, date("m")+1, 0, date("Y"));
					break;
				case 'week':
				default:
					$this->timestamp_start = Time::Weekstart(time());
					$this->timestamp_end   = Time::Weekend(time());
			}
		} else {
			$this->timestamp_start = $_GET['start'];
			$this->timestamp_end   = $_GET['end'];
		}

		$this->day_count = round(($this->timestamp_end - $this->timestamp_start) / 86400);
	}

	/**
	 * Init all days for beeing displayed
	 */
	protected function initDays() {
		$this->initShortSports();
		$this->initEmptyDays();

		$WhereNotPrivate = (FrontendShared::$IS_SHOWN && !CONF_TRAINING_LIST_ALL) ? 'AND is_public=1' : '';

		$AllTrainings = $this->Mysql->fetchAsArray('
			SELECT
				id,
				time,
				DATE(FROM_UNIXTIME(time)) as `date`
				'.$this->Dataset->getQuerySelectForAllDatasets().'
			FROM `'.PREFIX.'training`
			WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).'
				'.$WhereNotPrivate.'
			ORDER BY `time` ASC');

		foreach ($AllTrainings as $Training) {
			$w = Time::diffInDays($Training['time'], $this->timestamp_start);

			if (in_array($Training['sportid'], $this->sports_short))
				$this->days[$w]['shorts'][]    = $Training;
			else
				$this->days[$w]['trainings'][] = $Training;
		}
	}

	/**
	 * Init array with empty days
	 */
	protected function initEmptyDays() {
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
	protected function initShortSports() {
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
	protected function displayNavigationLinks() {
		echo $this->getPrevLink().NL;
		echo $this->getCalenderLink().NL;

		if ($this->timestamp_start < time() && time() < $this->timestamp_end)
			$timeForLinks = time();
		else
			$timeForLinks = $this->timestamp_start;

		echo self::getMonthLink(Time::Month(date("m", $timeForLinks)), $timeForLinks).', ';
		echo self::getYearLink(date("Y", $timeForLinks), $timeForLinks).', ';
		echo self::getWeekLink(date("W", $timeForLinks).'. Woche ', $timeForLinks);

		echo $this->getNextLink().NL;	
	}

	/**
	 * Display specific icon-links
	 */
	protected function displayIconLinks() {
		echo $this->getSharedListLink();
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
	protected function getPrevLink() {
		$timestamp_array = self::getPrevTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink(Icon::$BACK, $timestamp_array['start'], $timestamp_array['end'], 'zur&uuml;ck');
	}

	/**
	 * Get link to navigation forward
	 * @return string
	 */
	protected function getNextLink() {
		$timestamp_array = self::getNextTimestamps($this->timestamp_start, $this->timestamp_end);

		return self::getLink(Icon::$NEXT, $timestamp_array['start'], $timestamp_array['end'], 'vorw&auml;rts');
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 * @return string
	 */
	protected function getRefreshLink() {
		$Link = self::getLink(Ajax::tooltip(Icon::$REFRESH, 'Aktuelles Datenblatt neuladen'), $this->timestamp_start, $this->timestamp_end);

		return str_replace('<a ', '<a id="'.self::$REFRESH_BUTTON_ID.'" ', $Link);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 * @return string
	 */
	protected function getCalenderLink() {
		return '<span id="calendarLink" class="link" title="Kalender-Auswahl">'.Icon::$CALENDAR.'</span>';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	protected function getMonthKmLink() {
		return Ajax::window('<a href="call/window.monatskilometer.php">'.Ajax::tooltip(Icon::$BARS_BIG, 'Monatskilometer anzeigen').'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	protected function getWeekKmLink() {
		return Ajax::window('<a href="call/window.wochenkilometer.php">'.Ajax::tooltip(Icon::$BARS_SMALL, 'Wochenkilometer anzeigen').'</a>');
	}

	/**
	 * Get list to shared list
	 * @returns tring
	 */
	protected function getSharedListLink() {
		return SharedLinker::getListLinkForCurrentUser();
	}

	/**
	 * Get ajax-link for searching
	 * @return string
	 */
	protected function getNaviSearchLink() {
		return Ajax::window('<a href="'.self::$SEARCH_URL.'">'.Ajax::tooltip(Icon::$SEARCH, 'Trainings suchen').'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $name
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function getSearchLink($name, $var = '') {
		// TODO: Just get $name, $column, $option, $value (may be arrays)
		return Ajax::window('<a href="'.self::getSearchLinkUrl($var).'">'.$name.'</a>', 'big');
	}

	/**
	 * Get complete HTML-link for the search
	 * @param string $var Searchstring in format opt[typid]=is&val[typid][0]=3
	 * @return string
	 */
	static function getSearchLinkUrl($var) {
		if (empty($var))
			return self::$SEARCH_URL;

		$var = str_replace(' ', '+', $var);

		return self::$SEARCH_URL.'?get=true&'.$var;
	}

	/**
	 * Get ajax-link for adding a training
	 * @return string
	 */
	protected function getAddLink() {
		return TrainingCreator::getWindowLink();
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param string $title title for the link
	 * @return string HTML-link
	 */
	static function getLink($name, $start, $end, $title = '') {
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
	static function getWeekLink($name, $time) {
		return self::getLink($name, Time::Weekstart($time), Time::Weekend($time));
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
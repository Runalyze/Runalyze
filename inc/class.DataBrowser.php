<?php
/**
 * This file contains class::DataBrowser
 * @package Runalyze\DataBrowser
 */
/**
 * DataBrowser
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser
 */
class DataBrowser {
	/**
	 * CSS-ID for calendar-widget
	 * @var string
	 */
	static public $CALENDAR_ID = 'data-browser-calendar';

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
				`s` as `s_sum_with_distance`,
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
		echo $this->getCalenderLink().NBSP;
		echo $this->getPrevLink().NBSP;
		echo $this->getNextLink().NBSP;

		$timeForLinks = ($this->timestamp_start < time() && time() < $this->timestamp_end) ? time() : $this->timestamp_start;

		echo DataBrowserLinker::monthLink(Time::Month(date("m", $timeForLinks)), $timeForLinks).', ';
		echo DataBrowserLinker::yearLink(date("Y", $timeForLinks), $timeForLinks).', ';
		echo DataBrowserLinker::weekLink(date("W", $timeForLinks).'. Woche ', $timeForLinks);
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
		$timestamp_array = DataBrowserLinker::prevTimestamps($this->timestamp_start, $this->timestamp_end);

		return DataBrowserLinker::link(Icon::$BACK, $timestamp_array['start'], $timestamp_array['end'], 'zur&uuml;ck');
	}

	/**
	 * Get link to navigation forward
	 * @return string
	 */
	protected function getNextLink() {
		$timestamp_array = DataBrowserLinker::nextTimestamps($this->timestamp_start, $this->timestamp_end);

		return DataBrowserLinker::link(Icon::$NEXT, $timestamp_array['start'], $timestamp_array['end'], 'vorw&auml;rts');
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 * @return string
	 */
	protected function getRefreshLink() {
		$Link = DataBrowserLinker::link(Ajax::tooltip(Icon::$REFRESH, 'Aktuelles Datenblatt neuladen'), $this->timestamp_start, $this->timestamp_end);

		return str_replace('<a ', '<a id="'.self::$REFRESH_BUTTON_ID.'" ', $Link);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 * @return string
	 */
	protected function getCalenderLink() {
		return '<span id="calendar-link" class="link" title="Kalender-Auswahl">'.Icon::$CALENDAR.'</span>';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	protected function getMonthKmLink() {
		return Ajax::window('<a href="'.PlotSumData::$URL.'?type=month">'.Ajax::tooltip(Icon::$BARS_BIG, 'Monatstraining vergleichen').'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	protected function getWeekKmLink() {
		return Ajax::window('<a href="'.PlotSumData::$URL.'?type=week">'.Ajax::tooltip(Icon::$BARS_SMALL, 'Wochentraining vergleichen').'</a>');
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
		return Ajax::window('<a href="'.SearchLink::$WINDOW_URL.'">'.Ajax::tooltip(Icon::$SEARCH, 'Trainings suchen').'</a>', 'big');
	}

	/**
	 * Get ajax-link for adding a training
	 * @return string
	 */
	protected function getAddLink() {
		return ImporterWindow::link();
	}
}
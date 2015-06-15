<?php
/**
 * This file contains class::DataBrowser
 * @package Runalyze\DataBrowser
 */

use Runalyze\Configuration;

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
	 * Internal DB object
	 * @var \PDOforRunalyze
	 */
	protected $DB;

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
	 * Init pointer to DB/Error-object
	 */
	protected function initInternalObjects() {
		$this->DB    = DB::getInstance();
		$this->Error = Error::getInstance();
		$this->Dataset = new Dataset();
	}

	/**
	 * Init private timestamps from request
	 */
	protected function initTimestamps() {
		if (!isset($_GET['start']) || !isset($_GET['end'])) {
			$Mode = Configuration::DataBrowser()->mode();

			if ($Mode->showMonth()) {
				$this->timestamp_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
				$this->timestamp_end   = mktime(23, 59, 50, date("m")+1, 0, date("Y"));
			} else {
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

		$WhereNotPrivate = (FrontendShared::$IS_SHOWN && !Configuration::Privacy()->showPrivateActivitiesInList()) ? 'AND is_public=1' : '';

		$AllTrainings = $this->DB->query('
			SELECT
				id,
				time,
				`s` as `s_sum_with_distance`,
				DATE(FROM_UNIXTIME(time)) as `date`
				'.$this->Dataset->getQuerySelectForAllDatasets().'
			FROM `'.PREFIX.'training`
			WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).'
                        AND accountid = '.SessionAccountHandler::getId().'
				'.$WhereNotPrivate.'
			ORDER BY `time` ASC
		')->fetchAll();

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
		$sports = $this->DB->query('SELECT `id` FROM `'.PREFIX.'sport` WHERE `short`=1 AND accountid = '.SessionAccountHandler::getId())->fetchAll();

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
		echo $this->getCalenderLink();
		echo $this->getPrevLink();
		echo $this->getNextLink();
	}

	/**
	 * Display title
	 */
	protected function displayTitle() {
		$timeForLinks = ($this->timestamp_start < time() && time() < $this->timestamp_end) ? time() : $this->timestamp_start;

		echo DataBrowserLinker::monthLink(Time::Month(date("m", $timeForLinks)), $timeForLinks).', ';
		echo DataBrowserLinker::yearLink(date("Y", $timeForLinks), $timeForLinks).', ';
		echo DataBrowserLinker::weekLink(date("W", $timeForLinks).'. '.__('week') , $timeForLinks);
	}

	/**
	 * Display specific icon-links
	 */
	protected function displayIconLinks() {
		echo '<ul>';
		echo '<li>'.$this->getSharedListLink().'</li>';
		echo '<li>'.$this->getMonthKmLink().'</li>';
		echo '<li>'.$this->getWeekKmLink().'</li>';
		echo '<li>'.$this->getNaviSearchLink().'</li>';
		echo '<li>'.$this->getAddLink().'</li>';
		echo '</ul>';
	}

	/**
	 * Display hover links
	 */
	protected function displayHoverLinks() {
		echo $this->getConfigLink();
		echo $this->getRefreshLink();
	}

	/**
	 * Display config link
	 */
	protected function getConfigLink() {
		echo Ajax::window('<a class="tab" href="'.ConfigTabs::$CONFIG_URL.'?key=config_tab_dataset">'.Icon::$CONF.'</a>');
	}

	/**
	 * Get link to navigation back
	 * @return string
	 */
	protected function getPrevLink() {
		$timestamp_array = DataBrowserLinker::prevTimestamps($this->timestamp_start, $this->timestamp_end);

		return DataBrowserLinker::link(Icon::$BACK, $timestamp_array['start'], $timestamp_array['end'], __('back'));
	}

	/**
	 * Get link to navigation forward
	 * @return string
	 */
	protected function getNextLink() {
		$timestamp_array = DataBrowserLinker::nextTimestamps($this->timestamp_start, $this->timestamp_end);

		return DataBrowserLinker::link(Icon::$NEXT, $timestamp_array['start'], $timestamp_array['end'], __('next'));
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 * @return string
	 */
	protected function getRefreshLink() {
		$Link = DataBrowserLinker::link(Icon::$REFRESH, $this->timestamp_start, $this->timestamp_end);

		return str_replace('<a ', '<a id="'.self::$REFRESH_BUTTON_ID.'" '.Ajax::tooltip('', __('Reload current datasheet'), false, true), $Link);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 * @return string
	 */
	protected function getCalenderLink() {
		return '<span id="calendar-link" class="link" title="'.__('Calendar').'">'.Icon::$CALENDAR.'</span>';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	protected function getMonthKmLink() {
		return Ajax::window('<a href="'.PlotSumData::$URL.'?type=month" '.Ajax::tooltip('', __('Activity per month'), false, true).'>'.Icon::$BARS_BIG.'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	protected function getWeekKmLink() {
		return Ajax::window('<a href="'.PlotSumData::$URL.'?type=week" '.Ajax::tooltip('', __('Activity per week'), false, true).'>'.Icon::$BARS_SMALL.'</a>');
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
		return Ajax::window('<a href="'.SearchLink::$WINDOW_URL.'" '.Ajax::tooltip('', __('Search for an activity'), false, true).'>'.Icon::$SEARCH.'</a>', 'big');
	}

	/**
	 * Get ajax-link for adding a training
	 * @return string
	 */
	protected function getAddLink() {
		return ImporterWindow::link();
	}
}
<?php
/**
 * This file contains class::DataBrowser
 * @package Runalyze\DataBrowser
 */

use Runalyze\Configuration;
use Runalyze\Dataset;
use Runalyze\Model\Factory;
use Runalyze\Util\Time;

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
	const CALENDAR_ID = 'data-browser-calendar';

	/**
	 * CSS-ID for refresh button
	 * @var string
	 */
	const REFRESH_BUTTON_ID = 'refreshDataBrowser';

	/**
	 * Timestamp for first day to be displayed
	 * @var int
	 */
	protected $TimestampStart;

	/**
	 * Timestamp for last day to be displayed
	 * @var int
	 */
	protected $TimestampEnd;

	/**
	 * Number of days to be displayed
	 * @var int
	 */
	protected $DayCount;

	/**
	 * Days to be displayed
	 * @var array
	 */
	protected $Days;

	/**
	 * Array containing IDs for 'short' sports
	 * @var array
	 */
	protected $SportsShort;

	/**
	 * Array containing IDs for 'short' types
	 * @var array
	 */
	protected $TypesShort;

	/** @var int */
	protected $AccountID;

	/** @var \PDOforRunalyze */
	protected $DB;

	/** @var \Runalyze\Dataset\Configuration */
	protected $DatasetConfig;

	/** @var \Runalyze\Dataset\Query */
	protected $DatasetQuery;

	/** @var \Runalyze\Model\Factory */
	protected $Factory;

	/**
	 * Number of additional columns
	 * @var int
	 */
	protected $AdditionalColumns = 3;

	/**
	 * Boolean flag: show public link for trainings
	 * @var boolean
	 */
	protected $ShowPublicLink = false;

	/** @var bool */
	protected $ShowEditLink = true;

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
		$this->AccountID = SessionAccountHandler::getId();
		$this->DB = DB::getInstance();
		$this->DatasetConfig = new Dataset\Configuration($this->DB, $this->AccountID);
		$this->DatasetQuery = new Dataset\Query($this->DatasetConfig, $this->DB, $this->AccountID);
		$this->DatasetQuery->setAdditionalColumns(array('is_public'));
		$this->Factory = new Factory($this->AccountID);

		if (!Configuration::DataBrowser()->showEditLink()) {
			$this->ShowEditLink = false;
			$this->AdditionalColumns -= 1;
		}
	}

	/**
	 * Init private timestamps from request
	 */
	protected function initTimestamps() {
		if (!isset($_GET['start']) || !isset($_GET['end']) || !is_numeric($_GET['start']) || !is_numeric($_GET['end'])) {
			$Mode = Configuration::DataBrowser()->mode();

			if ($Mode->showMonth()) {
				$this->TimestampStart = mktime(0, 0, 0, date("m"), 1, date("Y"));
				$this->TimestampEnd   = mktime(23, 59, 50, date("m")+1, 0, date("Y"));
			} else {
				$this->TimestampStart = Time::weekstart(time());
				$this->TimestampEnd   = Time::weekend(time());
			}
		} else {
			$this->TimestampStart = $_GET['start'];
			$this->TimestampEnd   = $_GET['end'];
		}

		$this->DayCount = round(($this->TimestampEnd - $this->TimestampStart) / 86400);
	}

	/**
	 * Init all days for beeing displayed
	 */
	protected function initDays() {
		$this->initShortModes();
		$this->initEmptyDays();

		$Statement = $this->DatasetQuery->statementToFetchActivities($this->TimestampStart, $this->TimestampEnd);

		while ($Training = $Statement->fetch()) {
			$w = Time::diffInDays($Training['time'], $this->TimestampStart);

			if (in_array($Training['sportid'], $this->SportsShort) || in_array($Training['typeid'], $this->TypesShort)) {
				$this->Days[$w]['shorts'][]    = $Training;
			} else {
				$this->Days[$w]['trainings'][] = $Training;
			}
		}
	}

	/**
	 * Init array with empty days
	 */
	protected function initEmptyDays() {
		$this->Days = array();

		for ($w = 0; $w <= ($this->DayCount-1); $w++) {
			$this->Days[] = array(
				'date' => mktime(0, 0, 0, date("m",$this->TimestampStart), date("d",$this->TimestampStart)+$w, date("Y",$this->TimestampStart)),
				'shorts' => array(),
				'trainings' => array()
			);
		}
	}

	/**
	 * Init $this->sports_short
	 */
	protected function initShortModes() {
		$this->SportsShort = $this->DB->query('SELECT `id` FROM `'.PREFIX.'sport` WHERE `short`=1 AND accountid = '.$this->AccountID)->fetchAll(PDO::FETCH_COLUMN);
		$this->TypesShort = $this->DB->query('SELECT `id` FROM `'.PREFIX.'type` WHERE `short`=1 AND accountid = '.$this->AccountID)->fetchAll(PDO::FETCH_COLUMN);
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
		echo $this->getCurrentLink();
		echo $this->getNextLink();
	}

	/**
	 * Display title
	 */
	protected function displayTitle() {
		$timeForLinks = ($this->TimestampStart < time() && time() < $this->TimestampEnd) ? time() : $this->TimestampStart;

		echo DataBrowserLinker::monthLink(Time::month(date("m", $timeForLinks)), $timeForLinks).', ';
		echo DataBrowserLinker::yearLink(date("Y", $timeForLinks), $timeForLinks).', ';
		echo DataBrowserLinker::weekLink(Configuration::General()->weekStart()->phpWeek($timeForLinks).'. '.__('week') , $timeForLinks);
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
		$timestamp_array = DataBrowserLinker::prevTimestamps($this->TimestampStart, $this->TimestampEnd);

		return DataBrowserLinker::link(Icon::$BACK, $timestamp_array['start'], $timestamp_array['end'], __('back'));
	}

	/**
	 * Get link to navigation forward
	 * @return string
	 */
	protected function getNextLink() {
		$timestamp_array = DataBrowserLinker::nextTimestamps($this->TimestampStart, $this->TimestampEnd);

		return DataBrowserLinker::link(Icon::$NEXT, $timestamp_array['start'], $timestamp_array['end'], __('next'));
	}
	
	/**
	 * Get link to jump to today
	 * @return string
	 */
	protected function getCurrentLink() {
		return DataBrowserLinker::link('<i class="fa fa-fw fa-circle"></i>', '', '', __('today'));
	}

	/**
	 * Get ajax-link for reload this DataBrowser
	 * @return string
	 */
	protected function getRefreshLink() {
		$Link = DataBrowserLinker::link(Icon::$REFRESH, $this->TimestampStart, $this->TimestampEnd);

		return str_replace('<a ', '<a id="'.self::REFRESH_BUTTON_ID.'" '.Ajax::tooltip('', __('Reload current datasheet'), false, true), $Link);
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
	 * @returns string
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

	/**
	 * Get date string for given timestamp
	 * @param int $timestamp
	 * @return string
	 */
	protected function dateString($timestamp) {
		$date    = date('d.m.', $timestamp);
		$addLink = '';
		$weekDay = Time::weekday(date('w', $timestamp), true);

		if (Configuration::DataBrowser()->showCreateLink() && !FrontendShared::$IS_SHOWN) {
			$addLink = ImporterWindow::linkForDate($timestamp);
		}

		if (Time::isToday($timestamp)) {
			$weekDay = '<strong>'.$weekDay.'</strong>';
		}

		return $date.' '.$addLink.' '.$weekDay;
	}
}
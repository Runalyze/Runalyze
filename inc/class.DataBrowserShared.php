<?php
/**
 * This file contains class::DataBrowserShared
 * @package Runalyze\DataBrowser
 */
/**
 * Shared version of DataBrowser
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser
 */
class DataBrowserShared extends DataBrowser {
	/**
	 * Number of additional columns
	 * @var int
	 */
	protected $AdditionalColumns = 3;

	/**
	 * Boolean flag: show public link for trainings
	 * @var boolean
	 */
	protected $ShowPublicLink = true;

	/** @var bool */
	protected $ShowEditLink = false;

	/**
	 * Init pointer to DB/Error-object
	 */
	protected function initInternalObjects()
	{
		parent::initInternalObjects();

		if (!\Runalyze\Configuration::Privacy()->showPrivateActivitiesInList()) {
			$this->DatasetQuery->showOnlyPublicActivities();
		}
	}

	/**
	 * Init private timestamps from request
	 */
	protected function initTimestamps() {
		$this->TimestampStart = isset($_GET['start']) && is_numeric($_GET['start']) ? $_GET['start'] : mktime(0, 0, 0, date("m"), 1, date("Y"));
		$this->TimestampEnd   = isset($_GET['end']) && is_numeric($_GET['end']) ? $_GET['end'] : mktime(23, 59, 50, date("m")+1, 0, date("Y"));

		$this->DayCount = round(($this->TimestampEnd - $this->TimestampStart) / 86400);
	}

	/**
	 * Get ajax-link for choosing timestamps from calendar
	 * @return string
	 */
	protected function getCalenderLink() {
		return '';
	}

	/**
	 * Display specific icon-links
	 */
	protected function displayIconLinks() {
		echo $this->getMonthKmLink();
		echo $this->getWeekKmLink();
	}

	/**
	 * Display hover links
	 */
	protected function displayHoverLinks() {
		echo $this->getRefreshLink();
	}

	/**
	 * Get base url
	 * @return string
	 */
	public static function getBaseUrl() {
		return 'shared/'.Request::param('user').'/';
	}

	/**
	 * Get a ajax-link to a specified DataBrowser
	 * @param string $name Name to be displayed as link
	 * @param int $start Timestamp for first date in browser
	 * @param int $end Timestamp for last date in browser
	 * @param string $title title for the link
	 * @return string HTML-link
	 */
	public static function getLink($name, $start, $end, $title = '') {
		$href = self::getBaseUrl().'?start='.$start.'&end='.$end;

		return Ajax::link($name, DATA_BROWSER_SHARED_ID, $href, '', $title);
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	public static function getUrlForMonthKm() {
		return self::getBaseUrl().'?view=monthkm';
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	public static function getUrlForWeekKm() {
		return self::getBaseUrl().'?view=weekkm';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	protected function getMonthKmLink() {
		return Ajax::window('<a href="'.self::getUrlForMonthKm().'">'.Ajax::tooltip(Icon::$BARS_BIG, __('Activity per month')).'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	protected function getWeekKmLink() {
		return Ajax::window('<a href="'.self::getUrlForWeekKm().'">'.Ajax::tooltip(Icon::$BARS_SMALL, __('Activity per week')).'</a>');
	}
}
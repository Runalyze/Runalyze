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
	protected $additionalColumns = 3;

	/**
	 * Boolean flag: show public link for trainings
	 * @var boolean
	 */
	protected $showPublicLink = true;

	/**
	 * Init private timestamps from request
	 */
	protected function initTimestamps() {
		$this->timestamp_start = isset($_GET['start']) ? $_GET['start'] : mktime(0, 0, 0, date("m"), 1, date("Y"));
		$this->timestamp_end   = isset($_GET['end'])   ? $_GET['end']   : mktime(23, 59, 50, date("m")+1, 0, date("Y"));

		$this->day_count = round(($this->timestamp_end - $this->timestamp_start) / 86400);
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
	static function getBaseUrl() {
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
	static function getLink($name, $start, $end, $title = '') {
		$href = self::getBaseUrl().'?start='.$start.'&end='.$end;

		return Ajax::link($name, DATA_BROWSER_SHARED_ID, $href, '', $title);
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	static public function getUrlForMonthKm() {
		return self::getBaseUrl().'?view=monthkm';
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	static public function getUrlForWeekKm() {
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
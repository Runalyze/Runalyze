<?php
/**
 * Class: DataBrowserShared
 * @author Hannes Christiansen <mail@laufhannes.de>
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
	 * Display links to navigate in calendar
	 */
	protected function displayNavigationLinks() {
		echo $this->getPrevLink().NL;
		// TODO: echo $this->getCalenderLink().NL;

		echo self::getMonthLink(Time::Month(date("m", $this->timestamp_start)), $this->timestamp_start).', ';
		echo self::getYearLink(date("Y", $this->timestamp_start), $this->timestamp_start).', ';
		echo self::getWeekLink(strftime("%W", $this->timestamp_start).'. Woche ', $this->timestamp_start);

		echo $this->getNextLink().NL;	
	}

	/**
	 * Display specific icon-links
	 */
	protected function displayIconLinks() {
		//echo $this->getMonthKmLink();
		//echo $this->getWeekKmLink();
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
		$href = 'shared/'.Request::param('user').'/?start='.$start.'&end='.$end;

		return Ajax::link($name, DATA_BROWSER_SHARED_ID, $href, '', $title);
	}
}
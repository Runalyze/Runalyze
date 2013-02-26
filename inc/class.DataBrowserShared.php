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
		echo $this->getMonthKmLink();
		echo $this->getWeekKmLink();
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

	/**
	 * Get URL for month km
	 * @return string
	 */
	static public function getUrlForMonthKm() {
		return 'shared/'.Request::param('user').'/?view=monthkm';
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	static public function getUrlForWeekKm() {
		return 'shared/'.Request::param('user').'/?view=weekkm';
	}

	/**
	 * Get ajax-link for showing month-kilometer
	 * @return string
	 */
	protected function getMonthKmLink() {
		return Ajax::window('<a href="'.self::getUrlForMonthKm().'">'.Ajax::tooltip(Icon::$BARS_BIG, 'Monatskilometer anzeigen').'</a>');
	}

	/**
	 * Get ajax-link for showing week-kilometer
	 * @return string
	 */
	protected function getWeekKmLink() {
		return Ajax::window('<a href="'.self::getUrlForWeekKm().'">'.Ajax::tooltip(Icon::$BARS_SMALL, 'Wochenkilometer anzeigen').'</a>');
	}
}
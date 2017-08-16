<?php
/**
 * This file contains class::DataBrowserShared
 * @package Runalyze\DataBrowser
 */

use Runalyze\Util\LocalTime;
use Runalyze\Dataset;
use Runalyze\View;

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
		$this->TimestampStart = isset($_GET['start']) && is_numeric($_GET['start']) ? $_GET['start'] : LocalTime::fromString('first day of this month 00:00:00')->getTimestamp();
		$this->TimestampEnd   = isset($_GET['end']) && is_numeric($_GET['end']) ? $_GET['end'] : LocalTime::fromString('last day of this month 23:59:59')->getTimestamp();

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
		return 'athlete/'.Request::param('user').'';
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

		return Ajax::link($name, 'publicList', $href, '', $title);
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	public static function getUrlForMonthKm() {
		return self::getBaseUrl().'?type=month';
	}

	/**
	 * Get URL for month km
	 * @return string
	 */
	public static function getUrlForWeekKm() {
		return self::getBaseUrl().'?type=week';
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

	/**
	 * Additional columns that are shown next to date columns
	 * @param \Runalyze\View\Dataset\Table $table
	 * @param \Runalyze\Dataset\Context $context
	 * @return string html string that must contain `$this->AdditionalColumns - 2` columns
	 */
	protected function codeForAdditionalColumnsForActivity(View\Dataset\Table $table, Dataset\Context $context)
	{
		return $table->codeForPublicIcon($context);
	}
}

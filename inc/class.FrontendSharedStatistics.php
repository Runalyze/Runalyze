<?php
/**
 * This file contains class::FrontendSharedStatistics
 * @package Runalyze\Frontend
 */

use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;

/**
 * Class for general statistics shown in shared list
 *
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class FrontendSharedStatistics {
	/**
	 * Parent FrontendSharedList
	 * @var FrontendSharedList 
	 */
	protected $FrontendSharedList = null;

	/**
	 * Statistic Tabs
	 * @var AjaxTabs
	 */
	protected $StatisticTabs = null;

	/**
	 * Construct statistics
	 * @param FrontendSharedList $Parent
	 */
	public function __construct(FrontendSharedList &$Parent) {
		$this->FrontendSharedList = $Parent;
	}

	/**
	 * Display general statistics
	 */
	public function display() {
		$this->StatisticTabs = new AjaxTabs('public-tabs');
		$this->addAllStatisticTabs();
		$this->configureStatisticTabs();
	}

	/**
	 * Configure statistic tabs
	 */
	protected function configureStatisticTabs() {
		$this->StatisticTabs->setHeader( sprintf( __('Activity data of %s'), $this->FrontendSharedList->getUsername() ) );
		$this->StatisticTabs->setFirstTabActive();
		$this->StatisticTabs->display();
	}

	/**
	 * Add all statistic tabs
	 */
	protected function addAllStatisticTabs() {
		$this->addTabForGeneralStatistics();
		$this->addTabForComparisonOfYears();
		//$this->addTabForOtherStatistics();
	}

	/**
	 * Add tab for general statistics
	 */
	protected function addTabForGeneralStatistics() {
		$User = $this->FrontendSharedList->getUser();

		$Stats = DB::getInstance()->query('
			SELECT
				SUM(1) as num,
				SUM(distance) as dist_sum,
				SUM(s) as time_sum
			FROM `'.PREFIX.'training`
			WHERE `accountid`="'.$User['id'].'"
			GROUP BY `accountid`
			LIMIT 1
		')->fetch();

		$Content = '
			<table class="fullwidth">
				<tbody>
					<tr>
						<td class="b">'.__('Total distance:').'</td>
						<td>'.Distance::format($Stats['dist_sum']).'</td>
						<td class="b">'.__('Number of activities:').'</td>
						<td>'.$Stats['num'].'x</td>
						<td class="b">'.__('Registered since:').'</td>
						<td>'.date('d.m.Y', $User['registerdate']).'</td>
					</tr>
					<tr>
						<td class="b">'.__('Total duration:').'</td>
						<td>'.Duration::format($Stats['time_sum']).'</td>
						<td class="b">'.__('First activity:').'</td>
						<td>'.date('d.m.Y', START_TIME).'</td>
						<td class="b">'.__('Last login:').'</td>
						<td>'.date('d.m.Y', $User['lastaction']).'</td>
					</tr>
				</tbody>
			</table>';

		$this->StatisticTabs->addTab(__('General statistics'), 'statistics-general', $Content);
	}

	/**
	 * Add tab for comparison of years
	 */
	protected function addTabForComparisonOfYears() {
		$Content = '';
		$Factory = new PluginFactory();

		if ($Factory->isInstalled('RunalyzePluginStat_Statistiken')) {
			$Plugin = $Factory->newInstance('RunalyzePluginStat_Statistiken');
			$Content .= ($Plugin->getYearComparisonTable());
		}

		if ($Factory->isInstalled('RunalyzePluginStat_Wettkampf')) {
			if ($Content != '') {
				$Content .= '<tbody><tr class="no-zebra no-border"><td colspan="'.(date("Y") - START_YEAR + 2).'">&nbsp;</td></tr></tbody>';
			}

			$Plugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
			$Content .= ($Plugin->getYearComparisonTable());
		}

		if ($Content != '') {
			$this->StatisticTabs->addTab( __('Year on year').' ('.__('Running').')', 'statistics-years', $Content);
		}
	}

	/**
	 * Remove <table> from a string to extract <tbody>/<thead>...
	 * @param string $string
	 * @return string
	 */
	private function extractTbody($string) {
		return str_replace(
				array('<thead>', '</thead>'),
				array('<tbody class="asThead">', '</tbody>'),
				strip_tags($string, '<thead><tbody><th><tr><td><em><strong><a><span><i><img>')
			);
	}

	/**
	 * Add tab for other statistics
	 */
	protected function addTabForOtherStatistics() {
		$Content = 'Test';

		$this->StatisticTabs->addTab( __('Miscellaneous'), 'statistics-other', $Content);
	}
}
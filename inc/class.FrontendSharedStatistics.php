<?php
/**
 * This file contains class::FrontendSharedStatistics
 * @package Runalyze\Frontend
 */
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
		$this->StatisticTabs->setHeader('Trainingsdaten von '.$this->FrontendSharedList->getUsername());
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

		$Stats = Mysql::getInstance()->fetchSingle('
			SELECT
				SUM(1) as num,
				SUM(distance) as dist_sum,
				SUM(s) as time_sum
			FROM `'.PREFIX.'training`
			WHERE `accountid`="'.$User['id'].'"
			GROUP BY `accountid`
		');

		$Content = '
			<table class="small fullWidth">
				<tbody>
					<tr>
						<td class="b">Gesamte Trainingsdistanz:</td>
						<td>'.Running::Km($Stats['dist_sum']).'</td>
						<td class="b">Anzahl Trainings</td>
						<td>'.$Stats['num'].'x</td>
						<td class="b">Angemeldet seit:</td>
						<td>'.date('d.m.Y', $User['registerdate']).'</td>
					</tr>
					<tr>
						<td class="b">Gesamte Trainingsdauer:</td>
						<td>'.Time::toString($Stats['time_sum']).'</td>
						<td class="b">Erstes Training</td>
						<td>'.date('d.m.Y', START_TIME).'</td>
						<td class="b">Letzter Login:</td>
						<td>'.date('d.m.Y', $User['lastaction']).'</td>
					</tr>
				</tbody>
			</table>';

		$this->StatisticTabs->addTab('Allgemeines', 'statistics-general', $Content);
	}

	/**
	 * Add tab for comparison of years
	 */
	protected function addTabForComparisonOfYears() {
		$Content = '';

		if (Plugin::isInstalled('RunalyzePluginStat_Statistiken')) {
			$Plugin = Plugin::getInstanceFor('RunalyzePluginStat_Statistiken');
			$Content .= $this->extractTbody($Plugin->getYearComparisonTable());
		}

		if (Plugin::isInstalled('RunalyzePluginStat_Wettkampf')) {
			if ($Content != '')
				$Content .= '<tbod><tr><td colspan="'.(date("Y") - START_YEAR + 2).'">&nbsp;</td></tr></tbody>';

			$Plugin = Plugin::getInstanceFor('RunalyzePluginStat_Wettkampf');
			$Content .= $this->extractTbody($Plugin->getYearComparisonTable());
		}

		if ($Content != '') {
			$Content = '<table class="small r fullWidth">'.NL.$Content.NL.'</table>'.NL;
			$this->StatisticTabs->addTab('Jahresvergleich (Laufen)', 'statistics-years', $Content);
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

		$this->StatisticTabs->addTab('Sonstiges', 'statistics-other', $Content);
	}
}
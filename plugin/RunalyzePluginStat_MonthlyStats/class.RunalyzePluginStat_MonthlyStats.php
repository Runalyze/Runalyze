<?php
/**
 * This file contains the class of the RunalyzePluginStat "Laufabc".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_MonthlyStats';

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Configuration;

/**
 * Class: RunalyzePluginStat_Laufabc
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_MonthlyStats extends PluginStat {
	private $KmData = array();
	private $maxKm = 1;
	private $maxs = 1;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Monthly Stats');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return sprintf(__('How many %s/hours did you do per month'), Configuration::General()->distanceUnitSystem()->distanceUnit());
	}

	/**
	 * Init data
	 */
	protected function prepareForDisplay() {
		$this->setAnalysisNavigation();
		$this->setSportsNavigation(true, false);
		$this->initData();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayStyle();
		$this->displayData();
	}

	private function setAnalysisNavigation() {
		if ($this->dat == '') $this->dat = 'km';
		$LinkList = '<li class="with-submenu"><span class="link">' . $this->getAnalysisType() . '</span><ul class="submenu">';
		$LinkList .= '<li' . ('km' == $this->dat ? ' class="active"' : '') . '>' . $this->getInnerLink(__('by distance'), $this->sportid, $this->year, 'km') . '</li>';
		$LinkList .= '<li' . ('s' == $this->dat ? ' class="active"' : '') . '>' . $this->getInnerLink(__('by time'), $this->sportid, $this->year, 's') . '</li>';
		$LinkList .= '</ul></li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	private function getAnalysisType() {
		$types = ['km' => __('by distance'),
			's' => __('by time')];
		return $types[$this->dat];
	}

	/**
	 * Display style
	 */
	private function displayStyle() {
		echo '<style type="text/css">';
		echo '.analysis-table td { position: relative; vertical-align: bottom}';
		echo '.analysis-table td .analysis-bar { position: absolute; right: 3px; bottom: 2px; display: block; height: 2px; max-with: 100%; background-color: #800; }';
		echo '</style>';
	}

	/**
	 * Get bar
	 * @param float $percentage
	 * @return string
	 */
	private function getCircleFor($percentage) {
		$opacity = min(1, round($percentage * 0.8 / 100, 2) + 0.2);
		return ' <i class="fa fa-circle" style="width: 30px; text-align:center;  color: #800; opacity:' . $opacity . '; font-size: ' . floor(sqrt($percentage) * 30 / 10) . 'px"></i>';
	}

	/**
	 * Display the table with summed data for every month
	 */
	private function displayData() {
		echo '<table class="analysis-table fullwidth zebra-style r">';
		echo '<thead>' . HTML::monthTr(8, 1) . '</thead>';
		echo '<tbody>';

		if (empty($this->KmData)) {
			echo '<tr><td colspan="13" class="c"><em>' . __('No activities found.') . '</em></td></tr>';
		}

		foreach ($this->KmData as $y => $Data) {
			echo '<tr><td class="b l">' . $y . '</td>';

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m])) {
					if ($this->dat == 'km') {
						echo '<td title="' . Distance::format($Data[$m]['distance']) . '">' . Distance::format($Data[$m]['distance']) . $this->getCircleFor(100 * $Data[$m]['distance'] / $this->maxKm) . '</td>';
						//echo '<td style="vertical-align: bottom;">' . $tooltip . $circle . '</td>';
					} else {
						echo '<td title="' . $Data[$m]['s'] . '">' . Duration::format($Data[$m]['s']) . $this->getCircleFor(100 * $Data[$m]['s'] / $this->maxs) . '</td>';
					}
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Initialize $this->ABCData
	 */
	private function initData() {
		$result = DB::getInstance()->query('
			SELECT
				SUM(`distance`) as `distance`,
				SUM(`s`) as `s`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `' . PREFIX . 'training`
				WHERE `sportid`=' . $this->sportid . ' AND `accountid`=' . SessionAccountHandler::getId() . '
			GROUP BY `year` DESC, `month` ASC'
		)->fetchAll();

		foreach ($result as $dat) {
			if ($dat['distance'] > 0 || $dat['s'] > 0) {
				$this->KmData[$dat['year']][$dat['month']] = array('distance' => $dat['distance'], 's' => $dat['s']);
				if ($dat['distance'] > $this->maxKm) $this->maxKm = $dat['distance'];
				if ($dat['s'] > $this->maxs) $this->maxs = $dat['s'];
			}
		}
	}
}

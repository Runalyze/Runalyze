<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Sports".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Sports';

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;

/**
 * Class: RunalyzePluginPanel_Sports
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Sports extends PluginPanel {
	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Sports');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Summary of your activities for each sport.');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$OldTableView = new PluginConfigurationValueBool('show_as_table', __('Old table view'));
		$OldTableView->setDefaultValue(false);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($OldTableView);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		if (!$this->Configuration()->value('show_as_table')) {
			$this->removePanelContentPadding = true;
		}
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$html = '<ul>';

		foreach ($this->getTimeset() as $i => $timeset) {
			$html .= '<li>'.Ajax::change($timeset['name'], 'sports', '#sports_'.$i).'</li>';
		}

		return $html.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Query = '
			SELECT
				`sportid`,
				COUNT(`id`) as `count`,
				SUM(`distance`) as `distance`,
				SUM(`s`) as `time_in_s`,
				SUM(`distance` > 0) as `count_distance`
			FROM `'.PREFIX.'training`
			WHERE
				`time` >=:start
			GROUP BY `sportid`
			ORDER BY `distance` DESC, `time_in_s` DESC';
		$Request = DB::getInstance()->prepare($Query);

		echo '<div id="sports">';
	
		foreach ($this->getTimeset() as $i => $timeset) {
			echo '<div id="sports_'.$i.'" class="change"'.($i==0 ? '' : ' style="display:none;"').'>';

			$Request->bindValue('start', $timeset['start'], PDO::PARAM_INT);
			$Request->execute();
			$data = $Request->fetchAll();

			if ($this->Configuration()->value('show_as_table')) {
				$this->showDataInTableView($data, $timeset);
			} else {
				if (empty($data)) {
					echo '<div class="panel-content"><p><em>'.__('No data available since').' '.date("d.m.Y", $timeset['start']).'.</em></p></div>';
				} else {
					echo '<div class="'.BoxedValue::$SURROUNDING_DIV.' at-bottom">';
					$this->showDataAsBoxedValues($data);
					echo '</div>';
				}
			}

			echo '</div>';
		}
	
		echo '</div>';
	}

	/**
	 * Show boxed values
	 * @param array $data
	 */
	private function showDataAsBoxedValues($data) {
		foreach ($data as $dat) {
			// TODO: Define the decision (distance or time) somehow in the configuration
			$Sport = new Sport($dat['sportid']);

			$Value = new BoxedValue();
			$Value->setIcon($Sport->Icon());
			$Value->setInfo($Sport->name());
			$Value->defineAsFloatingBlock('w50');

			if ($dat['count_distance'] >= $dat['count']/2) {
				$Distance = new Distance($dat['distance']);
				$Value->setValue( $Distance->stringKilometer(false, false) );
				$Value->setUnit('km');
			} else {
				$Duration = new Duration($dat['time_in_s']);
				$Value->setValue($Duration->string(Duration::FORMAT_WITH_HOURS));
			}

			$Value->display();
		}
	}

	/**
	 * Show data in table view
	 * @param array $data
	 * @param array $timeset
	 */
	private function showDataInTableView($data, $timeset) {
		if (empty($data)) {
			echo '<p><em>'.__('No data available.').'</em></p>';
		} else {
			foreach ($data as $dat) {
				$Sport = new Sport($dat['sportid']);

				$result = $dat['count_distance'] >= $dat['count']/2
					? Distance::format($dat['distance'])
					: Duration::format($dat['time_in_s']);

				echo '<p><span class="right"><small><small>('.sprintf( __('%u-times'), Helper::Unknown($dat['count'], '0')).')</small></small> '.$result.'</span> ';
				echo $Sport->Icon().' <strong>'.$Sport->name().'</strong></p>';
			}
		}

		echo '<small class="right">'.__('since').' '.date("d.m.Y", $timeset['start']).'</small>';
		echo HTML::clearBreak();
	}

	/**
	 * Get the timeset as array for this panel
	 */
	private function getTimeset() {
		$timeset = array(
			array(
				'name'	=> __('Month'),
				'start'	=> mktime(0,0,0,date("m"),1,date("Y"))
			),
			array(
				'name'	=> __('Year'),
				'start'	=> mktime(0,0,0,1,1,date("Y"))
			),
			array(
				'name'	=> __('Total'),
				'start'	=> START_TIME
			)
		);
	
		return $timeset;
	}
}
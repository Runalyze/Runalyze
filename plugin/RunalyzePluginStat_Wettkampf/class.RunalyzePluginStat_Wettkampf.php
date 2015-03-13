<?php
/**
 * This file contains class::RunalyzePluginStat_Wettkampf
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\View\Icon;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Data\Weather;
use Runalyze\Activity\PersonalBest;

use Runalyze\Plugin\Statistic\Races\RaceContainer;

$PLUGINKEY = 'RunalyzePluginStat_Wettkampf';
/**
 * Plugin "Wettkampf"
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Wettkampf extends PluginStat {
	/**
	 * @var array
	 */
	private $PBdistances = array();

	/**
	 * @var \Runalyze\Plugin\Statistic\Races\RaceContainer 
	 */
	protected $RaceContainer;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Races');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Personal bests and everything else related to your races.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('This plugin lists all your races. It shows you a summary of all your races and'.
				'your personal bests (over all distances with at least two results).') );
		echo HTML::p(
			__('In addition, it plots the trend of your results over a specific distance.'.
				'If you run a race just for fun, you can mark it as a \'fun race\' to ignore it in the plot.') );

		echo HTML::info(
			__('You can define the activity type for races in your configuration.') );

		echo HTML::warning(
			__('Make sure that your activities hold the correct distance.'.
				'Only races with exactly 10.00 km will be considered as a race over 10 kilometers.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueFloat('main_distance', __('Main distance'), '', 10) );
		$Configuration->addValue( new PluginConfigurationValueArray('pb_distances', __('Distances for yearly comparison'), '', array(1, 3, 5, 10, 21.1, 42.2)) );
		$Configuration->addValue( new PluginConfigurationValueHiddenArray('fun_ids', '', '', array()) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Set own navigation
	 */
	protected function setOwnNavigation() {
		$LinkList  = '';
		$LinkList .= '<li>'.Ajax::change(__('Personal bests'), 'statistics-inner', '#bestzeiten', 'triggered').'</li>';
		$LinkList .= '<li>'.Ajax::change(__('All races'), 'statistics-inner', '#wk-tablelist').'</li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->setOwnNavigation();
		$this->loadRaces();
	}

	/**
	 * Load races
	 */
	protected function loadRaces() {
		require_once __DIR__.'/RaceContainer.php';

		$this->RaceContainer = new RaceContainer();
		$this->RaceContainer->fetchData();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->handleGetData();
		$this->displayDivs();
	}

	/**
	 * Get table for year comparison - not to use within this plugin!
	 * @return string
	 */
	public function getYearComparisonTable() {
		ob_start();
		$this->loadRaces();
		$this->displayPersonalBestYears();
		return ob_get_clean();
	}

	/**
	 * Display all divs 
	 */
	private function displayDivs() {
		echo HTML::clearBreak();

		echo '<div id="bestzeiten" class="change">';
		$this->displayPersonalBests();
		echo '</div>';

		echo '<div id="wk-tablelist" class="change" style="display:none;">';
		$this->displayAllCompetitions();
		$this->displayWeatherStatistics();
		echo '</div>';
	}

	/**
	 * Display all competitions
	 */
	private function displayAllCompetitions() {
		$this->displayTableStart('wk-table');

		foreach ($this->RaceContainer->allRaces() as $data) {
			$this->displayWkTr($data);
		}

		if (!isset($data)) {
			$this->displayEmptyTr( __('There are no races.') );
		}

		$this->displayTableEnd('wk-table');
	}

	/**
	 * Display all personal bests
	 */
	private function displayPersonalBests() {
		$this->displayTableStart('pb-table');
		$this->displayPersonalBestsTRs();
		$this->displayTableEnd('pb-table');

		if (!empty($this->PBdistances)) {
			$this->displayPersonalBestsImages();
		}

		$this->displayPersonalBestYears();
	}

	/**
	 * Display all table-rows for personal bests
	 */
	private function displayPersonalBestsTRs() {
		$this->PBdistances = array();
		$AllDistances = $this->RaceContainer->distances();
		sort($AllDistances);

		foreach ($AllDistances as $distance) {
			$Races = $this->RaceContainer->races((float)$distance);

			if (count($Races) > 1 || in_array($distance, $this->Configuration()->value('pb_distances'))) {
				$this->PBdistances[] = $distance;

				$PB = PHP_INT_MAX;
				$PBdata = array();

				foreach ($Races as $data) {
					if ($data['s'] < $PB) {
						$PBdata = $data;
						$PB = $data['s'];
					}
				}

				$this->displayWKTr($PBdata);
			}
		}

		if (empty($this->PBdistances)) {
			$this->displayEmptyTr('<em>'.__('There are no races for the given distances.').'</em>');
		}
	}

	/**
	 * Display all image-links for personal bests
	 */
	private function displayPersonalBestsImages() {
		$SubLinks = array();
		foreach ($this->PBdistances as $km) {
			$name       = Distance::format($km, $km <= 3, 1);
			$SubLinks[] = Ajax::flotChange($name, 'bestzeitenFlots', 'bestzeit'.($km*1000));
		}
		$Links = array(array('tag' => '<a href="#">'.__('Choose distance').'</a>', 'subs' => $SubLinks));

		echo '<div class="databox" style="float:none;padding:0;width:490px;margin:20px auto;">';
		echo '<div class="panel-heading">';
		echo '<div class="panel-menu">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
		echo '<h1>'.__('Results trend').'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';

		$display_km = $this->PBdistances[0];
		if (in_array($this->Configuration()->value('main_distance'), $this->PBdistances))
			$display_km = $this->Configuration()->value('main_distance');

		echo '<div id="bestzeitenFlots" class="flot-changeable" style="position:relative;width:482px;height:192px;">';
		foreach ($this->PBdistances as $km) {
			echo Plot::getInnerDivFor('bestzeit'.($km*1000), 480, 190, $km != $display_km);
			$_GET['km'] = $km;
			include 'Plot.Bestzeit.php';
		}
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display comparison for all years for personal bests
	 */
	private function displayPersonalBestYears() {
		$year = array();
		$dists = array();
		$kms = (is_array($this->Configuration()->value('pb_distances'))) ? $this->Configuration()->value('pb_distances') : array(3, 5, 10, 21.1, 42.2);
		foreach ($kms as $km)
			$dists[$km] = array('sum' => 0, 'pb' => INFINITY);

		if ($this->RaceContainer->num() == 0)
			return;

		foreach ($this->RaceContainer->allRaces() as $wk) {
			$wk['y'] = date('Y', $wk['time']);

			if (!isset($year[$wk['y']])) {
				$year[$wk['y']] = $dists;
				$year[$wk['y']]['sum'] = 0;
				$year['sum'] = 0;
			}
			$year[$wk['y']]['sum']++;
			foreach($kms as $km)
				if ($km == $wk['distance']) {
					$year[$wk['y']][$km]['sum']++;
					if ($wk['s'] < $year[$wk['y']][$km]['pb'])
						$year[$wk['y']][$km]['pb'] = $wk['s'];
				}
		}

		echo '<table class="fullwidth zebra-style">';
		echo '<thead>';
		echo '<tr>';
		echo '<th></th>';

		$Years = array_keys($year);
		sort($Years);

		foreach ($Years as $y)
			if ($y != 'sum')
				echo '<th>'.$y.'</th>';

		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		PersonalBest::activateStaticCache();
		PersonalBest::lookupDistances($kms);

		foreach ($kms as $i => $km) {
			echo '<tr class="r"><td class="b">'.Distance::format($km, $km <= 3, 1).'</td>';

			foreach ($Years as $key) {
				$y = $year[$key];

				if ($key != 'sum') {
					if ($y[$km]['sum'] != 0) {
						$PB = new PersonalBest($km);
						$distance = Duration::format($y[$km]['pb']);

						if ($PB->seconds() == $y[$km]['pb']) {
							$distance = '<strong>'.$distance.'</strong>';
						}

						echo '<td>'.$distance.' <small>'.$y[$km]['sum'].'x</small></td>';
					} else {
						echo '<td><em><small>---</small></em></td>';
					}
				}
			}

			echo '</tr>';
		}

		echo '<tr class="top-spacer no-zebra r">';
		echo '<td class="b">'.__('In total').'</td>';

		foreach ($Years as $key) {
			if ($key != 'sum') {
				$y = $year[$key];
				echo '<td>'.$year[$key]['sum'].'x</td>';
			}
		}

		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display table start
	 * @param string $id
	 */
	private function displayTableStart($id) {
		echo('
			<table class="fullwidth zebra-style" id="'.$id.'">
				<thead>
					<tr class="c">
						<th class="{sorter: false}">&nbsp;</th>
						<th class="{sorter: \'germandate\'}">'.__('Date').'</th>
						<th>'.__('Name').'</th>
						<th class="{sorter: \'distance\'}">'.__('Distance').'</th>
						<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>
						<th>'.__('Pace').'</th>
						<th>'.__('Heart rate').'</th>
						<th class="{sorter: \'temperature\'}">'.__('Weather').'</th>
					</tr>
				</thead>
				<tbody>');
	}

	/**
	 * Display table-row for a competition
	 * @param array $data
	 */
	private function displayWKTr(array $data) {
		$Activity = new Activity\Object($data);
		$Linker = new Linker($Activity);
		$Dataview = new Dataview($Activity);

		echo '<tr class="r">
				<td>'.$this->getIconForCompetition($data['id']).'</td>
				<td class="c small">'.$Linker->weekLink().'</a></td>
				<td class="l"><strong>'.$Linker->linkWithComment().'</strong></td>
				<td>'.$Dataview->distance(1).'</td>
				<td>'.$Dataview->duration()->string(Duration::FORMAT_COMPETITION).'</td>
				<td class="small">'.$Dataview->pace()->value().'</td>
				<td class="small">'.Helper::Unknown($Activity->hrAvg()).' / '.Helper::Unknown($Activity->hrMax()).' bpm</td>
				<td class="small">'.($Activity->weather()->isEmpty() ? '' : $Activity->weather()->fullString()).'</td>
			</tr>';
	}

	/**
	 * Display an empty table-row
	 * @param string $text [optional]
	 */
	private function displayEmptyTr($text = '') {
		echo '<tr class="a"><td colspan="8">'.$text.'</td></tr>';
	}

	/**
	 * Display table end
	 * @param string $id
	 */
	private function displayTableEnd($id) {
		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor('#'.$id, true);
	}

	/**
	 * Display statistics for weather
	 */
	private function displayWeatherStatistics() {
		$Condition = new Weather\Condition(0);
		$Strings = array();

		$Weather = DB::getInstance()->query('SELECT SUM(1) as num, weatherid FROM `'.PREFIX.'training` WHERE `typeid`='.Configuration::General()->competitionType().' AND `weatherid`!='.Weather\Condition::UNKNOWN.' GROUP BY `weatherid` ORDER BY `weatherid` ASC')->fetchAll();
		foreach ($Weather as $W) {
			$Condition->set($W['weatherid']);
			$Strings[] = $W['num'].'x '.$Condition->icon()->code();
		}

		if (!empty($Strings)) {
			echo '<strong>'.__('Weather statistics').':</strong> ';
			echo implode(', ', $Strings);
			echo '<br><br>';
		}
	}

	/**
	 * Get linked icon for this competition
	 * @param int $id ID of the training
	 * @return string
	 */
	private function getIconForCompetition($id) {
		if ($this->isFunCompetition($id)) {
			$tag = 'nofun';
			$icon = new Icon( Icon::CLOCK_GRAY );
			$icon->setTooltip( __('Fun race | Click to mark this activity as a \'normal race\'.') );
		} else {
			$tag = 'fun';
			$icon = new Icon( Icon::CLOCK );
			$icon->setTooltip( __('Race | Click to mark this activity as a \'fun race\'.') );
		}

		return $this->getInnerLink($icon->code(), 0, 0, $tag.'-'.$id);
	}

	/**
	 * Handle data from get-variables
	 */
	private function handleGetData() {
		if (isset($_GET['dat']) && strlen($_GET['dat']) > 0) {
			$parts = explode('-', $_GET['dat']);
			$tag   = $parts[0];
			$id    = $parts[1];

			$FunIDs = $this->Configuration()->value('fun_ids');

			if ($tag == 'fun' && is_numeric($id)) {
				$FunIDs[] = $id;
			} elseif ($tag == 'nofun' && is_numeric($id)) {
				if (($index = array_search($id, $FunIDs)) !== FALSE)
					unset($FunIDs[$index]);
			}

			$this->Configuration()->object('fun_ids')->setValue($FunIDs);
			$this->Configuration()->update('fun_ids');
		}
	}

	/**
	 * Is this competition just for fun?
	 * @param int $id
	 * @return bool
	 */
	public function isFunCompetition($id) {
		return (in_array($id, $this->Configuration()->value('fun_ids')));
	}
}
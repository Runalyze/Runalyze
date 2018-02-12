<?php
/**
 * This file contains class::RunalyzePluginStat_Wettkampf
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\AgeGrade\Lookup;
use Runalyze\AgeGrade\Table\FemaleTable;
use Runalyze\AgeGrade\Table\MaleTable;
use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\RaceResult;
use Runalyze\Model\Factory;
use Runalyze\View\Activity\Linker;
use Runalyze\View;
use Runalyze\View\Icon;
use Runalyze\View\Tooltip;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Data\Weather;
use Runalyze\Activity\PersonalBest;
use Runalyze\Profile\Athlete\Gender;

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

	/** @var null|\Runalyze\AgeGrade\Lookup */
	protected $AgeGradeLookup = null;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Race results');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Personal bests and everything else related to your competitions.');
	}

	/**
	 * Display long description
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('This plugin lists all your competitions. It shows you a summary of all race results and '.
				'of your personal bests (over all distances with at least two results).') );
		echo HTML::p(
			__('In addition, it plots the trend of your results over a specific distance.'.
				'If you do a race just for fun, you can mark it as a \'fun race\' to ignore it in the plot.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueDistance('main_distance', __('Main distance'), '', 10) );
		$Configuration->addValue( new PluginConfigurationValueDistances('pb_distances', __('Distances for yearly comparison'), '', array(1, 3, 5, 10, 21.1, 42.2)) );
		$Configuration->addValue( new PluginConfigurationValueHiddenArray('fun_ids', '', '', array()) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Set own navigation
	 */
	protected function setOwnNavigation() {
		$LinkList  = '';
		$LinkList .= '<li>'.Ajax::change(__('Personal bests'), 'statistics-inner', '#personal-bests', 'triggered').'</li>';
		$LinkList .= '<li>'.Ajax::change(__('All race results'), 'statistics-inner', '#all-competitions').'</li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	/**
	 * Init data
	 */
	protected function prepareForDisplay() {
		$this->setSportsNavigation();
		$this->setOwnNavigation();
		$this->initAgeGradeLookup();
		$this->loadRaces();
	}

	protected function initAgeGradeLookup() {
		$athlete = \Runalyze\Context::Athlete();

		if (Configuration::General()->runningSport() == $this->sportid && $athlete->knowsGender() && $athlete->knowsAge()) {
			$table = Gender::FEMALE === $athlete->gender() ? new FemaleTable() : new MaleTable();
			$this->AgeGradeLookup = new Lookup($table, $athlete->age());
		}
	}

	/**
	 * @return bool
	 */
	protected function knowsAgeGrade() {
		return null !== $this->AgeGradeLookup;
	}

	/**
	 * Load races
	 */
	protected function loadRaces() {
		require_once __DIR__.'/RaceContainer.php';

		$this->RaceContainer = new RaceContainer($this->sportid);
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

		echo '<div id="personal-bests" class="change">';
		$this->displayPersonalBests();
		echo '</div>';

		echo '<div id="all-competitions" class="change" style="display:none;">';
		$this->displayAllCompetitions();
		$this->displayWeatherStatistics();
		echo '</div>';
	}

	/**
	 * Display all competitions
	 */
	private function displayAllCompetitions() {
		$this->displayTableStart('all-competitions-table');

		foreach ($this->RaceContainer->allRaces() as $data) {
			$this->displayWkTr($data);
		}

		if (!isset($data)) {
			$this->displayEmptyTr( __('There are no races.') );
		}

		$this->displayTableEnd('all-competitions-table');
	}

	/**
	 * Display all personal bests
	 */
	private function displayPersonalBests() {
		$this->displayTableStart('pb-table');
		$this->displayPersonalBestsTRs();
		$this->displayTableEnd('pb-table');
		$this->displayHintForPersonalBests();

		if (Configuration::General()->runningSport() == $this->sportid) {
		    $this->displayLinkToPerformanceChart();
        }

		if (!empty($this->PBdistances)) {
			$this->displayPersonalBestsImages();
		}

		$this->displayPersonalBestYears();
	}

	private function displayHintForPersonalBests() {
		echo HTML::info(
			__('This list shows all distances selected for yearly comparison or with at least two results.').'<br>'.
			__('Distances have to match exactly, especially 21.10 km and 42.20 km for (half-)marathons.')
		);
	}

	private function displayLinkToPerformanceChart() {
	    echo '<div class="c blocklist blocklist-width-auto margin-top">
            <a class="window" href="my/raceresult/performance-chart"><i class="fa fa-fw fa-dashboard"></i> <strong>'.__('Performance chart').'</strong></a>
        </div>';
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
					if ($data['official_time'] < $PB) {
						$PBdata = $data;
						$PB = $data['official_time'];
					}
				}

				$this->displayWKTr($PBdata);
			}
		}

		if (empty($this->PBdistances)) {
			$this->displayEmptyTr('<em>'.__('There are no race results for the given distances.').'</em>');
		}
	}

	/**
	 * Display all image-links for personal bests
	 */
	private function displayPersonalBestsImages() {
		$display_km = $this->PBdistances[0];
		if (in_array($this->Configuration()->value('main_distance'), $this->PBdistances))
			$display_km = $this->Configuration()->value('main_distance');

		$SubLinks = array();
		foreach ($this->PBdistances as $km) {
			$name       = (new Distance($km))->stringAuto(true, 1);
			$SubLinks[] = Ajax::flotChange($name, 'bestzeitenFlots', 'bestzeit'.($km*1000));
		}
		$Links = array(array('tag' => '<span class="link">'.(new Distance($display_km))->stringAuto(true, 1).'</span>', 'subs' => $SubLinks));

		echo '<div class="databox" style="float:none;padding:0;width:490px;margin:20px auto;">';
		echo '<div class="panel-heading">';
		echo '<div class="panel-menu">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
		echo '<h1>'.__('Results trend').'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';

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
			$dists[(string)$km] = array('sum' => 0, 'pb' => INFINITY);

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
				if ($km == $wk['official_distance']) {
					$year[$wk['y']][(string)$km]['sum']++;
					if ($wk['official_time'] < $year[$wk['y']][(string)$km]['pb'])
						$year[$wk['y']][(string)$km]['pb'] = $wk['official_time'];
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
		PersonalBest::lookupDistances($kms, $this->sportid);

		foreach ($kms as $km) {
			echo '<tr class="r"><td class="b">'.(new Distance($km))->stringAuto(true, 1).'</td>';

			foreach ($Years as $key) {
				$y = $year[$key];

				if ($key != 'sum') {
					if ($y[(string)$km]['sum'] != 0) {
						$PB = new PersonalBest($km, $this->sportid);
						$distance = Duration::format($y[(string)$km]['pb']);

						if ($PB->seconds() == $y[(string)$km]['pb']) {
							$distance = '<strong>'.$distance.'</strong>';
						}

						echo '<td>'.$distance.' <small>'.$y[(string)$km]['sum'].'x</small></td>';
					} else {
						echo '<td><em><small>---</small></em></td>';
					}
				}
			}

			echo '</tr>';
		}

		$icon = (new Icon(Icon::INFO))->code();
		$tooltip = new Tooltip(__('This includes races of all distances, not only those selected for yearly comparison.'));
		$tooltip->setPosition(Tooltip::POSITION_RIGHT);
		$tooltip->wrapAround($icon);

		echo '<tr class="top-spacer no-zebra r">';
		echo '<td class="b">'.__('In total').' '.$icon.'</td>';

		foreach ($Years as $key) {
			if ($key != 'sum') {
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
		echo '<table class="fullwidth zebra-style" id="'.$id.'">
				<thead>
					<tr class="c">
						<th class="{sorter: false}" colspan="'.($this->knowsAgeGrade() ? 7 : 6).'">'.__('Official distance & time').'</th>
						<th class="{sorter: false}" colspan="3">'.__('Activity details').'</th>
						<th class="{sorter: false}" colspan="3">'.__('Placement').'</th>
						<th class="{sorter: false}">&nbsp;</th>
					</tr>
					<tr class="c">
						<th class="{sorter: false}">&nbsp;</th>
						<th class="{sorter: \'germandate\'}">'.__('Date').'</th>
						<th colspan="2">'.__('Name').'</th>
						<th class="{sorter: \'distance\'}">'.__('Distance').'</th>
						<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>
						'.($this->knowsAgeGrade() ? '<th>'.Ajax::tooltip(__('Age grade').(new \Runalyze\View\Icon(Runalyze\View\Icon::INFO))->code(), __('Your result in proportion to the world best performance in your age/gender.') ).'</th>' : '').'
						<th>'.__('Pace').'</th>
						<th '.(new Tooltip(__('Heart rate')))->attributes().'>'.__('HR').'</th>
						<th class="{sorter: \'temperature\'}">'.__('Weather').'</th>
						<th>'.__('Overall').'</th>
						<th '.(new Tooltip(__('Age group')))->attributes().'>'.__('AG').'</th>
						<th '.(new Tooltip(__('male/female')))->attributes().'>'.__('M/F').'</th>
						<th class="{sorter: false}">&nbsp;</th>
					</tr>
				</thead>
				<tbody>';
	}

	/**
	 * Display table-row for a competition
	 * @param array $data
	 */
	private function displayWKTr(array $data) {
		$Activity = new Activity\Entity($data);

		$Linker = new Linker($Activity);
		$RaceResult = new RaceResult\Entity($data);
		$RaceResultView = new View\RaceResult\Dataview($RaceResult);

		if ($this->knowsAgeGrade() && $this->AgeGradeLookup->getMinimalDistance() <= $RaceResult->officialDistance()) {
			$ageGrade = $this->AgeGradeLookup->getAgeGrade(
				$RaceResult->officialDistance(),
				$RaceResult->officialTime(),
				date('Y') - date('Y', $Activity->timestamp())
			);
			$ageGradeString = number_format(100 * $ageGrade->getPerformance(), 2).' &#37;';
			$ageGradeTooltip = __('Age standard').': '.Duration::format($ageGrade->getAgeStandard()).', '.
				__('Open standard').': '.Duration::format($ageGrade->getOpenStandard()).'<br>'.
				'<small><em>'.sprintf(__('via tables by %s'), 'Alan Jones / WMA / USATF').'</em></small>';
		} else {
			$ageGradeString = '-';
			$ageGradeTooltip = '';
		}

		echo '<tr class="r">
				<td>'.$this->getEditIconForRaceResult($data['id']).'</td>
				<td class="c small">'.$Linker->weekLink().'</a></td>
				<td>'.$this->getEditIconForActivity($data['id']).'</td>
				<td class="l"><strong>'.$Linker->link($RaceResult->name()).'</strong></td>
				<td>'.$RaceResultView->officialDistance(null, $Activity->isTrack()).'</td>
				<td>'.$RaceResultView->officialTime()->string(Duration::FORMAT_COMPETITION).'</td>
				'.($this->knowsAgeGrade() ? '<td class="small"'.('' != $ageGradeTooltip ? ' rel="tooltip" title="'.$ageGradeTooltip.'"' : '').'>'.$ageGradeString.'</td>' : '').'
				<td class="small">'.$RaceResultView->pace($this->sportid)->valueWithAppendix().'</td>
				<td class="small">'.Helper::Unknown($Activity->hrAvg()).' / '.Helper::Unknown($Activity->hrMax()).' bpm</td>
				<td class="small">'.($Activity->weather()->isEmpty() ? '' : $Activity->weather()->fullString($Activity->isNight())).'</td>
				<td>'.$RaceResultView->placementTotalWithTooltip().'</td>
				<td>'.$RaceResultView->placementAgeClassWithTooltip().'</td>
				<td>'.$RaceResultView->placementGenderWithTooltip().'</td>
				<td>'.$this->getIconForCompetition($data['id']).'</td>
			</tr>';
	}

	/**
	 * Display an empty table-row
	 * @param string $text [optional]
	 */
	private function displayEmptyTr($text = '') {
		echo '<tr class="a"><td colspan="11">'.$text.'</td></tr>';
	}

	/**
	 * Display table end
	 * @param string $id
	 */
	private function displayTableEnd($id) {
		echo '</tbody>';
		echo '</table>';

		if ($this->RaceContainer->num() < 5) {
			echo HTML::info(__(
				'You can mark any activity as race by checking the respective checkbox in the activity\'s form.'
			));
		}

		Ajax::createTablesorterFor('#'.$id, true);
	}

	/**
	 * Display statistics for weather
	 */
	private function displayWeatherStatistics() {
		$Condition = new Weather\Condition(0);
		$Strings = array();

		$Weather = DB::getInstance()->query('SELECT SUM(1) as num, tr.weatherid FROM `'.PREFIX.'raceresult` r LEFT JOIN `'.PREFIX.'training` tr ON tr.id=r.activity_id WHERE tr.`weatherid`!='.\Runalyze\Profile\Weather\WeatherConditionProfile::UNKNOWN.' AND r.`accountid` = '.SessionAccountHandler::getId().' GROUP BY tr.`weatherid` ORDER BY tr.`weatherid` ASC')->fetchAll();

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
	 * Get edit icon for this competition
	 * @param int $id ID of the training
	 * @return string
	 */
	private function getEditIconForActivity($id) {
		$SportIcon = (new Factory())->sport($this->sportid)->icon()->code();
		$url = (new Linker(new Runalyze\Model\Activity\Entity(['id' => $id])))->editUrl();
		$code = Ajax::window('<a href="'.$url.'">'.$SportIcon.'</a>', 'small');

		$Tooltip = new Tooltip(__('Edit the activity'));
		$Tooltip->wrapAround($code);

		return $code;
	}

	/**
	 * Get edit icon for this competition
	 * @param int $id ID of the training
	 * @return string
	 */
	private function getEditIconForRaceResult($id) {
		$code = (new Icon('fa-pencil'))->code();

		$Tooltip = new Tooltip(__('Edit the race result details'));
		$Tooltip->setPosition(Tooltip::POSITION_RIGHT);
		$Tooltip->wrapAround($code);

		return Ajax::window('<a href="my/raceresult/'.$id.'">'.$code.'</a>');
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

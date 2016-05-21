<?php
/**
 * This file contains class::RunalyzePluginStat_Wettkampf
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\RaceResult;
use Runalyze\Model\Factory;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\View;
use Runalyze\View\Icon;
use Runalyze\View\Tooltip;
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
		$this->loadRaces();
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
		PersonalBest::lookupDistances($kms, $this->sportid);

		foreach ($kms as $km) {
			echo '<tr class="r"><td class="b">'.(new Distance($km))->stringAuto(true, 1).'</td>';

			foreach ($Years as $key) {
				$y = $year[$key];

				if ($key != 'sum') {
					if ($y[$km]['sum'] != 0) {
						$PB = new PersonalBest($km, $this->sportid);
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
						<th class="{sorter: false}" colspan="6">'.__('Official distance & time').'</th>
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
		$Dataview = new Dataview($Activity);
		$RaceResult = new RaceResult\Entity($data);
		$RaceResultView = new View\RaceResult\Dataview($RaceResult);

		echo '<tr class="r">
				<td>'.$this->getEditIconForRaceResult($data['id']).'</td>
				<td class="c small">'.$Linker->weekLink().'</a></td>
				<td>'.$this->getEditIconForActivity($data['id']).'</td>
				<td class="l"><strong>'.$Linker->link($RaceResult->name()).'</strong></td>
				<td>'.$RaceResultView->officialDistance(null, $Activity->isTrack()).'</td>
				<td>'.$RaceResultView->officialTime()->string(Duration::FORMAT_COMPETITION).'</td>
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

		Ajax::createTablesorterFor('#'.$id, true);
	}

	/**
	 * Display statistics for weather
	 */
	private function displayWeatherStatistics() {
		$Condition = new Weather\Condition(0);
		$Strings = array();

		$Weather = DB::getInstance()->query('SELECT SUM(1) as num, tr.weatherid FROM `'.PREFIX.'raceresult` r LEFT JOIN `'.PREFIX.'training` tr ON tr.id=r.activity_id WHERE tr.`weatherid`!='.Weather\Condition::UNKNOWN.' AND r.`accountid` = '.SessionAccountHandler::getId().' GROUP BY tr.`weatherid` ORDER BY tr.`weatherid` ASC')->fetchAll();

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
		$code = Ajax::window('<a href="'.Linker::EDITOR_URL.'?id='.$id.'">'.$SportIcon.'</a>', 'small');

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

		return Ajax::window('<a href="plugin/RunalyzePluginStat_Wettkampf/window.raceResult.php?rid='.$id.'">'.$code.'</a>');
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
	
	/**
	 * RaceResult Formular
	 */
	 public function raceResultForm($id) {
	 	$Factory = Runalyze\Context::Factory();
	 	$RaceResult = $Factory->raceresult($id);

		if (isset($_GET['delete'])) {
			$this->deleteRaceResult($RaceResult);
			echo HTML::info(__('Race result was deleted.'));
		} else {
		 	if ($_POST) {
			 	$RaceResult = $this->validatePostDataAndUpdateEntity($RaceResult);
		 	}
		 	
			$Factory->clearCache('raceresult', $id);
		 	$Formular = new Formular('plugin/RunalyzePluginStat_Wettkampf/window.raceResult.php?rid='.$id, 'post');
			$Formular->setId('raceresult');
			$Formular->addCSSclass('ajax');
			$Formular->addCSSclass('no-automatic-reload');
			$FieldsetDetails = new FormularFieldset( __('Details') );

			$FieldName = new FormularInput('name', __('Event').' '.Ajax::tooltip('<i class="fa fa-fw fa-question-circle"></i>', __('If you participate in an event multiple times you should always enter the same name, i.e. don\'t append the event\'s number or year.')), $RaceResult->name()); 
			$FieldName->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
			$FieldName->setSize( FormularInput::$SIZE_MIDDLE);
	
			$FieldOfficiallyMeasured = new FormularCheckbox('officially_measured', __('Officially measured').' '.Ajax::tooltip('<i class="fa fa-fw fa-question-circle"></i>', __('Was the course officially measured?')), $RaceResult->officiallyMeasured() );
			$FieldOfficiallyMeasured->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
			
			$FieldOfficialDistance = new FormularInput('official_distance', __('Official distance'), str_replace(',', '.', (new Distance($RaceResult->officialDistance()))->stringAuto(false, 2)));
			$FieldOfficialDistance->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
			$FieldOfficialDistance->setUnit(FormularUnit::$KM);
			$FieldOfficialTime = new FormularInput('official_time', __('Official time'), Duration::format($RaceResult->officialTime()) );
			$FieldOfficialTime->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
	
			$FieldsetDetails->addFields(array($FieldName, $FieldOfficiallyMeasured, $FieldOfficialDistance, $FieldOfficialTime) );
	
			$FieldsetDetails->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100);
			$FieldsetPlacement = new FormularFieldset( __('Placement') );
			$FieldsetPlacement->addInfo( __('Your official placement.') );
			$FieldsetPlacement->addField( new FormularInput('placement_total', __('Total'),  $RaceResult->placeTotal() ?: ''));
			$FieldsetPlacement->addField( new FormularInput('placement_ageclass', __('Age group'), $RaceResult->placeAgeclass() ?: '') );
			$FieldsetPlacement->addField( new FormularInput('placement_gender', __('Gender'), $RaceResult->placeGender() ?: '') );
			$FieldsetParticipants = new FormularFieldset( __('Participants') );
			$FieldsetParticipants->addInfo( __('Number of participants in every category') );
			$FieldsetParticipants->addField( new FormularInput('participants_total', __('Total'), $RaceResult->participantsTotal() ?: '') );
			$FieldsetParticipants->addField( new FormularInput('participants_ageclass', __('Age group'), $RaceResult->participantsAgeclass() ?: '') );
			$FieldsetParticipants->addField( new FormularInput('participants_gender', __('Gender'), $RaceResult->participantsGender() ?: '') );
			$Formular->addFieldset( $FieldsetDetails );
			$Formular->addFieldset( $FieldsetPlacement );
			$Formular->addFieldset( $FieldsetParticipants );
			
			if (!$RaceResult->isEmpty()) {
				$deleteLink = Ajax::link('<strong>'.__('Delete this race result').' &raquo;</strong>', 'ajax', 'plugin/RunalyzePluginStat_Wettkampf/window.raceResult.php?rid='.$id.'&delete');
				$deleteInfo = __('This will only delete the entry as competition. The activity itself will stay untouched.');
	
				$FieldsetDeletion = new FormularFieldset( __('Delete race result') );
				$FieldsetDeletion->setCollapsed();
				$FieldsetDeletion->addWarning($deleteLink.'<br><br><small>'.$deleteInfo.'</small>');
				$Formular->addFieldset( $FieldsetDeletion );
			}
			
			if ($RaceResult->isEmpty()) {
				$Formular->addSubmitButton( __('Add race result to activity'), 'submit' );
			} else {
				$Formular->addSubmitButton( __('Save'), 'submit' );
			}
			
			$Formular->setSubmitButtonsCentered();
			$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
	
			$Formular->display();
		}
	}
	 
	/**
	 * Validate RaceResult Formular
	 * @param \Runalyze\Model\RaceResult\Entity $RaceResult
	 * @return \Runalyze\Model\RaceResult\Entity updated race result
	 */
	protected function validatePostDataAndUpdateEntity(RaceResult\Entity $RaceResult) {
		$OldRaceResult = clone $RaceResult;
		$somethingFailed = false;
		$fieldsToValidate = $this->fieldsToValidate();

		foreach ($fieldsToValidate as $key => $fieldValidation) {
			$validationResult = FormularValueParser::validatePost($key, $fieldValidation[1], $fieldValidation[2]);

			if (true !== $validationResult) {
				$somethingFailed = true;
				FormularField::setKeyAsFailed($key);
				FormularField::addValidationFailure(false !== $validationResult ? $validationResult : __('Invalid input.'));
			}

			$RaceResult->set($fieldValidation[0], $_POST[$key]);
		}
		if (!$somethingFailed && !$OldRaceResult->isEmpty()) {
			$this->updateRaceResult($RaceResult, $OldRaceResult);
		} elseif(!$somethingFailed && $OldRaceResult->isEmpty()) {
			if (is_numeric($_GET['rid'])) {
				$RaceResult->set('activity_id', $_GET['rid']);
				$this->insertRaceResult($RaceResult);
			}
		}

		return $RaceResult;
	}

	/**
	 * @return array ['post-key' => ['entity-key', $parser, $parserOptions], ...]
	 */
	protected function fieldsToValidate() {
		return array(
			'name' => array(RaceResult\Entity::NAME, FormularValueParser::$PARSER_STRING, array('notempty' => true)),
			'official_distance' => array(RaceResult\Entity::OFFICIAL_DISTANCE, FormularValueParser::$PARSER_DISTANCE, array()),
			'official_time' => array(RaceResult\Entity::OFFICIAL_TIME, FormularValueParser::$PARSER_TIME, array()),
			'officially_measured' => array(RaceResult\Entity::OFFICIALLY_MEASURED, FormularValueParser::$PARSER_BOOL, array()),
			'placement_total' => array(RaceResult\Entity::PLACE_TOTAL,FormularValueParser::$PARSER_INT,  array('null' => true, 'max' => $_POST['participants_total'])),
			'placement_ageclass' => array(RaceResult\Entity::PLACE_AGECLASS,FormularValueParser::$PARSER_INT, array('null' => true, 'max' => $_POST['participants_ageclass'])),
			'placement_gender' => array(RaceResult\Entity::PLACE_GENDER,FormularValueParser::$PARSER_INT, array('null' => true, 'max' => $_POST['participants_gender'])),
			'participants_total' => array(RaceResult\Entity::PARTICIPANTS_TOTAL, FormularValueParser::$PARSER_INT, array('null' => true, 'min' => $_POST['placement_total'])),
			'participants_ageclass' => array(RaceResult\Entity::PARTICIPANTS_AGECLASS, FormularValueParser::$PARSER_INT, array('null' => true, 'min' => $_POST['placement_ageclass'])),
			'participants_gender' => array(RaceResult\Entity::PARTICIPANTS_GENDER, FormularValueParser::$PARSER_INT, array('null' => true, 'min' => $_POST['placement_gender']))
		);
	}
	 
	/**
	 * Update race result
	 * 
	 * This will echo an ajax reload command.
	 * 
	 * @param \Runalyze\Model\RaceResult\Entity $NewRaceResult
	 * @param \Runalyze\Model\RaceResult\Entity $OldRaceResult
	 */
	protected function updateRaceResult(RaceResult\Entity $NewRaceResult, RaceResult\Entity $OldRaceResult) {
		$Update = new RaceResult\Updater(DB::getInstance(), $NewRaceResult, $OldRaceResult);
		$Update->setAccountID(SessionAccountHandler::getId());
		$Update->update();

		Ajax::setReloadFlag(AJAX::$RELOAD_PLUGINS);
		echo Ajax::getReloadCommand();
	}
	
	/**
	 * Insert race result
	 * 
	 * This will echo an ajax reload command.
	 * 
	 * @param \Runalyze\Model\RaceResult\Entity $RaceResult
	 */
	protected function insertRaceResult(RaceResult\Entity $RaceResult) {
		$Insert = new RaceResult\Inserter(DB::getInstance(), $RaceResult);
		$Insert->setAccountID(SessionAccountHandler::getId());
		$Insert->insert();

		Ajax::setReloadFlag(AJAX::$RELOAD_PLUGINS);
		echo Ajax::getReloadCommand();
	}
	
	/**
	 * Delete race result
	 * 
	 * This will echo an ajax reload command.
	 * 
	 * @param \Runalyze\Model\RaceResult\Entity $RaceResult
	 */
	protected function deleteRaceResult(RaceResult\Entity $RaceResult) {
		$Deleter = new RaceResult\Deleter(DB::getInstance(), $RaceResult);
		$Deleter->setAccountID(SessionAccountHandler::getId());
		$Deleter->delete();

		Ajax::setReloadFlag(AJAX::$RELOAD_PLUGINS);
		echo Ajax::getReloadCommand();
	}
}
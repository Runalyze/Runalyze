<?php
/**
 * This file contains class::SearchResults
 * @package Runalyze\Search
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\Energy;
use Runalyze\Activity\Pace;
use Runalyze\Activity\StrideLength;
use Runalyze\Configuration;
use Runalyze\Util\LocalTime;
use Runalyze\Activity\Temperature;
use Runalyze\Data\Weather\WindSpeed;

/**
 * Search results
 *
 * @author Hannes Christiansen
 * @package Runalyze\Search
 */
class SearchResults {
	/**
	 * @var int
	 */
	const MAX_LIMIT_FOR_MULTI_EDITOR = 100;

	/**
	 * Allowed keys to search for
	 * @var array
	 */
	protected $AllowedKeys = array();

	/** @var array */
	protected $KeysThatShouldIgnoreNull = array();

	/** @var array */
	protected $KeysThatShouldIgnoreZero = array();

	/**
	 * Colspan
	 * @var int
	 */
	protected $Colspan = 0;

	/**
	 * Trainings
	 * @var array
	 */
	protected $Trainings = array();

	/**
	 * Total number of trainings
	 * @var int
	 */
	protected $TotalNumberOfTrainings = 0;

	/**
	 * Page
	 * @var int
	 */
	protected $Page = 0;

	/**
	 * Search and show trainings=
	 * @var boolean
	 */
	protected $WithResults = true;

	/**
	 * Results per page
	 * @var int
	 */
	protected $ResultsPerPage;

	/** @var int */
	protected $AccountID;

	/** @var \PDOforRunalyze */
	protected $DB;

	/** @var \Runalyze\Dataset\Configuration */
	protected $DatasetConfig;

	/** @var \Runalyze\Dataset\Query */
	protected $DatasetQuery;

	/**
	 * Constructor
	 * @param boolean $withResults
	 */
	public function __construct($withResults = true) {
		$this->WithResults = $withResults;

		$this->setAllowedKeys();
		$this->setKeysThatShouldIgnoreNull();
		$this->setKeysThatShouldIgnoreZero();

        $this->ResultsPerPage = (is_numeric($_POST['resultsPerPage'])) ? $_POST['resultsPerPage'] : 20;


        if ($withResults) {
			$this->initDataset();
			$this->searchTrainings();
		}
	}

	/**
	 * Set allowed keys
	 *
	 * These keys are valid for 'sort by' and for any condition.
	 * `sportid` and `time` are valid anyway, they don't need to be set here.
	 */
	protected function setAllowedKeys() {
		// Keys are sorted as in structure.sql
		$this->AllowedKeys = array(
			'typeid',
			'is_public',
			'is_track',
			'distance',
			's',
			'elapsed_time',
			'elevation',
            'climb_score',
            'percentage_hilly',
			'kcal',
			'pulse_avg',
			'pulse_max',
			'vo2max',
			'vo2max_with_elevation',
			'use_vo2max',
			'fit_vo2max_estimate',
			'fit_recovery_time',
			'fit_hrv_analysis',
			'fit_training_effect',
			'fit_performance_condition',
			'rpe',
			'trimp',
			'cadence',
			'power',
			'total_strokes',
			'swolf',
			'stride_length',
			'groundcontact',
			'groundcontact_balance',
			'vertical_oscillation',
			'vertical_ratio',
			'temperature',
			'wind_speed',
			'wind_deg',
			'humidity',
			'pressure',
			'is_night',
			'weatherid',
			'route',
			'title',
			'partner',
			'notes'
		);
	}

	/**
	 * The following keys will add a `IS NOT NULL` if they have a condition (or order by)
	 */
	protected function setKeysThatShouldIgnoreNull() {
		$this->KeysThatShouldIgnoreNull = [
		    'climb_score',
			'percentage_hilly',
			'fit_training_effect',
			'fit_performance_condition',
			'rpe',
			'temperature',
			'wind_speed',
			'wind_deg',
			'humidity',
			'pressure'
		];
	}

	/**
	 * The following keys will add a `!= 0` if they have a condition (or order by)
	 */
	protected function setKeysThatShouldIgnoreZero() {
		$this->KeysThatShouldIgnoreZero = [
			'distance',
			'pulse_avg',
			'pulse_max',
			'vo2max',
			'fit_vo2max_estimate',
			'fit_recovery_time',
			'fit_hrv_analysis',
			'cadence',
			'power',
			'total_strokes',
			'swolf',
			'stride_length',
			'groundcontact',
			'groundcontact_balance',
			'vertical_oscillation',
			'vertical_ratio'
		];
	}

	/**
	 * Init dataset
	 */
	protected function initDataset() {
		$this->AccountID = SessionAccountHandler::getId();
		$this->DB = DB::getInstance();
		$this->DatasetConfig = new \Runalyze\Dataset\Configuration($this->DB, $this->AccountID);
		$this->DatasetQuery = new \Runalyze\Dataset\Query($this->DatasetConfig, $this->DB, $this->AccountID);

		$this->DatasetConfig->activateAllKeys();
		$this->Colspan = 2 + count($this->DatasetConfig->allKeys());
	}

	/**
	 * Search trainings
	 */
	protected function searchTrainings() {
		$where = $this->getWhere();

		$this->TotalNumberOfTrainings = DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` AS `t` '.$this->joinRaceResultIfRequired().' '.$where.' LIMIT 1')->fetchColumn();
		$this->Page = (int)Request::param('page');

		if (($this->Page-1)*$this->ResultsPerPage > $this->TotalNumberOfTrainings)
			$this->Page--;

		$this->DatasetQuery->resetJoins();

		$this->Trainings = DB::getInstance()->query(
			'SELECT DISTINCT
				`t`.`id`,
				`t`.`time`
				'.($this->multiEditorRequested() ? '' :
					', '.$this->DatasetQuery->queryToSelectAllKeys().' '.
					$this->DatasetQuery->queryToSelectJoinedFields()
				).'
				'.$this->columnFromRaceResultIfRequired().'
			FROM `'.PREFIX.'training` AS `t`
			'.$this->joinRaceResultIfRequired().'
			'.$this->DatasetQuery->queryToJoinTables().'
			'.$where.' '.
			$this->DatasetQuery->queryToGroupByActivity().
			$this->getOrder().$this->getLimit()
		)->fetchAll();
	}

	/**
	 * @return string
	 */
	protected function columnFromRaceResultIfRequired() {
		if (isset($_POST['is_race']) && $_POST['is_race'] != '') {
			return ', `rr`.`activity_id`';
		}

		return '';
	}

	/**
	 * @return string
	 */
	protected function joinRaceResultIfRequired() {
		if (isset($_POST['is_race']) && $_POST['is_race'] != '') {
			return 'LEFT JOIN `'.PREFIX.'raceresult` AS `rr` ON `t`.`id` = `rr`.`activity_id`';
		}

		return '';
	}

	/**
	 * Get where
	 * @return string
	 */
	protected function getWhere() {
		$conditions = array('`t`.`accountid`="'.$this->AccountID.'"');

		$this->addSpecialConditions($conditions);

		if (isset($_POST['s']) && strlen($_POST['s']) > 0) {
			$Time = new Duration($_POST['s']);
			$_POST['s'] = $Time->seconds();
		}

		foreach ($this->AllowedKeys as $key) {
			if (isset($_POST[$key])) {
				if (is_array($_POST[$key])) {
					$this->addConditionForArray($key, $conditions);
				} elseif (strlen($_POST[$key]) > 0) {
					$this->addConditionFor($key, $conditions);
				}
			}
		}

		$this->addConditionsForOrder($conditions);

		return $this->getEquipmentCondition().$this->getTagCondition().' WHERE '.implode(' AND ', array_unique($conditions));
	}

	/**
	 * @param array $conditions
	 */
	protected function addSpecialConditions(array &$conditions) {
		if (isset($_POST['sportid'])) {
			$this->addSportCondition($conditions);
		}

		if (isset($_POST['date-from']) && isset($_POST['date-to'])) {
			$this->addTimeRangeCondition($conditions);
		}

		if (isset($_POST['pace']) && $_POST['pace'] != '') {
			$this->addPaceCondition($conditions);
		}

		if (isset($_POST['gradient']) && $_POST['gradient'] != '') {
			$this->addGradientCondition($conditions);
		}

		if (isset($_POST['is_race']) && $_POST['is_race'] != '') {
			$this->addIsRaceCondition($conditions);
		}
	}

	/**
	 * @param array $conditions
	 */
	protected function addPaceCondition(array &$conditions) {
		$Pace = new Pace(0);
		$Pace->fromMinPerKm($_POST['pace']);
		$value = $Pace->secondsPerKm();
		$sign = $this->signForKey('pace');

		$conditions[] = '`t`.`distance` > 0';
		$conditions[] = ($sign == '=' ? 'ROUND(`t`.`s`/`t`.`distance`)' : '`t`.`s`/`t`.`distance`').' '.$sign.' '.DB::getInstance()->escape($value);
	}

	/**
	 * @param array $conditions
	 */
	protected function addGradientCondition(array &$conditions) {
		$value = 10*(float)str_replace(',', '.', $_POST['gradient']);

		$conditions[] = '`t`.`distance` > 0';
		$conditions[] = '`t`.`elevation` > 0';
		$conditions[] = '`t`.`elevation`/`t`.`distance` '.$this->signForKey('gradient').' '.DB::getInstance()->escape($value);
	}

	/**
	 * @param array $conditions
	 */
	protected function addIsRaceCondition(array &$conditions) {
		if ($_POST['is_race'] == 1) {
			$conditions[] = '`rr`.`activity_id` IS NOT NULL';
		} else {
			$conditions[] = '`rr`.`activity_id` IS NULL';
		}
	}

	/**
	 * Add condition for array from select box
	 * @param string $key
	 * @param array $conditions
	 */
	protected function addConditionForArray($key, array &$conditions) {
		$array = array_map(
			function ($value) {
				return (int)$value;
			},
			$_POST[$key]
		);

		$conditions[] = '`t`.`'.$key.'` IN('.implode(',', $array).')';
	}

	/**
	 * Add condition for single value
	 * @param string $key
	 * @param array $conditions
	 */
	protected function addConditionFor($key, array &$conditions) {
		$sign = (isset($_POST['opt'][$key])) ? $this->signFor($_POST['opt'][$key]) : '=';

		if ($sign == ' LIKE ') {
			$conditions[] = '`t`.`'.$key.'` '.$sign.' "%'.DB::getInstance()->escape($_POST[$key], false).'%"';
		} else {
			$value = $this->transformValueForDatabase($key, $_POST[$key]);

			$conditions[] = '`t`.`'.$key.'` '.$sign.' '.DB::getInstance()->escape($value);

			if ($sign == '<' || $sign == '<=') {
				if (in_array($key, $this->KeysThatShouldIgnoreZero)) {
					$conditions[] = '`t`.`'.$key.'` != 0';
				} elseif (in_array($key, $this->KeysThatShouldIgnoreNull)) {
					$conditions[] = '`t`.`'.$key.'` IS NOT NULL';
				}
			}
		}
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return float|int
	 */
	protected function transformValueForDatabase($key, $value) {
		if (in_array($key, array('distance', 'vertical_oscillation', 'vertical_ratio', 'stride_length', 'groundcontact_balance', 'fit_training_effect', 'kcal'))) {
			$value = (float)str_replace(',', '.', $value);
		}

		if ($key == 'elevation') {
			$value = (new Elevation())->setInPreferredUnit($value)->meter();
		} elseif ($key == 'distance') {
			$value = (new Distance())->setInPreferredUnit($value)->kilometer();
        } elseif ($key == 'percentage_hilly') {
            $value *= 0.01;
		} elseif ($key == 'vertical_oscillation' || $key == 'vertical_ratio') {
			$value *= 10;
		} elseif ($key == 'groundcontact_balance') {
			$value *= 100;
		} elseif ($key == 'stride_length') {
			$value = (new StrideLength())->setInPreferredUnit($value)->cm();
		} elseif ($key == 'temperature') {
			$value = (new Temperature())->setInPreferredUnit($value)->celsius();
		} elseif ($key == 'wind_speed') {
			$value = (new WindSpeed())->setInPreferredUnit($value)->value();
		} elseif (($key == 'vo2max' || $key == 'vo2max_with_elevation')) {
			$value /= Configuration::Data()->vo2maxCorrectionFactor();
		} elseif ($key == 'kcal') {
			$value = (new Energy())->setInPreferredUnit($value)->kcal();
		} elseif ($key == 'fit_recovery_time') {
            $value *= 60;
        }

		return $value;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function signForKey($key) {
		if (isset($_POST['opt'][$key])) {
			return $this->signFor($_POST['opt'][$key]);
		}

		return '=';
	}

	/**
	 * Equality sign
	 * @param string $postSign from $_POST
	 * @return string
	 */
	protected function signFor($postSign) {
		switch ($postSign) {
			case 'is': return '=';
			case 'gt': return '>';
			case 'ge': return '>=';
			case 'le': return '<=';
			case 'lt': return '<';
			case 'ne': return '!=';
			case 'like': return ' LIKE ';
		}

		return '=';
	}

	/**
	 * Add time range condition
	 * @param array $conditions
	 */
	protected function addTimeRangeCondition(array &$conditions) {
		if (
			FormularValueParser::validatePost('date-from', FormularValueParser::$PARSER_DATE) &&
			FormularValueParser::validatePost('date-to', FormularValueParser::$PARSER_DATE) &&
			$_POST['date-to'] > 0
		) {
			$conditions[] = '`t`.`time` BETWEEN '.LocalTime::fromServerTime($_POST['date-from'])->getTimestamp().' AND '.(LocalTime::fromServerTime($_POST['date-to'])->getTimestamp()+DAY_IN_S);
		}
	}

	/**
	 * Add sport condition
	 * @param array $conditions
	 */
	protected function addSportCondition(array &$conditions) {
		if (is_array($_POST['sportid'])) {
			$array = array_map(
				function ($value) {
					return (int)$value;
				},
				$_POST['sportid']
			);

			$conditions[] = '`t`.`sportid` IN('.implode(',', $array).')';
		} else {
			$conditions[] = '`t`.`sportid`="'.(int)$_POST['sportid'].'"';
		}
	}

	/**
	 * Get equipment condition
	 * @return string
	 */
	protected function getEquipmentCondition() {
		if (!isset($_POST['equipmentid'])) {
			return '';
		}

		if (is_array($_POST['equipmentid'])) {
			$array = array_map(
				function ($value) {
					return (int)$value;
				},
				$_POST['equipmentid']
			);

			return 'INNER JOIN (SELECT `ae`.`activityid` FROM `'.PREFIX.'activity_equipment` AS `ae` WHERE `ae`.`equipmentid` IN('.implode(',', $array).')) AS `suba` ON `suba`.`activityid` = `t`.`id`';
		}

		return 'INNER JOIN `'.PREFIX.'activity_equipment` AS `ae` ON `ae`.`activityid` = `t`.`id` AND `ae`.`equipmentid`="'.(int)$_POST['equipmentid'].'"';
	}

	/**
	 * Get tag condition
	 * @return string
	 */
	private function getTagCondition() {
		if (!isset($_POST['tagid'])) {
			return '';
		}

		if (is_array($_POST['tagid'])) {
			$array = array_map(
				function ($value) {
					return (int)$value;
				},
				$_POST['tagid']
			);

			return 'INNER JOIN (SELECT `at`.`activityid` FROM `'.PREFIX.'activity_tag` AS `at` WHERE `at`.`tagid` IN('.implode(',', $array).')) AS `subb` ON `subb`.`activityid` = `t`.`id`';
		}

		return 'INNER JOIN `'.PREFIX.'activity_tag` AS `at` ON `at`.`activityid` = `t`.`id` AND `at`.`tagid`="'.(int)$_POST['tagid'].'"';
	}

	/**
	 * Get order
	 * @return string
	 */
	protected function getOrder() {
		$order = (!isset($_POST['search-sort-order'])) ? 'DESC' : $this->DB->escape($_POST['search-sort-order'], false);

		if (isset($_POST['search-sort-by'])) {
			if ($_POST['search-sort-by'] == 'vo2max' && Configuration::VO2max()->useElevationCorrection()) {
				return ' ORDER BY IF(`t`.`vo2max_with_elevation`>0, `t`.`vo2max_with_elevation`, `t`.`vo2max`) '.$order;
			}

			if ($_POST['search-sort-by'] == 'pace') { // addConditionsForOrder() guarantees that `distance` > 0
				return ' ORDER BY `t`.`s`/`t`.`distance` '.$order;
			}

			if ($_POST['search-sort-by'] == 'gradient') { // addConditionsForOrder() guarantees that `distance` > 0
				return ' ORDER BY `t`.`elevation`/`t`.`distance` '.$order;
			}

            if ($_POST['search-sort-by'] == 'flight_time') {
                return ' ORDER BY (30000/`t`.`cadence` - `t`.`groundcontact`) '.$order;
            }

            if ($_POST['search-sort-by'] == 'flight_ratio') {
                return ' ORDER BY (1 - `t`.`cadence` * `t`.`groundcontact` / 30000) '.$order;
            }

			if (in_array($_POST['search-sort-by'], $this->AllowedKeys)) {
				return ' ORDER BY `t`.'.$this->DB->escape($_POST['search-sort-by'], false).' '.$order;
			}
		}

		return ' ORDER BY `t`.`time` '.$order;
	}

	/**
	 * Add additional conditions for order
	 * @param array $conditions
	 */
	protected function addConditionsForOrder(array &$conditions) {
		if (!isset($_POST['search-sort-by']))
			return;

		if ($_POST['search-sort-by'] == 'pace') {
			$conditions[] = '`t`.`distance` > 0';
		} elseif ($_POST['search-sort-by'] == 'gradient') {
			$conditions[] = '`t`.`distance` > 0';
			$conditions[] = '`t`.`elevation` > 0';
        } elseif ($_POST['search-sort-by'] == 'flight_time' || $_POST['search-sort-by'] == 'flight_ratio') {
            $conditions[] = '`t`.`cadence` > 0';
            $conditions[] = '`t`.`groundcontact` > 0';
		} elseif (in_array($_POST['search-sort-by'], $this->KeysThatShouldIgnoreZero)) {
			$conditions[] = '`t`.`'.$_POST['search-sort-by'].'` != 0';
		} elseif (in_array($_POST['search-sort-by'], $this->KeysThatShouldIgnoreNull)) {
			$conditions[] = '`t`.`'.$_POST['search-sort-by'].'` IS NOT NULL';
		}
	}

	/**
	 * Get limit
	 * @return string
	 */
	protected function getLimit() {
		if ($this->multiEditorRequested()) {
			return ' LIMIT '.self::MAX_LIMIT_FOR_MULTI_EDITOR;
		}

		if ($this->Page <= 0)
			$this->Page = 1;

		$limit = ($this->Page - 1)*$this->ResultsPerPage;

		return ' LIMIT '.$limit.','.$this->ResultsPerPage;
	}

	/**
	 * Display
	 */
	public function display() {
		if ($this->multiEditorRequested() && !empty($this->Trainings)) {
			$this->sendResultsToMultiEditor();
		} else {
			echo '<div id="searchResult">';
			$this->displayResults();
			echo '</div>';
		}
	}

	/**
	 * @return boolean
	 */
	protected function multiEditorRequested() {
		return isset($_POST['send-to-multi-editor']);
	}

	/**
	 * Send results to Multi Editor
	 */
	protected function sendResultsToMultiEditor() {
		$IDs = array();
		foreach ($this->Trainings as $data) {
			$IDs[] = $data['id'];
		}

		$_POST = array();

		$MultiEditor = new MultiEditor($IDs);
		$MultiEditor->display();

		echo Ajax::wrapJS('$("#ajax").removeClass("big-window").addClass("small-window");');
	}

	/**
	 * Display results
	 */
	protected function displayResults() {
		if (!$this->WithResults)
			return;

		$Table = new \Runalyze\View\Dataset\Table($this->DatasetConfig);
		$Icon = new \Runalyze\View\Icon(Runalyze\View\Icon::INFO);

		echo '<p class="c">'; $this->displayHeader(); echo '</p>';
		echo '<table class="fullwidth zebra-style">';
		echo '<thead>';
		echo '<tr style="font-size:.5em;line-height:1;"><td></td>'.$Table->codeForColumnLabels($Icon->code()).'</tr>';
		echo '</thead>';
		echo '<tbody>';

		$this->displayTrainingRows($Table);

		echo '</tbody>';
		echo '</table>';
	}

	/*
	 * Display header
	 */
	protected function displayHeader() {
		if ($this->Page != 1) {
			echo '<span id="search-back" class="link">'.Icon::$BACK.'</span>';
		}

		echo ' '.sprintf( __('Found %s activities'), $this->TotalNumberOfTrainings).' ';

		if ($this->Page*$this->ResultsPerPage < $this->TotalNumberOfTrainings) {
			echo '<span id="search-next" class="link">'.Icon::$NEXT.'</span>';
		}

		$this->connectPagination();
	}

	/**
	 * Connect pagination links
	 */
	protected function connectPagination() {
		echo Ajax::wrapJSforDocumentReady(
			'$("#search-back").click(function(){'.
				'var $i = $("#search input[name=\'page\']");'.
				'$i.val( parseInt($i.val()) - 1 );'.
				'$("#search").submit();'.
			'});'.
			'$("#search-next").click(function(){'.
				'var $i = $("#search input[name=\'page\']");'.
				'$i.val( parseInt($i.val()) + 1 );'.
				'$("#search").submit();'.
			'});'
		);
	}

	/**
	 * @param \Runalyze\View\Dataset\Table $Table
	 */
	protected function displayTrainingRows(\Runalyze\View\Dataset\Table $Table) {
		$Context = new \Runalyze\Dataset\Context(new Runalyze\Model\Activity\Entity(), $this->AccountID);

		foreach ($this->Trainings as $training) {
			$date = (new LocalTime($training['time']))->format("d.m.Y");
			$link = Ajax::trainingLink($training['id'], $date, true);
			$Context->setActivityData($training);

			echo '<tr class="r">';
			echo '<td class="l"><small>'.$link.'</small></td>';
			echo $Table->codeForColumns($Context);

			echo '</tr>';
		}
	}
}

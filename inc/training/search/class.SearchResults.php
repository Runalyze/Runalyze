<?php
/**
 * This file contains class::SearchResults
 * @package Runalyze\Search
 */

use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\StrideLength;
use Runalyze\Configuration;

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
	 * Allowed keys
	 * @var array
	 */
	protected $AllowedKeys = array();

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
		$this->ResultsPerPage = Configuration::Misc()->searchResultsPerPage();

		$this->setAllowedKeys();

		if ($withResults) {
			$this->initDataset();
			$this->searchTrainings();
		}
	}

	/**
	 * Set allowed keys
	 */
	protected function setAllowedKeys() {
		$this->AllowedKeys = array(
			'typeid',
			'weatherid',

			'distance',
			's',
			'pulse_avg',

			'elevation',
			'temperature',
			'kcal',

			'partner',
			'route',
			'comment',

			'pulse_max',
			'jd_intensity',
			'trimp',

			'cadence',
			'stride_length',
			'groundcontact',
			'vertical_oscillation',
			'vertical_ratio',
			'groundcontact_balance',

			'use_vdot',
			'is_public'
		);

		// Some additional keys
		$this->AllowedKeys[] = 'power';
		$this->AllowedKeys[] = 'is_track';
		$this->AllowedKeys[] = 'vdot';
		$this->AllowedKeys[] = 'notes';

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
		$this->TotalNumberOfTrainings = DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` AS `t` '.$this->getWhere().$this->getOrder().' LIMIT 1')->fetchColumn();
		$this->Page = (int)Request::param('page');

		if (($this->Page-1)*$this->ResultsPerPage > $this->TotalNumberOfTrainings)
			$this->Page--;

		$this->DatasetQuery->resetJoins();

		$this->Trainings = DB::getInstance()->query(
			'SELECT DISTINCT
				`t`.`id`,
				`t`.`time`
				'.($this->multiEditorRequested() ? '' :
					', '.str_replace('`t`.', '', $this->DatasetQuery->queryToSelectAllKeys()).' '.
					$this->DatasetQuery->queryToSelectJoinedFields()
				).'
			FROM `'.PREFIX.'training` AS `t`
			'.$this->DatasetQuery->queryToJoinTables().'
			'.$this->getWhere().' '.
			$this->DatasetQuery->queryToGroupByActivity().
			$this->getOrder().$this->getLimit() 
		)->fetchAll();
	}

	/**
	 * Get where
	 * @return string
	 */
	protected function getWhere() {
		$conditions = array('`t`.`accountid`="'.$this->AccountID.'"');

		if (isset($_POST['sportid']))
			$this->addSportCondition($conditions);
                
		if (isset($_POST['date-from']) && isset($_POST['date-to']))
			$this->addTimeRangeCondition($conditions);

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
                    
		return $this->getEquipmentCondition().$this->getTagCondition().' WHERE '.implode(' AND ', $conditions);
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
			if (in_array($key, array('distance', 'vertical_oscillation', 'stride_length'))) {
				$_POST[$key] = (float)str_replace(',', '.', $_POST[$key]);
			}

			if ($key == 'elevation') {
				$value = (new Elevation())->setInPreferredUnit($_POST[$key])->meter();
			} elseif ($key == 'distance') {
				$value = (new Distance())->setInPreferredUnit($_POST[$key])->kilometer();
			} elseif ($key == 'vertical_oscillation') {
				$value = 10*$_POST[$key];
			} elseif ($key == 'vertical_ratio') {
				$value = 10*$_POST[$key];
                        } elseif ($key == 'groundcontact_balance') {
                                $value = 100*$_POST[$key];
			} elseif ($key == 'stride_length') {
				$value = (new StrideLength())->setInPreferredUnit($_POST[$key])->cm();
			} else {
				$value = $_POST[$key];
			}

			$conditions[] = '`t`.`'.$key.'` '.$sign.' '.DB::getInstance()->escape($value);

			if (
				($sign == '<' || $sign == '<=') &&
				in_array($key, array('distance', 'pulse_avg', 'pulse_max', 'cadence', 'groundcontact', 'vertical_oscillation', 'vertical_ratio', 'groundcontact_balance', 'stride_length'))
			) {
				$conditions[] = '`t`.`'.$key.'` != 0';
			}
		}
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
			$conditions[] = '`t`.`time` BETWEEN '.(int)$_POST['date-from'].' AND '.((int)$_POST['date-to']+DAY_IN_S);
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
		$sort  = (!isset($_POST['search-sort-by']) || array_key_exists($_POST['search-sort-by'], $this->AllowedKeys)) ? '`time`' : $this->DB->escape($_POST['search-sort-by'], false);
		$order = (!isset($_POST['search-sort-order'])) ? 'DESC' : $this->DB->escape($_POST['search-sort-order'], false);

		if ($sort == 'vdot' && Configuration::Vdot()->useElevationCorrection()) {
			return ' ORDER BY IF(`t`.`vdot_with_elevation`>0, `t`.`vdot_with_elevation`, `t`.`vdot`) '.$order;
		}

		if ($sort == 'pace') {
			return ' ORDER BY IF(`t`.`distance`>0, `t`.`s`/`t`.`distance`, 0) '.$order;
		}

		return ' ORDER BY `t`.'.$sort.' '.$order;
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
		} elseif (in_array($_POST['search-sort-by'], array('pulse_avg', 'pulse_max', 'cadence', 'stride_length', 'groundcontact', 'vertical_oscillation', 'vertical_ratio'))) {
			$conditions[] = '`t`.`'.$_POST['search-sort-by'].'` > 0';
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
		if ($this->multiEditorRequested()) {
			$this->sendResultsToMultiEditor();
		} else {
			echo '<div id="'.DATA_BROWSER_SEARCHRESULT_ID.'">';
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

		$_POST['ids'] = implode(',', $IDs);

		$Factory = new PluginFactory();
		$MultiEditor = $Factory->newInstance('RunalyzePluginTool_MultiEditor');

		if ($MultiEditor) {
			$MultiEditor->display();
		} else {
			echo HTML::error( __('The multi editor could not be located.') );
		}

		echo Ajax::wrapJS('$("#search").remove();$("#ajax").removeClass("big-window");');
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
			$date = date("d.m.Y", $training['time']);
			$link = Ajax::trainingLink($training['id'], $date, true);
			$Context->setActivityData($training);

			echo '<tr class="r">';
			echo '<td class="l"><small>'.$link.'</small></td>';
			echo $Table->codeForColumns($Context);

			echo '</tr>';
		}
	}
}

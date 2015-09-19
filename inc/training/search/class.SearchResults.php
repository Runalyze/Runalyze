<?php
/**
 * This file contains class::SearchResults
 * @package Runalyze\Search
 */

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
	private $allowedKeys = array();

	/**
	 * Dataset
	 * @var Dataset
	 */
	private $Dataset = null;

	/**
	 * Colspan
	 * @var int
	 */
	private $colspan = 0;

	/**
	 * Trainings
	 * @var array
	 */
	private $Trainings = array();

	/**
	 * Total number of trainings
	 * @var int
	 */
	private $totalNumberOfTrainings = 0;

	/**
	 * Page
	 * @var int
	 */
	private $page = 0;

	/**
	 * Search and show trainings=
	 * @var boolean
	 */
	private $withResults = true;

	/**
	 * Results per page
	 * @var int
	 */
	private $resultsPerPage;

	/**
	 * Constructor
	 * @param boolean $withResults
	 */
	public function __construct($withResults = true) {
		$this->withResults = $withResults;
		$this->resultsPerPage = Configuration::Misc()->searchResultsPerPage();

		$this->setAllowedKeys();
		$this->initDataset();

		if ($withResults)
			$this->searchTrainings();
	}

	/**
	 * Set allowed keys
	 */
	private function setAllowedKeys() {
		$this->allowedKeys = array(
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

			'use_vdot',
			'is_public',
			'abc'
		);

		// Some additional keys
		$this->allowedKeys[] = 'power';
		$this->allowedKeys[] = 'is_track';
		$this->allowedKeys[] = 'vdot';
		$this->allowedKeys[] = 'notes';

	}

	/**
	 * Init dataset
	 */
	private function initDataset() {
		$this->Dataset = new Dataset();
		$this->Dataset->loadCompleteDataset();

		$this->colspan = $this->Dataset->cols() + 2;
	}

	/**
	 * Search trainings
	 */
	private function searchTrainings() {
		$this->totalNumberOfTrainings = DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` '.$this->getWhere().$this->getOrder().' LIMIT 1')->fetchColumn();
		$this->page = (int)Request::param('page');

		if (($this->page-1)*$this->resultsPerPage > $this->totalNumberOfTrainings)
			$this->page--;

		$this->Trainings = DB::getInstance()->query(
			'SELECT
				`id`,
				`time`
				'.($this->multiEditorRequested() ? '' : $this->Dataset->getQuerySelectForAllDatasets()).'
			FROM `'.PREFIX.'training`
			'.$this->getWhere().$this->getOrder().$this->getLimit()
		)->fetchAll();
	}

	/**
	 * Get where
	 * @return string
	 */
	private function getWhere() {
		$conditions = array('`accountid`="'.SessionAccountHandler::getId().'"');

		if (isset($_POST['sportid']))
			$this->addSportCondition($conditions);
                
		if (isset($_POST['date-from']) && isset($_POST['date-to']))
			$this->addTimeRangeCondition($conditions);

		foreach ($this->allowedKeys as $key) {
			if (isset($_POST[$key])) {
				if (is_array($_POST[$key])) {
					$this->addConditionForArray($key, $conditions);
				} elseif (strlen($_POST[$key]) > 0) {
					$this->addConditionFor($key, $conditions);
				}
			}
		}
	
		$this->addConditionsForOrder($conditions);

		return $this->getEquipmentCondition().' WHERE '.implode(' AND ', $conditions);
	}

	/**
	 * Add condition for array from select box
	 * @param string $key
	 * @param array $conditions
	 */
	private function addConditionForArray($key, array &$conditions) {
		$array = array_map(
			function ($value) {
				return (int)$value;
			},
			$_POST[$key]
		);

		$conditions[] = '`'.$key.'` IN('.implode(',', $array).')';
	}

	/**
	 * Add condition for single value
	 * @param string $key
	 * @param array $conditions
	 */
	private function addConditionFor($key, array &$conditions) {
		$sign = (isset($_POST['opt'][$key])) ? $this->signFor($_POST['opt'][$key]) : '=';

		if ($sign == ' LIKE ') {
			$conditions[] = '`'.$key.'` '.$sign.' "%'.DB::getInstance()->escape($_POST[$key], false).'%"';
		} else {
			if (in_array($key, array('distance', 'vertical_oscillation', 'stride_length'))) {
				$_POST[$key] = (float)str_replace(',', '.', $_POST[$key]);
			}

			if ($key == 'vertical_oscillation') {
				$conditions[] = '`'.$key.'` '.$sign.' '.DB::getInstance()->escape(10*$_POST[$key]);
			} elseif ($key == 'stride_length') {
				$conditions[] = '`'.$key.'` '.$sign.' '.DB::getInstance()->escape(100*$_POST[$key]);
			} else {
				$conditions[] = '`'.$key.'` '.$sign.' '.DB::getInstance()->escape($_POST[$key]);
			}

			if (
				($sign == '<' || $sign == '<=') &&
				in_array($key, array('distance', 'pulse_avg', 'pulse_max', 'cadence', 'groundcontact', 'vertical_oscillation', 'stride_length'))
			) {
				$conditions[] = '`'.$key.'` != 0';
			}
		}
	}

	/**
	 * Equality sign
	 * @param string $postSign from $_POST
	 * @return string
	 */
	private function signFor($postSign) {
		switch($postSign) {
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
	private function addTimeRangeCondition(array &$conditions) {
		if (FormularValueParser::validatePost('date-from', FormularValueParser::$PARSER_DATE)
				&& FormularValueParser::validatePost('date-to', FormularValueParser::$PARSER_DATE)
				&& $_POST['date-to'] > 0)
			$conditions[] = '`time` BETWEEN '.(int)$_POST['date-from'].' AND '.((int)$_POST['date-to']+DAY_IN_S);
	}

	/**
	 * Add sport condition
	 * @param array $conditions
	 */
	private function addSportCondition(array &$conditions) {
		if (is_array($_POST['sportid'])) {
			$array = array_map(
				function ($value) {
					return (int)$value;
				},
				$_POST['sportid']
			);

			$conditions[] = '`sportid` IN('.implode(',', $array).')';
		} else {
			$conditions[] = '`sportid`="'.(int)$_POST['sportid'].'"';
		}
	}

	/**
	 * Get equipment condition
	 * @return string
	 */
	private function getEquipmentCondition() {
		if (is_array($_POST['equipmentid'])) {
			$array = array_map(
				function ($value) {
					return (int)$value;
				},
				$_POST['equipmentid']
			);

			return 'INNER JOIN (SELECT `ae`.`activityid` FROM `'.PREFIX.'activity_equipment` AS `ae` WHERE `ae`.`equipmentid` IN('.implode(',', $array).')) AS `sub` ON `sub`.`activityid` = `'.PREFIX.'training`.`id`';
		} elseif (isset($_POST['equipmentid'])) {
			return 'INNER JOIN `'.PREFIX.'activity_equipment` AS `ae` ON `ae`.`activityid` = `'.PREFIX.'training`.`id` AND `ae`.`equipmentid`="'.(int)$_POST['equipmentid'].'"';
		} else {
			return '';
		}
	}

	/**
	 * Get order
	 * @return string
	 */
	private function getOrder() {
		$sort  = (!isset($_POST['search-sort-by']) || array_key_exists($_POST['search-sort-by'], $this->allowedKeys)) ? '`time`' : DB::getInstance()->escape($_POST['search-sort-by'], false);
		$order = (!isset($_POST['search-sort-order'])) ? 'DESC' : DB::getInstance()->escape($_POST['search-sort-order'], false);

		if ($sort == 'vdot' && Configuration::Vdot()->useElevationCorrection())
			return ' ORDER BY IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`) '.$order;

		if ($sort == 'pace')
			$sort = 'IF(`distance`>0,`s`/`distance`,0)';

		return ' ORDER BY '.$sort.' '.$order;
	}

	/**
	 * Add additional conditions for order
	 * @param array $conditions
	 */
	private function addConditionsForOrder(array &$conditions) {
		if (!isset($_POST['search-sort-by']))
			return;

		if ($_POST['search-sort-by'] == 'pace') {
			$conditions[] = '`distance` > 0';
		} elseif (in_array($_POST['search-sort-by'], array('pulse_avg', 'pulse_max', 'cadence', 'stride_length', 'groundcontact', 'vertical_oscillation'))) {
			$conditions[] = '`'.$_POST['search-sort-by'].'` > 0';
		}
	}

	/**
	 * Get limit
	 * @return string
	 */
	private function getLimit() {
		if ($this->multiEditorRequested()) {
			return ' LIMIT '.self::MAX_LIMIT_FOR_MULTI_EDITOR;
		}

		if ($this->page <= 0)
			$this->page = 1;

		$limit = ($this->page - 1)*$this->resultsPerPage;

		return ' LIMIT '.$limit.','.$this->resultsPerPage;
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
	private function sendResultsToMultiEditor() {
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
	private function displayResults() {
		if (!$this->withResults)
			return;

		echo '<table class="fullwidth zebra-style">';
		echo '<thead><tr class="c"><th colspan="'.$this->colspan.'">';
		$this->displayHeader();
		echo '</th></tr></thead>';
		echo '<tbody>';

		$this->displayTrainingRows();

		echo '</tbody>';
		echo '</table>';
	}

	/*
	 * Display header
	 */
	private function displayHeader() {
		if ($this->page != 1) {
			echo '<span id="search-back" class="link">'.Icon::$BACK.'</span>';
		}

		echo ' '.sprintf( __('Found %s activities'), $this->totalNumberOfTrainings).' ';

		if ($this->page*$this->resultsPerPage < $this->totalNumberOfTrainings) {
			echo '<span id="search-next" class="link">'.Icon::$NEXT.'</span>';
		}

		$this->connectPagination();
	}

	/**
	 * Connect pagination links
	 */
	private function connectPagination() {
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
	 * Display all training rows
	 */
	private function displayTrainingRows() {
		foreach ($this->Trainings as $training) {
			$date = date("d.m.Y", $training['time']);
			$link = Ajax::trainingLink($training['id'], $date, true);

			echo '<tr class="r">';
			echo '<td class="l"><small>'.$link.'</small></td>';

			$this->Dataset->setActivityData($training);
			$this->Dataset->displayTableColumns();

			echo '</tr>';
		}
	}
}

<?php
/**
 * This file contains class::SearchResults
 * @package Runalyze\Search
 */
/**
 * Search results
 *
 * @author Hannes Christiansen
 * @package Runalyze\Search
 */
class SearchResults {
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
	 * Constructor
	 * @param boolean $withResults
	 */
	public function __construct($withResults = true) {
		$this->withResults = $withResults;

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
			'distance',
			'route',
			's',
			'comment',
			'temperature',
			'pulse_avg',
			'partner',
			'kcal',
			'typeid',
			'weatherid',
			'shoeid'
		);

		// Some additional keys
		$this->allowedKeys[] = 'is_public';
		$this->allowedKeys[] = 'is_track';
		$this->allowedKeys[] = 'trimp';
		$this->allowedKeys[] = 'vdot';
		$this->allowedKeys[] = 'pulse_max';
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
		$this->totalNumberOfTrainings = Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` '.$this->getWhere().$this->getOrder());
		$this->page = (int)Request::param('page');

		if (($this->page-1)*CONF_RESULTS_AT_PAGE > $this->totalNumberOfTrainings)
			$this->page--;

		$this->Trainings = Mysql::getInstance()->fetchAsArray(
			'SELECT
				id,
				time
				'.$this->Dataset->getQuerySelectForAllDatasets().'
			FROM `'.PREFIX.'training`
			'.$this->getWhere().$this->getOrder().$this->getLimit()
		);
	}

	/**
	 * Get where
	 * @return string
	 */
	private function getWhere() {
		$conditions = array();

		if (isset($_POST['date-from']) && isset($_POST['date-to']))
			$this->addTimeRangeCondition($conditions);

		if (isset($_POST['sportid']))
			$this->addSportCondition($conditions);

		if (isset($_POST['clothes']))
			$this->addClothesCondition($conditions);

		foreach ($this->allowedKeys as $key)
			if (isset($_POST[$key]))
				if (is_array($_POST[$key]))
					$this->addConditionForArray($key, $conditions);
				elseif (strlen($_POST[$key]) > 0)
					$this->addConditionFor($key, $conditions);

		if (empty($conditions))
			return 'WHERE 1';

		return 'WHERE '.implode(' AND ', $conditions);
	}

	/**
	 * Add condition for array from select box
	 * @param string $key
	 * @param array $conditions
	 */
	private function addConditionForArray($key, array &$conditions) {
		$array = array_map(
			create_function('$value', 'return (int)$value;'),
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

		if ($sign == ' LIKE ')
			$conditions[] = '`'.$key.'` '.$sign.' "%'.mysql_real_escape_string($_POST[$key]).'%"';
		else
			$conditions[] = '`'.$key.'` '.$sign.' "'.mysql_real_escape_string($_POST[$key]).'"';
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
			$conditions[] = '`time` BETWEEN '.$_POST['date-from'].' AND '.$_POST['date-to'];
	}

	/**
	 * Add sport condition
	 * @param array $conditions
	 */
	private function addSportCondition(array &$conditions) {
		if (is_array($_POST['sportid'])) {
			$array = array_map(
				create_function('$value', 'return (int)$value;'),
				$_POST['sportid']
			);

			$conditions[] = '`sportid` IN('.implode(',', $array).')';
		} else {
			$conditions[] = '`sportid`="'.(int)$_POST['sportid'].'"';
		}
	}

	/**
	 * Add clothes condition
	 * @param array $conditions
	 */
	private function addClothesCondition(array &$conditions) {
		if (!is_array($_POST['clothes']))
			$_POST['clothes'] = array((int)$_POST['clothes']);

		foreach ($_POST['clothes'] as $id)
			$conditions[] = 'FIND_IN_SET('.(int)$id.',`clothes`)';
	}

	/**
	 * Get order
	 * @return string
	 */
	private function getOrder() {
		$sort  = (!isset($_POST['search-sort-by']) || array_key_exists($_POST['search-sort-by'], $this->allowedKeys)) ? 'time' : mysql_real_escape_string($_POST['search-sort-by']);
		$order = (!isset($_POST['search-sort-order'])) ? 'DESC' : mysql_real_escape_string($_POST['search-sort-order']);

		return ' ORDER BY `'.$sort.'` '.$order;
	}

	/**
	 * Get limit
	 * @return string
	 */
	private function getLimit() {
		$limit = ($this->page - 1)*CONF_RESULTS_AT_PAGE;

		return ' LIMIT '.$limit.','.CONF_RESULTS_AT_PAGE;
	}

	/**
	 * Display
	 */
	public function display() {
		if (isset($_POST['send-to-multi-editor'])) {
			$this->sendResultsToMultiEditor();
		} else {
			echo '<div id="'.DATA_BROWSER_SEARCHRESULT_ID.'">';
			$this->displayResults();
			echo '</div>';
		}
	}

	/**
	 * Send results to Multi Editor
	 */
	private function sendResultsToMultiEditor() {
		$IDs = array();
		foreach ($this->Trainings as $data)
			$IDs[] = $data['id'];

		$_POST['ids'] = implode(',', $IDs);

		$MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');

		if ($MultiEditor)
			$MultiEditor->display();
		else
			echo HTML::error('Der Multi-Editor konnte nicht gefunden werden.');

		echo Ajax::wrapJS('$("#search").remove();$("#ajax").removeClass("bigWin");');
	}

	/**
	 * Display results
	 */
	private function displayResults() {
		if (!$this->withResults)
			return;

		echo '<table class="fullWidth">';
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
		if ($this->page != 1)
			echo '<span class="link" onclick="Runalyze.searchPageBack();">'.Icon::$BACK.'</span>';

		echo ' Insgesamt wurden '.$this->totalNumberOfTrainings.' Trainings gefunden. ';

		if ($this->page*CONF_RESULTS_AT_PAGE < $this->totalNumberOfTrainings)
			echo '<span class="link" onclick="Runalyze.searchPageNext();">'.Icon::$NEXT.'</span>';
	}

	/**
	 * Display all training rows
	 */
	private function displayTrainingRows() {	
		foreach ($this->Trainings as $i => $training) {
			$date = date("d.m.Y", $training['time']);
			$link = Ajax::trainingLink($training['id'], $date, true);

			echo '<tr class="a'.($i%2+1).' r">';
			echo '<td class="l"><small>'.$link.'</small></td>';

			$this->Dataset->setTrainingId($training['id'], $training);
			$this->Dataset->displayTableColumns();

			echo '</tr>';
		}
	}
}
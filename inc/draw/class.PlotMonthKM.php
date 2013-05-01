<?php
/**
 * This file contains class::PlotMonthKM
 * @package Runalyze\Plot
 */
/**
 * Plot month kilometers
 * @package Runalyze\Plot
 */
class PlotMonthKM extends Plot {
	/**
	 * Year
	 * @var string
	 */
	protected $Year = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->Year = Request::param('y');

		parent::__construct($this->getCSSid(), 800, 500);

		$this->init();
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	private function getCSSid() {
		return 'monthKM'.$this->Year;
	}

	/**
	 * Get title
	 * @return string
	 */
	private function getTitle() {
		if ($this->comparesYears())
			return 'Monatskilometer Jahresvergleich';

		return 'Monatskilometer '.$this->Year;
	}

	/**
	 * Does this plot compare years?
	 * @return boolean
	 */
	private function comparesYears() {
		return strlen($this->Year) == 0;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	private function getXLabels() {
		$months = array();

		for ($m = 1; $m <= 12; $m++)
			$months[] = array($m-1, Time::Month($m, true));

		return $months;
	}

	/**
	 * Init
	 */
	protected function init() {
		$this->initData();
		$this->setAxis();
		$this->setOptions();
	}

	/**
	 * Set axis
	 */
	protected function setAxis() {
		$this->setMarginForGrid(5);
		$this->setXLabels($this->getXLabels());
		$this->addYAxis(1, 'left');
		$this->addYUnit(1, 'km');
		$this->setYTicks(1, 10, 0);
	}

	/**
	 * Set options
	 */
	protected function setOptions() {
		$this->showBars(true);
		$this->enableTracking();
		$this->setTitle($this->getTitle());

		if (!$this->comparesYears()) {
			$this->stacked();
			$this->addCurrentLevel();
		}
	}

	/**
	 * Add current level
	 */
	protected function addCurrentLevel() {
		$possibleKM = Running::possibleKmInOneMonth();

		if ($possibleKM > 0) {
			$this->addThreshold('y', $possibleKM);
			$this->addAnnotation(0, $possibleKM, 'aktuelles Leistungslevel');
		}
	}

	/**
	 * Init data
	 */
	protected function initData() {
		if ($this->comparesYears())
			$this->initToCompareYears();
		else if ($this->Year >= START_YEAR && $this->Year <= date('Y') && START_TIME != time())
			$this->initToShowYear();
		else
			$this->raiseError('F&uuml;r dieses Jahr liegen keine Daten vor.');
	}

	/**
	 * Init to compare years
	 */
	protected function initToCompareYears() {
		$Years = array();

		for ($y = START_YEAR; $y <= date('Y'); $y++)
			$Years[$y] = array_fill(0, 12, 0);

		$Data = Mysql::getInstance()->fetchAsArray('
			SELECT
				SUM(`distance`) as `km`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`,
				YEAR(FROM_UNIXTIME(`time`)) as `y`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.CONF_RUNNINGSPORT.'
			GROUP BY `y`,`m`'
		);

		foreach ($Data as $dat)
			$Years[$dat['y']][$dat['m']-1] = $dat['km'];

		$width = 1 / (count($Years) + 2);

		foreach ($Years as $year => $data)
			$this->Data[] = array('label' => $year, 'data' => $data, 'bars' => array('show' => true, 'order' => $year, 'barWidth' => $width), 'lineWidth' => 0);
	}

	/**
	 * Init to show year
	 */
	protected function initToShowYear() {
		$Kilometers            = array_fill(0, 12, 0);
		$KilometersCompetition = array_fill(0, 12, 0);

		$Data = Mysql::getInstance()->fetchAsArray('
			SELECT
				(`typeid` = '.CONF_WK_TYPID.') as `wk`,
				SUM(`distance`) as `km`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.CONF_RUNNINGSPORT.' AND
				YEAR(FROM_UNIXTIME(`time`))='.$this->Year.'
			GROUP BY (`typeid` = '.CONF_WK_TYPID.'), m'
		);

		foreach ($Data as $dat) {
			if ($dat['wk'] == 1)
				$KilometersCompetition[$dat['m']-1] = $dat['km'];
			else
				$Kilometers[$dat['m']-1]            = $dat['km'];
		}

		$this->Data[] = array('label' => 'Wettkampf-Kilometer', 'data' => $KilometersCompetition);
		$this->Data[] = array('label' => 'Kilometer', 'data' => $Kilometers);
	}
}
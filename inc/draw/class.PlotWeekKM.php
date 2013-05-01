<?php
/**
 * This file contains class::PlotWeekKM
 * @package Runalyze\Plot
 */
/**
 * Plot week kilometers
 * @package Runalyze\Plot
 */
class PlotWeekKM extends Plot {
	/**
	 * Year
	 * @var string
	 */
	protected $Year = '';

	/**
	 * First week
	 * @var int
	 */
	protected $firstWeek = 1;

	/**
	 * Last week
	 * @var int
	 */
	protected $lastWeek = 52;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->Year      = Request::param('y');
		$this->firstWeek = 1;
		$this->lastWeek  = date("W", mktime(0,0,0,12,28,$this->Year)); // http://de.php.net/manual/en/function.date.php#49457

		parent::__construct($this->getCSSid(), 800, 500);

		$this->init();
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	private function getCSSid() {
		return 'weekKM'.$this->Year;
	}

	/**
	 * Get title
	 * @return string
	 */
	private function getTitle() {
		return 'Wochenkilometer '.$this->Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	private function getXLabels() {
		$weeks = array();

		for ($w = $this->firstWeek; $w <= $this->lastWeek; $w++)
			$weeks[] = array($w-$this->firstWeek, ($w%5 == 0) ? $w : '');

		return $weeks;
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

		$this->stacked();
		$this->addCurrentLevel();
	}

	/**
	 * Add current level
	 */
	protected function addCurrentLevel() {
		$possibleKM = Running::possibleKmInOneWeek();

		if ($possibleKM > 0) {
			$this->addThreshold('y', $possibleKM);
			$this->addAnnotation(0, $possibleKM, 'aktuelles Leistungslevel');
		}
	}

	/**
	 * Init data
	 */
	protected function initData() {
		if ($this->Year >= START_YEAR && $this->Year <= date('Y') && START_TIME != time())
			$this->initToShowYear();
		else
			$this->raiseError('F&uuml;r dieses Jahr liegen keine Daten vor.');
	}

	/**
	 * Init to show year
	 */
	protected function initToShowYear() {
		$Kilometers            = array_fill(0, $this->lastWeek - $this->firstWeek + 1, 0);
		$KilometersCompetition = array_fill(0, $this->lastWeek - $this->firstWeek + 1, 0);

		$Data = Mysql::getInstance()->fetchAsArray('
			SELECT
				(`typeid` = '.CONF_WK_TYPID.') as `wk`,
				SUM(`distance`) as `km`,
				WEEK(FROM_UNIXTIME(`time`),1) as `w`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.CONF_RUNNINGSPORT.' AND
				YEAR(FROM_UNIXTIME(`time`))='.$this->Year.'
			GROUP BY (`typeid` = '.CONF_WK_TYPID.'), WEEK(FROM_UNIXTIME(`time`),1)'
		);

		foreach ($Data as $dat) {
			if ($dat['w'] >= $this->firstWeek && $dat['w'] <= $this->lastWeek) {
				if ($dat['wk'] == 1)
					$KilometersCompetition[$dat['w']-$this->firstWeek] = $dat['km'];
				else
					$Kilometers[$dat['w']-$this->firstWeek] = $dat['km'];
			}
		}

		$this->Data[] = array('label' => 'Wettkampf-Kilometer', 'data' => $KilometersCompetition);
		$this->Data[] = array('label' => 'Kilometer', 'data' => $Kilometers);
	}
}
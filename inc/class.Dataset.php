<?php
/**
 * This file contains class::Dataset
 * @package Runalyze\DataBrowser\Dataset
 */
/**
 * Load dataset row for a given training or a group of trainings
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser\Dataset
 */
class Dataset {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $TrainingObject = null;

	/**
	 * Counter for displayed columns
	 * @var int
	 */
	private $cols = 0;

	/**
	 * Data array from database
	 * @var array
	 */
	private $data;

	/**
	 * Boolean flag: compare km of datasets (preferred for group of trainings)
	 * @var boolean
	 */
	private $compareKM = false;

	/**
	 * Kilometer for last set
	 * @var double
	 */
	private $kmOfLastSet = -1;

	/**
	 * Constructor
	 */
	public function __construct() {
		$dat = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'dataset` WHERE `modus`>=2 AND `position`!=0 ORDER BY `position` ASC')->fetchAll();

		if ($dat === false || empty($dat)) {
			Error::getInstance()->addError('No dataset in database is active.');
			return false;
		}

		$this->data = $dat;
		$this->cols = count($dat);

		if (Configuration::DataBrowser()->showEditLink())
			$this->cols++;
	}

	/**
	 * Number of columns
	 * @return int
	 */
	public function cols() {
		return $this->cols;
	}

	/**
	 * Activate kilometer comparison 
	 */
	public function activateKilometerComparison() {
		$this->compareKM = true;
	}

	/**
	 * Set manually distance of last set (e.g. for different order)
	 * @param double $km 
	 */
	public function setKilometerToCompareTo($km) {
		$this->kmOfLastSet = $km;
	}

	/**
	 * Load complete dataset where position != 0
	 */
	public function loadCompleteDataset() {
		$this->data = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'dataset` WHERE `position`!=0 ORDER BY `position` ASC')->fetchAll();
		$this->cols = count($this->data);
	}

	/**
	 * Set training
	 * @param int $id
	 * @param array $data [optional]
	 */
	public function setTrainingId($id, $data = array()) {
		if (empty($data))
			$data = $id;

		$this->TrainingObject = new TrainingObject($data);
	}

	/**
	 * Load a group of trainings for summary
	 * @param int $sportid
	 * @param int $timestart
	 * @param int $timeend
	 * @return boolean Are any trainings loaded?
	 */
	public function loadGroupOfTrainings($sportid, $timestart, $timeend) {
		$SummaryData = DB::getInstance()->query('
			SELECT
				sportid,
				time,
				SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`,
				SUM(1) as `num`
				'.$this->getQuerySelectForSet().'
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.$sportid.'
				AND `time` BETWEEN '.($timestart-10).' AND '.($timeend-10).'
				'.$this->getQueryWhereNotPrivate().'
			GROUP BY `sportid`
			LIMIT 1
		')->fetch();

		return $this->setGroupOfTrainings($SummaryData);
	}

	/**
	 * Get group of trainings for a given timerange to set them manually (much faster!)
	 * @param int $sportid
	 * @param int $timerange default 7*24*60*60
	 * @param int $timestart default 0
	 * @param int $timeend   default time()
	 * @return array 
	 */
	public function getGroupOfTrainingsForTimerange($sportid, $timerange = 604800, $timestart = 0, $timeend = 0) {
		if ($timeend == 0)
			$timeend = time();

		$SummaryData = DB::getInstance()->query('
			SELECT
				`sportid`,
				`time`,
				SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`,
				SUM(1) as `num`,
				FLOOR(('.$timeend.'-`time`)/('.$timerange.')) as `timerange`
				'.$this->getQuerySelectForSet().'
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.$sportid.'
				AND `time` BETWEEN '.($timestart-10).' AND '.($timeend-10).'
				'.$this->getQueryWhereNotPrivate().'
			GROUP BY `timerange`, `sportid`
			ORDER BY `timerange` ASC
		')->fetchAll();

		return $SummaryData;
	}

	/**
	 * Set group of trainings for summary
	 * @param array $SummaryData
	 * @return boolean 
	 */
	public function setGroupOfTrainings($SummaryData) {
		$this->kmOfLastSet = 0;

		$this->setTrainingId( DataObject::$DEFAULT_ID );

		if ($SummaryData === false || empty($SummaryData))
			return false;

		$this->TrainingObject->setFromArray($SummaryData);

		return true;
	}

	/**
	 * Get string for selecting dataset in query
	 * @return string 
	 */
	private function getQuerySelectForSet() {
		$String = '';
		$Sum = Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`)*`s`' : '`vdot`*`s`';

		foreach ($this->data as $set)
			if ($set['summary'] == 1) {
				if ($set['name'] == 'vdot') {
					$String .= ', SUM(IF(`use_vdot`=1 AND `vdot`>0,'.$Sum.',0))/SUM(IF(`use_vdot`=1 AND `vdot`>0,`s`,0)) as `vdot`';
				} elseif ($set['name'] == 'pulse_avg') {
					$String .= ', SUM(`s`*`pulse_avg`*(`pulse_avg` > 0))/SUM(`s`*(`pulse_avg` > 0)) as `pulse_avg`';
				} else {
					if ($set['summary_mode'] != 'AVG')
						$String .= ', '.$set['summary_mode'].'(`'.$set['name'].'`) as `'.$set['name'].'`';
					else
						$String .= ', '.$set['summary_mode'].'(NULLIF(`'.$set['name'].'`,0)) as `'.$set['name'].'`';
				}
			}

		return $String;
	}

	/**
	 * Get all datasets as comma separated string for query
	 * @return string 
	 */
	public function getQuerySelectForAllDatasets() {
		$String = ',`is_track`,`use_vdot`,`vdot_with_elevation`,`is_public`,`elevation_corrected`,SUBSTR(`arr_alt`,1,1) as `arr_alt`';

		foreach ($this->data as $set)
			$String .= ', `'.$set['name'].'`';

		return $String;
	}

	/**
	 * Get string for query to not select private trainings
	 * @return string
	 */
	private function getQueryWhereNotPrivate() {
		return (FrontendShared::$IS_SHOWN && !Configuration::Privacy()->showPrivateActivitiesInList()) ? 'AND is_public=1' : '';
	}

	/**
	 * Is Dataset running in summary-mode?
	 * @return bool
	 */
	private function isSummaryMode() {
		return $this->TrainingObject->isDefaultId();
	}

	/**
	 * Display short link for e.g. 'Gymnastik'
	 */
	public function displayShortLink() {
		echo $this->TrainingObject->Linker()->linkWithSportIcon();
	}

	/**
	 * Get date string for given timestamp
	 * @param int $timestamp
	 * @return string
	 */
	static public function getDateString($timestamp) {
		$date    = date('d.m.', $timestamp);
		$addLink = '';
		$weekDay = Time::Weekday(date('w', $timestamp), true);

		if (Configuration::DataBrowser()->showCreateLink() && !FrontendShared::$IS_SHOWN)
			$addLink = ImporterWindow::linkForDate($timestamp);

		if (Time::isToday($timestamp))
			$weekDay = '<strong>'.$weekDay.'</strong>';

		return $date.' '.$addLink.' '.$weekDay;
	}

	/**
	 * Display this dataset as a table-row
	 */
	public function displayTableColumns() {
		$this->displayEditLink();

		foreach ($this->data as $set)
			$this->displayDataset($set);
	}

	/**
	 * Display a single dataset
	 * @param array $set
	 */
	private function displayDataset($set) {
		if ($this->isSummaryMode() && $set['summary'] == 0)
			echo HTML::emptyTD();
		else
			echo HTML::td($this->getDataset($set['name']), $set['class'], $set['style']);
	}

	/**
	 * Display edit link if used in DataBrowser 
	 */
	public function displayEditLink() {
		if (Configuration::DataBrowser()->showEditLink())
			if ($this->isSummaryMode() || FrontendShared::$IS_SHOWN)
				echo HTML::emptyTD();
			else
				echo HTML::td($this->TrainingObject->Linker()->smallEditLink()).NL;
	}

	/**
	 * Display public icon
	 */
	public function displayPublicIcon() {
		if (!is_object($this->TrainingObject))
			echo HTML::emptyTD();
		elseif (!$this->TrainingObject->isPublic())
			echo HTML::emptyTD();
		else
			echo HTML::td(Icon::$ADD_SMALL_GREEN, 'link');
	}

	/**
	 * Get content for a given dataset
	 * @param string $name
	 * @return string
	 */
	public function getDataset($name) {
		switch($name) {
			case 'sportid':
				return $this->TrainingObject->Sport()->Icon();

			case 'typeid':
				if ($this->TrainingObject->Type()->isUnknown())
					return '';

				return $this->TrainingObject->Type()->formattedAbbr();

			case 'time':
				return $this->TrainingObject->DataView()->getDaytimeString();

			case 'distance':
				return $this->TrainingObject->DataView()->getDistanceString().$this->getDistanceComparison();

			case 's':
				return $this->TrainingObject->DataView()->getTimeString();

			case 'pace':
				if ($this->TrainingObject->getDistance() == 0)
					return '';

				return $this->TrainingObject->DataView()->getSpeedStringForTime( $this->TrainingObject->getTimeInSecondsSumWithDistance() );

			case 'elevation':
				return $this->TrainingObject->DataView()->getElevationWithTooltip();

			case 'kcal':
				return $this->TrainingObject->DataView()->getCalories();

			case 'pulse_avg':
				return $this->TrainingObject->DataView()->getPulseAvg();

			case 'pulse_max':
				return $this->TrainingObject->DataView()->getPulseMax();

			case 'trimp':
				return $this->TrainingObject->DataView()->getTrimpString();

			case 'cadence':
				return $this->TrainingObject->DataView()->getCadence();

			case 'power':
				return $this->TrainingObject->DataView()->getPower();

			case 'temperature':
				if ($this->TrainingObject->Weather()->temperature()->isUnknown() || !$this->TrainingObject->Sport()->isOutside())
					return '';

				return $this->TrainingObject->Weather()->temperature()->asString();

			case 'weatherid':
				if ($this->TrainingObject->Weather()->isEmpty() || !$this->TrainingObject->Sport()->isOutside())
					return '';

				return $this->TrainingObject->Weather()->condition()->icon()->code();

			case 'route':
				return $this->cut( $this->TrainingObject->getRoute() );

			case 'clothes':
				return $this->cut( $this->TrainingObject->Clothes()->asString() );

			case 'splits':
				$isCompetition  = $this->TrainingObject->Type()->isCompetition();
				$splitsAreDifferent = round($this->TrainingObject->getDistance()) != round($this->TrainingObject->Splits()->totalDistance());
				$splitsAreDirectlySet = $this->TrainingObject->Splits()->hasActiveAndInactiveLaps();

				if ($isCompetition || $splitsAreDifferent || $splitsAreDirectlySet)
					return $this->TrainingObject->Splits()->asIconWithTooltip();

				return '';

			case 'comment':
				return $this->cut( $this->TrainingObject->getComment() );

			case 'partner':
				return $this->cut( $this->TrainingObject->getPartner() );

			case 'abc':
				return $this->TrainingObject->DataView()->getABCicon();

			case 'shoeid':
				return $this->TrainingObject->Shoe()->getName();

			case 'vdot':
				if (!$this->TrainingObject->Sport()->isRunning())
					return '';

				return $this->TrainingObject->DataView()->getVDOTicon();

			case 'jd_intensity':
				if (!$this->TrainingObject->Sport()->isRunning())
					return '';

				return $this->TrainingObject->DataView()->getJDintensityWithStresscolor();

		}

		return '&nbsp;';
	}

	/**
	 * Cut string to 20 characters
	 * @param string $string
	 * @return string
	 */
	private function cut($string) {
		return Helper::Cut($string, 20);
	}

	/**
	 * Dataset for: `distance`
	 * @return string
	 */
	private function getDistanceComparison() {
		if (!$this->compareKM)
			return '';

		$Percentage = $this->getDistanceComparisonPercentage();
		$String     = ($Percentage > 0) ? Math::WithSign($Percentage).'&nbsp;&#37;' : '-';
		$this->kmOfLastSet = $this->TrainingObject->getDistance();

		return ' <small style="display:inline-block;width:55px;color:#'.Running::Stresscolor(100*$Percentage/20).'">'.$String.'</small>';
	}

	/**
	 * Get percentage of last distance
	 * @return int
	 */
	private function getDistanceComparisonPercentage() {
		if ($this->kmOfLastSet == 0)
			return 0;

		return round(100*($this->TrainingObject->getDistance() - $this->kmOfLastSet ) / $this->kmOfLastSet, 1);
	}
}
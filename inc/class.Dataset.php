<?php
/**
 * This file contains class::Dataset
 * @package Runalyze\DataBrowser\Dataset
 */

use Runalyze\Activity\Pace;
use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\Factory;
use Runalyze\Util\Time;
use Runalyze\View\Activity\Dataview;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Icon;
use Runalyze\View\Stresscolor;

/**
 * Load dataset row for a given training or a group of trainings
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser\Dataset
 */
class Dataset {
	/**
	 * Factory
	 * @var \Runalyze\Model\Factory
	 */
	protected $Factory;

	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity = null;

	/**
	 * @var array
	 */
	protected $ActivityData = array();

	/**
	 * Sport
	 * @var \Runalyze\Model\Sport\Object
	 */
	protected $Sport = null;

	/**
	 * Type
	 * @var \Runalyze\Model\Type\Object
	 */
	protected $Type = null;

	/**
	 * Dataview
	 * @var \Runalyze\View\Activity\Dataview
	 */
	protected $Dataview = null;

	/**
	 * Activity linker
	 * @var \Runalyze\View\Activity\Linker
	 */
	protected $Linker = null;

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
	 * Is in summary mode?
	 * @var boolean
	 */
	protected $isSummary = false;

	/**
	 * Constructor
	 */
	public function __construct($accountID = null) {
		if (is_null($accountID)) {
			$accountID = SessionAccountHandler::getId();
		}

		$this->Factory = new Factory($accountID);

		$this->data = $this->datasetArray();
		$this->cols = count($this->data);

		if (Configuration::DataBrowser()->showEditLink()) {
			$this->cols++;
		}
	}

	/**
	 * Dataset
	 * @param boolean $complete [optional] set to true to load hidden fields as well
	 * @return array
	 */
	protected function datasetArray($complete = false) {
		$key = $complete ? 'Dataset-complete' : 'Dataset';
		$modus = $complete ? 0 : 2;

		$dataset = Cache::get($key);

		if (is_null($dataset)) {
			$dataset = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'dataset` WHERE `modus`>='.$modus.' AND `position`!=0 AND `accountid` = '.SessionAccountHandler::getId().' ORDER BY `position` ASC')->fetchAll();
			Cache::set($key, $dataset, '600');
		}

		if (empty($dataset)) {
			throw new \RuntimeException('No active dataset found.');
		}

		return $dataset;
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
		$this->data = $this->datasetArray(true);
		$this->cols = count($this->data);
	}

	/**
	 * Set activity
	 * @param \Runalyze\Model\Activity\Object $object activity object
	 */
	public function setActivity(Activity\Object $object) {
		$this->Activity = $object;
		$this->Dataview = new Dataview($object);
		$this->Linker = new Linker($object);
		$this->Sport = $this->Activity->sportid() > 0 ? $this->Factory->sport($this->Activity->sportid()) : null;
		$this->Type = $this->Activity->typeid() > 0 ? $this->Factory->type($this->Activity->typeid()) : null;
		$this->ActivityData = $this->Activity->completeData();
	}

	/**
	 * Set activity data
	 * @param array $data activity data
	 */
	public function setActivityData(array $data) {
		$this->isSummary = false;

		if (!empty($data)) {
			$this->setActivity( new Activity\Object($data) );
			$this->ActivityData = $data;
		} else {
			$this->Activity = null;
			$this->Dataview = null;
			$this->Linker = null;
			$this->Sport = null;
			$this->Type = null;
		}
	}

	/**
	 * Load a group of trainings for summary
	 * @param int $sportid
	 * @param int $timestart
	 * @param int $timeend
	 * @return boolean Are any trainings loaded?
	 */
	public function loadGroupOfTrainings($sportid, $timestart, $timeend) {
		return $this->setGroupOfTrainings(
			DB::getInstance()->query('
				SELECT
					`sportid`,
					`time`,
					SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`,
					SUM(1) as `num`
					'.$this->getQuerySelectForSet().'
				FROM `'.PREFIX.'training`
				WHERE
					`sportid`='.$sportid.'
					AND `accountid` = '.SessionAccountHandler::getId().'
					AND `time` BETWEEN '.($timestart-10).' AND '.($timeend-10).'
					'.$this->getQueryWhereNotPrivate().'
				GROUP BY `sportid`
				LIMIT 1
			')->fetch()
		);
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
		if ($timeend == 0) {
			$timeend = time();
		}

		if ($timerange == 366*DAY_IN_S) {
			$timerangeQuery = date('Y', $timeend).' - YEAR(FROM_UNIXTIME(`time`)) as `timerange`';
		} elseif ($timerange == 31*DAY_IN_S) {
			$timerangeQuery = date('m', $timeend).' - MONTH(FROM_UNIXTIME(`time`)) + 12*('.date('Y', $timeend).' - YEAR(FROM_UNIXTIME(`time`))) as `timerange`';
		} else {
			$timerangeQuery = 'FLOOR(('.$timeend.'-`time`)/('.$timerange.')) as `timerange`';
		}

		return DB::getInstance()->query('
			SELECT
				`sportid`,
				`time`,
				SUM(IF(`distance`>0,`s`,0)) as `s_sum_with_distance`,
				SUM(1) as `num`,
				'.$timerangeQuery.'
				'.$this->getQuerySelectForSet().'
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.$sportid.'
				AND `accountid` = '.SessionAccountHandler::getId().'
				AND `time` BETWEEN '.($timestart-10).' AND '.($timeend-10).'
				'.$this->getQueryWhereNotPrivate().'
			GROUP BY `timerange`, `sportid`
			ORDER BY `timerange` ASC
		')->fetchAll();
	}

	/**
	 * Set group of trainings for summary
	 * @param array $SummaryData
	 */
	public function setGroupOfTrainings($SummaryData) {
		$this->setActivityData($SummaryData);

		$this->kmOfLastSet = 0;
		$this->isSummary = true;
	}

	/**
	 * Get string for selecting dataset in query
	 * @return string
	 */
	public function getQuerySelectForSet() {
		$String = '';
		$Sum = Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`)*`s`' : '`vdot`*`s`';

		$showVdot=0;

		foreach ($this->data as $set) {
			if ($set['summary'] == 1) {
				if ($set['name'] == 'vdot' || $set['name'] == 'vdoticon') {
					$showVdot = 1;
				} elseif ($set['name'] == 'temperature') {
					$String .= ', ' . $set['summary_mode'] . '(NULLIF(`' . $set['name'] . '`,0)) as `' . $set['name'] . '`';
				} elseif ($set['name'] != 'pace') {
					if ($set['summary_mode'] != 'AVG') {
						$String .= ', ' . $set['summary_mode'] . '(`' . $set['name'] . '`) as `' . $set['name'] . '`';
					} else {
						$String .= ', SUM(`s`*`'.$set['name'].'`*(`'.$set['name'].'` > 0))'
									.'/SUM(`s`*(`'.$set['name'].'` > 0)) as `'.$set['name'].'`';
					}
				}
			}
		}
		if ($showVdot) {
			$String .= ', SUM(IF(`use_vdot`=1 AND `vdot`>0,' . $Sum . ',0))/SUM(IF(`use_vdot`=1 AND `vdot`>0,`s`,0)) as `vdot`';
		}

		return $String;
	}

	/**
	 * Get all datasets as comma separated string for query
	 * @return string
	 */
	public function getQuerySelectForAllDatasets() {
		$String = ',`is_track`,`use_vdot`,`vdot_with_elevation`,`is_public`';

		foreach ($this->data as $set) {
			if ($set['name'] != 'pace'  && $set['name'] != 'vdoticon') {
				$String .= ', `'.$set['name'].'`';
			}
		}

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
	 * Display short link for e.g. 'Gymnastik'
	 */
	public function displayShortLink() {
		echo $this->Linker->linkWithSportIcon('atRight');
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

		if (Configuration::DataBrowser()->showCreateLink() && !FrontendShared::$IS_SHOWN) {
			$addLink = ImporterWindow::linkForDate($timestamp);
		}

		if (Time::isToday($timestamp)) {
			$weekDay = '<strong>'.$weekDay.'</strong>';
		}

		return $date.' '.$addLink.' '.$weekDay;
	}

	/**
	 * Display this dataset as a table-row
	 */
	public function displayTableColumns() {
		$this->displayEditLink();

		foreach ($this->data as $set) {
			$this->displayDataset($set);
		}
	}

	/**
	 * Display labels
	 */
	public function displayTableLabels() {
		if (Configuration::DataBrowser()->showEditLink()) {
			echo HTML::emptyTD();
		}

		$Labels = new DatasetLabels();

		foreach ($this->data as $set) {
			echo '<td><span '.Ajax::tooltip('', $Labels->get($set['name']), false, true).'>'.$Labels->get($set['name']).'</span></td>';
		}
	}

	/**
	 * Display a single dataset
	 * @param array $set
	 */
	private function displayDataset($set) {
		if ($this->isSummary && $set['summary'] == 0) {
			echo HTML::emptyTD();
		} else {
			echo HTML::td($this->getDataset($set['name']), $set['class'], $set['style']);
		}
	}

	/**
	 * Display edit link if used in DataBrowser
	 */
	public function displayEditLink() {
		if (Configuration::DataBrowser()->showEditLink()) {
			if ($this->isSummary || FrontendShared::$IS_SHOWN) {
				echo HTML::emptyTD();
			} else {
				echo HTML::td($this->Linker->smallEditLink());
			}
		}
	}

	/**
	 * Display public icon
	 */
	public function displayPublicIcon() {
		if (!is_object($this->Activity) || !$this->Activity->isPublic()) {
			echo HTML::emptyTD();
		} else {
			echo HTML::td(\Icon::$ADD_SMALL_GREEN, 'link');
		}
	}

	/**
	 * Get content for a given dataset
	 * @param string $name
	 * @return string
	 */
	public function getDataset($name) {
		switch($name) {
			case 'sportid':
				if (!is_null($this->Sport)) {
					return $this->Sport->icon()->code();
				}

				return '';

			case 'typeid':
				if (!is_null($this->Type)) {
					if ($this->Type->isQualitySession()) {
						return '<strong>'.$this->Type->abbreviation().'</strong>';
					}

					return $this->Type->abbreviation();
				}

				return '';

			case 'time':
				return $this->Dataview->daytime();

			case 'distance':
				return $this->Dataview->distance().$this->distanceComparison();

			case 's':
				return $this->Dataview->duration()->string();

			case 'pace':
				if ($this->Activity->distance() > 0) {
					if (isset($this->ActivityData['s_sum_with_distance'])) {
						if ($this->ActivityData['s_sum_with_distance'] > 0) {
							$Pace = new Pace($this->ActivityData['s_sum_with_distance'], $this->Activity->distance(), SportFactory::getSpeedUnitFor($this->Activity->sportid()));
							return $Pace->valueWithAppendix();
						}

						return '';
					}

					return $this->Dataview->pace()->valueWithAppendix();
				}

				return '';

			case 'elevation':
				return $this->Dataview->elevation();

			case 'kcal':
				return $this->Dataview->calories();

			case 'pulse_avg':
				if ($this->Activity->hrAvg() > 0) {
					return $this->Dataview->hrAvg()->string();
				}

				return '';

			case 'pulse_max':
				if ($this->Activity->hrMax() > 0) {
					return $this->Dataview->hrMax()->string();
				}

				return '';

			case 'trimp':
				return $this->Dataview->trimp();

			case 'cadence':
				if ($this->Dataview->cadence()->value() > 0) {
					return $this->Dataview->cadence()->asString();
				}

				return '';

			case 'stride_length':
				if ($this->Dataview->strideLength()->inCM() > 0) {
					return $this->Dataview->strideLength()->string();
				}

				return '';

			case 'groundcontact':
				return $this->Dataview->groundcontact();

			case 'vertical_oscillation':
				return $this->Dataview->verticalOscillation();

			case 'power':
				return $this->Dataview->power();

			case 'temperature':
				if (!$this->Activity->weather()->temperature()->isUnknown() && (is_null($this->Sport) || $this->Sport->isOutside())) {
					return $this->Activity->weather()->temperature()->asString();
				}

				return '';

			case 'weatherid':
				if (!$this->Activity->weather()->isEmpty() && (is_null($this->Sport) || $this->Sport->isOutside())) {
					return $this->Activity->weather()->condition()->icon()->code();
				}

				return '';

			case 'routeid':
				if ($this->Activity->get(Activity\Object::ROUTEID) > 0) {
					return $this->cut(
						$this->Factory->route($this->Activity->get(Activity\Object::ROUTEID))->name()
					);
				}

				return '';

			case 'splits':
				if (!$this->Activity->splits()->isEmpty()) {
					if (
						$this->Activity->splits()->hasActiveAndInactiveLaps() ||
						round($this->Activity->splits()->totalDistance()) != round($this->Activity->distance()) ||
						(!is_null($this->Type) && $this->Type->id() == Configuration::General()->competitionType())
					) {
						// TODO: Icon with tooltip?
						$Icon = new Icon( Icon::CLOCK );
						return $Icon->code();
					}
				}

				return '';

			case 'comment':
				return $this->cut( $this->Activity->comment() );

			case 'partner':
				return $this->cut( $this->Activity->partner()->asString() );

			case 'abc':
				return $this->Dataview->abcIcon();


			case 'vdoticon':
				if (!is_null($this->Sport) && $this->Sport->id() == Configuration::General()->runningSport()) {
					return $this->Dataview->vdotIcon();
				}

				return '';

			case 'vdot':
				if (!is_null($this->Sport) && $this->Sport->id() == Configuration::General()->runningSport() && $this->Activity->vdotByHeartRate() > 0) {
					if (!$this->Activity->usesVDOT()) {
						return '<span class="unimportant">'.$this->Dataview->vdot()->value().'</span>';
					}

					return $this->Dataview->vdot()->value();
				}

				return '';

			case 'jd_intensity':
				if (!is_null($this->Sport) && $this->Sport->id() == Configuration::General()->runningSport()) {
					return $this->Dataview->jdIntensityWithStresscolor();
				}

				return '';

			case 'fit_vdot_estimate':
				if (!is_null($this->Sport) && $this->Sport->id() == Configuration::General()->runningSport()) {
					return $this->Dataview->fitVdotEstimate();
				}

				return '';

			case 'fit_recovery_time':
				return $this->Dataview->fitRecoveryTime();

			case 'fit_hrv_analysis':
				return $this->Dataview->fitHRVscore();
		}

		return '';
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
	 * Compare distance to last set
	 * @return string
	 */
	private function distanceComparison() {
		if (!$this->compareKM)
			return '';

		$Percentage = $this->distanceComparisonPercentage();
		$String     = ($Percentage != 0) ? sprintf("%+d", $Percentage).'&nbsp;&#37;' : '-';
		$this->kmOfLastSet = $this->Activity->distance();

		$Stress = new Stresscolor($Percentage);
		$Stress->scale(0, 30);

		return ' <small style="display:inline-block;width:55px;color:#'.$Stress->rgb().'">'.$String.'</small>';
	}

	/**
	 * Get percentage of last distance
	 * @return int
	 */
	private function distanceComparisonPercentage() {
		if ($this->kmOfLastSet == 0) {
			return 0;
		}

		return round(100*($this->Activity->distance() - $this->kmOfLastSet ) / $this->kmOfLastSet, 1);
	}

	/**
	 * public getter for Dataset data
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

}

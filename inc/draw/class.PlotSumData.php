<?php
/**
 * This file contains class::PlotSumData
 * @package Runalyze\Plot
 */

use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;

/**
 * Plot sum data
 * @package Runalyze\Plot
 */
abstract class PlotSumData extends Plot {
	/**
	 * Key as year for last 6 months
	 * @var string
	 */
	const LAST_6_MONTHS = 'last6months';

	/**
	 * Key as year for last 12 months
	 * @var string
	 */
	const LAST_12_MONTHS = 'last12months';

	/**
	 * @var string
	 */
	const ANALYSIS_DEFAULT = 'kmorh';

	/**
	 * @var string
	 */
	const ANALYSIS_TRIMP = 'trimp';

	/**
	 * URL to window
	 * @var string
	 */
	public static $URL = 'call/window.plotSumData.php';

	/**
	 * URL to shared window
	 * @var string
	 */
	public static $URL_SHARED = 'call/window.plotSumData.shared.php';

	/**
	 * Year
	 * @var string
	 */
	protected $Year = '';

	/**
	 * Sport
	 * @var Sport
	 */
	protected $Sport = null;

	/**
	 * Raw data from database
	 * @var array
	 */
	protected $RawData = array();

	/**
	 * First week/month/etc.
	 * @var int
	 */
	protected $timerStart = 0;

	/**
	 * Last week/month/etc.
	 * @var int
	 */
	protected $timerEnd = 0;

	/**
	 * Show distance instead of time?
	 * @var bool
	 */
	protected $usesDistance = false;

	/**
	 * Which analysis to show
	 * @var string
	 */
	protected $Analysis;

	/** @var mixed */
	protected $ParamSportId;

    /** @var mixed */
    protected $ParamYear;

    /** @var mixed */
    protected $ParamGroup;

    /** @var mixed */
    protected $ParamType;

	/**
	 * Constructor
	 */
	public function __construct() {
	    $this->ParamSportId = Request::param('sportid');
        $this->ParamYear = Request::param('y');
        $this->ParamGroup = Request::param('group');
        $this->ParamType = Request::param('type');

		$sportid = strlen($this->ParamSportId) > 0 ? $this->ParamSportId : Configuration::General()->runningSport();

		$this->Year  = $this->getRequestedYear();
		$this->Sport = new Sport($sportid);

		parent::__construct($this->getCSSid(), 800, 500);

		$this->init();
		$this->addAverage();
		$this->addTarget();
	}

	/**
	 * Get requested year/key
	 * @return int|string
	 */
	protected function getRequestedYear() {
		if (self::LAST_12_MONTHS == $this->ParamYear || self::LAST_6_MONTHS == $this->ParamYear) {
			return $this->ParamYear;
		}

		return (int)$this->ParamYear;
	}

	/**
	 * Display
	 */
	final public function display() {
		$this->displayHeader();
		$this->displayContent();
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo '<div class="panel-heading">';
		echo '<div class="panel-menu">';
		echo $this->getNavigationMenu();
		echo '</div>';
		echo HTML::h1( $this->getTitle() . ' ' . $this->getTitleAppendix() );
		echo '</div>';
	}

	/**
	 * Display content
	 */
	private function displayContent() {
		echo '<div class="panel-content">';
		$this->outputDiv();
		$this->outputJavaScript();
		$this->displayInfos();
		echo '</div>';
	}

	/**
	 * Get navigation
	 */
	private function getNavigationMenu() {
		$Menus = array(
			$this->getMenuLinksForGrouping(),
			$this->getMenuLinksForAnalysis(),
			$this->getMenuLinksForSports(),
			$this->getMenuLinksForYears()
		);

		if ('sport' == $this->ParamGroup || 'mainsport' == $this->ParamGroup)
			unset($Menus[0]);

		$Code  = '<ul>';

		foreach ($Menus as $Menu) {
			$Code .= '<li class="with-submenu"><span class="link">'.$Menu['title'].'</span><ul class="submenu">';
			$Code .= implode('', $Menu['links']);
			$Code .= '</ul></li>';
		}

		$Code .= '</ul>';

		return $Code;
	}

	/**
	 * Get menu links for grouping
	 * @return array
	 */
	private function getMenuLinksForGrouping() {
		if ('' == $this->ParamGroup) {
			$Current = __('Total');
		} else {
			$Current = __('By type');
		}

		$Links = array();
		$Links[] = $this->link( __('Total'), $this->Year, $this->ParamSportId, '', '' == $this->ParamGroup);
		$Links[] = $this->link( __('By type'), $this->Year, $this->ParamSportId, 'types', 'types' == $this->ParamGroup);

		return ['title' => $Current, 'links' => $Links];
	}

	/**
	 * Get menu links for analysis
	 * @return array
	 */
	private function getMenuLinksForAnalysis() {
		if ($this->Analysis == self::ANALYSIS_DEFAULT) {
			$Current = __('Distance/Duration');
		} else {
			$Current = __('TRIMP');
		}

		$Links = array(
			$this->link( __('Distance/Duration'), $this->Year, $this->ParamSportId, $this->ParamGroup, $this->Analysis == self::ANALYSIS_DEFAULT, self::ANALYSIS_DEFAULT),
			$this->link( __('TRIMP'), $this->Year, $this->ParamSportId, $this->ParamGroup, $this->Analysis == self::ANALYSIS_TRIMP, self::ANALYSIS_TRIMP)
		);

		return ['title' => $Current, 'links' => $Links];
	}

	/**
	 * Get menu links for sports
	 * @return array
	 */
	private function getMenuLinksForSports() {
		$CurrentId = $this->Sport->id();
		$Current = __('All sports');
		$Links = array(
			$this->link( __('All sports'), $this->Year, 0, 'sport', 'sport' == $this->ParamGroup),
            $this->link( __('Main sports'), $this->Year, 0, 'mainsport', 'mainsport' == $this->ParamGroup)
		);

		$SportGroup = $this->ParamGroup == 'sport' ? 'types' : $this->ParamGroup;
		$Sports = SportFactory::NamesAsArray();

		foreach ($Sports as $id => $name) {
			if ($CurrentId == $id) {
				$Current = $name;
			}

			$Links[] = $this->link($name, $this->Year, $id, $SportGroup, $CurrentId == $id);
		}

		return ['title' => $Current, 'links' => $Links];
	}

	/**
	 * Get menu links for years
	 * @return array
	 */
	private function getMenuLinksForYears() {
		if (self::LAST_6_MONTHS == $this->Year) {
			$Current = __('Last 6 months');
		} elseif (self::LAST_12_MONTHS == $this->Year) {
			$Current = __('Last 12 months');
		} else {
			$Current = $this->Year;
		}

		$Links = array(
			$this->link(__('Last 6 months'), self::LAST_6_MONTHS, $this->ParamSportId, $this->ParamGroup, self::LAST_6_MONTHS == $this->Year),
			$this->link(__('Last 12 months'), self::LAST_12_MONTHS, $this->ParamSportId, $this->ParamGroup, self::LAST_12_MONTHS == $this->Year)
		);

		for ($Y = date('Y'); $Y >= START_YEAR; $Y--)
			$Links[] = $this->link($Y, $Y, $this->ParamSportId, $this->ParamGroup, $Y == $this->Year);

		return ['title' => $Current, 'links' => $Links];
	}

	/**
	 * Link to plot
	 * @param string $text
	 * @param int $year
	 * @param int $sportid
	 * @param string $group
	 * @param boolean $current
	 * @return string
	 */
	private function link($text, $year, $sportid, $group, $current = false, $analysis = false) {
		if (!$analysis) {
			$analysis = $this->Analysis;
		}

		if (FrontendShared::$IS_SHOWN)
			return Ajax::window('<li'.($current ? ' class="active"' : '').'><a href="'.DataBrowserShared::getBaseUrl().'?type='.($this->ParamType=='week'?'week':'month').'&type='.$this->ParamType.'&y='.$year.'&sportid='.$sportid.'&group='.$group.'&analysis='.$analysis.'">'.$text.'</a></li>');
		else
			return Ajax::window('<li'.($current ? ' class="active"' : '').'><a href="'.self::$URL.'?type='.$this->ParamType.'&y='.$year.'&sportid='.$sportid.'&group='.$group.'&analysis='.$analysis.'">'.$text.'</a></li>');
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	abstract protected function getCSSid();

	/**
	 * Get title
	 * @return string
	 */
	abstract protected function getTitle();

	/**
	 * @return string
	 */
	protected function getTitleAppendix() {
		if ($this->Year == self::LAST_6_MONTHS) {
			return __('last 6 months');
		} elseif ($this->Year == self::LAST_12_MONTHS) {
			return __('last 12 months');
		}

		return $this->Year;
	}

	/**
	 * Get X labels
	 * @return array
	 */
	abstract protected function getXLabels();

	/**
	 * Init
	 */
	private function init() {
		$this->initData();
		$this->adjustDataForUnit();
		$this->setAxis();
		$this->setOptions();
	}

	protected function adjustDataForUnit() {
	    if (self::ANALYSIS_DEFAULT == $this->Analysis && $this->usesDistance) {
	        $factor = Configuration::General()->distanceUnitSystem()->distanceToPreferredUnitFactor();

	        if (1 != $factor) {
	            foreach ($this->Data as $index => $plotData) {
	                $this->Data[$index]['data'] = array_map(function ($v) use ($factor) {
                        return $v * $factor;
                    }, $this->Data[$index]['data']);
                }
            }
        }
    }

	/**
	 * Set axis
	 */
	private function setAxis() {
		$this->setXLabels($this->getXLabels());
		$this->addYAxis(1, 'left');

		if ($this->Analysis == self::ANALYSIS_DEFAULT) {
			if ($this->usesDistance) {
				$this->addYUnit(1, Configuration::General()->distanceUnitSystem()->distanceUnit());
				$this->setYTicks(1, 10, 0);
			} else {
				$this->addYUnit(1, 'h');
				$this->setYTicks(1, 1, 0);
			}
		}
	}

	/**
	 * Set options
	 */
	private function setOptions() {
		$this->showBars(true);
		$this->setTitle($this->getTitle());

		$this->stacked();
	}

	/**
	 * Init data
	 */
	private function initData() {
		if (START_TIME != time() && (
			($this->Year >= START_YEAR && $this->Year <= date('Y') && START_TIME != time()) ||
			$this->Year == self::LAST_6_MONTHS ||
			$this->Year == self::LAST_12_MONTHS
		)) {
			$this->defineAnalysis();
			$this->loadData();
			$this->setData();
		} else {
			$this->raiseError( __('There are no data for this timerange.') );
		}
	}

	/**
	 * Define analysis
	 */
	private function defineAnalysis() {
		$request = Request::param('analysis');

		if ($request == self::ANALYSIS_TRIMP) {
			$this->Analysis = self::ANALYSIS_TRIMP;
		} else {
			$this->Analysis = self::ANALYSIS_DEFAULT;
		}
	}

	/**
	 * Init to show year
	 */
	private function loadData() {
		$whereSport = ('sport' == $this->ParamGroup || 'mainsport' == $this->ParamGroup) ? '' : '`sportid`='.$this->Sport->id().' AND';

		$this->usesDistance = $this->Sport->usesDistance();
		if ($this->ParamGroup != 'sport' && $this->ParamGroup != 'mainsport' && $this->Analysis == self::ANALYSIS_DEFAULT && $this->usesDistance) {
			$num = DB::getInstance()->query('
				SELECT COUNT(*) FROM `'.PREFIX.'training`
				WHERE
					'.$whereSport.'
					`distance` = 0 AND `s` > 0 AND
				'.$this->whereDate().'
			')->fetchColumn();

			if ($num > 0)
				$this->usesDistance = false;
		}

		$this->RawData = DB::getInstance()->query('
			SELECT
				`sportid`,
				`typeid`,
				(r.`official_time` IS NOT NULL )as `wk`,
				'.$this->dataSum().' as `sum`,
				'.$this->timer().' as `timer`
			FROM `'.PREFIX.'training` tr
			    LEFT JOIN `'.PREFIX.'raceresult` r ON tr.id = r.activity_id
			WHERE
				`tr`.`accountid` = '.SessionAccountHandler::getId().' AND
				'.$whereSport.'
				'.$this->whereDate().'
			GROUP BY '.$this->groupBy().', '.$this->timer()
		)->fetchAll();
	}

	/**
	 * @return string
	 */
	protected function whereDate() {
		if (is_numeric($this->Year)) {
			return '`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->Year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->Year+1).'-01-01\')-1';
		} elseif ($this->Year == self::LAST_6_MONTHS) {
			return '`time` >= '.$this->beginningOfLast6Months();
		} else {
			return '`time` >= '.$this->beginningOfLast12Months();
		}
	}

	/**
	 * @return int
	 */
	abstract protected function beginningOfLast6Months();

	/**
	 * @return int
	 */
	abstract protected function beginningOfLast12Months();

	/**
	 * Sum data for query
	 * @return string
	 */
	private function dataSum() {
		if ($this->Analysis == self::ANALYSIS_TRIMP) {
			return 'SUM(`trimp`)';
		} elseif ($this->usesDistance) {
			return 'SUM(`distance`)';
		}

		return 'SUM(`s`)/3600';
	}

	/**
	 * Timer table for query
	 * @return string
	 */
	abstract protected function timer();

	/**
	 * Group by table for query
	 * @return string
	 */
	private function groupBy() {
		if ('sport' == $this->ParamGroup || 'mainsport' == $this->ParamGroup)
			return '`sportid`';

		if ('types' == $this->ParamGroup)
			return '`typeid`';

		return 'wk';
	}

	/**
	 * Set data
	 */
	private function setData() {
		if ('sport' == $this->ParamGroup || 'mainsport' == $this->ParamGroup)
			$this->setDataForSports();
		elseif ('types' == $this->ParamGroup)
			$this->setDataForTypes();
		else
			$this->setDataForCompetitionAndTraining();

		if (empty($this->RawData))
			$this->setYLimits(1, 0, 10);
		else
			$this->setYLimitsFromData();
	}

	/**
	 * Set Y limits from data
	 */
	private function setYLimitsFromData() {
		$values = array();

		foreach ($this->Data as $data) {
			foreach ($data['data'] as $i => $val)
				if (!isset($values[$i]))
					$values[$i] = $val;
				else
					$values[$i] += $val;
		}

		$this->setYLimits(1, 0, Helper::ceilFor(max($values), 10));
	}

	/**
	 * Set data to compare training and competition
	 */
	private function setDataForSports() {
		$emptyData  = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$Sports     = array();
		$idMapping = array();

		foreach (\Runalyze\Context::Factory()->allSports() as $Sport) {
		    if ('mainsport' == $this->ParamGroup && !$Sport->isMainSport()) {
                $idMapping[$Sport->id()] = -1;
            } else {
    			$Sports[$Sport->id()] = array('name' => $Sport->name(), 'data' => $emptyData);
                $idMapping[$Sport->id()] = $Sport->id();
            }
		}

		if ('mainsport' == $this->ParamGroup) {
		    $altId = max(array_keys($Sports)) + 1;
		    $Sports[$altId] = ['name' => 'Alternative sports', 'data' => $emptyData];

		    foreach ($idMapping as $from => $to) {
		        if (-1 == $to) {
		            $idMapping[$from] = $altId;
                }
            }
        }

		foreach ($this->RawData as $dat)
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd)
				$Sports[$idMapping[$dat['sportid']]]['data'][$dat['timer']-$this->timerStart] += $dat['sum'];

		foreach ($Sports as $Sport)
			$this->Data[] = array('label' => isset($Sport['name']) ? $Sport['name'] : '?', 'data' => $Sport['data']);
	}

	/**
	 * Set data to compare training and competition
	 */
	private function setDataForTypes() {
		$emptyData = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$Types     = array(array('name' => __('without'), 'data' => $emptyData));

		foreach (\Runalyze\Context::Factory()->typeForSport($this->Sport->id()) as $Type) {
		    $name = ($Type->abbreviation() == '') ? $Type->name() : $Type->abbreviation();
			$Types[$Type->id()] = array('name' => $name, 'data' => $emptyData);
		}

		foreach ($this->RawData as $dat)
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd)
				$Types[isset($Types[$dat['typeid']]) ? $dat['typeid'] : 0]['data'][$dat['timer']-$this->timerStart] = $dat['sum'];

		foreach ($Types as $Type)
			$this->Data[] = array('label' => $Type['name'], 'data' => $Type['data']);
	}

	/**
	 * Set data to compare training and competition
	 */
	private function setDataForCompetitionAndTraining() {
		$Kilometers            = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$KilometersCompetition = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$hasCompetitions = false;

		foreach ($this->RawData as $dat) {
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd) {
				if ($dat['wk'] == 1) {
					$KilometersCompetition[$dat['timer']-$this->timerStart] = $dat['sum'];
					$hasCompetitions = true;
				} else {
					$Kilometers[$dat['timer']-$this->timerStart] = $dat['sum'];
				}
			}
		}

		if ($hasCompetitions) {
			$this->Data[] = array('label' => __('Races'), 'data' => $KilometersCompetition);
			$this->Data[] = array('label' => __('Training'), 'data' => $Kilometers, 'color' => '#E68617');
		} else {
			$this->Data[] = array('label' => $this->Sport->name(), 'data' => $Kilometers, 'color' => '#E68617');
		}
	}

	/**
	 * @return bool
	 */
	protected function showsTarget() {
		return ($this->Sport->isRunning() && $this->Analysis == self::ANALYSIS_DEFAULT && $this->usesDistance);
	}

	/**
	 * Add line for target
	 */
	protected function addTarget() {
		if ($this->showsTarget()) {
			$BasicEndurance = new BasicEndurance();
			$BasicEndurance->readSettingsFromConfiguration();

			$target = new Distance($this->factorForWeekKm() * $BasicEndurance->getTargetWeekKm());
			$labelKeys = array_keys($this->getXLabels());

			$this->addThreshold('y', round($target->valueInPreferredUnit()), '#999');
			$this->addAnnotation(end($labelKeys), round($target->valueInPreferredUnit()), __('target:').'&nbsp;'.$target->string(true, 0), 0, -10);
		}
	}

	/**
	 * Add line for average
	 */
	protected function addAverage() {
		$avg = $this->getAverage();

		if ($this->usesDistance && self::ANALYSIS_DEFAULT == $this->Analysis) {
			$avgDistance = new Distance($avg);
			$avg = $avgDistance->valueInPreferredUnit();
			$string = $avgDistance->string(true, 0);
		} elseif (self::ANALYSIS_DEFAULT == $this->Analysis) {
			$string = $avg.' h';
		} else {
			$string = $avg;
		}

		$this->addThreshold('y', $avg, '#999');
		$this->addAnnotation(-1, $avg, __('avg:').'&nbsp;'.$string, 0, -10);
	}

	/**
	 * @return int
	 */
	protected function getAverage() {
		$sum = 0;

		foreach ($this->Data as $series) {
			foreach ($series['data'] as $value) {
				$sum += $value;
			}
		}

		return (int)round($sum / ($this->timerEnd - $this->timerStart + 1));
	}

	/**
	 * @return float
	 */
	abstract protected function factorForWeekKm();

	/**
	 * Display additional info
	 */
	protected function displayInfos() {
		if ($this->showsTarget()) {
			$BasicEndurance = new BasicEndurance();
			$BasicEndurance->readSettingsFromConfiguration();

			echo HTML::info( __('Target is based on current marathon shape calculations.') );
		}
	}
}

<?php
/**
 * This file contains class::PlotSumData
 * @package Runalyze\Plot
 */
/**
 * Plot sum data
 * @package Runalyze\Plot
 */
abstract class PlotSumData extends Plot {
	/**
	 * URL to window
	 * @var string
	 */
	static public $URL = 'call/window.plotSumData.php';

	/**
	 * URL to shared window
	 * @var string
	 */
	static public $URL_SHARED = 'call/window.plotSumData.shared.php';

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
	 * Constructor
	 */
	public function __construct() {
		$sportid = strlen(Request::param('sportid')) > 0 ? Request::param('sportid') : Configuration::General()->runningSport();

		$this->Year  = (int)Request::param('y');
		$this->Sport = new Sport($sportid);

		parent::__construct($this->getCSSid(), 800, 500);

		$this->init();
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
		echo HTML::h1( $this->getTitle() );
		echo '</div>';
	}

	/**
	 * Display content
	 */
	private function displayContent() {
		echo '<div class="panel-content">';
		$this->outputDiv();
		$this->outputJavaScript();
		echo '</div>';
	}

	/**
	 * Get navigation
	 */
	private function getNavigationMenu() {
		$Menus = array(
			array(
				'title' => __('Choose analysis'),
				'links'	=> $this->getMenuLinksForGrouping()
			),
			array(
				'title' => __('Choose sport'),
				'links'	=> $this->getMenuLinksForSports()
			),
			array(
				'title' => __('Choose year'),
				'links'	=> $this->getMenuLinksForYears()
			)
		);

		if (Request::param('group') == 'sport')
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
		$Links = array();

		if ($this->Sport->isRunning())
			$Links[] = $this->link( __('Activity &amp; Competition'), $this->Year, Request::param('sportid'), '', Request::param('group') == '');
		else
			$Links[] = $this->link( __('Total'), $this->Year, Request::param('sportid'), '', Request::param('group') == '');

		$Links[] = $this->link( __('By type'), $this->Year, Request::param('sportid'), 'types', Request::param('group') == 'types');

		return $Links;
	}

	/**
	 * Get menu links for sports
	 * @return array
	 */
	private function getMenuLinksForSports() {
		$Links = array(
			$this->link( __('All sports'), $this->Year, 0, 'sport', Request::param('group') == 'sport')
		);

		$SportGroup = Request::param('group') == 'sport' ? 'types' : Request::param('group');
		$Sports     = SportFactory::NamesAsArray();
		foreach ($Sports as $id => $name)
			$Links[] = $this->link($name, $this->Year, $id, $SportGroup, $this->Sport->id() == $id);

		return $Links;
	}

	/**
	 * Get menu links for years
	 * @return array
	 */
	private function getMenuLinksForYears() {
		$Links = array();

		for ($Y = date('Y'); $Y >= START_YEAR; $Y--)
			$Links[] = $this->link($Y, $Y, Request::param('sportid'), Request::param('group'), $Y == $this->Year);

		return $Links;
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
	private function link($text, $year, $sportid, $group, $current = false) {
		if (FrontendShared::$IS_SHOWN)
			return Ajax::window('<li'.($current ? ' class="active"' : '').'><a href="'.DataBrowserShared::getBaseUrl().'?view='.(Request::param('type')=='week'?'weekkm':'monthkm').'&type='.Request::param('type').'&y='.$year.'&sportid='.$sportid.'&group='.$group.'">'.$text.'</a></li>');
		else
			return Ajax::window('<li'.($current ? ' class="active"' : '').'><a href="'.self::$URL.'?type='.Request::param('type').'&y='.$year.'&sportid='.$sportid.'&group='.$group.'">'.$text.'</a></li>');
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
	 * Get X labels
	 * @return array
	 */
	abstract protected function getXLabels();

	/**
	 * Init
	 */
	private function init() {
		$this->initData();
		$this->setAxis();
		$this->setOptions();
	}

	/**
	 * Set axis
	 */
	private function setAxis() {
		$this->setXLabels($this->getXLabels());
		$this->addYAxis(1, 'left');

		if ($this->usesDistance) {
			$this->addYUnit(1, 'km');
			$this->setYTicks(1, 10, 0);
		} else {
			$this->addYUnit(1, 'h');
			$this->setYTicks(1, 1, 0);
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
		if ($this->Year >= START_YEAR && $this->Year <= date('Y') && START_TIME != time()) {
			$this->loadData();
			$this->setData();
		} else {
			$this->raiseError( __('There are no data for this timerange.') );
		}
	}

	/**
	 * Init to show year
	 */
	private function loadData() {
		$whereSport = (Request::param('group') == 'sport') ? '' : '`sportid`='.$this->Sport->id().' AND';

		$this->usesDistance = $this->Sport->usesDistance();
		if (Request::param('group') != 'sport' && $this->usesDistance) {
			$num = DB::getInstance()->query('
				SELECT COUNT(*) FROM `'.PREFIX.'training`
				WHERE
					'.$whereSport.'
					`distance` = 0 AND `s` > 0 AND
					YEAR(FROM_UNIXTIME(`time`))='.(int)$this->Year.'
			')->fetchColumn();

			if ($num > 0)
				$this->usesDistance = false;
		}

		$this->RawData = DB::getInstance()->query('
			SELECT
				`sportid`,
				`typeid`,
				(`typeid` = '.Configuration::General()->competitionType().') as `wk`,
				'.$this->dataSum().' as `sum`,
				'.$this->timer().' as `timer`
			FROM `'.PREFIX.'training`
			WHERE
				'.$whereSport.'
				YEAR(FROM_UNIXTIME(`time`))='.(int)$this->Year.'
			GROUP BY '.$this->groupBy().', '.$this->timer()
		)->fetchAll();
	}

	/**
	 * Sum data for query
	 * @return string
	 */
	private function dataSum() {
		if ($this->usesDistance)
			return 'SUM(`distance`)';

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
		if (Request::param('group') == 'sport')
			return '`sportid`';

		if (Request::param('group') == 'types' && $this->Sport->hasTypes())
			return '`typeid`';

		return '(`typeid` = '.Configuration::General()->competitionType().')';
	}

	/**
	 * Set data
	 */
	private function setData() {
		if (Request::param('group') == 'sport')
			$this->setDataForSports();
		elseif (Request::param('group') == 'types' && $this->Sport->hasTypes())
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
		$SportsData = DB::getInstance()->query('
			SELECT
				id, name
			FROM
				`'.PREFIX.'sport`
		')->fetchAll();

		foreach ($SportsData as $Sport)
			$Sports[$Sport['id']] = array('name' => $Sport['name'], 'data' => $emptyData);

		foreach ($this->RawData as $dat)
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd)
				$Sports[$dat['sportid']]['data'][$dat['timer']-$this->timerStart] = $dat['sum'];

		foreach ($Sports as $Sport)
			$this->Data[] = array('label' => isset($Sport['name']) ? $Sport['name'] : '?', 'data' => $Sport['data']);
	}

	/**
	 * Set data to compare training and competition
	 */
	private function setDataForTypes() {
		$emptyData = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$Types     = array(array('name' => 'ohne', 'data' => $emptyData));
		$TypesData = DB::getInstance()->query('
			SELECT
				id, name
			FROM
				`'.PREFIX.'type`
			WHERE
				`sportid`="'.$this->Sport->id().'"
		')->fetchAll();

		foreach ($TypesData as $Type)
			$Types[$Type['id']] = array('name' => $Type['name'], 'data' => $emptyData);

		foreach ($this->RawData as $dat)
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd)
				$Types[$dat['typeid']]['data'][$dat['timer']-$this->timerStart] = $dat['sum'];

		foreach ($Types as $Type)
			$this->Data[] = array('label' => $Type['name'], 'data' => $Type['data']);
	}

	/**
	 * Set data to compare training and competition
	 */
	private function setDataForCompetitionAndTraining() {
		$Kilometers            = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);
		$KilometersCompetition = array_fill(0, $this->timerEnd - $this->timerStart + 1, 0);

		foreach ($this->RawData as $dat) {
			if ($dat['timer'] >= $this->timerStart && $dat['timer'] <= $this->timerEnd) {
				if ($dat['wk'] == 1)
					$KilometersCompetition[$dat['timer']-$this->timerStart] = $dat['sum'];
				else
					$Kilometers[$dat['timer']-$this->timerStart] = $dat['sum'];
			}
		}

		// TODO: currently, only ONE competition type is allowed (and used for running)
		// if ($this->Sport->hasTypes())
		if ($this->Sport->isRunning())
			$this->Data[] = array('label' => __('Competition'), 'data' => $KilometersCompetition);

		$this->Data[] = array('label' => __('Activity'), 'data' => $Kilometers, 'color' => '#E68617');
	}
}
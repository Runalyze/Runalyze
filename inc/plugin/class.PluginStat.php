<?php
/**
 * This file contains class::PluginStat
 * @package Runalyze\Plugin
 */

use Runalyze\Util\Time;

/**
 * Abstract plugin class for statistics
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
abstract class PluginStat extends Plugin {
	/**
	 * Boolean flag: show sports-navigation
	 * @var bool
	 */
	protected $ShowSportsNavigation = false;

	/**
	 * Boolean flag: show link for all sports
	 * @var bool
	 */
	protected $ShowAllSportsLink = false;

	/**
	 * Boolean flag: show years-navigation
	 * @var bool
	 */
	protected $ShowYearsNavigation = false;

	/**
	 * Boolean flag: show compare-link in years-navigation
	 * @var bool
	 */
	protected $ShowCompareYearsLink = true;

	/**
	 * Boolean flag: show last 6/12 months in years-navigation
	 * @var bool
	 */
	protected $ShowTimeRangeLinks = false;

	/**
	 * Array of links (each wrapped in a <li>-tag
	 * @var array
	 */
	private $LinkList = array();

	/**
	 * Header
	 */
	private $Header = '';

	/**
	 * Type
	 * @return int
	 */
	final public function type() {
		return PluginType::STAT;
	}

	/**
	 * Set flag for sports-navigation
	 * @param bool $flag
	 * @param bool $allFlag
	 */
	protected function setSportsNavigation($flag = true, $allFlag = false) {
		$this->ShowSportsNavigation = $flag;
		$this->ShowAllSportsLink = $allFlag;
	}

	/**
	 * Set flag for years-navigation
	 * @param bool $flag
	 * @param bool $compareFlag [optional]
	 * @param bool $timeRangeFlag [optional]
	 */
	protected function setYearsNavigation($flag = true, $compareFlag = true, $timeRangeFlag = false) {
		$this->ShowYearsNavigation = $flag;
		$this->ShowCompareYearsLink = $compareFlag;
		$this->ShowTimeRangeLinks = $timeRangeFlag;
	}

	/**
	 * Set array of links for toolbar-navigation
	 * @param array $Links
	 */
	protected function setToolbarNavigationLinks($Links) {
		$this->LinkList = $Links;
	}

	/**
	 * Includes the plugin-file for displaying the statistics
	 */
	public function display() {
		$this->prepareForDisplay();

		echo '<div class="panel-heading">';
		$this->displayHeader($this->Header, $this->getNavigation());
		echo '</div>';
		echo '<div class="panel-content statistics-container">';
		$this->displayContent();
		echo '</div>';
	}

	/**
	 * Set header
	 * @param string $Header
	 */
	protected function setHeader($Header) {
		$this->Header = $Header;
	}

	/**
	 * Add sport and year (if set) to header
	 */
	protected function setHeaderWithSportAndYear() {
		$HeaderParts = array();

		if ($this->sportid > 0 && $this->ShowSportsNavigation) {
			$Sport = new Sport($this->sportid);
			$HeaderParts[] = $Sport->name();
		}

		if ($this->ShowYearsNavigation) {
			$HeaderParts[] = $this->getYearString();
		}

		if (!empty($HeaderParts)) {
			$this->setHeader($this->name().': '.implode(', ', $HeaderParts));
		}
	}

	/**
	 * Get query for sport and year
	 * @param bool $addTableName must be used if query contains joins
	 * @return string
	 */
	protected function getSportAndYearDependenceForQuery($addTableName = false) {
		$Query = '';

		if ($this->sportid > 0) {
			$sport = $addTableName ? '`'.PREFIX.'training`.`sportid`' : '`sportid`';
			$Query .= ' AND '.$sport.'='.(int) $this->sportid;
		}

		$Query .= $this->getYearDependenceForQuery($addTableName);

		return $Query;
	}

	/**
	 * Get query for year
	 * @param bool $addTableName must be used if query contains joins
	 * @return string
	 */
	protected function getYearDependenceForQuery($addTableName = false) {
		$Query = '';
		$time = $addTableName ? '`'.PREFIX.'training`.`time`' : '`time`';

		if ($this->showsLast6Months()) {
			$Query .= ' AND '.$time.' > '.strtotime("first day of -5 months ");
		} elseif ($this->showsLast12Months()) {
			$Query .= ' AND '.$time.' > '.strtotime("first day of -11 months ");
		} elseif (!$this->showsAllYears()) {
			$Query .= ' AND '.$time.' BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1 ';
		}

		return $Query;
	}

	/**
	 * Timer for year or ordered months
	 * @param bool $addTableName must be used if query contains joins
	 * @return string
	 */
	protected function getTimerForOrderingInQuery($addTableName = false) {
		$time = $addTableName ? '`'.PREFIX.'training`.`time`' : '`time`';

		if ($this->showsAllYears()) {
			return 'YEAR(FROM_UNIXTIME('.$time.'))';
		} elseif (!$this->showsSpecificYear()) {
			return 'DATE_FORMAT(FROM_UNIXTIME('.$time.'), "%Y-%m")';
		}

		return 'MONTH(FROM_UNIXTIME('.$time.'))';
	}

	/**
	 * Index for timer
	 * @param bool $addTableName must be used if query contains joins
	 * @return string
	 */
	protected function getTimerIndexForQuery($addTableName = false) {
		$time = $addTableName ? '`'.PREFIX.'training`.`time`' : '`time`';

		if ($this->showsLast6Months()) {
			return '((MONTH(FROM_UNIXTIME('.$time.')) + 5 - '.date('m').')%12 + 1)';
		} elseif ($this->showsLast12Months()) {
			return '((MONTH(FROM_UNIXTIME('.$time.')) + 11 - '.date('m').')%12 + 1)';
		}

		return $this->getTimerForOrderingInQuery($addTableName);
	}

	/**
	 * Display header
	 * @param string $name
	 * @param string $rightMenu
	 * @param string $leftMenu
	 */
	private function displayHeader($name = '', $rightMenu = '', $leftMenu = '') {
		if ($name == '') {
			$name = $this->name();
		}

		if (!empty($leftMenu)) {
			echo '<div class="icons-left">'.$leftMenu.'</div>';
		}

		if (!empty($rightMenu)) {
			echo '<div class="panel-menu">'.$rightMenu.'</div>';
		}

		echo '<h1>'.$name.'</h1>';
		echo '<div class="hover-icons">'.$this->getConfigLink().$this->getReloadLink().'</div>';
	}

	/**
	 * Get navigation
	 */
	private function getNavigation() {
		if ($this->ShowSportsNavigation) {
			$this->LinkList[] = '<li class="with-submenu"><span class="link">'.$this->getSportString().'</span><ul class="submenu">'.$this->getSportLinksAsList().'</ul>';
		}

		if ($this->ShowYearsNavigation) {
			$this->LinkList[] = '<li class="with-submenu"><span class="link">'.$this->getYearString().'</span><ul class="submenu">'.$this->getYearLinksAsList($this->ShowCompareYearsLink, $this->ShowTimeRangeLinks).'</ul>';
		}

		if (!empty($this->LinkList)) {
			return '<ul>'.implode('', $this->LinkList).'</ul>';
		}

		return '';
	}

	/**
	 * Get links for all sports
	 * @return array
	 */
	private function getSportLinksAsList() {
		$Links = '';

		if ($this->ShowAllSportsLink) {
			$Links .= '<li'.(-1==$this->sportid ? ' class="active"' : '').'>'.$this->getInnerLink(__('All'), -1, $this->year, $this->dat).'</li>';
		}

		$Sports = SportFactory::NamesAsArray();
		foreach ($Sports as $id => $name) {
			$Links .= '<li'.($id == $this->sportid ? ' class="active"' : '').'>'.$this->getInnerLink($name, $id, $this->year, $this->dat).'</li>';
		}

		return $Links;
	}

	/**
	 * Get links for all years
	 * @param bool $CompareYears If set, adds a link with year=-1
	 * @param bool $TimeRanges If set, adds links with year=6/12
	 * @return string
	 */
	private function getYearLinksAsList($CompareYears = true, $TimeRanges = false) {
		$Links = '';

		if ($CompareYears) {
			$Links .= '<li'.(-1==$this->year ? ' class="active"' : '').'>'.$this->getInnerLink($this->titleForAllYears(), $this->sportid, -1, $this->dat).'</li>';
		}

		if ($TimeRanges) {
			$Links .= '<li'.(6 == $this->year ? ' class="active"' : '').'>'.$this->getInnerLink(__('Last 6 months'), $this->sportid, 6, $this->dat).'</li>';
			$Links .= '<li'.(12 == $this->year ? ' class="active"' : '').'>'.$this->getInnerLink(__('Last 12 months'), $this->sportid, 12, $this->dat).'</li>';
		}

		for ($x = date("Y"); $x >= START_YEAR; $x--) {
			$Links .= '<li'.($x==$this->year ? ' class="active"' : '').'>'.$this->getInnerLink($x, $this->sportid, $x, $this->dat).'</li>';
		}

		return $Links;
	}

	/**
	 * Get the year as string
	 * @return string
	 */
	protected function getYearString() {
		if ($this->showsAllYears()) {
			return $this->titleForAllYears();
		} elseif ($this->showsLast6Months()) {
			return __('Last 6 months');
		} elseif ($this->showsLast12Months()) {
			return __('Last 12 months');
		}

		return $this->year;
	}

	/**
	 * Get sport as string
	 * @return string
	 */
	protected function getSportString() {
		return ($this->sportid == -1 ? __('All') : SportFactory::name($this->sportid));
	}

	/**
	 * Display an empty th and ths for chosen years/months
	 * @param bool $prependEmptyTag
	 * @param string $width
	 */
	protected function displayTableHeadForTimeRange($prependEmptyTag = true, $width = '8%') {
		if ($prependEmptyTag) {
			echo '<th></th>';
		}

		if (!empty($width)) {
			$width = ' width="'.$width.'"';
		}

		if ($this->showsAllYears()) {
			$year = date('Y');

			for ($i = START_YEAR; $i <= $year; $i++) {
				echo '<th'.$width.'>'.$i.'</th>';
			}
			echo '<th>'.__('In total').'</th>';
		} else {
			$num = $this->showsLast6Months() ? 6 : 12;
			$add = $this->showsTimeRange() ? date('m') - $num - 1 + 12 : -1;

			for ($i = 1; $i <= 12; $i++) {
				echo '<th'.$width.'>'.Time::month(($i + $add)%12 + 1, true).'</th>';
			}
		}
	}

	/**
	 * Returns the html-link to this statistic for tab-navigation
	 * @return string
	 */
	public function getLink() {
		return '<a rel="statistics" href="'.self::$DISPLAY_URL.'?id='.$this->id().'">'.$this->name().'</a>';
	}

	/**
	 * Returns the html-link for inner-html-navigation
	 * @param string $name displayed link-name
	 * @param int $sport id of sport, default $this->sportid
	 * @param int $year year, default $this->year
	 * @param string $dat optional dat-parameter
	 * @return string
	 */
	protected function getInnerLink($name, $sport = 0, $year = 0, $dat = '') {
		if ($sport == 0) {
			$sport = $this->sportid;
		}

		if ($year == 0) {
			$year = $this->year;
		}

		return Ajax::link($name, 'statistics-inner', self::$DISPLAY_URL.'?id='.$this->id().'&sport='.$sport.'&jahr='.$year.'&dat='.$dat);
	}

	/**
	 * Are various statistics installed?
	 * @return bool
	 */
	public static function hasVariousStats() {
		$Factory = new PluginFactory();
		$array = $Factory->variousPlugins();

		return (!empty($array));
	}

	/**
	 * Get the link for first various statistic
	 * @return string
	 */
	public static function getLinkForVariousStats() {
		$Factory = new PluginFactory();
		$array = $Factory->variousPlugins();

		return $Factory->newInstance($array[0])->getLink();
	}
}
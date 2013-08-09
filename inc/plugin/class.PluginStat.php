<?php
/**
 * This file contains class::PluginStat
 * @package Runalyze\Plugin
 */
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
	 * Array of links for toolbar-navigation
	 * @var array
	 */
	protected $Links = array();

	/**
	 * Boolean flag: header was shown
	 * @var boolean
	 */
	private $headerWasShown = false;

	/**
	 * Method for initializing default config-vars (implemented in each plugin)
	 */
	protected function getDefaultConfigVars() { return array(); }

	/**
	 * Constructor (needs ID)
	 * @param int $id
	 */
	public function __construct($id) {
		if ($id == parent::$INSTALLER_ID) {
			$this->id = $id;
			return;
		}

		if (!is_numeric($id) || $id <= 0) {
			Error::getInstance()->addError('An object of class::Plugin must have an ID: <$id='.$id.'>');
			return false;
		}

		$this->id = $id;
		$this->type = parent::$STAT;

		$this->initVars();
		$this->initPlugin();
	}

	/**
	 * Set flag for sports-navigation
	 * @param bool $flag
	 */
	protected function setSportsNavigation($flag = true) {
		$this->ShowSportsNavigation = $flag;
	}

	/**
	 * Set flag for years-navigation
	 * @param bool $flag
	 * @param bool $compareFlag [optional]
	 */
	protected function setYearsNavigation($flag = true, $compareFlag = true) {
		$this->ShowYearsNavigation = $flag;
		$this->ShowCompareYearsLink = $compareFlag;
	}

	/**
	 * Set array of links for toolbar-navigation
	 * @param array $Links
	 */
	protected function setToolbarNavigationLinks($Links) {
		$this->Links = $Links;
	}

	/**
	 * Includes the plugin-file for displaying the statistics
	 */
	public function display() {
		$this->prepareForDisplay();

		$this->displayNavigation();

		$this->displayContent();

		if ($this->headerWasShown)
			echo '</div>';
	}

	/**
	 * Display header
	 * @param string $name
	 */
	protected function displayHeader($name = '') {
		if ($name == '')
			$name = $this->name;

		echo '<h1 class="show-on-hover-parent">
			'.$name.'
				<span class="show-on-hover">
				'.$this->getConfigLink().'
				</span>
			</h1>'.NL;

		$this->headerWasShown = true;
		echo '<div class="stat-content-container">';
	}

	/**
	 * Display navigation
	 */
	private function displayNavigation() {
		if ($this->ShowSportsNavigation)
			$this->Links[] = array('tag' => '<a href="#">Sportart w&auml;hlen</a>', 'subs' => $this->getSportLinksAsArray());
		if ($this->ShowYearsNavigation)
			$this->Links[] = array('tag' => '<a href="#">Jahr w&auml;hlen</a>', 'subs' => $this->getYearLinksAsArray($this->ShowCompareYearsLink));

		if ($this->isVariousStat())
			$this->Links = array_merge($this->Links, $this->getLinksForVariousStatistics());

		if (!empty($this->Links))
			echo Ajax::toolbarNavigation($this->Links, 'right');
	}

	/**
	 * Get links to all various statistics
	 * @return array
	 */
	private function getLinksForVariousStatistics() {
		$Links = array();

		$others = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC');
		foreach ($others as $other)
			$Links[] = self::getInnerLinkFor($other['id'], $other['name']);

		return array( array('tag' => '<a href="#">Statistik w&auml;hlen</a>', 'subs' => $Links) );
	}

	/**
	 * Get links for all sports
	 * @return array
	 */
	private function getSportLinksAsArray() {
		$Links = '';

		$Sports = Mysql::getInstance()->fetchAsArray('SELECT `name`, `id` FROM `'.PREFIX.'sport` ORDER BY `id` ASC');
		foreach ($Sports as $Sport)
			$Links[] = $this->getInnerLink($Sport['name'], $Sport['id'], $this->year);

		return $Links;
	}

	/**
	 * Get links for all years
	 * @param bool $CompareYears If set, adds a link with year=-1
	 */
	private function getYearLinksAsArray($CompareYears = true) {
		$Links = '';

		if ($CompareYears)
			$Links[] = $this->getInnerLink('Jahresvergleich', $this->sportid, -1);

		for ($x = date("Y"); $x >= START_YEAR; $x--)
			$Links[] = $this->getInnerLink($x, $this->sportid, $x);

		return $Links;
	}
		
	/**
	 * Get the year as string or 'Jahresvergleich' for year=-1
	 * @return string
	 */
	protected function getYearString() {
		return ($this->year != -1 ? $this->year : 'Jahresvergleich');
	}

	/**
	 * Returns the html-link to this statistic for tab-navigation
	 * @return string
	 */
	public function getLink() {
		if ($this->isVariousStat())
			return '<a rel="statistiken" href="'.self::$DISPLAY_URL.'?id='.$this->id.'" alt="Kleinere Statistiken">Sonstiges</a>';
		return '<a rel="statistiken" href="'.self::$DISPLAY_URL.'?id='.$this->id.'" alt="'.$this->description.'">'.$this->name.'</a>';
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
		if ($sport == 0)
			$sport = $this->sportid;
		if ($year == 0)
			$year = $this->year;

		return Ajax::link($name, 'tab_content', self::$DISPLAY_URL.'?id='.$this->id.'&sport='.$sport.'&jahr='.$year.'&dat='.$dat);
	}

	/**
	 * Returns the html-link for inner-html-navigation for a plugin
	 * @param int $id
	 * @param string $name [optional]
	 */
	static public function getInnerLinkFor($id, $name = '') {
		if ($name == '') {
			$dat = Mysql::getInstance()->fetchSingle('SELECT `name` FROM `'.PREFIX.'plugin` WHERE `id`='.$id);
			$name = $dat['name'];
		}

		return Ajax::link($name, 'tab_content', self::$DISPLAY_URL.'?id='.$id);
	}

	/**
	 * Is this a various statistic?
	 * @return bool
	 */
	public function isVariousStat() {
		return ($this->active == self::$ACTIVE_VARIOUS);
	}

	/**
	 * Are various statistics installed?
	 * @return bool
	 */
	static public function hasVariousStats() {
		$array = Plugin::getKeysAsArray(self::$STAT, self::$ACTIVE_VARIOUS);

		return (!empty($array));
	}

	/**
	 * Get the link for first various statistic
	 * @return string
	 */
	static public function getLinkForVariousStats() {
		$array = Plugin::getKeysAsArray(self::$STAT, self::$ACTIVE_VARIOUS);

		return Plugin::getInstanceFor($array[0])->getLink();
	}
}
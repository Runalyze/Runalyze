<?php
/**
 * This file contains the abstract class to handle every statistic-plugin.
 */
/**
 * Class: PluginStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 */

abstract class PluginStat extends Plugin {
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
	 * Includes the plugin-file for displaying the statistics
	 */
	public function display() {
		$this->displayConfigLinkForHeader();

		if ($this->isVariousStat())
			$this->displayLinksForVariousStatistics();

		$this->displayContent();
	}

	/**
	 * Display links to all various statistics
	 */
	protected function displayLinksForVariousStatistics() {
		echo(NL.'<small class="right margin-5">'.NL);
		$others = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC');
		foreach ($others as $i => $other) {
			if ($i != 0)
				echo(' | ');
			echo self::getInnerLinkFor($other['id'], $other['name']);
		}
		echo(NL.'</small>'.NL);
	}

	/**
	 * Display header
	 * @param string $name
	 */
	protected function displayHeader($name = '') {
		if ($name == '')
			$name = $this->name;

		echo '<h1>'.$name.'</h1>'.NL;
	}

	/**
	 * Display config link
	 */
	protected function displayConfigLinkForHeader() {
		echo '<span class="right margin-5">'.$this->getConfigLink().'</span>'.NL;
	}

	
	/**
	 * Print inner links to every year
	 * @param bool $CompareYears If set, adds a link with year=-1
	 */
	protected function displayYearNavigation($CompareYears = true) {
		echo '<small class="right">';

		for ($x = START_YEAR; $x <= date("Y"); $x++)
			echo $this->getInnerLink($x, $this->sportid, $x).' | ';

		if ($CompareYears)
			echo $this->getInnerLink('Jahresvergleich', $this->sportid, -1);

		echo '</small>';
	}

	
	/**
	 * Print inner links to every sport
	 */
	protected function displaySportsNavigation() {
		echo '<small class="left">';
		
		$sports = Mysql::getInstance()->fetchAsArray('SELECT `name`, `id` FROM `'.PREFIX.'sport` ORDER BY `id` ASC');
		foreach ($sports as $i => $sportlink) {
			if ($i != 0)
				echo(' |'.NL);
			echo $this->getInnerLink($sportlink['name'], $sportlink['id'], $this->year);
		}

		echo '</small>';
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
			$dat = Mysql::getInstance()->fetchSingle('SELECT `name`, `key` FROM `'.PREFIX.'plugin` WHERE `id`='.$id);
			$name = $dat['name'];
			$key  = $dat['key'];
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
	public static function hasVariousStats() {
		$array = Plugin::getKeysAsArray(self::$STAT, self::$ACTIVE_VARIOUS);

		return (!empty($array));
	}

	/**
	 * Get the link for first various statistic
	 * @return string
	 */
	public static function getLinkForVariousStats() {
		$array = Plugin::getKeysAsArray(self::$STAT, self::$ACTIVE_VARIOUS);

		return Plugin::getInstanceFor($array[0])->getLink();
	}
}
?>
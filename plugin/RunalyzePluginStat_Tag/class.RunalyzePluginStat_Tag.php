<?php
/**
 * This file contains the class of the RunalyzePluginStat "Tag".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Tag';

/**
 * Class: RunalyzePluginStat_Tag
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Tag extends PluginStat {
	/** @var int */
	protected $TagId;

	/** @var array array(id => 'tag') */
	protected $AllTags = array();

	/** @var array array(year => array(month => num)) */
	protected $TagData = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Tag analysis');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('How often have you tagged your activities with tag x?');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$this->AllTags = array();
		$AllTags = DB::getInstance()->query('SELECT `id`, `tag` FROM `'.PREFIX.'tag` WHERE `accountid`="'.SessionAccountHandler::getId().'" ORDER BY `tag` ASC')->fetchAll();

		foreach ($AllTags as $data) {
			$this->AllTags[$data['id']] = $data['tag'];
		}

		$Tags = new PluginConfigurationValueSelect('tag', __('Tag to analyze'));
		$Tags->setOptions($this->AllTags);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Tags);

		if (isset($_GET['dat']) && isset($this->AllTags[$_GET['dat']])) {
			$Configuration->object('tag')->setValue($_GET['dat']);
			$Configuration->update('tag');
			Cache::delete(PluginConfiguration::CACHE_KEY);
		}

		$this->setConfiguration($Configuration);
		$this->TagId = (int)$this->Configuration()->value('tag');
	}

	/**
	 * Default year
	 * @return int year, can be -1 for no year/comparison of all years
	 */
	protected function defaultYear() {
		return -1;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->setSportsNavigation(true, true);
		$this->setToolbarNavigationLinks($this->getToolbarNavigationLinks());
		$this->initData();

		$this->setHeaderWithSportAndYear();
	}

	/**
	 * @return array
	 */
	private function getToolbarNavigationLinks() {
		if (empty($this->AllTags)) {
			return '';
		}

		$LinkList = array();
		$LinkList[] = '<li class="with-submenu"><span class="link">'.$this->AllTags[$this->TagId].'</span><ul class="submenu">';

		foreach ($this->AllTags as $id => $name) {
			$active = ($id == $this->TagId);
		    $LinkList[] = '<li'.($active ? ' class="active"' : '').'>'.$this->getInnerLink($name, false, false, $id).'</li>';
		}

		$LinkList[] = '</ul></li>';

		return $LinkList;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		if (!isset($this->AllTags[$this->TagId])) {
			echo '<p class="warning"><em>'.__('Please choose a tag.').'</em></p>';
		} else {
			$this->displayData();
		}
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayData() {
		echo '<p><strong>'.__('Tag').': '.$this->AllTags[$this->TagId].'</p>';
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTr(8, 1).'</thead>';
		echo '<tbody>';

		if (empty($this->TagData)) {
			echo '<tr><td colspan="13" class="c"><em>'.__('No activities found.').'</em></td></tr>';
		}

		foreach ($this->TagData as $y => $Data) {
			echo '<tr><td class="b l">'.$y.'</td>';

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m] > 0) {
					echo '<td>'.$Data[$m].'x</td>';
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	
	
	/**
	 * Initialize $this->TagData
	 */
	private function initData() {
		if ($this->TagId > 0) {
			$Statement = DB::getInstance()->query(
				'SELECT
					SUM(1) as `num`,
					YEAR(FROM_UNIXTIME(`'.PREFIX.'training`.`time`)) as `year`,
					MONTH(FROM_UNIXTIME(`'.PREFIX.'training`.`time`)) as `month`
				FROM `'.PREFIX.'activity_tag` as `at`
				LEFT JOIN `'.PREFIX.'training` ON `'.PREFIX.'training`.`id` = `at`.`activityid`
				WHERE
					`tagid` = '.$this->TagId.' AND
					`'.PREFIX.'training`.`accountid`='.SessionAccountHandler::getId().'
					'.$this->getSportAndYearDependenceForQuery(true).'
				GROUP BY `year` DESC, `month` ASC
				'
			);

			while ($data = $Statement->fetch()) {
					$this->TagData[$data['year']][$data['month']] = $data['num'];
			}
	    }
	}
}
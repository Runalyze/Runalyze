<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wetter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Wetter';

use Runalyze\Activity\Temperature;
use Runalyze\Data\Weather\WindSpeed;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Data\Weather\Pressure;

/**
 * Class: RunalyzePluginStat_Wetter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Wetter extends PluginStat {
	/** @var int */
	protected $EquipmentTypeId;

	/** @var array array(id => name) */
	protected $AllTypes = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Weather');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Statistics about weather conditions and temperatures.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('There is no bad weather, there is only bad clothing.') );
		echo HTML::p( __('Are you a wimp or a tough runner? '. 
						'Have a look at these statistics about the weather conditions.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$this->AllTypes = array(0 => __('none'));
		$AllTypes = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'equipment_type` WHERE `accountid`="'.SessionAccountHandler::getId().'" ORDER BY `name` ASC')->fetchAll();

		foreach ($AllTypes as $data) {
			$this->AllTypes[$data['id']] = $data['name'];
		}

		$Types = new PluginConfigurationValueSelect('equipment_type', __('Equipment type to display'));
		$Types->setOptions($this->AllTypes);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Types);

		if (isset($_GET['dat']) && isset($this->AllTypes[$_GET['dat']])) {
			$Configuration->object('equipment_type')->setValue($_GET['dat']);
			$Configuration->update('equipment_type');
			Cache::delete(PluginConfiguration::CACHE_KEY);
		}

		$this->setConfiguration($Configuration);
		$this->EquipmentTypeId = (int)$this->Configuration()->value('equipment_type');
	}

	/**
	 * Get own links for toolbar navigation
	 * @return array
	 */
	protected function getToolbarNavigationLinks() {
		$LinkList = array();
		$LinkList[] = '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php">'.Ajax::tooltip(Icon::$LINE_CHART, __('Show temperature plots')).'</a>').'</li>';
		$LinkList[] = '<li class="with-submenu"><span class="link">'.__('Equipment types').'</span><ul class="submenu">';

		foreach ($this->AllTypes as $id => $name) {
			$active = ($id == $this->EquipmentTypeId);
		    $LinkList[] = '<li'.($active ? ' class="active"' : '').'>'.$this->getInnerLink($name, false, false, $id).'</li>';
		}

		$LinkList[] = '</ul></li>';

		return $LinkList;
	}

	/**
	 * Timer for year or ordered months
	 * @param bool $addTableName must be used if query contains joins
	 * @return string
	 */
	protected function getTimerForOrderingInQuery($addTableName = false) {
		$time = $addTableName ? '`'.PREFIX.'training`.`time`' : '`time`';

		if ($this->showsAllYears()) {
			// Ensure month-wise data
			return 'MONTH(FROM_UNIXTIME('.$time.'))';
		}

		return parent::getTimerForOrderingInQuery($addTableName);
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->setSportsNavigation(true, true);
		$this->setYearsNavigation(true, true, true);
		$this->setToolbarNavigationLinks($this->getToolbarNavigationLinks());

		$this->setHeaderWithSportAndYear();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayExtremeTrainings();
		$this->displayMonthwiseTable();

		if ($this->knowsEquipmentType()) {
			$this->displayEquipmentTable();
		}
	}

    /**
	 * Display extreme trainings
	 */
	protected function displayExtremeTrainings() {
		$hot  = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getSportAndYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` DESC LIMIT 5')->fetchAll();
		$cold = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getSportAndYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` ASC LIMIT 5')->fetchAll();
		$windiest = DB::getInstance()->query('SELECT `wind_speed`, `id`, `time` FROM `'.PREFIX.'training` WHERE `wind_speed` IS NOT NULL '.$this->getSportAndYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `wind_speed` DESC LIMIT 5')->fetchAll();
		$maxhumidity = DB::getInstance()->query('SELECT `humidity`, `id`, `time` FROM `'.PREFIX.'training` WHERE `humidity` IS NOT NULL '.$this->getSportAndYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `humidity` DESC LIMIT 5')->fetchAll();

		foreach ($hot as $i => $h) {
			$hot[$i] = Temperature::format($h['temperature'], true).' ' .__('on').' '.Ajax::trainingLink($h['id'], date('d.m.Y', $h['time']));
		}

		foreach ($cold as $i => $c) {
			$cold[$i] = Temperature::format($c['temperature'], true).' ' .__('on').' '.Ajax::trainingLink($c['id'], date('d.m.Y', $c['time']));
		}
		
		foreach ($windiest as $i => $w) {
			$windiest[$i] = (new WindSpeed($w['wind_speed']))->string().' '.__('on').' '.Ajax::trainingLink($w['id'], date('d.m.Y', $w['time']));
		}
		
		foreach ($maxhumidity as $i => $h) {
			$maxhumidity[$i] = (new Humidity($h['humidity']))->string().' '.__('on').' '.Ajax::trainingLink($w['id'], date('d.m.Y', $h['time']));
		}
		
		echo '<p>';
		echo '<strong>'.__('Hottest activities').':</strong> ';
		echo (empty($hot) ? __('none') : implode(', ', $hot)).'<br>';
		echo (empty($hot) ? __('none') : '<strong>'.__('Coldest activities')).':</strong> ';
		echo implode(', ', $cold).'<br>';
		echo '<strong>'.__('Most windy activities').':</strong> ';
		echo (empty($windiest) ? __('none') : implode(', ', $windiest)).'<br>';
		echo '<strong>'.__('Highest humidity activities').':</strong> ';
		echo (empty($maxhumidity) ? __('none') : implode(', ', $maxhumidity)).'<br>';
		echo '</p>';
	}

	/**
	 * @return bool
	 */
	protected function knowsEquipmentType() {
		return ($this->EquipmentTypeId > 0) && isset($this->AllTypes[$this->EquipmentTypeId]);
	}

	/**
	 * Display table for clothes
	 */
	protected function displayMonthwiseTable() {
		require_once __DIR__.'/MonthwiseTable.php';

		$num = $this->showsLast6Months() ? 6 : 12;
		$offset = $this->showsTimeRange() ? date('m') - $num - 1 + 12 : -1;

		$Table = new \Runalyze\Plugin\Stat\Wetter\MonthwiseTable(
			DB::getInstance(),
			SessionAccountHandler::getId(),
			$this->EquipmentTypeId
		);
		$Table->setDependency($this->getSportAndYearDependenceForQuery(true));
		$Table->setGroupBy($this->getTimerIndexForQuery(true));
		$Table->setOrderBy($this->getTimerForOrderingInQuery(true));
		$Table->setMonthOffset($offset);
		$Table->display();
	}

	/**
	 * Display table for clothes
	 */
	protected function displayEquipmentTable() {
		require_once __DIR__.'/MinMaxTableForEquipment.php';

		$Table = new \Runalyze\Plugin\Stat\Wetter\MinMaxTableForEquipment(
			DB::getInstance(),
			SessionAccountHandler::getId(),
			$this->EquipmentTypeId
		);
		$Table->setDependency($this->getSportAndYearDependenceForQuery(true));
		$Table->display();
	}
}
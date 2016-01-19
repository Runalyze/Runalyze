<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Equipment".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Equipment';

use Runalyze\Activity\Duration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Pace;
use Runalyze\Model;

/**
 * Class: RunalyzePluginPanel_Equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Equipment extends PluginPanel {
	/**
	 * Internal array with all equipment from database and statistic values
	 * @var array 
	 */
	private $Equipment = null;

	/**
	 * @var array
	 */
	protected $AllTypes = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Equipment');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Display statistics for your equipment.');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$this->AllTypes = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'equipment_type` WHERE `accountid`="'.SessionAccountHandler::getId().'" ORDER BY `name` ASC')->fetchAll();
		$Options = array();

		foreach ($this->AllTypes as $data) {
			$Options[$data['id']] = $data['name'];
		}

		$Types = new PluginConfigurationValueSelect('type', __('Equipment type to display'));
		$Types->setOptions($Options);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Types);

		if (isset($_GET['type']) && isset($Options[$_GET['type']])) {
			$Configuration->object('type')->setValue($_GET['type']);
			$Configuration->update('type');
			Cache::delete(PluginConfiguration::CACHE_KEY);
		}

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li class="with-submenu">'.Ajax::link(__('Type'), 'panel-'.$this->id(), Plugin::$DISPLAY_URL.'?id='.$this->id());
		$Links .= '<ul class="submenu">';

		foreach ($this->AllTypes as $Type) {
			$active = $Type['id'] == (int)$this->Configuration()->value('type');
			$Links .= '<li'.($active ? ' class="active"' : '').'>'.Ajax::link($Type['name'], 'panel-'.$this->id(), Plugin::$DISPLAY_URL.'?id='.$this->id().'&type='.$Type['id']).'</li>';
		}

		$Links .= '</ul>';
		$Links .= '</li>';
		$Links .= '<li>'.Ajax::window('<a href="'.ConfigTabs::$CONFIG_URL.'?key=config_tab_equipment" '.Ajax::tooltip('', __('Add/Edit equipment'), true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.equipment.table.php" '.Ajax::tooltip('', __('Show all equipment'), true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Factory = new Model\Factory(SessionAccountHandler::getId());
		$EquipmentType = $Factory->equipmentType((int)$this->Configuration()->value('type'));

		if ($EquipmentType->isEmpty()) {
			echo HTML::warning(__('Please choose an equipment type in the plugin configuration.'));
			return;
		}

		echo $this->getStyle();
		echo '<div id="equipment">';

		$inuse = true;
		$this->showListFor($EquipmentType, $inuse);

		echo '</div>';

		if (!$inuse)
			echo Ajax::toggle('<a class="right" href="#equipment" name="equipment">'.__('Show unused equipment').'</a>', 'hiddenequipment');

		echo HTML::clearBreak();
	}

	/**
	 * @param \Runalyze\Model\EquipmentType\Entity $EquipmentType
	 * @param boolean $inuse
	 */
	protected function showListFor(Model\EquipmentType\Entity $EquipmentType, &$inuse) {
		$max = 0;
		$showDistance = $EquipmentType->hasMaxDistance();
		$hasMaxDuration = $showDistance || $EquipmentType->hasMaxDuration();
		$allEquipment = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'equipment` WHERE `typeid`="'.$EquipmentType->id().'" AND `accountid`="'.SessionAccountHandler::getId().'" ORDER BY ISNULL(`date_end`) DESC, `distance` DESC')->fetchAll();

		foreach ($allEquipment as $data) {
			$Object = new Model\Equipment\Entity($data);
			$Distance = new Distance($Object->totalDistance());
			$Duration = new Duration($Object->duration());

			if ($inuse && !$Object->isInUse()) {
				echo '<div id="hiddenequipment" style="display:none;">';
				$inuse = false;
			}

			if ($max == 0) {
				$max = $Object->duration();
			}

			echo '<p style="position:relative;">
				<span class="right">'.($showDistance ? $Distance->string() : $Duration->string()).'</span>
				<strong>'.SearchLink::to('equipmentid', $Object->id(), $Object->name()).'</strong>
				'.$this->getUsageImage(
					$showDistance
						? $Object->totalDistance() / $EquipmentType->maxDistance()
						: $Object->duration() / ($hasMaxDuration ? $EquipmentType->maxDuration() : max(1, $max))
				).'
			</p>';
		}

		if (empty($allEquipment))
			echo HTML::em( __('You don\'t have any equipment') );

		if (!$inuse)
			echo '</div>';
	}

	/**
	 * Get style
	 * @return string
	 */
	protected function getStyle() {
		return '<style type="text/css">.equipment-usage { position: absolute; bottom: 0; left: 0; background-image:url(plugin/'.$this->key().'/damage.png); background-position:left center; height: 2px; max-width: 100%; }</style>';
	}

	/**
	 * Get shoe usage image
	 * @param float $percentage [0.0 .. 1.0]
	 * @return string
	 */
	protected function getUsageImage($percentage) {
		return '<span class="equipment-usage" style="width:'.round($percentage * 330).'px;"></span>';
	}

	/**
	 * Display table
	 */
	public function displayTable() {
		if (is_null($this->Equipment))
			$this->initTableData();

		echo '<table id="list-of-all-equipment" class="fullwidth zebra-style">
			<thead>
				<tr>
					<th class="{sorter: \'x\'} small">'.__('x-times').'</th>
					<th>'.__('Name').'</th>
					<th class="{sorter: \'germandate\'} small">'.__('since').'</th>
					<th class="{sorter: \'distance\'}">&Oslash; '.Runalyze\Configuration::General()->distanceUnitSystem()->distanceUnit().'</th>
					<th>&Oslash; '.__('Pace').'</th>
					<th class="{sorter: \'distance\'} small"><small>'.__('max.').'</small> '.Runalyze\Configuration::General()->distanceUnitSystem()->distanceUnit().'</th>
					<th class="small"><small>'.__('min.').'</small> '.__('Pace').'</th>
					<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>
					<th class="{sorter: \'distance\'}">'.__('Distance').'</th>
					<th>'.__('Notes').'</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($this->Equipment)) {
			foreach ($this->Equipment as $data) {
				$Object = new Model\Equipment\Entity($data);
				$in_use = $Object->isInUse() ? '' : ' unimportant';

				$Pace = new Pace($Object->duration(), $Object->distance());
				$MaxPace = new Pace($data['pace_in_s'], 1);

				echo '<tr class="'.$in_use.' r" style="position: relative">
					<td class="small">'.$data['num'].'x</td>
					<td class="b l">'.SearchLink::to('equipmentid', $Object->id(), $Object->name()).'</td>
					<td class="small">'.$this->formatData($Object->startDate()).'</td>
					<td>'.(($data['num'] != 0) ? Distance::format($Object->distance()/$data['num']) : '-').'</td>
					<td>'.(($Object->duration() > 0) ? $Pace->asMinPerKm().'/km' : '-').'</td>
					<td class="small">'.Distance::format($data['dist']).'</td>
					<td class="small">'.$MaxPace->asMinPerKm().'/km'.'</td>
					<td>'.Duration::format($Object->duration()).'</td>
					<td>'.Distance::format($Object->totalDistance()).'</td>
					<td class="small">'.$Object->notes().'</td>
				</tr>';
			}
		} else {
			echo '<tr><td colspan="9">'.__('You don\'t have any shoes').'</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor("#list-of-all-equipment", true);
	}

	/**
	 * Format date
	 * @param string $fromDatabase
	 * @return string
	 */
	protected function formatData($fromDatabase) {
		if (!$fromDatabase) {
			return '-';
		}

		return date('d.m.Y', strtotime($fromDatabase));
	}

	/**
	 * Table link
	 * @return string
	 */
	public function tableLink() {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.equipment.table.php">'.Icon::$TABLE.' '.__('Show all equipment').'</a>');
	}

	/**
	 * Initialize internal data
	 */
	private function initTableData() {
		$this->Equipment = array();
		$Statistics = array();

		// TODO: cache or optimize this query
		$AllStatistics = DB::getInstance()->query(
			'SELECT
				`eq`.`equipmentid` as `equipmentid`,
				COUNT(*) as `num`,
				MIN(`s`/`distance`) as `pace_in_s`,
				MAX(`distance`) as `dist`
			FROM `'.PREFIX.'training` AS `act`
            INNER JOIN `'.PREFIX.'activity_equipment` as `eq` ON `act`.`id` = `eq`.`activityid`
			GROUP BY `eq`.`equipmentid`'
		)->fetchAll();

		foreach ($AllStatistics as $Statistic)
			$Statistics[$Statistic['equipmentid']] = $Statistic;

		$AllEquipment = DB::getInstance()->query(
			'SELECT * FROM `'.PREFIX.'equipment`
			WHERE `typeid`="'.(int)$this->Configuration()->value('type').'" AND `accountid`="'.SessionAccountHandler::getId().'"
			ORDER BY ISNULL(`date_end`) DESC, `distance` DESC'
		)->fetchAll();

		foreach ($AllEquipment as $Equipment) {
			if (isset($Statistics[$Equipment['id']]))
				$this->Equipment[] = array_merge($Equipment, $Statistics[$Equipment['id']]);
			else
				$this->Equipment[] = array_merge($Equipment, array('num' => 0, 'pace_in_s' => 0, 'dist' => 0));
		}
	}
}

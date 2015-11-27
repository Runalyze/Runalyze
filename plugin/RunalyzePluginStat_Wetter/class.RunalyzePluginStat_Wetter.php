<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wetter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Wetter';

use Runalyze\Data\Weather;
use Runalyze\Configuration;
use Runalyze\Util\Time;
use Runalyze\Activity\Temperature;

/**
 * Class: RunalyzePluginStat_Wetter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Wetter extends PluginStat {
	private $i      = 0;
	private $jahr   = '';
	private $jstart = 0;
	private $jende  = 0;
	private $EquipmentTypes = array();
	private $Equipment = array();

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
	 * Get own links for toolbar navigation
	 * @return array
	 */
	protected function getToolbarNavigationLinks() {
		$LinkList = array();
		$LinkList[] = '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php">'.Ajax::tooltip(Icon::$LINE_CHART, __('Show temperature plots')).'</a>').'</li>';
		$LinkList[] = '<li class="with-submenu"><span class="link">' . __('Equipment types') . '</span><ul class="submenu">';

		foreach($this->EquipmentTypes as $EqType) {
		    $LinkList[] = '<li>' . $this->getInnerLink($EqType->name(), false, false, $EqType->id()) . '</li>';
		}

		$LinkList[] = '</ul></li>';

		return $LinkList;
	}

	/**
	 * Timer for year or ordered months
	 * @return string
	 */
	protected function getTimerForOrderingInQuery() {
		if ($this->showsAllYears()) {
			// Ensure month-wise data
			return 'MONTH(FROM_UNIXTIME(`time`))';
		}

		return parent::getTimerForOrderingInQuery();
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();

		$this->setYearsNavigation(true, true, true);
		$this->setToolbarNavigationLinks($this->getToolbarNavigationLinks());

		$this->setHeader($this->getHeader());
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayExtremeTrainings();
		$this->displayMonthTable();
	}

	/**
	 * Display month-table
	 */
	private function displayMonthTable() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>';
		$this->displayTableHeadForTimeRange();
		echo '</thead>';
		echo '<tbody>';

		$this->displayMonthTableTemp();
		$this->displayMonthTableWeather();
                $this->displayMonthTableEquipment();
		$this->displayEquipmentTable();

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display an empty th and ths for chosen years/months
	 * @param bool $prependEmptyTag
	 * @param string $width
	 */
	protected function displayTableHeadForTimeRange($prependEmptyTag = true, $width = '8%') {
		echo '<th></th>';

		$width = ' width="'.$width.'"';
		$num = $this->showsLast6Months() ? 6 : 12;
		$add = $this->showsTimeRange() ? date('m') - $num - 1 + 12 : -1;

		for ($i = 1; $i <= 12; $i++) {
			echo '<th'.$width.'>'.Time::month(($i + $add)%12 + 1, true).'</th>';
		}
	}

	/**
	* Display month-table for temperature
	*/
	private function displayMonthTableTemp() {
                $Temperature = new Temperature;
		echo '<tr class="top-spacer"><td>'.$Temperature->unit().'</td>';

		$temps = DB::getInstance()->query('
			SELECT
				AVG(`temperature`) as `temp`,
				'.$this->getTimerIndexForQuery().' as `m`
			FROM `'.PREFIX.'training` WHERE
				`temperature` IS NOT NULL
				'.$this->getSportAndYearDependenceForQuery().'
			GROUP BY '.$this->getTimerIndexForQuery().'
			ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
			LIMIT 12')->fetchAll();

		$i = 1;

		if (!empty($temps)) {
			foreach ($temps as $temp) {
				for (; $i < $temp['m']; $i++)
					echo HTML::emptyTD();
				$i++;
		
				echo '<td>'. $Temperature->format(round($temp['temp']), true, false) .'</td>';
			}

			for (; $i <= 12; $i++)
				echo HTML::emptyTD();
		} else {
			echo HTML::emptyTD(12);
		}

		echo '</tr>';
	}

	/**
	 * Display month-table for weather
	 */
	private function displayMonthTableWeather() {
		$Condition = new Weather\Condition(0);
		$Statement = DB::getInstance()->prepare(
			'SELECT
				SUM(1) as `num`,
				'.$this->getTimerIndexForQuery().' as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`=?
				AND `weatherid`=?
				'.$this->getYearDependenceForQuery().'
			GROUP BY '.$this->getTimerIndexForQuery().'
			ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
			LIMIT 12'
		);

		foreach (Weather\Condition::completeList() as $id) {
			$Condition->set($id);
			$Statement->execute(array(Configuration::General()->mainSport(), $id));
			$data = $Statement->fetchAll();
			$i = 1;

			echo '<tr><td>'.$Condition->icon()->code().'</td>';

			if (!empty($data)) {
				foreach ($data as $dat) {
					for (; $i < $dat['m']; $i++) {
						echo HTML::emptyTD();
					}

					$i++;
			
					echo ($dat['num'] != 0)
						? '<td>'.$dat['num'].'x</td>'
						: HTML::emptyTD();
				}
			
				for (; $i <= 12; $i++) {
					echo HTML::emptyTD();
				}
			} else {
				echo HTML::emptyTD(12);
			}
		}
	
		echo '</tr>';
	}

    /**
	 * Display extreme trainings
	 */
	private function displayExtremeTrainings() {
		$hot  = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` DESC LIMIT 5')->fetchAll();
		$cold = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` ASC LIMIT 5')->fetchAll();

		foreach ($hot as $i => $h)
			$hot[$i] = Temperature::format($h['temperature'], true).' ' .__('on').' '.Ajax::trainingLink($h['id'], date('d.m.Y', $h['time']));
		foreach ($cold as $i => $c)
			$cold[$i] = Temperature::format($c['temperature'], true).' ' .__('on').' '.Ajax::trainingLink($c['id'], date('d.m.Y', $c['time']));

		echo '<p>';
		echo '<strong>'.__('Hottest activities').':</strong> ';
		echo implode(', ', $hot).'<br>';
		echo '<strong>'.__('Coldest activities').':</strong> ';
		echo implode(', ', $cold).'<br>';
		echo '</p>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sportid = Configuration::General()->mainSport();
		
		$Factory = new \Runalyze\Model\Factory(SessionAccountHandler::getId());
		$this->EquipmentTypes = $Factory->allEquipmentTypes();
		
		if (isset($_GET['dat'])) {
		    $this->Equipment = $Factory->equipmentForEquipmentType($_GET['dat']);
		} elseif (!empty($this->EquipmentTypes)) {
			$this->Equipment = $Factory->equipmentForEquipmentType($this->EquipmentTypes[0]->id());
		}

		if ($this->showsAllYears()) {
			$this->i      = 0;
			$this->jahr   = __('In total');
			$this->jstart = mktime(0,0,0,1,1,START_YEAR);
			$this->jende  = time();
		} else {
			$this->i      = $this->year;
			$this->jahr   = $this->year;
			$this->jstart = mktime(0,0,0,1,1,$this->i);
			$this->jende  = mktime(23,59,59,1,0,$this->i+1);
		}

        }

	/**
	 * Get header depending on config
	 */
	private function getHeader() {
		return __('Weather').': '.$this->getYearString();
	}
	
	/**
	* Display month-table for equipment
	*/
	private function displayMonthTableEquipment() {

		$nums = DB::getInstance()->query('SELECT
				SUM(1) as `num`,
				'.$this->getTimerIndexForQuery().' as `m`
			FROM `'.PREFIX.'training` WHERE 1 
				'.$this->getSportAndYearDependenceForQuery().'
			GROUP BY '.$this->getTimerIndexForQuery().'
			ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
			LIMIT 12')->fetchAll();

		if (!empty($nums)) {
			foreach ($nums as $dat)
				$num[$dat['m']] = $dat['num'];
		}

		if (!empty($this->Equipment)) {
                    
                    $EquipmentTable = array();
                    
                    $Equipmentquery = DB::getInstance()->query(
                    'SELECT
                            SUM(IF(eq.activityid = id, 1,0)) as `num`, eq.equipmentid,
                            '.$this->getTimerIndexForQuery().' as `m`
                    FROM `'.PREFIX.'activity_equipment` eq
                    LEFT JOIN runalyze_training ON id=eq.activityid 
                    WHERE accountid = '.SessionAccountHandler::getId().' ' .$this->getYearDependenceForQuery(). '
                        GROUP BY eq.equipmentid, '.$this->getTimerIndexForQuery().'  
                        HAVING `num`!=0
                    ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
                    LIMIT 12'
                    )->fetchAll();
                    
                    if(!empty($Equipmentquery)) {
                        foreach ($Equipmentquery as $eqp)
                                $EquipmentTable[$eqp['equipmentid']][$eqp['m']] = $eqp['num'];
                    }

                    foreach ($this->Equipment as $e => $equipment) {
                            echo '<tr class="'.($e == 0 ? 'top-spacer' : '').'"><td>'.$equipment->name().'</td>';
                            if(!isset($EquipmentTable[$equipment->id()])) {
                                echo '<td colspan="12"></td>';
                            } else {
                                $i = 1;
                                for (; $i <= 12; $i++) {
                                    if(!isset($EquipmentTable[$equipment->id()][$i])) {
                                        echo HTML::emptyTD();
                                    } else {
                                            echo '
                                                    <td class="r"><span title="'.$EquipmentTable[$equipment->id()][$i].'x">
                                                                    '.round($EquipmentTable[$equipment->id()][$i]*100/$num[$i]).' &#37;
                                                    </span></td>';
                                    }
                                }
                            }
                            echo '</tr>';
			}
		}
	}
	
	private function getEquipmentTypeNavigation() {
		$LinkList = '<li class="with-submenu"><span class="link">' . __('Equipment types') . '</span><ul class="submenu">';
		foreach($this->EquipmentTypes as $EqType) {
		    $LinkList .= '<li>' . $this->getInnerLink($EqType->name(), false, false, $EqType->id()) . '</li>';
		}
		$LinkList .= '</ul></li>';
		return $LinkList;
	}

	/**
	 * Display table for clothes
	 */
	private function displayEquipmentTable() {
		echo '<table class="fullwidth zebra-style">
			<thead><tr>
				<th></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
			</tr></thead>
			<tbody><tr class="r">';

		$EquipmentQuery = DB::getInstance()->query(
			'SELECT
				AVG(`temperature`) as `avg`,
				MAX(`temperature`) as `max`,
				MIN(`temperature`) as `min`,
				`eq`.`equipmentid`
			FROM `'.PREFIX.'training`
			LEFT JOIN `runalyze_activity_equipment` AS `eq` ON `id` = `eq`.`activityid`
			WHERE accountid = '.SessionAccountHandler::getId().' AND `eq`.`activityid` IS NOT NULL AND `temperature` IS NOT NULL
				'.$this->getYearDependenceForQuery().'
			GROUP BY `eq`.`equipmentid`'
		)->fetchAll();

		$EquipmentsTemperatures = array();

		foreach($EquipmentQuery as $Equipment) {
		   $EquipmentsTemperatures[$Equipment['equipmentid']] = $Equipment;
		}

		if (!empty($this->Equipment)) {
			foreach ($this->Equipment as $i => $equipment) {
				echo ($i%3 == 0) ? '</tr><tr class="r">' : '<td>&nbsp;&nbsp;</td>';
				echo '<td class="l">'.$equipment->name().'</td>';
				
				if (isset($EquipmentsTemperatures[$equipment->id()])) {
				    $Temperatures = $EquipmentsTemperatures[$equipment->id()];
					echo '<td>'.(Temperature::format($Temperatures['min'], true)).' '.__('to').' '.(Temperature::format($Temperatures['max'], true)).'</td>';
					echo '<td>'.(Temperature::format(round($Temperatures['avg']), true)).'</td>';
				} else {
					echo '<td colspan="2" class="c"><em>-</em></td>';
				}
			}
		}

		for (; $i%3 != 2; $i++)
			echo HTML::emptyTD(3);

		echo '</tr></tbody>';
		echo '</table>';
	}
	
}
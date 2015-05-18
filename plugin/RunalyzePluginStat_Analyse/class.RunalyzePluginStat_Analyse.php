<?php
/**
 * This file contains class::RunalyzePluginStat_Analyse
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;

$PLUGINKEY = 'RunalyzePluginStat_Analyse';
/**
 * "Analyse" plugin
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Analyse extends PluginStat {
	private $where_time = '';
	private $group_time = '';
	private $timer = '';
	private $timer_start = 0;
	private $timer_end = 1;

	/**
	 * Data
	 * @var array
	 */
	private $AnalysisData = array();

	/**
	 * Empty data array
	 * @var array
	 */
	private $emptyData = array(
			'all_sum_km'	=> 0,
			'all_sum_s'		=> 0,
			'timer_sum_km'	=> array(),
			'timer_sum_s'	=> array(),
			'id_sum_km'		=> array(),
			'id_sum_s'		=> array());

	/**
	 * Sport
	 * @var Sport
	 */
	private $Sport = null;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Analysis');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Analyze your training (only running) by means of pace, heart rate and different types.');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueBool('use_type', __('Analyze types'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('use_pace', __('Analyze pace zones'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('use_pulse', __('Analyze heart rate zones'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueInt('lowest_pulsegroup', __('Lowest heart rate zone'), __('[%HFmax]'), 65) );
		$Configuration->addValue( new PluginConfigurationValueInt('pulsegroup_step', __('Heart rate zone: Increment'), __('[%HFmax]'), 5) );
		$Configuration->addValue( new PluginConfigurationValueInt('lowest_pacegroup', __('Lowest pace zone'), __('[s/km]'), 450) );
		$Configuration->addValue( new PluginConfigurationValueInt('highest_pacegroup', __('Highest pace zone'), __('[s/km]'), 240) );
		$Configuration->addValue( new PluginConfigurationValueInt('pacegroup_step', __('Pace zone: Increment'), __('[s/km]'), 15) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->Sport = new Sport($this->sportid);

		$this->initTimer();
		$this->initData();

		$this->setAnalysisNavigation();
		$this->setSportsNavigation();
		$this->setYearsNavigation();

		$this->setHeader($this->Sport->name().' '.$this->getYearString());
	}

	private function setAnalysisNavigation() {
		$LinkList  = '<li class="with-submenu"><span class="link">'.__('Choose evaluation').'</span><ul class="submenu">';
		$LinkList .= '<li'.('' == $this->dat ? ' class="active"' : '').'>'.$this->getInnerLink( __('in percent'), $this->sportid, $this->year, '').'</li>';
		$LinkList .= '<li'.('km' == $this->dat ? ' class="active"' : '').'>'.$this->getInnerLink( __('by distance'), $this->sportid, $this->year, 'km').'</li>';
		$LinkList .= '<li'.('s' == $this->dat ? ' class="active"' : '').'>'.$this->getInnerLink( __('by time'), $this->sportid, $this->year, 's').'</li>';
		$LinkList .= '</ul></li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayStyle();
		$this->displayAnalysis();

		echo HTML::info('* '.__('The values consider only the average data fields of your activities.'));
	}

	/**
	 * Initialize the internal timer
	 */
	private function initTimer() {
		if ($this->year != -1) {
			$this->where_time = 'AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1';
			$this->group_time = 'MONTH(FROM_UNIXTIME(`time`))';
			$this->timer = 'MONTH';
			$this->timer_start = 1;
			$this->timer_end = 12;
		} else {
			$this->where_time = '';
			$this->group_time = 'YEAR(FROM_UNIXTIME(`time`))';
			$this->timer = 'YEAR';
			$this->timer_start = START_YEAR;
			$this->timer_end = YEAR;
		}

		$this->colspan = $this->timer_end - $this->timer_start + 3;
	}

	/**
	 * Initialize analysis data
	 */
	private function initData() {
		if ($this->Configuration()->value('use_type') && $this->Sport->hasTypes()) {
			$this->AnalysisData[] = $this->getTypeArray();
		}

		if ($this->Configuration()->value('use_pace')) {
			$this->AnalysisData[] = $this->getPaceArray();
		}

		if ($this->Configuration()->value('use_pulse')) {
			$this->AnalysisData[] = $this->getPulseArray();
		}
	}

	/**
	 * Display style
	 */
	private function displayStyle() {
		echo '<style type="text/css">';
		echo '.analysis-table td { position: relative; }';
		echo '.analysis-table td .analysis-bar { position: absolute; right: 3px; bottom: 2px; display: block; height: 2px; max-with: 100%; background-color: #800; }';
		echo '</style>';
	}

	/**
	 * Display the analysis
	 */
	private function displayAnalysis() {
		if (empty($this->AnalysisData)) {
			echo HTML::info( __('There is no data for this sport.') );
		}

		foreach ($this->AnalysisData as $i => $Data) {
			if (!is_array($Data))
				continue;

			$this->printTableStart($Data['name']);

			if (empty($Data['foreach'])) {
				echo '<tr class="c">'.HTML::emptyTD($this->colspan, '<em>'.__('No data available.').'</em>').'</tr>';
			} else {
				foreach ($Data['foreach'] as $i => $Each) {
					echo '<tr><td class="c b">'.$Each['name'].'</td>';

					for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
						if (isset($Data['array'][$Each['id']][$t])) {
							$num  = $Data['array'][$Each['id']][$t]['num'];
							$dist = $Data['array'][$Each['id']][$t]['distance'];
							$time = $Data['array'][$Each['id']][$t]['s'];

							if ($this->dat == 'km') {
								$percent = $Data['array']['timer_sum_km'][$t] > 0 ? round(100 * $dist / $Data['array']['timer_sum_km'][$t], 1) : 0;
							} else {
								$percent = $Data['array']['timer_sum_s'][$t] > 0 ? round(100 * $time / $Data['array']['timer_sum_s'][$t], 1) : 0;
							}

							$this->displayTDfor($num, $time, $dist, $percent);
						} else {
							echo HTML::emptyTD();
						}
					}

					if (isset($Data['array']['id_sum_s'][$Each['id']])) {
						$num  = $Data['array']['id_sum_num'][$Each['id']];
						$time = $Data['array']['id_sum_s'][$Each['id']];
						$dist = $Data['array']['id_sum_km'][$Each['id']];

						if ($this->dat == 'km') {
							$percent = $Data['array']['all_sum_km'] > 0 ? round(100 * $dist / $Data['array']['all_sum_km'], 1) : 0;
						} else {
							$percent = $Data['array']['all_sum_s'] > 0 ? round(100 * $time / $Data['array']['all_sum_s'], 1) : 0;
						}

						$this->displayTDfor($num, $time, $dist, $percent);
					} else {
						echo HTML::emptyTD();
					}

					echo '</tr>';
				}

				if ($i == count($Data['foreach']) - 1) {
					echo '<tr class="top-spacer no-zebra"><td class="c b">'.__('Total').'</td>';

					for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
						if (isset($Data['array']['timer_sum_km'][$t])) {
							if ($this->dat != 's')
								echo '<td>'.Distance::format($Data['array']['timer_sum_km'][$t], false, 0).'</td>';
							else
								echo '<td>'.Duration::format($Data['array']['timer_sum_s'][$t]).'</td>';
						} else {
							echo HTML::emptyTD();
						}
					}

					if ($this->dat != 's')
						echo '<td>'.Distance::format($Data['array']['all_sum_km'], false, 0).'</td></tr>';
					else
						echo '<td>'.Duration::format($Data['array']['all_sum_s']).'</td></tr>';
				}
			}

			$this->printTableEnd();
		}
	}

	/**
	 * Display td
	 * @param int $num
	 * @param int $time
	 * @param float $dist
	 * @param float $percent
	 */
	private function displayTDfor($num, $time, $dist, $percent) {
		$tooltip = $num.'x';
		$number  = number_format($percent, 1).' &#37;';

		if ($this->dat == 'km') {
			$number   = Distance::format($dist, false, 0);
			$tooltip .= ', '.Duration::format($time);
		} elseif ($this->dat == 's') {
			$number   = Duration::format($time);
		} else {
			$number   = number_format($percent, 1).' &#37;';
			$tooltip .= ', '.Duration::format($time);
		}

		echo '<td>'.Ajax::tooltip($number, $tooltip).$this->getBarFor($percent).'</td>';
	}

	/**
	 * Get bar
	 * @param float $percentage
	 * @return string
	 */
	private function getBarFor($percentage) {
		$width = min(95, round($percentage));
		$opacity = round($percentage/100, 2);
		return '<div class="analysis-bar" style="width:'.$width.'%;opacity:'.$opacity.';"></div>';
	}

	/**
	 * Get array for "Trainingstyp"
	 */
	private function getTypeArray() {
		$result = DB::getInstance()->query('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				`typeid`,
				`typeid` as `group`,
				`RPE`
			FROM `'.PREFIX.'training`
			LEFT OUTER JOIN `'.PREFIX.'type` ON (
				'.PREFIX.'training.typeid='.PREFIX.'type.id AND
				'.PREFIX.'type.accountid="'.SessionAccountHandler::getId().'"
			)
			WHERE '.PREFIX.'training.`sportid`="'.$this->sportid.'"
				AND '.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'" '.$this->where_time.'
			GROUP BY `typeid`, '.$this->group_time.'
			ORDER BY `RPE`, `timer` ASC
		')->fetchAll();

		$type_data = $this->emptyData;

		foreach ($result as $dat) {
			if (!isset($type_data[$dat['typeid']]))
				$type_data[$dat['typeid']] = array();
			
			$type_data[$dat['typeid']][$dat['timer']] = array(
				'num'		=> $dat['num'],
				'distance'	=> $dat['distance'],
				's'			=> $dat['s']
			);

			$this->setSumData($type_data, $dat);
		}

		$type_foreach = array();

		if (!empty($result)) {
			if (isset($type_data['id_sum_num'][0])) {
				$type_foreach[] = array(
					'name' => '<span style="font-weight:normal;">'.__('without').'</span>',
					'id' => 0
				);
			}

			$types = DB::getInstance()->query('SELECT `id`, `name`, `abbr` FROM `'.PREFIX.'type` WHERE `sportid`="'.$this->sportid.'" ORDER BY `RPE` ASC')->fetchAll();
			foreach ($types as $type) {
				$type_foreach[] = array(
					'name' => '<span title="'.$type['name'].'">'.($type['abbr'] != '' ? $type['abbr'] : $type['name']).'</span>',
					'id' => $type['id']
				);
			}
		}

		return array('name' => __('Training types'), 'array' => $type_data, 'foreach' => $type_foreach);
	}

	/**
	 * Get array for "Tempobereiche"
	 */
	private function getPaceArray() {
		$speed_min = $this->Configuration()->value('lowest_pacegroup');
		$speed_max = $this->Configuration()->value('highest_pacegroup');
		$speed_step = $this->Configuration()->value('pacegroup_step');
		$ceil_corr  = $speed_min % $speed_step;

		if ($this->sportid != Configuration::General()->runningSport()) {
			$MinMax = DB::getInstance()->query('
				SELECT
					MIN(`s`/`distance`) as `min`,
					MAX(`s`/`distance`) as `max`
				FROM `'.PREFIX.'training`
				WHERE `sportid`='.$this->sportid.' AND '.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'" '.$this->where_time.' AND `distance`>0
			')->fetch();

			if (!empty($MinMax)) {
				$speed_min  = round((float)$MinMax['max']);
				$speed_max  = round((float)$MinMax['min']);
				$speed_step = ($speed_min == $speed_max) ? 1 : round(($speed_min - $speed_max)/10);
				$ceil_corr  = ($speed_min == $speed_max) ? 1 : $speed_min % $speed_step;
			}
		}

		$result = DB::getInstance()->query('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				FLOOR( (`s`/`distance` - '.$ceil_corr.')/'.$speed_step.')*'.$speed_step.' + '.$ceil_corr.' AS `group`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.$this->sportid.' AND '.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'" '.$this->where_time.' AND `distance`>0
			GROUP BY `group`, '.$this->group_time.'
			ORDER BY `group` DESC, `timer` ASC
		')->fetchAll();

		$speed_data = $this->emptyData;
		
		foreach ($result as $dat) {
			if ($this->sportid == Configuration::General()->runningSport()) {
				if ($dat['group'] > $speed_min)
					$dat['group'] = $speed_min;
				else if ($dat['group'] < $speed_max)
					$dat['group'] = $speed_max;
			}

			$this->setGroupData($speed_data, $dat);
			$this->setSumData($speed_data, $dat);
		}
	
		$speed_foreach = array();

		if (!empty($result)) {
			$Pace = new Pace(0, 1, SportFactory::getSpeedUnitFor($this->sportid));

			for ($speed = $speed_min; $speed > ($speed_max - $speed_step); $speed -= $speed_step) {
				if ($speed <= $speed_max)
					$name = '<small>'.__('faster than').'</small>&nbsp;'.$Pace->setTime($speed + $speed_step)->valueWithAppendix();
				else if ($speed == $speed_min)
					$name = '<small>'.__('up to').'</small>&nbsp;'.$Pace->setTime($speed)->valueWithAppendix();
				else
					$name = $Pace->setTime($speed+$speed_step)->value().' <small>'.__('to').'</small>&nbsp;'.$Pace->setTime($speed)->valueWithAppendix();
				$speed_foreach[] = array( 'name' => $name, 'id' => max($speed, $speed_max));
			}
		}

		return array('name' => __('Pace Zones').'*', 'array' => $speed_data, 'foreach' => $speed_foreach);
	}

	/**
	 * Get array for "Pulsbereiche"
	 */
	private function getPulseArray() {
		$pulse_min  = max((int)$this->Configuration()->value('lowest_pulsegroup'), 0);
		$pulse_step = max((int)$this->Configuration()->value('pulsegroup_step'), 1);
		$ceil_corr  = $pulse_min % $pulse_step;

		$result = DB::getInstance()->query('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				'.(Configuration::General()->heartRateUnit()->isHRreserve() ?
					'CEIL( (100 * (`pulse_avg` - '.$ceil_corr.' - '.HF_REST.') / ('.HF_MAX.' - '.HF_REST.')) /'.$pulse_step.')*'.$pulse_step.' + '.$ceil_corr.' AS `group`'
				:
					'CEIL( (100 * (`pulse_avg` - '.$ceil_corr.') / '.HF_MAX.') /'.$pulse_step.')*'.$pulse_step.' + '.$ceil_corr.' AS `group`').'
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.$this->sportid.' AND '.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'" '.$this->where_time.' && `pulse_avg`!=0
			GROUP BY `group`, '.$this->group_time.'
			ORDER BY `group`, `timer` ASC
		')->fetchAll();

		$pulse_data = $this->emptyData;
		
		foreach ($result as $dat) {
			if ($dat['group'] < $pulse_min)
				$dat['group'] = $pulse_min;

			$this->setGroupData($pulse_data, $dat);
			$this->setSumData($pulse_data, $dat);
		}
	
		$pulse_foreach = array();

		if (!empty($result)) {
			$absoluteValues = Configuration::General()->heartRateUnit()->isBPM();

			for ($pulse = $pulse_min; $pulse < (100 + $pulse_step); $pulse += $pulse_step) {
				$from = max(($pulse == $pulse_min) ? 0 : $pulse-$pulse_step, 0);
				$to = min($pulse, 100);

				if ($absoluteValues) {
					$from = ceil(HF_MAX * $from / 100);
					$to = floor(HF_MAX * $to / 100).' bpm';
				} else {
					$to .= ' &#37;';
				}

				$pulse_foreach[] = array(
					'name' => $from.' <small>'.__('to').'</small> '.$to,
					'id' => $pulse
				);
			}
		}

		return array('name' => __('Heart Rate Zones').'*', 'array' => $pulse_data, 'foreach' => $pulse_foreach);
	}

	/**
	 * Set group data
	 * @param array $data
	 * @param array $result
	 */
	private function setGroupData(array &$data, array &$result) {
		if (!isset($data[$result['group']]))
			$data[$result['group']] = array();
		if (!isset($data[$result['group']][$result['timer']]))
			$data[$result['group']][$result['timer']] = array('num' => 0, 'distance' => 0, 's' => 0);

		$data[$result['group']][$result['timer']]['num']      += $result['num'];
		$data[$result['group']][$result['timer']]['distance'] += $result['distance'];
		$data[$result['group']][$result['timer']]['s']        += $result['s'];
	}

	/**
	 * Set sum data
	 * @param array $data
	 * @param array $result
	 */
	private function setSumData(array &$data, array &$result) {
		if (!isset($data['timer_sum_km'][$result['timer']])) {
			$data['timer_sum_km'][$result['timer']] = 0;
			$data['timer_sum_s'][$result['timer']]  = 0;
		}
		if (!isset($data['id_sum_km'][$result['group']])) {
			$data['id_sum_num'][$result['group']] = 0;
			$data['id_sum_km'][$result['group']]  = 0;
			$data['id_sum_s'][$result['group']]   = 0;
		}

		$data['all_sum_km']                     += $result['distance'];
		$data['all_sum_s']                      += $result['s'];
		$data['timer_sum_km'][$result['timer']] += $result['distance'];
		$data['timer_sum_s'][$result['timer']]  += $result['s'];
		$data['id_sum_num'][$result['group']]   += $result['num'];
		$data['id_sum_km'][$result['group']]    += $result['distance'];
		$data['id_sum_s'][$result['group']]     += $result['s'];
	}

	/**
	 * Print beginning of the table
	 * @param string $name
	 */
	private function printTableStart($name) {
		echo '<table class="fullwidth zebra-style analysis-table small r"><thead><tr class="c b"><th>'.$name.'</th>';

		$this->printTableHeader();

		echo '</tr></thead><tbody>';
	}

	/**
	 * Print header columns for a table
	 */
	private function printTableHeader() {
		for ($i = $this->timer_start; $i <= $this->timer_end; $i++)
			echo ($this->year != -1)
				? '<th width="7%">'.Time::Month($i, true).'</th>'
				: '<th>'.$i.'</th>';

		echo '<th>'.__('In total').'</th>';
	}

	/**
	 * Print the ending of a table for one analysis
	 */
	private function printTableEnd() {
		echo '</tbody></table>'.HTML::clearBreak();
	}
}

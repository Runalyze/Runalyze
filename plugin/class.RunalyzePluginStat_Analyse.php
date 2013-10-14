<?php
/**
 * This file contains class::RunalyzePluginStat_Analyse
 * @package Runalyze\Plugins\Stats
 */
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
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Analyse';
		$this->description = 'Analyse des Lauftrainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Runalyze soll die perfekte Hilfe sein, um das eigene Training professionell auszuwerten.');
		echo HTML::p('Dieses Plugin hilft dabei, die eigenen Trainingseinheiten zu analysieren.
					Es gibt an, wie oft in welchem Trainingsbereich trainiert wurde.');
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['use_type']  = array('type' => 'bool', 'var' => true, 'description' => 'Trainingstypen analysieren');
		$config['use_pace']  = array('type' => 'bool', 'var' => true, 'description' => 'Tempobereiche analysieren');
		$config['use_pulse'] = array('type' => 'bool', 'var' => true, 'description' => 'Pulsbereiche analysieren');
		$config['lowest_pulsegroup'] = array('type' => 'int', 'var' => 65, 'description' => '<span class="atLeft" rel="tooltip" title="in %HFmax">Niedrigster Pulsbereich</span>');
		$config['pulsegroup_step']   = array('type' => 'int', 'var' => 5, 'description' => '<span class="atLeft" rel="tooltip" title="in %HFmax">Pulsbereich: Schrittweite</span>');
		$config['lowest_pacegroup']  = array('type' => 'int', 'var' => 450, 'description' => '<span class="atLeft" rel="tooltip" title="in s/km">Niedrigster Tempobereich</span>');
		$config['highest_pacegroup'] = array('type' => 'int', 'var' => 240, 'description' => '<span class="atLeft" rel="tooltip" title="in s/km">H&ouml;chster Tempobereich</span>');
		$config['pacegroup_step']    = array('type' => 'int', 'var' => 15, 'description' => '<span class="atLeft" rel="tooltip" title="in s/km">Tempobereich: Schrittweite</span>');

		return $config;
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
	}

	private function setAnalysisNavigation() {
		$SubLinks = array();
		$SubLinks[] = $this->getInnerLink('in Prozent', $this->sportid, $this->year, '');

		if ($this->Sport->usesDistance())
			$SubLinks[] = $this->getInnerLink('nach Distanz', $this->sportid, $this->year, 'km');
		$SubLinks[] = $this->getInnerLink('nach Dauer', $this->sportid, $this->year, 's');

		$this->Links[] = array('tag' => '<a href="#">Auswertung w&auml;hlen</a>', 'subs' => $SubLinks);
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader($this->Sport->name().' '.$this->getYearString());

		$this->displayAnalysis();

		echo HTML::info('* Die Werte beziehen sich auf die Durchschnittswerte der Trainings.');
	}

	/**
	 * Initialize the internal timer
	 */
	private function initTimer() {
		if ($this->year != -1) {
			$this->where_time = '&& YEAR(FROM_UNIXTIME(`time`))='.$this->year;
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
		if ($this->config['use_type']['var'] && $this->Sport->hasTypes())
			$this->AnalysisData[] = $this->getTypeArray();
		if ($this->config['use_pace']['var'] && $this->Sport->usesDistance())
			$this->AnalysisData[] = $this->getPaceArray();
		if ($this->config['use_pulse']['var'] && $this->Sport->usesPulse())
			$this->AnalysisData[] = $this->getPulseArray();
	}

	/**
	 * Display the analysis
	 */
	private function displayAnalysis() {
		if (empty($this->AnalysisData))
			echo HTML::info('F&uuml;r diese Sportart gibt es keine Analysedaten zum Anzeigen.');

		foreach ($this->AnalysisData as $i => $Data) {
			if (!is_array($Data))
				continue;

			$this->printTableStart($Data['name']);

			if (empty($Data['foreach'])) {
				echo '<tr class="a1 c">'.HTML::emptyTD($this->colspan, '<em>Keine Daten vorhanden.</em>').'</tr>';
			} else {
				foreach ($Data['foreach'] as $i => $Each) {
					echo('
						<tr class="a'.($i%2+1).'">
							<td class="c b">'.$Each['name'].'</td>');

					for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
						if (isset($Data['array'][$Each['id']][$t])) {
							$num     = $Data['array'][$Each['id']][$t]['num'];
							$dist    = $Data['array'][$Each['id']][$t]['distance'];
							$time    = $Data['array'][$Each['id']][$t]['s'];
							$percent = $Data['array']['timer_sum_s'][$t] > 0 ? round(100 * $time / $Data['array']['timer_sum_s'][$t], 1) : 0;

							$this->displayTDfor($num, $time, $dist, $percent);
						} else {
							echo HTML::emptyTD();
						}
					}

					if (isset($Data['array']['id_sum_s'][$Each['id']])) {
						$num     = $Data['array']['id_sum_num'][$Each['id']];
						$time    = $Data['array']['id_sum_s'][$Each['id']];
						$dist    = $Data['array']['id_sum_km'][$Each['id']];
						$percent = $Data['array']['all_sum_s'] > 0 ? round(100 * $time / $Data['array']['all_sum_s'], 1) : 0;

						$this->displayTDfor($num, $time, $dist, $percent);
						//echo '<td title="'.Time::toString($time, true, true).'">'.number_format($percent, 1).' &#37;</td>';
					} else {
						echo HTML::emptyTD();
					}

					echo('
						</tr>');
				}

				if ($i == count($Data['foreach']) - 1) {
					echo('
						<tr class="space"><td colspan="'.$this->colspan.'" /></tr>
						<tr class="a'.(($i+1)%2+1).'">
							<td class="c b">Gesamt</td>');

					for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
						if (isset($Data['array']['timer_sum_km'][$t])) {
							if ($this->Sport->usesDistance() && $this->dat != 's')
								echo '<td>'.Running::Km($Data['array']['timer_sum_km'][$t], 0).'</td>'.NL;
							else
								echo '<td>'.Time::toString($Data['array']['timer_sum_s'][$t], true, true).'</td>'.NL;
						} else {
							echo HTML::emptyTD();
						}
					}

					if ($this->Sport->usesDistance() && $this->dat != 's')
						echo '<td>'.Running::Km($Data['array']['all_sum_km'], 0).'</td></tr>'.NL;
					else
						echo '<td>'.Time::toString($Data['array']['all_sum_s'], true, true).'</td></tr>'.NL;
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
		$tooltip = $num.'-mal';
		$number  = number_format($percent, 1).' &#37;';

		if ($this->dat == 'km') {
			$number   = Running::Km($dist, 0);
			$tooltip .= ', '.Time::toString($time, true, true);
		} elseif ($this->dat == 's') {
			$number   = Time::toString($time, true, true);
		} else {
			$number   = number_format($percent, 1).' &#37;';
			$tooltip .= ', '.Time::toString($time, true, true);
		}

		echo '<td>'.Ajax::tooltip($number, $tooltip).'</td>';
	}

	/**
	 * Get array for "Trainingstyp"
	 */
	private function getTypeArray() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				`typeid`,
				`typeid` as `group`,
				`RPE`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'type` ON ('.PREFIX.'training.typeid='.PREFIX.'type.id)
			WHERE '.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'"
				AND '.PREFIX.'type.accountid="'.SessionAccountHandler::getId().'"
				AND '.PREFIX.'training.`sportid`="'.$this->sportid.'" '.$this->where_time.'
			GROUP BY `typeid`, '.$this->group_time.'
			ORDER BY `RPE`, `timer` ASC');

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
			$types = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name`, `abbr` FROM `'.PREFIX.'type` WHERE `sportid`="'.$this->sportid.'" ORDER BY `RPE` ASC');
			foreach ($types as $type) {
				$type_foreach[] = array(
					'name' => '<span title="'.$type['name'].'">'.($type['abbr'] != '' ? $type['abbr'] : $type['name']).'</span>',
					'id' => $type['id']);
			}
		}

		return array('name' => 'Trainingstypen', 'array' => $type_data, 'foreach' => $type_foreach);
	}

	/**
	 * Get array for "Tempobereiche"
	 */
	private function getPaceArray() {
		$speed_min = $this->config['lowest_pacegroup']['var'];
		$speed_max = $this->config['highest_pacegroup']['var'];
		$speed_step = $this->config['pacegroup_step']['var'];
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				FLOOR( (`s`/`distance`)/'.$speed_step.')*'.$speed_step.' AS `group`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.$this->sportid.' '.$this->where_time.' AND `distance`>0
			GROUP BY `group`, '.$this->group_time.'
			ORDER BY `group` DESC, `timer` ASC');
		
		$speed_data = $this->emptyData;
		
		foreach ($result as $dat) {
			if ($this->sportid == CONF_RUNNINGSPORT) {
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
			if ($this->sportid != CONF_RUNNINGSPORT) {
				$speed_min = $result[0]['group'];
				$speed_max = $result[count($result)-1]['group'];
			}

			for ($speed = $speed_min; $speed >= $speed_max; $speed -= $speed_step) {
				$name = ($speed == $speed_max)
					? '<small>schneller&nbsp;als</small>&nbsp;'.SportFactory::getSpeedWithAppendix(1, $speed + $speed_step, $this->sportid)
					: '<small>bis</small>&nbsp;'.SportFactory::getSpeedWithAppendix(1, $speed, $this->sportid);
				$speed_foreach[] = array( 'name' => $name, 'id' => $speed);
			}
		}

		return array('name' => 'Tempobereiche*', 'array' => $speed_data, 'foreach' => $speed_foreach);
	}

	/**
	 * Get array for "Pulsbereiche"
	 */
	private function getPulseArray() {
		$pulse_min = $this->config['lowest_pulsegroup']['var'];
		$pulse_step = $this->config['pulsegroup_step']['var'];
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distance`) AS `distance`,
				SUM(`s`) AS `s`,
				CEIL( (100 * `pulse_avg` / '.HF_MAX.') /'.$pulse_step.')*'.$pulse_step.' AS `group`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.$this->sportid.' '.$this->where_time.' && `pulse_avg`!=0
			GROUP BY `group`, '.$this->group_time.'
			ORDER BY `group`, `timer` ASC');
		
		$pulse_data = $this->emptyData;
		
		foreach ($result as $dat) {
			if ($dat['group'] < $pulse_min)
				$dat['group'] = $pulse_min;

			$this->setGroupData($pulse_data, $dat);
			$this->setSumData($pulse_data, $dat);
		}
	
		$pulse_foreach = array();

		if (!empty($result)) {
			for ($pulse = $pulse_min; $pulse <= 100; $pulse += 5) {
				$pulse_foreach[] = array(
					'name' => '<small>bis</small> '.$pulse.' &#37;',
					'id' => $pulse);
			}
		}

		return array('name' => 'Pulsbereiche*', 'array' => $pulse_data, 'foreach' => $pulse_foreach);
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
		echo('
		<table class="small r" style="width:100%;">
			<tr class="c b">
				<td>'.$name.'</td>');

		$this->printTableHeader();

		echo('
			</tr>
			<tr class="space"><td colspan="'.$this->colspan.'" /></tr>');
	}

	/**
	 * Print header columns for a table
	 */
	private function printTableHeader() {
		for ($i = $this->timer_start; $i <= $this->timer_end; $i++)
			echo ($this->year != -1)
				? '<td width="7%">'.Time::Month($i, true).'</td>'.NL
				: '<td>'.$i.'</td>'.NL;

		echo ('<td>Gesamt</td>'.NL);
	}

	/**
	 * Print the ending of a table for one analysis
	 */
	private function printTableEnd() {
		echo('</table>'.HTML::clearBreak());
	}
}
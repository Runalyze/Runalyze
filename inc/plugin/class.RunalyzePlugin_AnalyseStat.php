<?php
/**
 * This file contains the class of the RunalyzePlugin "AnalyseStat".
 */
$PLUGINKEY = 'RunalyzePlugin_AnalyseStat';
/**
 * Class: RunalyzePlugin_AnalyseStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses START_YEAR
 * @uses HF_MAX
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_AnalyseStat extends PluginStat {
	private $where_time = '';
	private $group_time = '';
	private $timer = '';
	private $timer_start = 0;
	private $timer_end = 1;

	private $AnalysisData = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Analyse';
		$this->description = 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.';

		$this->initTimer();
		$this->initData();

		Error::getInstance()->addTodo('Add plot for trainingtypes', __FILE__, __LINE__);
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
		$config['lowest_pulsegroup'] = array('type' => 'int', 'var' => 65, 'description' => 'Niedrigster Pulsbereich (%HFmax)');
		$config['pulsegroup_step']   = array('type' => 'int', 'var' => 5, 'description' => 'Pulsbereich: Schrittweite');
		$config['lowest_pacegroup']  = array('type' => 'int', 'var' => 450, 'description' => 'Niedrigster Tempobereich (s/km)');
		$config['highest_pacegroup'] = array('type' => 'int', 'var' => 240, 'description' => 'H&ouml;chster Tempobereich (s/km)');
		$config['pacegroup_step']    = array('type' => 'int', 'var' => 15, 'description' => 'Tempobereich: Schrittweite');

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('Training '.$this->getYearString());
		$this->displayYearNavigation();

		echo Helper::clearBreak();

		$this->displayAnalysis();
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
	}

	/**
	 * Initialize analysis data
	 */
	private function initData() {
		if ($this->config['use_type']['var'])
			$this->AnalysisData[] = $this->getTypeArray();
		if ($this->config['use_pace']['var'])
			$this->AnalysisData[] = $this->getPaceArray();
		if ($this->config['use_pulse']['var'])
			$this->AnalysisData[] = $this->getPulseArray();
	}

	/**
	 * Display the analysis
	 */
	private function displayAnalysis() {
		foreach ($this->AnalysisData as $i => $Data) {
			$this->printTableStart($Data['name']);

			foreach ($Data['foreach'] as $i => $Each) {
				echo('
					<tr class="a'.($i%2+1).'">
						<td class="c b">'.$Each['name'].'</td>');

				for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
					if (isset($Data['array'][$Each['id']][$t])) {
						$num     = $Data['array'][$Each['id']][$t]['num'];
						$dist    = $Data['array'][$Each['id']][$t]['distanz'];
						$percent = round(100 * $dist / $Data['array']['timer_sum'][$t], 1);

						echo '<td title="'.$num.'x - '.Helper::Km($dist).'">'.number_format($percent, 1).' &#37;</td>';
					} else {
						echo Helper::emptyTD();
					}
				}

				if (isset($Data['array']['id_sum'][$Each['id']])) {
					$dist    = $Data['array']['id_sum'][$Each['id']];
					$percent = round(100 * $dist / $Data['array']['all_sum'], 1);

					echo '<td title="'.Helper::Km($dist).'">'.number_format($percent, 1).' &#37;</td>';
				} else {
					echo Helper::emptyTD();
				}

				echo('
					</tr>');
			}

			if ($i == 0) {
				echo('
					<tr class="space"><td colspan="14" /></tr>
					<tr class="a'.(($i+1)%2+1).'">
						<td class="b">Gesamt</td>');

				for ($t = $this->timer_start; $t <= $this->timer_end; $t++) {
					if (isset($Data['array']['timer_sum'][$t])) {
						echo '<td>'.Helper::Km($Data['array']['timer_sum'][$t], 0).'</td>'.NL;
					} else {
						echo Helper::emptyTD();
					}
				}

				echo('
						<td>'.Helper::Km($Data['array']['all_sum'], 0).'</td>
					</tr>').NL;
			}

			$this->printTableEnd();
		}
	}

	/**
	 * Get array for "Trainingstyp"
	 */
	private function getTypeArray() {
		$result = Mysql::getInstance()->fetchAsArray('
			SELECT '.$this->timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
				COUNT(*) AS `num`,
				SUM(`distanz`) AS `distanz`,
				`typid`,
				`RPE`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'typ` ON ('.PREFIX.'training.typid='.PREFIX.'typ.id)
			WHERE `sportid`='.RUNNINGSPORT.' '.$this->where_time.'
			GROUP BY `typid`, '.$this->group_time.'
			ORDER BY `RPE`, `timer` ASC');
		
		$type_data = array(
			'all_sum' => 0,
			'timer_sum' => array(),
			'id_sum' => array());
		
		foreach ($result as $dat) {
			$type_data[$dat['typid']][$dat['timer']] = array(
				'num' => $dat['num'],
				'distanz' => $dat['distanz']);
			$type_data['all_sum'] += $dat['distanz'];
			$type_data['timer_sum'][$dat['timer']] += $dat['distanz'];
			$type_data['id_sum'][$dat['typid']] += $dat['distanz'];
		}
	
		$type_foreach = array();
	
		$types = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name`, `abk` FROM `'.PREFIX.'typ` ORDER BY `RPE` ASC');
		foreach ($types as $i => $type) {
			$type_foreach[] = array(
				'name' => '<span title="'.$type['name'].'">'.$type['abk'].'</span>',
				'id' => $type['id']);
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
				SUM(`distanz`) AS `distanz`,
				FLOOR( (`dauer`/`distanz`)/'.$speed_step.')*'.$speed_step.' AS `pacegroup`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.RUNNINGSPORT.' '.$this->where_time.'
			GROUP BY `pacegroup`, '.$this->group_time.'
			ORDER BY `pacegroup` DESC, `timer` ASC');
		
		$speed_data = array(
			'all_sum' => 0,
			'timer_sum' => array(),
			'id_sum' => array());
		
		foreach ($result as $dat) {
			if ($dat['pacegroup'] > $speed_min)
				$dat['pacegroup'] = $speed_min;
			else if ($dat['pacegroup'] < $speed_max)
				$dat['pacegroup'] = $speed_max;
	
			$speed_data[$dat['pacegroup']][$dat['timer']]['num'] += $dat['num'];
			$speed_data[$dat['pacegroup']][$dat['timer']]['distanz'] += $dat['distanz'];
			$speed_data['all_sum'] += $dat['distanz'];
			$speed_data['timer_sum'][$dat['timer']] += $dat['distanz'];
			$speed_data['id_sum'][$dat['pacegroup']] += $dat['distanz'];
		}
	
		$speed_foreach = array();
	
		for ($speed = $speed_min; $speed >= $speed_max; $speed -= $speed_step) {
			$name = ($speed == $speed_max)
				? 'schneller'
				: '<small>bis</small> '.Helper::Speed(1, $speed, RUNNINGSPORT);
			$speed_foreach[] = array( 'name' => $name, 'id' => $speed);
		}

		return array('name' => 'Tempobereiche', 'array' => $speed_data, 'foreach' => $speed_foreach);
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
				SUM(`distanz`) AS `distanz`,
				CEIL( (100 * `puls` / '.HF_MAX.') /'.$pulse_step.')*'.$pulse_step.' AS `pulsegroup`
			FROM `'.PREFIX.'training`
			WHERE `sportid`='.RUNNINGSPORT.' '.$this->where_time.' && `puls`!=0
			GROUP BY `pulsegroup`, '.$this->group_time.'
			ORDER BY `pulsegroup`, `timer` ASC');
		
		$pulse_data = array(
			'all_sum' => 0,
			'timer_sum' => array(),
			'id_sum' => array());
		
		foreach ($result as $dat) {
			if ($dat['pulsegroup'] < $pulse_min)
				$dat['pulsegroup'] = $pulse_min;
	
			$pulse_data[$dat['pulsegroup']][$dat['timer']]['num'] += $dat['num'];
			$pulse_data[$dat['pulsegroup']][$dat['timer']]['distanz'] += $dat['distanz'];
			$pulse_data['all_sum'] += $dat['distanz'];
			$pulse_data['timer_sum'][$dat['timer']] += $dat['distanz'];
			$pulse_data['id_sum'][$dat['pulsegroup']] += $dat['distanz'];
		}
	
		$pulse_foreach = array();
	
		for ($pulse = $pulse_min; $pulse <= 100; $pulse += 5) {
			$pulse_foreach[] = array(
				'name' => '<small>bis</small> '.$pulse.' &#37;',
				'id' => $pulse);
		}

		return array('name' => 'Pulsbereiche', 'array' => $pulse_data, 'foreach' => $pulse_foreach);
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
			<tr class="space"><td colspan="14" /></tr>');
	}

	/**
	 * Print header columns for a table
	 */
	private function printTableHeader() {
		for ($i = $this->timer_start; $i <= $this->timer_end; $i++)
			echo ($this->year != -1)
				? '<td width="7%">'.Helper::Month($i, true).'</td>'.NL
				: '<td>'.$i.'</td>'.NL;

		echo ('<td>Gesamt</td>'.NL);
	}

	/**
	 * Print the ending of a table for one analysis
	 */
	private function printTableEnd() {
		echo('</table>'.Helper::clearBreak());
	}
}
?>
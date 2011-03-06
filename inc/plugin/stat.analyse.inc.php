<?php
/**
 * This file contains the plugin "Analyse".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 * @uses START_YEAR
 * @uses HF_MAX
 *
 * Last modified 2010/08/09 19:41 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_analyse_installer() {
	$type = 'stat';
	$filename = 'stat.analyse.inc.php';
	$name = 'Analyse';
	$description = 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Error->addTodo('Add plot for trainingtypes', __FILE__, __LINE__);

// Get data from database
if ($this->year != -1) {
	$where_time = '&& YEAR(FROM_UNIXTIME(`time`))='.$this->year;
	$group_time = 'MONTH(FROM_UNIXTIME(`time`))';
	$timer = 'MONTH';
	$timer_start = 1;
	$timer_end = 12;
} else {
	$where_time = '';
	$group_time = 'YEAR(FROM_UNIXTIME(`time`))';
	$timer = 'YEAR';
	$timer_start = START_YEAR;
	$timer_end = YEAR;
}

// TRAININGSTYPEN
	$result = $Mysql->fetch('
		SELECT '.$timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
			COUNT(*) AS `num`,
			SUM(`distanz`) AS `distanz`,
			`typid`,
			`RPE`
		FROM `ltb_training`
		LEFT JOIN `ltb_typ` ON (ltb_training.typid=ltb_typ.id)
		WHERE `sportid`='.RUNNINGSPORT.' '.$where_time.'
		GROUP BY `typid`, '.$group_time.'
		ORDER BY `RPE`, `timer` ASC', false, true);
	
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

	$types = $Mysql->fetch('SELECT `id`, `name`, `abk` FROM `ltb_typ` ORDER BY `RPE` ASC', false, true);
	foreach ($types as $i => $type) {
		$type_foreach[] = array(
			'name' => '<span title="'.$type['name'].'">'.$type['abk'].'</span>',
			'id' => $type['id']);
	}

// PULSBEREICHE
	$pulse_min = $this->config['lowest_pulsegroup']['var'];
	$pulse_step = $this->config['pulsegroup_step']['var'];
	$result = $Mysql->fetch('
		SELECT '.$timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
			COUNT(*) AS `num`,
			SUM(`distanz`) AS `distanz`,
			CEIL( (100 * `puls` / '.HF_MAX.') /'.$pulse_step.')*'.$pulse_step.' AS `pulsegroup`
		FROM `ltb_training`
		WHERE `sportid`='.RUNNINGSPORT.' '.$where_time.' && `puls`!=0
		GROUP BY `pulsegroup`, '.$group_time.'
		ORDER BY `pulsegroup`, `timer` ASC', false, true);
	
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

// TEMPOBEREICHE
	$speed_min = $this->config['lowest_pacegroup']['var'];
	$speed_max = $this->config['highest_pacegroup']['var'];
	$speed_step = $this->config['pacegroup_step']['var'];
	$result = $Mysql->fetch('
		SELECT '.$timer.'(FROM_UNIXTIME(`time`)) AS `timer`,
			COUNT(*) AS `num`,
			SUM(`distanz`) AS `distanz`,
			FLOOR( (`dauer`/`distanz`)/'.$speed_step.')*'.$speed_step.' AS `pacegroup`
		FROM `ltb_training`
		WHERE `sportid`='.RUNNINGSPORT.' '.$where_time.'
		GROUP BY `pacegroup`, '.$group_time.'
		ORDER BY `pacegroup` DESC, `timer` ASC', false, true);
	
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

$AnalysisData = array();
$AnalysisData[] = array('name' => 'Trainingstypen', 'array' => $type_data, 'foreach' => $type_foreach);
$AnalysisData[] = array('name' => 'Pulsbereiche', 'array' => $pulse_data, 'foreach' => $pulse_foreach);
$AnalysisData[] = array('name' => 'Tempobereiche', 'array' => $speed_data, 'foreach' => $speed_foreach);

/**
 * Print inner links to every year
 * @param Stat $Object
 */
function printInnerLinks($Object) {
	for ($x = START_YEAR; $x <= date("Y"); $x++)
		echo $Object->getInnerLink($x, 0, $x).' | ';

	echo $Object->getInnerLink('Jahresvergleich', 0, -1);
}

/**
 * Print the beginning of a table for one analysis
 * @param Stat $Object
 * @param string $name
 */
function printTableStart($Object, $name) {
	echo('
	<table cellspacing="0" width="100%" class="small r">
		<tr class="c b">
			<td>'.$name.'</td>');

	printTableHeader($Object);

	echo('
		</tr>
		<tr class="space"><td colspan="14" /></tr>');
}

/**
 * Print header columns for a table
 * @param Stat $Object
 */
function printTableHeader($Object) {
	if ($Object->get('year') != -1) {
		$timer_start = 1;
		$timer_end = 12;
	} else {
		$timer_start = START_YEAR;
		$timer_end = date("Y");
	}

	for ($i = $timer_start; $i <= $timer_end; $i++)
		echo ($Object->get('year') != -1)
			? '<td width="7%">'.Helper::Month($i, true).'</td>'.NL
			: '<td>'.$i.'</td>'.NL;

	echo ('<td>Gesamt</td>'.NL);
}

/**
 * Print the ending of a table for one analysis
 * @param Stat $Object
 */
function printTableEnd($Object) {
	echo('
	</table>
	
	<br class="clear" />');
}
?>
<h1>Training <?php echo ($this->year != -1) ? $this->year : 'Jahresvergleich'; ?></h1>

<small class="right">
	<?php printInnerLinks($this); ?>
</small>

<br class="clear" />

<?php
foreach ($AnalysisData as $i => $Data) {
	printTableStart($this, $Data['name']);

	foreach ($Data['foreach'] as $i => $Each) {
		echo('
			<tr class="a'.($i%2+1).'">
				<td class="c b">'.$Each['name'].'</td>');

		for ($t = $timer_start; $t <= $timer_end; $t++) {
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
	
		for ($t = $timer_start; $t <= $timer_end; $t++) {
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

	printTableEnd($this);
}
?>
<?php
/**
 * This file contains the class of the RunalyzePluginStat "Trainingspartner".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Trainingspartner';
/**
 * Class: RunalyzePluginStat_Trainingspartner
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Trainingspartner extends PluginStat {
	/**
	 * Array for all trainingspartner
	 * @var array
	 */
	protected $Partner = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Trainingspartner';
		$this->description = 'Wie oft hast du mit wem gemeinsam trainiert?';
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initTrainingspartner();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		echo '<table class="fullwidth zebra-style margin-5 small">';
		echo '<thead><tr><th colspan="2">Alle Trainingspartner</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->Partner))
			echo('
				<tr>
					<td class="b">0x</td>
					<td><em>Du hast bisher nur alleine trainiert.</em></td>
				</tr>');
		else {
			$row_num = INFINITY;
			$i = 0;
		
			foreach ($this->Partner as $name => $name_num) {
				if ($row_num == $name_num)
					echo(', ');
				else {
					if ($name_num != 1 && $row_num != INFINITY)
						echo '</td></tr>';
		
					$row_num = $name_num;
					$i++;
					echo '<tr><td class="b">'.$row_num.'x</td><td>';
				}

				echo SearchLink::to('partner', $name, $name, 'like');
			}
			echo '</td></tr>';
		}

		echo '</tobdy>';
		echo '</table>';
	}

	/**
	 * Init all trainingspartner
	 */
	protected function initTrainingspartner() {
		$trainings = Mysql::getInstance()->fetchAsArray('SELECT `partner` FROM `'.PREFIX.'training` WHERE `partner` != ""');

		if (empty($trainings))
			return;

		foreach ($trainings as $training) {
			$trainingspartner = explode(', ', $training['partner']);
			foreach ($trainingspartner as $name) {
				if (!isset($this->Partner[$name]))
					$this->Partner[$name] = 1;
				else
					$this->Partner[$name]++;
			}
		}

		array_multisort($this->Partner, SORT_DESC);
	}
}
?>
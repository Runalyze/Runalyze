<?php
/**
 * This file contains the class of the RunalyzePluginStat "Trainingspartner".
 */
$PLUGINKEY = 'RunalyzePluginStat_Trainingspartner';
/**
 * Class: RunalyzePluginStat_Trainingspartner
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
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

		$this->initTrainingspartner();
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
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('Trainingspartner');

		echo '<table class="fullWidth margin-5 small">';
		echo '<thead><tr><th colspan="2">Alle Trainingspartner</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->Partner))
			echo('
				<tr class="a1">
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
					echo '<tr class="a'.($i%2+1).'"><td class="b">'.$row_num.'x</td><td>';
				}
		
				echo DataBrowser::getSearchLink($name, 'opt[partner]=like&val[partner]='.$name);
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
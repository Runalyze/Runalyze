<?php
/**
 * This file contains the class of the RunalyzePlugin "TrainingspartnerStat".
 */
$PLUGINKEY = 'RunalyzePlugin_TrainingspartnerStat';
/**
 * Class: RunalyzePlugin_TrainingspartnerStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_TrainingspartnerStat extends PluginStat {
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
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('Trainingspartner');
		echo '<table style="width:95%;" style="margin:0 5px;" class="small">';
		echo '<tr class="b c"><td colspan="2">Alle Trainingspartner</td></tr>';
		echo Helper::spaceTR(2);

		$partner = array();
		$trainings = Mysql::getInstance()->fetchAsArray('SELECT `trainingspartner` FROM `ltb_training` WHERE `trainingspartner` != ""');
		if (empty($trainings))
			echo('
				<tr class="a1">
					<td class="b">0x</td>
					<td><em>Du hast bisher nur alleine trainiert.</em></td>
				</tr>');
		else {
			foreach ($trainings as $training) {
				$trainingspartner = explode(', ', $training['trainingspartner']);
				foreach ($trainingspartner as $name) {
					if (!isset($partner[$name]))
						$partner[$name] = 1;
					else
						$partner[$name]++;
				}
			}
		
			$row_num = INFINITY;
			$i = 0;
			array_multisort($partner, SORT_DESC);
		
			foreach ($partner as $name => $name_num) {
				if ($row_num == $name_num)
					echo(', ');
				else {
					if ($name_num != 1 && $row_num != INFINITY)
						echo '</td></tr>';
		
					$row_num = $name_num;
					$i++;
					echo '<tr class="a'.($i%2+1).'"><td class="b">'.$row_num.'x</td><td>';
				}
		
				echo DataBrowser::getSearchLink($name, 'opt[trainingspartner]=like&val[trainingspartner]='.$name);
			}
			echo '</td></tr>';
		}

		echo '</table>';
	}
}
?>
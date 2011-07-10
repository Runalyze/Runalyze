<?php
/**
 * This file contains the class of the RunalyzePlugin "StreckenStat".
 */
$PLUGINKEY = 'RunalyzePlugin_StreckenStat';
/**
 * Class: RunalyzePlugin_StreckenStat
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
class RunalyzePlugin_StreckenStat extends PluginStat {
	private $orte = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Strecken';
		$this->description = 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.';

		$this->initCities();
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
		$this->displayHeader('Strecken');
		$this->displayRoutes();
		$this->displayCities();

		echo Helper::clearBreak();
		echo Helper::clearBreak();

		$this->displayLonelyCities();
	}

	/**
	 * Display routes
	 */
	private function displayRoutes() {
		echo '<table style="width:70%;" style="margin:0 5px;" class="left small">';
		echo '<tr class="b c"><td colspan="3">H&auml;ufigsten Strecken</td></tr>';
		echo Helper::spaceTR(3);

		$strecken = Mysql::getInstance()->fetchAsArray('
			SELECT `strecke`, SUM(`distanz`) as `km`, SUM(1) as `num`
			FROM `ltb_training`
			WHERE `strecke`!=""
			GROUP BY `strecke`
			ORDER BY `num` DESC
			LIMIT 10');
		foreach ($strecken as $i => $strecke) {
			echo('
				<tr class="a'.($i%2+1).' r">
					<td>'.$strecke['num'].'x</td>
					<td class="l">	
						'.DataBrowser::getSearchLink(Helper::Cut($strecke['strecke'],100), 'opt[strecke]=is&val[strecke]='.$strecke['strecke']).'
					</td>
					<td>'.Helper::Km($strecke['km']).'</td>
				</tr>');
		}

		echo '</table>';
	}

	/**
	 * Display most visited cities
	 */
	private function displayCities() {
		echo '<table style="width:25%;" style="margin:0 5px;" class="left small">';
		echo '<tr class="b c"><td colspan="2">H&auml;ufigsten Orte</td></tr>';
		echo Helper::spaceTR(2);
		
		$i = 1;
		array_multisort($this->orte, SORT_DESC);

		foreach ($this->orte as $ort => $num) {
			$i++;
			echo('
				<tr class="a'.($i%2+1).'">
					<td>'.$num.'x</td>
					<td>'.DataBrowser::getSearchLink($ort, 'opt[strecke]=like&val[strecke]='.$ort).'</td>
				</tr>');

			if ($i == 11)
				break;
		}

		echo '</table>';
	}

	/**
	 * Display less visited cities
	 */
	private function displayLonelyCities() {
		echo '<table style="width:95%;" style="margin:0 5px;" class="small">';
		echo '<tr class="b c"><td colspan="2">Seltensten Orte</td></tr>';
		echo Helper::spaceTR(2);

		$num_x = 0;
		array_multisort($this->orte);
		
		foreach ($this->orte as $ort => $num) {
			if ($num_x <= 4) {
				if ($num_x != $num) {
					if ($num != 1)
						echo '</td></tr>';
					$num_x = $num;
					echo '<tr class="a'.($num_x%2+1).'"><td class="b">'.$num.'x</td><td>';
				} else
					echo(', ');

				echo DataBrowser::getSearchLink($ort, 'opt[strecke]=like&val[strecke]='.$ort);
			}
			else {
				echo '</td></tr>';
				break;
			}
		}

		echo('
			<tr class="a'.(($num_x+1)%2+1).'">
				<td colspan="2" class="c">
					Insgesamt wurden <strong>'.count($this->orte).' verschiedene Orte</strong> sportlich besucht.
				</td>
			</tr>
		</table>');
	}

	/**
	 * Initialize internal array for all cities
	 */
	private function initCities() {
		$this->orte = array();
		$strecken = Mysql::getInstance()->fetchAsArray('SELECT `strecke`, `distanz` FROM `ltb_training` WHERE `strecke`!=""');
		foreach ($strecken as $strecke) {
			$streckenorte = explode(" - ", $strecke['strecke']);
			foreach ($streckenorte as $streckenort) {
				if (!isset($this->orte[$streckenort]))
					$this->orte[$streckenort] = 1;
				else
					$this->orte[$streckenort]++;
			}
		}
	}
}
?>
<?php
/**
 * This file contains the class of the RunalyzePlugin "SchuheStat".
 */
$PLUGINKEY = 'RunalyzePlugin_SchuheStat';
/**
 * Class: RunalyzePlugin_SchuheStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 * @uses inc/draw/plugin.schuhe.php
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_SchuheStat extends PluginStat {
	private $schuhe = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Schuhe';
		$this->description = 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).';

		$this->initData();
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
		$this->displayHeader('Schuhe');

		echo '
		<style type="text/css">
		tr.shoe { height:2px; }
		tr.shoe td { padding: 0; }
		</style>
		<table style="width:100%;">
			<tr class="b c">
				<td colspan="2" />
				<td class="small">Kaufdatum</td>
				<td>&Oslash; km</td>
				<td>&Oslash; Pace</td>
				<td class="small" colspan="2">max.</td>
				<td>Dauer</td>
				<td>Distanz</td>
			</tr>';
		echo Helper::spaceTr(9);

		if (!empty($this->schuhe)) {
			foreach ($this->schuhe as $i => $schuh) {
				$training_dist = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `distanz` DESC');
				$training_pace = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `pace` ASC');
				$trainings     = Mysql::getInstance()->num('SELECT * FROM `'.PREFIX.'training` WHERE `schuhid`="'.$schuh['id'].'"');
				$in_use = $schuh['inuse']==1 ? '' : ' small';

				echo('
				<tr class="a'.($i%2 + 1).' r">
					<td class="small">'.$trainings.'x</td>
					<td class="b'.$in_use.' l">'.DataBrowser::getSearchLink($schuh['name'], 'opt[schuhid]=is&val[schuhid][0]='.$schuh['id']).'</td>
					<td class="small">'.$schuh['kaufdatum'].'</td>
					<td >'.(($trainings != 0) ? Helper::Km($schuh['km']/$trainings) : '-').'</td>
					<td >'.(($trainings != 0) ? Helper::Speed($schuh['km'], $schuh['dauer']) : '-').'</td>
					<td class="small">'.Ajax::trainingLink($training_dist['id'], Helper::Km($training_dist['distanz'])).'</td>
					<td class="small">'.Ajax::trainingLink($training_pace['id'], $training_pace['pace'].'/km').'</td>
					<td>'.Helper::Time($schuh['dauer']).'</td>
					<td>'.Helper::Km($schuh['km']).'</td>
				</tr>
				<tr class="shoe" style="background:url(inc/draw/plugin.schuhe.php?km='.round($schuh['km']).') no-repeat bottom left;">
					<td colspan="9"></td>
				</tr>');
			}
		} else {
			echo('<tr class="a1"><td colspan="9">Keine Schuhe vorhanden.</td></tr>');
			Error::getInstance()->addWarning('Bisher keine Schuhe eingetragen', __FILE__, __LINE__);
		}

		echo Helper::spaceTR(9);
		echo '</table>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->schuhe = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'schuhe` ORDER BY `inuse` DESC, `km` DESC');
	}
}
?>
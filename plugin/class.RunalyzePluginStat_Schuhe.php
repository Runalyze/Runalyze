<?php
/**
 * This file contains the class of the RunalyzePluginStat "Schuhe".
 */
$PLUGINKEY = 'RunalyzePluginStat_Schuhe';
/**
 * Class: RunalyzePluginStat_Schuhe
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 * @uses inc/draw/plugin.schuhe.php
 */
class RunalyzePluginStat_Schuhe extends PluginStat {
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
		<table id="listOfAllShoes" class="fullWidth">
			<thead>
				<tr>
					<th class="{sorter: \'x\'} small">x-mal</th>
					<th>Name</th>
					<th class="{sorter: \'germandate\'} small">Kaufdatum</th>
					<th class="{sorter: \'distance\'}">&Oslash; km</th>
					<th>&Oslash; Pace</th>
					<th class="{sorter: \'distance\'} small"><small>max.</small> km</th>
					<th class="small"><small>max.</small> Pace</th>
					<th class="{sorter: \'resulttime\'}">Dauer</th>
					<th class="{sorter: \'distance\'}">Distanz</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($this->schuhe)) {
			foreach ($this->schuhe as $i => $schuh) {
				$training_dist = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `shoeid`='.$schuh['id'].' ORDER BY `distance` DESC');
				$training_pace = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'training` WHERE `shoeid`='.$schuh['id'].' ORDER BY `pace` ASC');
				$trainings     = Mysql::getInstance()->num('SELECT * FROM `'.PREFIX.'training` WHERE `shoeid`="'.$schuh['id'].'"');
				$in_use = ($schuh['inuse'] == 1) ? '' : ' unimportant';

				echo('
				<tr class="'.HTML::trClass($i).$in_use.' r">
					<td class="small">'.$trainings.'x</td>
					<td class="b l">'.Shoe::getSeachLink($schuh['id']).'</td>
					<td class="small">'.$schuh['since'].'</td>
					<td >'.(($trainings != 0) ? Helper::Km($schuh['km']/$trainings) : '-').'</td>
					<td >'.(($trainings != 0) ? Helper::Speed($schuh['km'], $schuh['time']) : '-').'</td>
					<td class="small">'.Ajax::trainingLink($training_dist['id'], Helper::Km($training_dist['distance'])).'</td>
					<td class="small">'.Ajax::trainingLink($training_pace['id'], $training_pace['pace'].'/km').'</td>
					<td>'.Helper::Time($schuh['time']).'</td>
					<td>'.Helper::Km($schuh['km']).' '.Shoe::getIcon($schuh['km']).'</td>
				</tr>');
			}
		} else {
			echo('<tr class="a1"><td colspan="9">Keine Schuhe vorhanden.</td></tr>');
			Error::getInstance()->addWarning('Bisher keine Schuhe eingetragen', __FILE__, __LINE__);
		}

		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor("#listOfAllShoes");
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->schuhe = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'shoe` ORDER BY `inuse` DESC, `km` DESC');
	}
}
?>
<?php
/**
 * This file contains the class of the RunalyzePlugin "SportsPanel".
 */
$PLUGINKEY = 'RunalyzePlugin_SportsPanel';
/**
 * Class: RunalyzePlugin_SportsPanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Mysql
 * @uses class::Helper
 * @uses START_TIME
 *
 * Last modified 2011/07/10 16:00 by Hannes Christiansen
 */
class RunalyzePlugin_SportsPanel extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Sports';
		$this->description = '&Uuml;bersicht der Leistungen aller Sportarten f&uuml;r den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.';
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$html = '';
		foreach ($this->getTimeset() as $i => $timeset) {
			if ($i != 0)
				$html .= ' | ';
			$html .= Ajax::change($timeset['name'], 'sports', '#sports_'.$i);
		}
	
		return '<small>'.$html.'</small>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Mysql = Mysql::getInstance();
	
		echo('<div id="sports">');
	
		foreach ($this->getTimeset() as $i => $timeset) {
			echo('<div id="sports_'.$i.'" class="change"'.($i==0 ? '' : 'style="display:none;"').'>');
	
			$sports = $Mysql->fetchAsArray('SELECT * FROM `ltb_sports` WHERE `online`=1 ORDER BY `distanz` DESC, `dauer` DESC');
			foreach ($sports as $sport) {
				$data = $Mysql->fetchSingle('SELECT `sportid`, COUNT(`id`) as `anzahl`, SUM(`distanz`) as `distanz_sum`, SUM(`dauer`) as `dauer_sum`  FROM `ltb_training` WHERE `sportid`='.$sport['id'].' AND `time` > '.$timeset['start'].' GROUP BY `sportid`');
				$leistung = ($sport['distanztyp'] == 1)
					? Helper::Unknown(Helper::Km($data['distanz_sum']), '0,0 km')
					: Helper::Time($data['dauer_sum']); 		
			
				echo('
		<p>
			<span>
				<small><small>('.Helper::Unknown($data['anzahl'], '0').'-mal)</small></small>
				'.$leistung.'
			</span>
			'.Icon::getSportIcon($sport['id']).'
			<strong>'.$sport['name'].'</strong>
		</p>'.NL);	
			}
	
			echo('<small class="right">seit '.date("d.m.Y",$timeset['start']).'</small>');
			echo Helper::clearBreak();
			echo('</div>');
		}
	
		echo('</div>');
	}

	/**
	 * Get the timeset as array for this panel
	 */
	private function getTimeset() {
		$timeset = array();
		$timeset[] = array('name' => 'Diesen Monat', 'start' => mktime(0,0,0,date("m"),1,date("Y")));
		$timeset[] = array('name' => 'Dieses Jahr', 'start' => mktime(0,0,0,1,1,date("Y")));
		$timeset[] = array('name' => 'Gesamt', 'start' => START_TIME);
	
		return $timeset;
	}
}
?>
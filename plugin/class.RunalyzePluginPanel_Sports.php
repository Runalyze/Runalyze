<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Sports".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Sports';
/**
 * Class: RunalyzePluginPanel_Sports
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Sports extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Sportarten';
		$this->description = '&Uuml;bersicht der Leistungen aller Sportarten f&uuml;r den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.';

		$this->textAsRightSymbol = true;
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
	
		return '<span class="smallHeadNavi">'.$html.'</span>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Mysql = Mysql::getInstance();
	
		echo('<div id="sports">');
	
		foreach ($this->getTimeset() as $i => $timeset) {
			echo('<div id="sports_'.$i.'" class="change"'.($i==0 ? '' : ' style="display:none;"').'>');
	
			$data = $Mysql->fetchAsArray('SELECT `sportid`, COUNT(`id`) as `anzahl`, SUM(`distance`) as `distanz_sum`, SUM(`s`) as `dauer_sum`  FROM `'.PREFIX.'training` WHERE `time` >= '.$timeset['start'].' GROUP BY `sportid` ORDER BY `distanz_sum` DESC, `dauer_sum` DESC');
			foreach ($data as $dat) {
				$Sport = new Sport($dat['sportid']);
				$leistung = $Sport->usesDistance()
					? Helper::Unknown(Running::Km($dat['distanz_sum']), '0,0 km')
					: Time::toString($dat['dauer_sum']); 		
			
				echo('
		<p>
			<span class="right">
				<small><small>('.Helper::Unknown($dat['anzahl'], '0').'-mal)</small></small>
				'.$leistung.'
			</span>

			'.$Sport->Icon().'
			<strong>'.$Sport->name().'</strong>
		</p>'.NL);	
			}

			if (empty($data))
				echo('<p><em>Noch keine Daten vorhanden.</em></p>');
	
			echo('<small class="right">seit '.date("d.m.Y", $timeset['start']).'</small>');
			echo HTML::clearBreak();
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
<?php
/**
 * This file contains the panel-plugin "Sportarten".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Helper
 * @uses START_TIME
 *
 * Last modified 2010/09/07 21:34 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function sportarten_installer() {
	$type = 'panel';
	$filename = 'panel.sports.inc.php';
	$name = 'Sports';
	$description = 'Übersicht der Leistungen aller Sportarten für den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.';
	// TODO Include the plugin-installer
}

/**
 * Sets the right symbol in the h1-header of this panel
 * @return string (HTML)
 */
function sportarten_rightSymbol() {
	// TODO Use class::AJAX for these links
	$html = '';
	foreach(sports_getTimeset() as $i => $timeset) {
		if ($i != 0)
			$html .= ' | ';
		$html .= '<a class="change" href="#sports_'.$i.'" target="sports">'.$timeset['name'].'</a>';
	}

	return '<small>'.$html.'</small>';
}

/**
 * Display-function for this plugin, will be called by class::Panel::display()
 */
function sportarten_display() {
	$Mysql = Mysql::getInstance();

	echo('<div id="sports">');

	foreach(sports_getTimeset() as $i => $timeset) {
		echo('<div id="sports_'.$i.'" class="change"'.($i==0?'':'style="display:none;"').'>');

		$sports = $Mysql->fetch('SELECT * FROM `ltb_sports` WHERE `online`=1 ORDER BY `distanz` DESC, `dauer` DESC');
		foreach($sports as $sport) {
			$data = $Mysql->fetch('SELECT `sportid`, COUNT(`id`) as `anzahl`, SUM(`distanz`) as `distanz_sum`, SUM(`dauer`) as `dauer_sum`  FROM `ltb_training` WHERE `sportid`='.$sport['id'].' AND `time` > '.$timeset['start'].' GROUP BY `sportid`');
			$leistung = ($sport['distanztyp'] == 1)
				? Helper::Unbekannt(km($data['distanz_sum']),'0,0 km')
				: Helper::Time($data['dauer_sum']); 		
		
			echo('
	<p>
		<span>
			<small><small>('.Helper::Unbekannt($data['anzahl'],'0').'-mal)</small></small>
			'.$leistung.'
		</span>
		<img src="img/sports/'.$sport['bild'].'" alt="'.$sport['name'].'" />
		<strong>'.$sport['name'].'</strong>
	</p>'.NL);	
		}

		echo('<small class="right">seit '.date("d.m.Y",$timeset['start']).'</small><br class="break" />');
		echo('</div>');
	}

	echo('</div>');
}

/**
 * Get the timeset as array for this panel
 */
function sports_getTimeset() {
	$timeset = array();
	$timeset[] = array('name' => 'Diesen Monat', 'start' => mktime(0,0,0,date("m"),1,date("Y")));
	$timeset[] = array('name' => 'Dieses Jahr', 'start' => mktime(0,0,0,1,1,date("Y")));
	$timeset[] = array('name' => 'Gesamt', 'start' => START_TIME);

	return $timeset;
}
?>
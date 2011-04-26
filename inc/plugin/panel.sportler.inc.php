<?php
/**
 * This file contains the panel-plugin "Sportler".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses $config
 * @uses lib/draw/gewicht.php
 * @uses lib/draw/fett.php
 *
 * Last modified 2010/08/11 22:46 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function sportler_installer() {
	$type = 'panel';
	$filename = 'panel.sportler.inc.php';
	$name = 'Sportler';
	$description = 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).';
	// TODO Include the plugin-installer
}

/**
 * Sets the right symbol in the h1-header of this panel
 * @return string (HTML)
 */
function sportler_rightSymbol() {
	return Ajax::window('<a href="inc/plugin/window.sportler.php" title="Daten hinzuf�gen"><img src="img/add.png" alt="Daten hinzuf�gen" /></a>');
}

/**
 * Display-function for this plugin, will be called by class::Panel::display()
 */
function sportler_display() {
	global $global;
?>
	<div id="sportler">
		<div id="sportler-gewicht" class="change">
<?php
	$dat = Mysql::getInstance()->fetch('ltb_user','LAST');
	if (CONFIG_USE_GEWICHT == 1)
		$left = '<strong title="'.date("d.m.Y",$dat['time']).'">'.$dat['gewicht'].' kg</strong>';
	
	if (CONFIG_USE_RUHEPULS == 1)
		$right = $dat['puls_ruhe'].' bpm / '.$dat['puls_max'].' bpm';
	
	echo('    <p><span>'.$right.'</span> <a class="change" href="sportler-analyse" target="sportler"><del>Analyse</del>/Allgemein:</a> '.$left.'</p>'.NL);
?>
			<center>
				<img src="inc/draw/plugin.sportler.gewicht.php" alt="Diagramm" style="width:320px; height:148px;" />
			</center> 
		</div>
		<div id="sportler-analyse" class="change" style="display:none;">
<?php $left = ''; $right = '';
	if (CONFIG_USE_KOERPERFETT == 1)
		$left = '<small>'.$dat['fett'].'&#37;Fett, '.$dat['wasser'].'&#37;Wasser, '.$dat['muskeln'].'&#37;Muskeln</small>';
	
	if (CONFIG_USE_BLUTDRUCK == 1) 
		$right = '<small>Blutdruck: '.$dat['blutdruck_min'].' zu '.$dat['blutdruck_max'];
	
	echo('    <p><span>'.$right.'</span> <a class="change" href="sportler-gewicht" target="sportler">Analyse/<del>Allgemein</del>:</a> '.$left.'</p>'.NL);
?>
			<center>
				<img src="inc/draw/plugin.sportler.fett.php" alt="Diagramm" style="width:320px; height:148px;" />
			</center> 
		</div>
	</div>
<?php
}
?>
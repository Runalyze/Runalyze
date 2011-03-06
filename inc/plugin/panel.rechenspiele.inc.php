<?php
/**
 * This file contains the panel-plugin "Rechenspiele".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Helper
 * @uses class::Ajax
 * @uses CONFIG_MAX_ATL
 * @uses CONFIG_MAX_CTL
 * @uses VDOT_FORM
 *
 * Last modified 2010/08/29 16:08 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function rechenspiele_installer() {
	$type = 'panel';
	$filename = 'panel.rechenspiele.inc.php';
	$name = 'Rechenspiele';
	$description = 'Anzeige von Rechenspielen zur M&uuml;digkeit, Grundlagenausdauer und Trainingsform. Zus&auml;tzlich werden auch empfohlene Trainingsgeschwindigkeiten angezeigt.';
	// TODO Include the plugin-installer
}

/**
 * Sets the right symbol in the h1-header of this panel
 * @return string (HTML)
 */
function rechenspiele_rightSymbol() {
	$symbols = Ajax::window('<a href="inc/plugin/window.monatskilometer.php" title="Monatskilometer anzeigen"><img src="img/mk.png" alt="Monatskilometer anzeigen" /></a>').NL;
	$symbols .= Ajax::window('<a href="inc/plugin/window.wochenkilometer.php" title="Wochenkilometer anzeigen"><img src="img/wk.png" alt="Wochenkilometer anzeigen" /></a>').NL;
	$symbols .= Ajax::window('<a href="inc/plugin/window.rechenspiele.form.php" title="Form anzeigen"><img src="img/mued.png" alt="Form anzeigen" /></a>').NL;

	return $symbols;
}

/**
 * Display-function for this plugin, will be called by class::Panel::display()
 */
function rechenspiele_display() {
	global $global, $config;
?>
	<small class="right r">
<?php
$vVDOT = JD::VDOT2v(VDOT_FORM);

$t = array();
$t[] = array('kurz' => 'RL', 'pVDOT' => '59-64');
$t[] = array('kurz' => 'DL', 'pVDOT' => '65-74');
$t[] = array('kurz' => 'LL', 'pVDOT' => '65-74');
$t[] = array('kurz' => 'TDL', 'pVDOT' => '83-88');
$t[] = array('kurz' => 'IT', 'pVDOT' => '95-100');
$t[] = array('kurz' => 'WHL', 'pVDOT' => '105-110');

foreach ($t as $train) {
	$train_tempo = explode('-',$train['pVDOT']);
	echo ('
		'.$train['kurz'].': <em>'.JD::v2Pace($vVDOT*$train_tempo[1]/100).'</em> - <em>'.JD::v2Pace($vVDOT*$train_tempo[0]/100).'</em>/km<br />');
}
?>

	</small>
	<span class="left" style="width:60%;">
<?php
echo('
		<p><span>'.round(100*Helper::ATL()/CONFIG_MAX_ATL).' &#37;</span> <strong>M&uuml;digkeit</strong> <small>(ATL)</small></p>
		<p><span>'.round(100*Helper::CTL()/CONFIG_MAX_CTL).' &#37;</span> <strong>Fitnessgrad</strong> <small>(CTL)</small></p>
		<p><span>'.Helper::TSB().'</span> <strong>Stress Balance</strong> <small>(TSB)</small></p>
		<p><span>'.round(VDOT_FORM,2).'</span> <strong>VDOT</strong></p>
		<p><span>'.Helper::BasicEndurance().'</span> <strong>Grundlagenausdauer</strong></p>');
?>

	</span>
	<br class="clear" />
<?php 
}
?>
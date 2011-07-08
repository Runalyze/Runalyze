<?php
/**
 * This file contains the panel-plugin "Schuhe".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses lib/draw/schuhbalken.php
 *
 * Last modified 2010/08/11 22:46 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function schuhe_installer() {
	$type = 'panel';
	$filename = 'panel.schuhe.inc.php';
	$name = 'Schuhe';
	$description = 'Anzeige der gelaufenen Kilometer aller Schuhe.';
	// TODO Include the plugin-installer
}

/**
 * Sets the right symbol in the h1-header of this panel
 * @return string (HTML)
 */
function schuhe_rightSymbol() {
	return Ajax::window('<a href="inc/plugin/window.schuhe.php" title="Schuh hinzuf&uuml;gen">'.Icon::get(Icon::$RUNNINGSHOE, 'Schuh hinzuf&uuml;gen').'</a>');
}

/**
 * Display-function for this plugin, will be called by class::Panel::display()
 */
function schuhe_display() {
	global $global, $config;
?>
	<div id="schuhe">
<?php
	$inuse = true;
	$schuhe = Mysql::getInstance()->fetchAsArray('SELECT `name`, `km`, `inuse` FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
	foreach($schuhe as $i => $schuh) {
		if ($inuse && $schuh['inuse'] == 0) {
			echo('	<div id="hiddenschuhe" style="display:none;">'.NL);
			$inuse = false;
		}
		echo('
		<p style="background-image:url(lib/draw/schuhbalken.php?km='.round($schuh['km']).');">
			<span>'.Helper::Km($schuh['km']).'</span>
			<strong>'.DataBrowser::getSearchLink($schuh['name'], 'opt[schuhid]=is&val[schuhid]='.$schuh['id']).'</strong>
		</p>'.NL);	
	}
	echo('	</div>');
?>
	</div>
<?php echo Ajax::toggle('<a class="right" href="#schuhe" name="schuhe">Alte Schuhe anzeigen</a>', 'hiddenschuhe'); ?>
	<br class="clear" />
<?php
}
?>
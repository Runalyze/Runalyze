<?php
/**
 * File for displaying sum data for each week or month
 * Call:   call/window.plotSumData.php
 */
require '../inc/class.Frontend.php';
require '../inc/class.FrontendShared.php';
require '../inc/class.FrontendSharedList.php';

$Frontend = new FrontendSharedList();

echo '<div class="panel-content">';

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");

if (!isset($_GET['type']))
	$_GET['type'] = 'month';

if ($_GET['type'] == 'week') {
	$Plot = new PlotWeekSumData();
	$Plot->display();
} elseif ($_GET['type'] == 'month') {
	$Plot = new PlotMonthSumData();
	$Plot->display();
} else {
	echo HTML::error('Wochen- oder Monatsdaten? Es ist kein korrekter <em>type</em> f&uuml;r dieses Diagramm angegeben.');
}

echo '</div>';
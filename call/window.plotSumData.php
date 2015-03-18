<?php
/**
 * File for displaying sum data for each week or month
 * Call:   call/window.plotSumData.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = PlotSumData::LAST_12_MONTHS;

if (!isset($_GET['type']))
	$_GET['type'] = 'month';

if ($_GET['type'] == 'week') {
	$Plot = new PlotWeekSumData();
	$Plot->display();
} elseif ($_GET['type'] == 'month') {
	$Plot = new PlotMonthSumData();
	$Plot->display();
} else {
	echo HTML::error( __('There was a problem.') );
}
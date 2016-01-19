<?php
/**
 * File for correction the elevation data of this training.
 * Call:   call/call.Training.elevationCorrection.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\Context;
use Runalyze\Model\Activity;
use Runalyze\Model\Route;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\View\Activity\Linker;
use Runalyze\Data\Elevation\Correction\NoValidStrategyException;

$Frontend = new Frontend();

$Factory = Context::Factory();
$Activity = $Factory->activity(Request::sendId());
$ActivityOld = clone $Activity;
$Route = $Factory->route($Activity->get(Activity\Entity::ROUTEID));
$RouteOld = clone $Route;

try {
	$Calculator = new Calculator($Route);
	$result = $Calculator->tryToCorrectElevation(Request::param('strategy'));
} catch (NoValidStrategyException $Exception) {
	$result = false;
}

if ($result) {
	$Calculator->calculateElevation();
	$Activity->set(Activity\Entity::ELEVATION, $Route->elevation());

	$UpdaterRoute = new Route\Updater(DB::getInstance(), $Route, $RouteOld);
	$UpdaterRoute->setAccountID(SessionAccountHandler::getId());
	$UpdaterRoute->update();

	$UpdaterActivity = new Activity\Updater(DB::getInstance(), $Activity, $ActivityOld);
	$UpdaterActivity->setAccountID(SessionAccountHandler::getId());
	$UpdaterActivity->update();

	if (Request::param('strategy') == 'none') {
		echo __('Corrected elevation data has been removed.');
	} else {
		echo __('Elevation data has been corrected.');
	}

	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
	echo Ajax::getReloadCommand();
	echo Ajax::wrapJS(
		'if ($("#ajax").is(":visible") && $("#training").length) {'.
			'Runalyze.Overlay.load(\''.Linker::EDITOR_URL.'?id='.Request::sendId().'\');'.
		'} else if ($("#ajax").is(":visible") && $("#gps-results").length) {'.
			'Runalyze.Overlay.load(\''.Linker::ELEVATION_INFO_URL.'?id='.Request::sendId().'\');'.
		'}'
	);
} else {
	echo __('Elevation data could not be retrieved.');
}
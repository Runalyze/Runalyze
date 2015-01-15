<?php
/**
 * File for correction the elevation data of this training.
 * Call:   call/call.Training.elevationCorrection.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\View\Activity\Linker;

$Frontend = new Frontend();
/*
$Training = new TrainingObject( Request::sendId() );
$Training->tryToCorrectElevation();

if ($Training->elevationWasCorrected()) {
	echo __('Elevation data has been corrected.');

	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
	echo Ajax::getReloadCommand();
	echo Ajax::wrapJS('if($("#ajax").is(":visible"))Runalyze.Overlay.load(\''.Linker::EDITOR_URL.'?id='.Request::sendId().'\')');
} else {
	echo __('Elevation data could not be retrieved.');
}
 */
// TODO
echo __('Elevation correction does not work at the moment. Tell Hannes that he has to hurry up with finishing the refactoring!');
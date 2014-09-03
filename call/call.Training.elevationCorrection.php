<?php
/**
 * File for correction the elevation data of this training.
 * Call:   call/call.Training.elevationCorrection.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$Training = new TrainingObject( Request::sendId() );
$Training->tryToCorrectElevation();

if ($Training->elevationWasCorrected()) {
	echo __('Elevation data have been corrected.');

	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
	echo Ajax::getReloadCommand();
	echo Ajax::wrapJS('if($("#ajax").is(":visible"))Runalyze.Overlay.load(\''.$Training->Linker()->editUrl().'\')');
} else {
	echo __('Elevation data could not be retrieved.');
}
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
	echo 'Die H&ouml;hendaten wurden korrigiert.';

	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
	echo Ajax::getReloadCommand();
	echo Ajax::wrapJS('if($("#ajax").is(":visible"))Runalyze.loadOverlay(\''.$Training->Linker()->editUrl().'\')');
} else {
	echo 'Es konnten keine H&ouml;hendaten empfangen werden.';
}
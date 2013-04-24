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
	echo 'Es ist ein Problem aufgetreten.';
} else {
	echo 'Die H&ouml;hendaten wurden korrigiert.';

	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
	echo Ajax::getReloadCommand();
}
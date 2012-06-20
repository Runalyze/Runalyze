<?php
/**
 * File for correction the elevation data of this training.
 * Call:   call/call.Training.elevationCorrection.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$Training = new Training($_GET['id']);
$Training->elevationCorrection();

if (Error::getInstance()->hasErrors())
	echo 'Es ist ein Problem aufgetreten.';
else
	echo 'Die H&ouml;hendaten wurden korrigiert.';
?>
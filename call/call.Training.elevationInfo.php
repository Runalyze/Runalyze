<?php
/**
 * File for displaying elevation info for a training.
 * Call:   call.Training.elevationInfo.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$ElevationInfo = new ElevationInfo(new TrainingObject(Request::sendId()));
$ElevationInfo->display();
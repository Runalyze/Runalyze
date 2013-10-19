<?php
/**
 * File for displaying rounds info for a training.
 * Call:   call.Training.roundsInfo.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$RoundsInfo = new RoundsInfo(new TrainingObject(Request::sendId()));
$RoundsInfo->display();
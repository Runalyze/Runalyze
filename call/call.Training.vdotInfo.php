<?php
/**
 * File for displaying a training.
 * Call:   call.Training.vdotInfo.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$VDOTinfo = new VDOTinfo(new TrainingObject(Request::sendId()));
$VDOTinfo->display();
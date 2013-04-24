<?php
/**
 * File for displaying a training.
 * Call:   call.Training.display.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$View = new TrainingView(new TrainingObject(Request::sendId()));
$View->display();
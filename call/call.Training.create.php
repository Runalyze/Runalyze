<?php
/**
 * File for displaying the formular for creating a new training.
 * Call:   call.Training.create.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

System::setMaximalLimits();

Training::displayCreateWindow();
?>
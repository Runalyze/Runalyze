<?php
/**
 * File for displaying the formular for creating a new training.
 * Call:   call.Training.create.php
 */
require '../inc/class.Frontend.php';

ini_set('memory_limit', '-1');
set_time_limit(0);

$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

Training::displayCreateWindow();

$Frontend->displayFooter();
$Frontend->close();
?>
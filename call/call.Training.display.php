<?php
/**
 * File for displaying a training.
 * Call:   call.Training.display.php?id=
 */
require '../inc/class.Frontend.php';

new Frontend();

$Training = new Training($_GET['id']);

if (!$Training->isValid())
	return;

$Training->display();
?>
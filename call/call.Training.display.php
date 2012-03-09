<?php
/**
 * File for displaying a training.
 * Call:   call.Training.display.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Training = new Training($_GET['id']);

if (!$Training->isValid())
	return;

$Training->display();

$Frontend->displayFooter();
$Frontend->close();
?>
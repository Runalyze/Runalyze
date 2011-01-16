<?php
/**
 * File for displaying a training.
 * Call:   class.Training.display.php?id=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Training = new Training($_GET['id']);
$Training->display();

$Frontend->displayFooter();
$Frontend->close();
?>
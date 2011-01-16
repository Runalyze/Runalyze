<?php
/**
 * File for correction the elevation data of this training.
 * Call:   class.Training.elevationCorrection.php?id=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Training = new Training($_GET['id']);
$Training->elevationCorrection();

$Frontend->displayFooter();
$Frontend->close();
?>
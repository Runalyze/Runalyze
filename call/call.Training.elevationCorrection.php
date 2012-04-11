<?php
/**
 * File for correction the elevation data of this training.
 * Call:   call/call.Training.elevationCorrection.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$Training = new Training($_GET['id']);
$Training->elevationCorrection();
?>
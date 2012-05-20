<?php
/**
 * File for displaying a training.
 * Call:   call.SharedTraining.php?url=
 */
require '../inc/class.Frontend.php';
require '../inc/class.FrontendShared.php';

$Frontend = new FrontendShared();
$Frontend->displaySharedView();
?>
<?php
/**
 * File for displaying elevation info for a training.
 * Call:   call.Training.elevationInfo.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\View\Activity\Context;

$Frontend = new Frontend();

$ElevationInfo = new ElevationInfo(new Context(Request::sendId(), SessionAccountHandler::getId()));
$ElevationInfo->display();
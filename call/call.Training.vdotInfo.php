<?php
/**
 * File for displaying a training.
 * Call:   call.Training.vdotInfo.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\View\Activity\Context;

$Frontend = new Frontend();

$VDOTinfo = new VDOTinfo(new Context(Request::sendId(), SessionAccountHandler::getId()));
$VDOTinfo->display();
<?php
/**
 * File for displaying rounds info for a training.
 * Call:   call.Training.roundsInfo.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\View\Activity\Context;
use Runalyze\View\Window\Laps\Window;

$Frontend = new Frontend();

$Window = new Window(new Context(Request::sendId(), SessionAccountHandler::getId()));
$Window->display();
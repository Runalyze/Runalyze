<?php
/**
 * File for displaying a training.
 * Call:   call.Training.display.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\View\Activity\Context;

$Frontend = new Frontend();

$Context = new Context(Request::sendId(), SessionAccountHandler::getId());
$View = new TrainingView($Context);
$View->display();
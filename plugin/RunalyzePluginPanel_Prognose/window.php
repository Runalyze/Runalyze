<?php
/**
 * Window: prognosis
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

require_once 'class.Prognose_PrognosisWindow.php';

$Window = new Prognose_PrognosisWindow();
$Window->display();
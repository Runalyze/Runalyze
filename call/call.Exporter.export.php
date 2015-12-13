<?php
/**
 * File for displaying statistic plugins.
 * Call:   call.Exporter.export.php?id=...[&type=...]
 */
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true);

$View = new ExporterWindow();
$View->display();
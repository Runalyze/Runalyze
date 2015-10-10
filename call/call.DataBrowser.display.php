<?php
/**
 * File for displaying the databrowser.
 * Call:   call.Databrowser.display.php?start=&end=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$DataBrowser = new DataBrowser();
$DataBrowser->display();
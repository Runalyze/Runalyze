<?php
/**
 * File for displaying the databrowser.
 * Call:   call.Databrowser.display.php?start=&end=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$DataBrowser = new DataBrowser();
$DataBrowser->display();

$Frontend->displayFooter();
$Frontend->close();
?>
<?php
/**
 * File for displaying panels.
 * Call:   class.Panel.display.php?id=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Panel = new Panel($_GET['id']);
$Panel->display(false);

#$Frontend->displayFooter();
$Frontend->close();
?>
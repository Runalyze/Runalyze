<?php
/**
 * File for displaying statistic plugins.
 * Call:   class.Stat.display.php?id= [&sport= &jahr= &dat= ]
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Stat = new Stat($_GET['id']);
$Stat->display();

$Frontend->displayFooter();
$Frontend->close();
?>
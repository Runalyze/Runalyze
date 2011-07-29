<?php
/**
 * File for displaying div for tools.
 * Call:   class.PluginTool.display.php [?list=true]
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

if (!isset($_GET['list']))
	PluginTool::displayToolsHeader();
PluginTool::displayToolsContent();

$Frontend->displayFooter();
$Frontend->close();
?>
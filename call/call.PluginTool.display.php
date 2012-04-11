<?php
/**
 * File for displaying div for tools.
 * Call:   call.PluginTool.display.php [?list=true]
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['list']))
	PluginTool::displayToolsHeader();

PluginTool::displayToolsContent();
?>
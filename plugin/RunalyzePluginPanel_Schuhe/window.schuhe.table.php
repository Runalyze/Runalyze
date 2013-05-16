<?php
/**
 * Window: shoes table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Schuhe');
$Plugin->displayTable();
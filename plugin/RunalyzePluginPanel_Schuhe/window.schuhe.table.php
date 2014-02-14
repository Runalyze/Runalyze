<?php
/**
 * Window: shoes table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Schuhe');
echo '<div class="panel-heading">';
echo '<div class="panel-menu"><ul><li>'.$Plugin->addLink().'</li></ul></div>';
echo '<h1>Alle Laufschuhe in der &Uuml;bersicht</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Plugin->displayTable();
echo '</div>';
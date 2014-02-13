<?php
/**
 * Window: shoes table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();


echo '<div class="panel-heading">';
echo '<h1>Alle Laufschuhe in der &Uuml;bersicht</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Schuhe');
$Plugin->displayTable();
echo '</div>';
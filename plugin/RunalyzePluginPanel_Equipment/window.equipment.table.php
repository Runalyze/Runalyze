<?php
/**
 * Window: equipment table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Equipment');

echo '<div class="panel-heading">';
echo '<h1>'.__('Your equipment').'</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Plugin->displayTable();
echo '</div>';
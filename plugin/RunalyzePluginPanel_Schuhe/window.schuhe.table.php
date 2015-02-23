<?php
/**
 * Window: shoes table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Schuhe');

echo '<div class="panel-heading">';
echo '<div class="panel-menu"><ul><li>'.$Plugin->addLink().'</li></ul></div>';
echo '<h1>'.__('Your Shoes').'</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Plugin->displayTable();
echo '</div>';
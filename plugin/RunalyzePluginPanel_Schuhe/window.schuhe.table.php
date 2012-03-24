<?php
require '../../inc/class.Frontend.php';

new Frontend();

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Schuhe');
$Plugin->displayTable();
?>
<?php
include_once('../config/functions.php');
connect();

$panel_db = mysql_query('SELECT `clapped` FROM `ltb_modules` WHERE `id`='.$_GET['id'].' LIMIT 1');
$panel = mysql_fetch_assoc($panel_db);

mysql_query('UPDATE `ltb_modules` SET `clapped`='.($panel['clapped'] == 0 ? 1 : 0).' WHERE `id`='.$_GET['id'].' LIMIT 1');

close();
?>
<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$panel = new Panel($_GET['id']);
	$panel->move($_GET['mode']);
}

$error->display();
?>
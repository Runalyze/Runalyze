<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$key   = Plugin::getKeyForId($_GET['id']);
	$Panel = Plugin::getInstanceFor($key);
	if ($Panel->get('type') == Plugin::$PANEL)
		$Panel->move($_GET['mode']);
}

Error::getInstance()->display();
?>
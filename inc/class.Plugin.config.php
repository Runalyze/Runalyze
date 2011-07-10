<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);
$Frontend->displayHeader();

if (is_numeric($_GET['id'])) {
	$key = Plugin::getKeyForId($_GET['id']);
	$Plugin = Plugin::getInstanceFor($key);
	$Plugin->displayConfigWindow();
} else {
	Error::getInstance()->addError('ID must be set as GET-variable', __FILE__, __LINE__);
	echo('<em>Hier ist etwas schiefgelaufen ...</em>');
}

$Frontend->displayFooter();
?>
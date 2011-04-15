<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$Panel = new Panel($_GET['id']);
	$Panel->displayConfigWindow();
} else {
	Error::getInstance()->addError('ID must be set as GET-variable', __FILE__, __LINE__);
	echo('<em>Hier ist etwas schiefgelaufen ...</em>');
}

echo('<br /><br />');
Error::getInstance()->display();
?>
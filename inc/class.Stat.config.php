<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$stat = new Stat($_GET['id']);
	$stat->displayConfigWindow();
} else {
	Error::getInstance()->add('ERROR','ID must be set as GET-variable',__FILE__,__LINE__);
	echo('<em>Hier ist etwas schiefgelaufen ...</em>');
}

echo('<br /><br />');
Error::getInstance()->display();
?>
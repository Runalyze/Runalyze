<?php
require_once('class.Frontend.php');
$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$panel = new Panel($_GET['id']);
	$panel->displayConfigWindow();
} else {
	$error->add('ERROR','ID must be set as GET-variable',__FILE__,__LINE__);
	echo('<em>Hier ist etwas schiefgelaufen ...</em>');
}

echo('<br /><br />');
$error->display();
?>
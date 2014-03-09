<?php
/**
* Delete Account
 * Call:   call/window.delete.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();
$Errors   = array();
AccountHandler::setAndSendDeletionKeyFor($Errors);

echo HTML::h1('Account l&ouml;schen');

if (!empty($Errors)) {
	foreach ($Errors as $Error)
		echo HTML::error($Error);
} else {
	echo HTML::info('<em>Es wurde eine Best&auml;tigungsmail an dich versandt!</em><br>
		Schade, dass du dich dazu entschieden hast deinen Account zu l&ouml;schen.<br>
		Du bekommst eine Mail mit einem Best&auml;tigungslink zur L&ouml;schung deines Accounts.<br>
		Erst danach ist der Account vollst&auml;ndig gel&ouml;scht.');
}
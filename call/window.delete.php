<?php
/**
* Delete Account
 * Call:   call/window.delete.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();
AccountHandler::setAndSendDeletionKeyFor($errors);
echo '<i class="toolbar-icon-help"></i><em>Es wurde eine Best&auml;tigungsmail an dich versandt!</em><br>';
echo 'Schade, dass du dich dazu entschieden hast deinen Account zu l&ouml;schen.<br>';
echo 'Du bekommst eine Mail mit einem Best&auml;tigungslink zur L&ouml;schung deines Accounts. 
        <br> Erst danach ist der Account vollständig gel&ouml;scht!';
echo 'Wir empfehlen dir eine Sicherung mit dem Datenbank-Backup Tool zu machen und diese herunterzuladen';

?>
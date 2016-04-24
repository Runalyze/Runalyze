<?php
require '../../inc/class.Frontend.php';
require_once __DIR__.'/class.RunalyzeBackupFileHandler.php';
$Frontend = new Frontend(true);

if ($_GET['backup']) {
    RunalyzeBackupFileHandler::download($_GET['backup']);
}
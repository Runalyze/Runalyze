<?php
require_once __DIR__.'/class.RunalyzeBackupFileHandler.php';

if ($_GET['backup']) {
    RunalyzeBackupFileHandler::download($_GET['backup']);
}
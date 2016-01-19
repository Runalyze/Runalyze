<?php
/**
 * File for displaying statistic plugins.
 * Call:   call.Exporter.export.php?id=...[&typeid=...[&social=true]]
 */
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true);

use Runalyze\View\Activity\Context;
use Runalyze\Export\Share;
use Runalyze\Export\File;

if (isset($_GET['social']) && Share\Types::isValidValue((int)$_GET['typeid'])) {
    $Context = new Context((int)$_GET['id'], SessionAccountHandler::getId());
    $Exporter = Share\Types::get((int)$_GET['typeid'], $Context);

    if ($Exporter instanceof Share\AbstractSnippetSharer) {
        $Exporter->display();
    }
} elseif (isset($_GET['file']) && File\Types::isValidValue((int)$_GET['typeid'])) {
    $Context = new Context((int)$_GET['id'], SessionAccountHandler::getId());
    $Exporter = File\Types::get((int)$_GET['typeid'], $Context);

    if ($Exporter instanceof File\AbstractFileExporter) {
        $Exporter->downloadFile();
        exit;
    }
}
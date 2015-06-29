<?php
/**
 * File displaying the config panel
 * Call:   call/window.config.php[?key=...]
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$ConfigTabs = new ConfigTabs();
$ConfigTabs->addDefaultTab(new ConfigTabGeneral());
$ConfigTabs->addTab(new ConfigTabPlugins());
$ConfigTabs->addTab(new ConfigTabDataset());
$ConfigTabs->addTab(new ConfigTabSports());
$ConfigTabs->addTab(new ConfigTabTypes());
$ConfigTabs->addTab(new ConfigTabEquipment());
$ConfigTabs->addTab(new ConfigTabAccount());
$ConfigTabs->display();

echo Ajax::wrapJSforDocumentReady('Runalyze.Overlay.removeClasses();');
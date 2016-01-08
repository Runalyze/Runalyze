<?php
/**
 * RUNALYZE - Updater
 *
 * @author Hannes Christiansen
 * @copyright http://runalyze.laufhannes.de/
 * @package Runylze
 *
 * With this file you are able to update RUNALYZE.
 * Don't change anything in this file!
 */

require_once 'inc/class.Installer.php';
require_once 'inc/class.InstallerUpdate.php';
require_once 'inc/system/class.Request.php';
require_once 'inc/system/class.System.php';
require_once 'inc/html/class.Ajax.php';
require_once 'inc/system/class.Cache.php';

$Updater = new InstallerUpdate();

$title = 'Update: Runalyze v'.RUNALYZE_VERSION;
include 'inc/tpl/tpl.installerHeader.php';

$Updater->display();

include 'inc/tpl/tpl.installerFooter.php';
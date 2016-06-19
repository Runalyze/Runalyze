<?php
use Symfony\Component\Yaml\Yaml;

/**
 * RUNALYZE - Updater
 *
 * @author Hannes Christiansen
 * @copyright https://runalyze.com
 * @package Runalyze
 *
 * With this file you are able to update RUNALYZE.
 * Don't change anything in this file!
 */

if (!file_exists('../data/config.yml') && file_exists('../data/config.php')) {
    //TODO  - maybe automated migration of config file
    die('We have changed the configuration file.');
}

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
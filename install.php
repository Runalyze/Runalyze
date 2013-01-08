<?php
/**
 * RUNALYZE
 *
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://runalyze.laufhannes.de/
 *
 * With this file you are able to install RUNALYZE.
 * Don't change anything in this file!
 */

require_once 'inc/html/class.Ajax.php';
require_once 'inc/system/class.Mysql.php';
require_once 'inc/system/class.Request.php';
require_once 'inc/system/class.System.php';
require_once 'inc/class.Installer.php';

$title = 'Installation: Runalyze';
include 'inc/tpl/tpl.installerHeader.php';

$Installer = new Installer();

include 'inc/tpl/tpl.installerFooter.php';
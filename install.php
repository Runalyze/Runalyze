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

$title = 'Installation: Runalyze';
include 'inc/html/tpl/tpl.installerHeader.php';

require_once 'inc/class.Mysql.php';
require_once 'inc/class.Installer.php';

$Installer = new Installer();

include 'inc/html/tpl/tpl.installerFooter.php';
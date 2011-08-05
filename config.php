<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://runalyze.laufhannes.de/
 * 
 * In this config-file your personal mysql-connection has to be set.
 */
/**
 * Please set the information for connecting to the MySql-databse.
 * These information will be read by 'class.Frontend.php'.
 * 
 * @var string $host       The servername. On local servers: 'localhost'
 * @var string $database   The database used for this project.
 * @var string $username   The username to connect to the database. On local servers it may be 'root'.
 * @var string $passwort   The password for the given user. Please note: Never leave a mysql-user without password.
 */
$host = 'localhost';
$database = 'runalyze_empty';
$username = 'runalyze';
$password = 'runalyze';

/**
 * Please set these global constants.
 * 
 * @var string PREFIX		Database prefix, normally 'runalyze_'
 * @var int ATL_DAYS		Number of days considered for 'Actual Training Load', default 7
 * @var int CTL_DAYS		Number of days considered for 'Chroni Training Load', default 42
 */
define('PREFIX', 'runalyze_');
define('ATL_DAYS', 7); // TODO
define('CTL_DAYS', 42); // TODO

/**
 * To set off debugging, just comment out the line with "//" at the beginning.
 * @var bool RUNALYZE_DEBUG Set to true for debugging mode
 */
define('RUNALYZE_DEBUG', true);
?>
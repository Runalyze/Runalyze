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
$host = '{config::host}';
$database = '{config::database}';
$username = '{config::username}';
$password = '{config::password}';

/**
 * Please set this global constant.
 * @var string PREFIX Database prefix, normally 'runalyze_'
 */
define('PREFIX', '{config::prefix}');

/**
 * To set off debugging, just comment out the line with "//" at the beginning.
 * @var bool RUNALYZE_DEBUG Set to true for debugging mode
 */
{config::debug_slashes}define('RUNALYZE_DEBUG', true);
?>
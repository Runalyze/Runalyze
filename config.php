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
$database = 'runalyze'; // runalyze | runalyze_empty
$username = 'runalyze';
$password = 'runalyze';

/**
 * Please set this global constant.
 * @var string PREFIX Database prefix, normally 'runalyze_'
 */
define('PREFIX', 'runalyze_');

/**
 * To set off debugging, define this constant as false
 * @var bool RUNALYZE_DEBUG Set to true for debugging mode
 */
define('RUNALYZE_DEBUG', true);

/**
 * To force users to login, define this constant as true
 * @var bool USER_MUST_LOGIN It set to true, users have to login
 */
define('USER_MUST_LOGIN', true);
?>
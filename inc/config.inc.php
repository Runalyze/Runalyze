<?php
/**
 * Please set the information for connecting to the MySql-databse.
 * These information will be read by 'class.Frontend.php'.
 * This file has to be in the same folder as 'class.Frontend.php'.
 * 
 * @var   $host       The servername. On local servers: 'localhost'
 * @var   $database   The database used for this project.
 * @var   $username   The username to connect to the database. On local servers it may be 'root'.
 * @var   $passwort   The password for the given user. Please note: Never leave a mysql-user without password.
 */
$host = 'localhost';
$database = 'ltb';
$username = 'd0033d80';
$password = 'fc683f6a';

/**
 * Please set these global constants.
 * 
 * @var   MAINSPORT   The ID of the main sport, normally '1' for 'Laufen'
 * @var   RUNNINGSPORT   The ID of the running sport
 * @var   WK_TYPID    The ID of the competition-type for 'Laufen'
 * @var   LL_TYPID    The ID of the longjog-type for 'Laufen'
 * @var   ATL_DAYS    Number of days considered for 'Actual Training Load', default 7
 * @var   CTL_DAYS    Number of days considered for 'Chroni Training Load', default 42
 */
// TODO Get these information from config-db?
define('MAINSPORT',1);
define('RUNNINGSPORT',1);
define('WK_TYPID',5);
define('LL_TYPID',7);
define('ATL_DAYS',7);
define('CTL_DAYS',42);
?>
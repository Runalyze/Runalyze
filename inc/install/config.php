<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen
 * @copyright http://runalyze.de/
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
 * To set off debugging, define this constant as false
 * @var bool RUNALYZE_DEBUG Set to true for debugging mode
 */
define('RUNALYZE_DEBUG', {config::debug});

/**
 * To force users to login, define this constant as true
 * @var bool USER_MUST_LOGIN It set to true, users have to login
 */
define('USER_MUST_LOGIN', {config::login});

/**
 * Working on your site? Disable login with this variable.
 * @var bool USER_CANT_LOGIN Set to disable login
 */
define('USER_CANT_LOGIN', false);

/**
 * Allow registration for new users
 * @var bool USER_CAN_REGISTER Set to false to close registration
 */
define('USER_CAN_REGISTER', true);

/**
 * Garmin API key is needed for using Garmin Communicator
 * @var bool GARMIN_API_KEY Garmin API key
 * @see http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/
 */
define('GARMIN_API_KEY', '{config::garminkey}');
?>
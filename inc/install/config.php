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
 * @var string $port       The database server port. Standard '3306'
 * @var string $database   The database used for this project.
 * @var string $username   The username to connect to the database. On local servers it may be 'root'.
 * @var string $passwort   The password for the given user. Please note: Never leave a mysql-user without password.
 */
$host = '{config::host}';
$port = '{config::port}';
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
 * Path to perl scripts
 * Relative to FRONTEND_PATH
 * @var string PERL_PATH Path for perl scripts
 */
define('PERL_PATH', '/usr/bin/perl');

/**
 * TTBIN Converter path
 * @var string TTBIN_PATH for TTBIN Converter
 */
define('TTBIN_PATH', FRONTEND_PATH.'../call/perl/ttbincnv');

/**
 * Geonames.org API username
 * @var string GEONAMES_USERNAME for geonames API username
 */
define('GEONAMES_USERNAME', '');

/**
 * Garmin API key is needed for using Garmin Communicator
 * @var bool GARMIN_API_KEY Garmin API key
 * @see http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/
 */
define('GARMIN_API_KEY', '{config::garminkey}');

/**
 * Address for sending mails to users
 * @var string
 */
define('MAIL_SENDER', 'mail@runalyze.de');

/**
 * Sender name for sending mails to users
 * @var string
 */
define('MAIL_NAME', 'Runalyze');


/**
 * OpenWeatherMap: API key
 * @var string OPENWEATHERMAP_API_KEY api key
 * @see http://openweathermap.org/appid
 */
define('OPENWEATHERMAP_API_KEY', '');

/**
 * App-ID for Nokia/Here maps in Leaflet
 * @var string
 * @see https://developer.here.com
 */
define('NOKIA_HERE_APPID', '');

/**
 * Token/App-Code for Nokia/Here maps in Leaflet
 * @var string
 * @see https://developer.here.com
 */
define('NOKIA_HERE_TOKEN', '');

/**
 * Define the mail sending server
 * @var string
 */
define('SMTP_HOST', 'localhost');

/**
 * Define the smtp port
 * @var string
 */
define('SMTP_PORT', '25');

/**
 * Define the smtp encryption
 * @var string
 */
define('SMTP_SECURITY', '');

/**
 * Define the auth username for the smtp server
 * @var string
 */
define('SMTP_USERNAME', '');

/**
 * Define the auth password for the smtp server
 * @var string
 */
define('SMTP_PASSWORD', '');


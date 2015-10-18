<?php
use League\Geotools\Geotools;
use \League\Geotools\Coordinate\Coordinate;
require 'vendor/autoload.php';

/**
 * Script to refactor equipment
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
require 'vendor/autoload.php';
include_once 'config.php';
/**
 * Protect script
 */


if (empty($database) && empty($host)) {
	echo 'Database connection has to be set within the file.'.NL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	try {
		$PDO = new PDO('mysql:dbname='.$database.';host='.$host, $username, $password);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage().NL;
		exit;
	}
}

$Routes = $PDO->query('SELECT id, lats, lngs, geohash, startpoint, endpoint, startpoint_lat, startpoint_lng, endpoint_lat, endpoint_lng, min_lat, min_lng, max_lat, max_lng FROM runalyze_route WHERE lats != "" AND id = 3');

$geotools   = new Geotools();	  
$routePrecision = 7;
while ($Route = $Routes->fetch()) {

    $Startpoint = $geotools->geohash()->decode($Route['startpoint']);
    echo $Route['startpoint_lat'].', '.$Route['startpoint_lng'].' | '. $Route['startpoint'].' | '.round($Startpoint->getCoordinate()->getLongitude(), 5).','.round($Startpoint->getCoordinate()->getLatitude(),5)."\n";
   
   $lats = explode('|', $Route['lats']);
   $lngs = explode('|', $Route['lngs']);
   $geohash = explode('|', $Route['geohash']);
   $dur = count($geohash);
   for($i = 0; $i < $dur; $i++) {
       $geo = $geotools->geohash()->decode($geohash[$i]);
       echo $lats[$i].', '.$lngs[$i].' | ';
       echo $geohash[$i]. ' | ';
       if($lats[$i] == round($geo->getCoordinate()->getLatitude(),$routePrecision)) 
	echo "\033[1;32m".round($geo->getCoordinate()->getLatitude(),$routePrecision)."\033[0m"." ,";
       else
	   echo round($geo->getCoordinate()->getLatitude(),$routePrecision).", ";
       
       if($lngs[$i] == round($geo->getCoordinate()->getLongitude(),$routePrecision)) 
	echo "\033[1;32m".round($geo->getCoordinate()->getLongitude(),$routePrecision)."\033[0m"."\n";
       else
	   echo round($geo->getCoordinate()->getLongitude(), $routePrecision)."\n";
       
   }
}   
	    
	    
	    

/*
$geotools   = new Geotools();
$Routes = $PDO->query('SELECT id, lats, lngs, startpoint_lat, startpoint_lng, endpoint_lat, endpoint_lng, min_lat, min_lng, max_lat, max_lng FROM runalyze_route WHERE lats != ""');
$InsertGeohash = $PDO->prepare('UPDATE '.PREFIX.'route SET `geohash`=:geohash, `startpoint`=:startpoint, `endpoint`=:endpoint, `min`=:min, `max`=:max WHERE `id` = :id');
while ($Route = $Routes->fetch()) {*/

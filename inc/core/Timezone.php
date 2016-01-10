<?php
/**
 * Class: Timezone  
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze
 */

namespace Runalyze;
use \DateTimeZone;
use \DateTime;
use \DB;
use Runalyze\Parameter\Application\Timezone as TimezoneConfiguration;

class Timezone {
    
    public static function setPHPTimezone($timezone) {
	$timezoneName = (new TimezoneConfiguration())->getFullNameByEnum($timezone);
	if(self::isValidTimezone($timezoneName))
	    date_default_timezone_set($timezoneName);
    }
    
    public static function setMysql() {
	DB::getInstance()->exec("SET time_zone = '+00:00'");
    }
    /*
     * Get list of timezones for showing up a list (with identifier as key)
     * @return array
     */
    public static function listTimezones() 
    {
	$timezones = array();
	$timezoneList = (new TimezoneConfiguration())->getMapping();
	foreach($timezoneList as $key => $timezone) {
	    $timezones[$key] = $timezone;
	}
	$timezoneList = array_flip($timezoneList);
	
	$timezone_offsets = array();
	foreach( $timezones as $timezone )
	{
	    $tz = new DateTimeZone($timezone);
	    $timezone_offsets[$tz->getOffset(new DateTime)][] = $timezone;
	}
	ksort($timezone_offsets);

	$timezone_list = array();
	foreach( $timezone_offsets as $offset => $timezones)
	{
	    foreach($timezones as $timezone) {
		$offset_prefix = $offset < 0 ? '-' : '+';
		$offset_formatted = gmdate( 'H:i', abs($offset) );

		$pretty_offset = "UTC${offset_prefix}${offset_formatted}";

		$t = new DateTimeZone($timezone);
		$c = new DateTime(null, $t);
		$current_time = $c->format('g:i A');
		$timezoneId  = constant('Runalyze\Parameter\Application\Timezone::'.strtoupper($timezoneList[$timezone]));
		$timezone_list[$timezoneId] = "(${pretty_offset}) ".str_replace('_', '', $timezone)." - $current_time";
	    }
	}

	return $timezone_list;
    }
    
    /*
     * Check if TimezoneName is valid
     * @return bool
     */
    public static function isValidTimezone($timezone) {
	try {
	    new DateTimeZone($timezone);
	} catch(\Exception $e) {
	    return false;
	}
	    return true;
    }
    
}
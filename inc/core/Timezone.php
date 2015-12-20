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

class Timezone {
    
    public static function setPHPTimezone($timezone) {
	if(self::isValidTimezone($timezone))
	    date_default_timezone_set($timezone);
    }
    
    public static function setMysql($timezone) {
	DB::getInstance()->exec("SET time_zone = ".$timezone);
    }
    /*
     * Get list of timezones for showing up a list (with identifier as key)
     * @return array
     */
    public static function listTimezones() 
    {
	static $regions = array(
	    DateTimeZone::AFRICA,
	    DateTimeZone::AMERICA,
	    DateTimeZone::ANTARCTICA,
	    DateTimeZone::ASIA,
	    DateTimeZone::ATLANTIC,
	    DateTimeZone::AUSTRALIA,
	    DateTimeZone::EUROPE,
	    DateTimeZone::INDIAN,
	    DateTimeZone::PACIFIC,
	);

	$timezones = array();
	foreach( $regions as $region )
	{
	    $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
	}

	$timezone_offsets = array();
	foreach( $timezones as $timezone )
	{
	    $tz = new DateTimeZone($timezone);
	    $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
	}

	asort($timezone_offsets);

	$timezone_list = array();
	foreach( $timezone_offsets as $timezone => $offset )
	{
	    $offset_prefix = $offset < 0 ? '-' : '+';
	    $offset_formatted = gmdate( 'H:i', abs($offset) );

	    $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

	    $t = new DateTimeZone($timezone);
	    $c = new DateTime(null, $t);
	    $current_time = $c->format('g:i A');

	    $timezone_list[$timezone] = "(${pretty_offset}) ".str_replace('_', '', $timezone)." - $current_time";
	}

	return $timezone_list;
    }
    
    /*
     * Check if TimezoneID is valid
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
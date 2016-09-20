<?php
/**
 * This file contains class::Timezone
 * @package Runalyze
 */

namespace Runalyze;

use \DateTimeZone;
use \DateTime;
use \DB;
use Runalyze\Parameter\Application\Timezone as TimezoneEnum;
use Runalyze\Util\InterfaceChoosable;

/**
 * Class: Timezone
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze
 */
class Timezone implements InterfaceChoosable
{

    public static function getChoices()
    {
        return array_flip(self::listTimezones());
    }

    /**
     * Set server timezone to given enum
     *
     * This sets only a default timezone if the given identifier is valid
     *
     * @param int $timezone
     */
    public static function setPHPTimezone($timezone)
    {
        if (TimezoneEnum::isValidValue((int)$timezone)) {
            $timezoneName = TimezoneEnum::getFullNameByEnum((int)$timezone);

            if (self::isValidTimezone($timezoneName)) {
                date_default_timezone_set($timezoneName);
            }
        }
    }

    /**
     * Set MySQL timezone to UTC
     */
    public static function setMysql()
    {
        DB::getInstance()->exec("SET time_zone = '+00:00'");
    }

    /**
     * Get list of timezones for showing up a list (with identifier as key)
     * @return array [id => pretty string], internal id's from enum und strings with offset
     */
    public static function listTimezones()
    {
        $timezones = array();
        $timezoneList = TimezoneEnum::getMapping();
        foreach ($timezoneList as $key => $timezone) {
            $timezones[$key] = $timezone;
        }
        $timezoneList = array_flip($timezoneList);

        $currentTime = new DateTime;
        $timezone_offsets = array();
        foreach ($timezones as $timezone) {
            try {
                $timezone_offsets[(new DateTimeZone($timezone))->getOffset($currentTime)][] = $timezone;
            } catch (\Exception $e) {
                // Invalid time zone
            }
        }
        ksort($timezone_offsets);

        return self::listPrettyTimezones($timezone_offsets, $timezoneList);
    }

    /**
     * @param array $timezoneOffsets [offset => [timezone_id_1, timezone_id_2, ...]]
     * @param array $timezoneEnum [timezone_id => enum]
     * @return array [timezone_id => "(UTC+XX:XX) Timezone/Name - HH:MM"]
     */
    private static function listPrettyTimezones(array $timezoneOffsets, array $timezoneEnum)
    {
        foreach ($timezoneOffsets as $offset => $timezones) {
            foreach ($timezones as $timezone) {
                $offsetPrefix = $offset < 0 ? '-' : '+';
                $offsetFormatted = gmdate('H:i', abs($offset));

                $prettyOffset = 'UTC'.$offsetPrefix.$offsetFormatted;

                $currentTime = (new DateTime(null, new DateTimeZone($timezone)))->format('g:i A');
                $timezoneId = constant('Runalyze\Parameter\Application\Timezone::'.strtoupper($timezoneEnum[$timezone]));
                $timezoneList[$timezoneId] = '('.$prettyOffset.') '.str_replace('_', '', $timezone).' - '.$currentTime;
            }
        }

        return $timezoneList;
    }

    /**
     * Check if TimezoneName is valid
     * @param string $timezone
     * @return bool
     */
    public static function isValidTimezone($timezone)
    {
        try {
            new DateTimeZone($timezone);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
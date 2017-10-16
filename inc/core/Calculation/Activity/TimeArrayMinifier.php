<?php
/**
 * This file contains class::TimeArrayMinifier
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

class TimeArrayMinifier
{
    /**
     * @param array $timeArray
     * @return array
     */
    public static function extend(array $timeArray)
    {
        $extended = [];
        $extended[] = $timeArray[0];
        $lastValue = $timeArray[0];
        foreach ($timeArray as $key => $seconds) {
            if (isset($timeArray[$key + 1])) {
                $lastValue = (int)$timeArray[$key + 1] + $lastValue;
                $extended[] = $lastValue;
            }
        }
        return $extended;
    }

    /**
     * @param array $timeArray
     * @return array
     */
    public static function shorten(array $timeArray)
    {
        $shortend = [];
        $shortend[] = $timeArray[0];
        foreach ($timeArray as $key => $seconds) {
            if (isset($timeArray[$key + 1])) {
                $shortend[] = (int)$timeArray[$key + 1] - $seconds;
            }
        }
        return $shortend;
    }

}
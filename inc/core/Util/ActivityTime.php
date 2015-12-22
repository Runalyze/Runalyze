<?php
/**
 * This file contains class::Time
 * @package Runalyze\Util
 */

namespace Runalyze\Util;
use Runalyze\Configuration;

/**
 * Class for standard operations for timestamps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Util
 */
class ActivityTime extends Time {
    
	/**
	 * Get the timestamp of the start of the week
	 * @param int $time
	 */
	public static function weekstart($time) {
                $date = new \DateTime($date, new \DateTimeZone('UTC'));
                $date->setTimestamp($time);
                $w = $date->format("w");
                
		if (Configuration::General()->weekStart()->isMonday()) {
			if ($w == 0) {
				$w = 6;
			} else {
				$w -= 1;
			}
		}
                $date->setTime(0, 0, 0);
                return $date->getTimestamp() - ($w * 86400);
	}
	
	/**
	 * Get the timestamp of the end of the week
	 * @param int $time
	 */
	public static function weekend($time) {
		    $start = self::weekstart($time);
                    $date = new \DateTime($date, new \DateTimeZone('UTC'));
                    $date->setTimestamp($start);
                    $date->add(new \DateInterval('P7D'));
                return $date->getTimestamp();
	}
	
}
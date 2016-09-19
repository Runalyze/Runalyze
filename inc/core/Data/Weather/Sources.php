<?php
/**
 * This file contains class::Sources
 * @package Runalyze
 */

namespace Runalyze\Data\Weather;

use Runalyze\Util\AbstractEnum;

/**
 * Enum for weather sources and their internal ids
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
final class Sources extends AbstractEnum
{
	/** @var int */
	const OPENWEATHERMAP = 1;
	
	/** @var int */
	const DBWEATHERCACHE = 2;

    /** @var int */
    const FORECASTIO = 3;

	/**
	 * @param int $sourceId id from internal enum
	 * @return string
	 */
	static public function stringFor($sourceId)
	{
		switch ($sourceId) {
            case self::FORECASTIO:
                return '<a href="http://forecast.io/" target="_blank">Powered by Forecast</a>';
			case self::OPENWEATHERMAP:
				return '<a href="http://openweathermap.org/" target="_blank">openweathermap.org</a>';
			case self::DBWEATHERCACHE:
				return __('internal database');
			default:
				throw new \InvalidArgumentException('Invalid source id');
		}
	}
}
<?php
/**
 * This file contains class::Sources
 * @package Runalyze
 */

namespace Runalyze\Data\Weather;

use Runalyze\Common\Enum\AbstractEnum;

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
    const DARKSKY = 3;

	/**
	 * @param int $sourceId id from internal enum
	 * @return string
	 */
	static public function stringFor($sourceId)
	{
		switch ($sourceId) {
            case self::DARKSKY:
                return '<a href="https://darksky.net/poweredby/" target="_blank">Powered by Dark Sky</a>';
			case self::OPENWEATHERMAP:
				return '<a href="http://openweathermap.org/" target="_blank">openweathermap.org</a>';
			case self::DBWEATHERCACHE:
				return __('internal database');
			default:
				throw new \InvalidArgumentException('Invalid source id');
		}
	}
}

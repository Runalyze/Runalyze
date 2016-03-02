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

	/**
	 * @param int $sourceId id from internal enum
	 * @return string
	 */
	static public function stringFor($sourceId)
	{
		switch ($sourceId) {
			case self::OPENWEATHERMAP:
				return '<a href="http://openweathermap.org/" target="_blank">openweathermap.org</a>';
			default:
				throw new \InvalidArgumentException('Invalid source id');
		}
	}
}
<?php
/**
 * This file contains class::Types
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use Runalyze\Util\AbstractEnum;
use Runalyze\View\Activity\Context;

/**
 * Enum for export file types and their internal ids
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
final class Types extends AbstractEnum
{
	/** @var int */
	const TCX = 1;

	/** @var int */
	const GPX = 2;

	/** @var int */
	const KML = 3;

	/** @var int */
	const FITLOG = 4;

	/**
	 * Get exporter
	 * @param int $typeid int from internal enum
	 * @param \Runalyze\View\Activity\Context $context
	 * @return \Runalyze\Export\File\AbstractFileExporter
	 * @throws \InvalidArgumentException
	 */
	public static function get($typeid, Context $context)
	{
		$classNames = self::classNamesArray();

		if (!isset($classNames[$typeid])) {
			throw new \InvalidArgumentException('Invalid type id "'.$typeid.'".');
		}

		$className = 'Runalyze\\Export\\File\\'.$classNames[$typeid];

		return new $className($context);
	}

	/**
	 * Get array with class names
	 * @return array
	 */
	private static function classNamesArray()
	{
		return array(
			self::TCX => 'Tcx',
			self::GPX => 'Gpx',
			self::KML => 'Kml',
			self::FITLOG => 'Fitlog',
		);
	}
}
<?php
/**
 * This file contains class::Types
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

use Runalyze\Util\AbstractEnum;
use Runalyze\View\Activity\Context;

/**
 * Enum for share types and their internal ids
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Share
 */
final class Types extends AbstractEnum
{
	/** @var int */
	const TWITTER = 1;

	/** @var int */
	const FACEBOOK = 2;

	/** @var int */
	const GOOGLE_PLUS = 3;

	/** @var int */
	const HTML = 4;

	/** @var int */
	const IFRAME = 5;

	/**
	 * Get sharer
	 * @param int $typeid int from internal enum
	 * @param \Runalyze\View\Activity\Context $context
	 * @return \Runalyze\Export\Share\AbstractSharer
	 * @throws \InvalidArgumentException
	 */
	public static function get($typeid, Context $context)
	{
		$classNames = self::classNamesArray();

		if (!isset($classNames[$typeid])) {
			throw new \InvalidArgumentException('Invalid type id "'.$typeid.'".');
		}

		$className = 'Runalyze\\Export\\Share\\'.$classNames[$typeid];

		return new $className($context);
	}

	/**
	 * Get array with class names
	 * @return array
	 */
	private static function classNamesArray()
	{
		return array(
			self::TWITTER => 'Twitter',
			self::FACEBOOK => 'Facebook',
			self::GOOGLE_PLUS => 'GooglePlus',
			self::HTML => 'Html',
			self::IFRAME => 'IFrame',
		);
	}
}
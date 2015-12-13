<?php
/**
 * This file contains class::Route
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Model\Activity;
use Runalyze\Dataset\Context;

/**
 * Dataset key: Route
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Route extends AbstractKey
{
	/** @var int */
	const DEFAULT_CUT = 20;

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::ROUTE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'routeid';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Route');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return sprintf(
			__('Route names are automatically cut after %u characters.'),
			self::DEFAULT_CUT
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->activity()->get(Activity\Entity::ROUTEID) > 0) {
			return \Helper::Cut(
				$context->factory()->route($context->activity()->get(Activity\Entity::ROUTEID))->name(),
				self::DEFAULT_CUT
			);
		}

		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small l';
	}
}
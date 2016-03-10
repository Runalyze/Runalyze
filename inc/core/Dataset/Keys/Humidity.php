<?php
/**
 * This file contains class::Humidity
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Humidity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Humidity extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::HUMIDITY;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'humidity';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Humidity');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if (
			!$context->activity()->weather()->humidity()->isUnknown() &&
			!($context->hasSport() && !$context->sport()->isOutside())
		) {
			return $context->activity()->weather()->humidity()->string();
		}

		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small';
	}
}
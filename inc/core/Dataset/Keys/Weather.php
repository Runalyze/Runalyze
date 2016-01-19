<?php
/**
 * This file contains class::Weather
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Weather
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Weather extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::WEATHER;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return ['weatherid', 'is_night'];
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Weather');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return '';
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if (!$context->activity()->weather()->condition()->isUnknown() && ($context->hasSport() || $context->sport()->isOutside())) {
			$icon = $context->activity()->weather()->condition()->icon();

			if ($context->activity()->isNight()) {
				$icon->setAsNight();
			}

			return $icon->code();
		}

		return '';
	}
}
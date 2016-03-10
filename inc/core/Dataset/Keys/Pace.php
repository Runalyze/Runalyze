<?php
/**
 * This file contains class::Pace
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Activity;
use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Pace
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Pace extends AbstractKey
{
	/** @var string */
	const DURATION_SUM_WITH_DISTANCE_KEY = 's_sum_with_distance';

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::PACE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return '';
	}

	/**
	 * @return bool
	 */
	public function isInDatabase()
	{
		return false;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Pace');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->activity()->distance() > 0) {
			if ($context->hasData(self::DURATION_SUM_WITH_DISTANCE_KEY))  {
				if ($context->data(self::DURATION_SUM_WITH_DISTANCE_KEY) > 0) {
					$Pace = new Activity\Pace(
						$context->data(self::DURATION_SUM_WITH_DISTANCE_KEY),
						$context->activity()->distance(),
						$context->hasSport() ? $context->sport()->paceUnitEnum() : Activity\Pace::STANDARD
					);

					return $Pace->valueWithAppendix();
				}

				return '';
			}

			return $context->dataview()->pace()->valueWithAppendix();
		}

		return '';
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::AVG;
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
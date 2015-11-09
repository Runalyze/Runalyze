<?php
/**
 * This file contains class::Distance
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Distance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Distance extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::DISTANCE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'distance';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Distance');
	}

	/**
	 * Get string to display this dataset value
	 * @param Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		// TODO: distance comparison?
		return $context->dataview()->distance();
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::SUM;
	}
}
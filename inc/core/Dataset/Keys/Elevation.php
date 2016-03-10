<?php
/**
 * This file contains class::Elevation
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Elevation extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::ELEVATION;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'elevation';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Elevation');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('Elev.');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Calculated value for total elevation, i.e. maximum of total ascent or descent.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->activity()->elevation() > 0) {
			return $context->dataview()->elevation()->string();
		}

		return '';
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::SUM;
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
<?php
/**
 * This file contains class::Calories
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Calories
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Calories extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::CALORIES;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'kcal';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Calories');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Calories are taken from your device or calculated by a simple formula.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->calories();
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
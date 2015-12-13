<?php
/**
 * This file contains class::Duration
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Duration
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Duration extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::DURATION;
	}

	/**
	 * Is this key always shown?
	 * @return bool
	 * @codeCoverageIgnore
	 */
	public function mustBeShown()
	{
		return true;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 's';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Duration');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->duration()->string();
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::SUM;
	}
}
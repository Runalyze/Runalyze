<?php
/**
 * This file contains class::RPE
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: RPE
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class RPE extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::RPE;
	}

	/**
	 * @return bool
	 */
	public function isInDatabase()
	{
		return true;
	}
	
	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'rpe';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('RPE');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Rating of Perceived Exertion or Borg scale, a scale devised to show perceived exertion during exercise');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
	    return $context->dataview()->rpe();
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::NO;
	}
}
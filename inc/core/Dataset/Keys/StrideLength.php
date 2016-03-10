<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: StrideLength
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class StrideLength extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::STRIDE_LENGTH;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'stride_length';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Stride length');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->activity()->strideLength() > 0) {
			return $context->dataview()->strideLength()->string();
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
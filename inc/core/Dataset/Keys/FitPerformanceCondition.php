<?php
/**
 * This file contains class::FitPerformanceCondition
 * @package Runalyze\Dataset\Keys
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: FitPerformanceCondition
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitPerformanceCondition extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::FIT_PERFORMANCE_CONDITION;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'fit_performance_condition';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Performance Condition').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('PC');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'The performance condition is an assessment, detected by some Garmin devices, '.
			'of your ability to perform compared to your average fitness level.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->fitPerformanceCondition();
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
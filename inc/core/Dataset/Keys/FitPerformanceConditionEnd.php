<?php
/**
 * This file contains class::FitPerformanceConditionEnd
 * @package Runalyze\Dataset\Keys
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: FitPerformanceConditionEnd
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitPerformanceConditionEnd extends AbstractKey
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
		return 'fit_performance_condition_end';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Performance Condition Ending').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('PC End');
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

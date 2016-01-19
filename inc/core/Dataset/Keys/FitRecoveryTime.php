<?php
/**
 * This file contains class::FitRecoveryTime
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: FitRecoveryTime
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitRecoveryTime extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::FIT_RECOVERY_TIME;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'fit_recovery_time';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Recovery time').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('Recovery time');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'Garmin calculates a suggested recovery time based on your '.
			'heart rate variablitity right after the end of your activity.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->fitRecoveryTime();
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
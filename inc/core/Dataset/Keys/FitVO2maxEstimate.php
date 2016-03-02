<?php
/**
 * This file contains class::FitVO2maxEstimate
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: FitVO2maxEstimate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitVO2maxEstimate extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::FIT_VO2MAX_ESTIMATE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'fit_vdot_estimate';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('VO2max').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('VO2max').' '.__('(file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Garmin\'s newer devices have an integrated VO2max estimation.').' '.
			__('This value refers to your estimated VO2max at the start of the activity.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->isRunning()) {
			return $context->dataview()->fitVO2maxEstimate();
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
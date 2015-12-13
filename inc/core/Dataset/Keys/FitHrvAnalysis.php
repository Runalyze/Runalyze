<?php
/**
 * This file contains class::FitHrvAnalysis
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: FitHrvAnalysis
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitHrvAnalysis extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::FIT_HRV_ANALYSIS;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'fit_hrv_analysis';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('HRV score').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('HRV');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'Garmin calculates some HRV score within the first 12 minutes of '.
			'every activity. The value - probably the recovery time in minutes - '.
			'is display as a colored marker, green for low values and red for high values. '.
			'Garmin shows this value as recovery status.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->fitHRVscore();
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
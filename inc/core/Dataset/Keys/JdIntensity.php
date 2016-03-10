<?php
/**
 * This file contains class::JdIntensity
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: JdIntensity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class JdIntensity extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::JD_INTENSITY;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'jd_intensity';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('JD intensity');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('JD');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Intensity points based on Jack Daniels\' Running formula.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->isRunning()) {
			return $context->dataview()->jdIntensityWithStresscolor();
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
}
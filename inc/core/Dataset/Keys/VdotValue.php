<?php
/**
 * This file contains class::VdotValue
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: VdotIcon
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class VdotValue extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::VDOT_VALUE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		if (\Runalyze\Configuration::Vdot()->useElevationCorrection()) {
			return 'vdot_with_elevation';
		}

		return 'vdot';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('VDOT value');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('VDOT');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'Estimated VDOT based on pace and heart rate. '.
			'The value is slightly transparent if it is not used for your VDOT shape.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->isRunning() && $context->dataview()->usedVdot() > 0) {
			if (!$context->activity()->usesVDOT()) {
				return '<span class="unimportant">'.$context->dataview()->vdot()->value().'</span>';
			}

			return $context->dataview()->vdot()->value();
		}

		return '';
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::VDOT;
	}
}
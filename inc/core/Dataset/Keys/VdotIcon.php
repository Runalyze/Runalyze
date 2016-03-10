<?php
/**
 * This file contains class::VdotIcon
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
class VdotIcon extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::VDOT_ICON;
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
		return __('VDOT icon');
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
		$text = __(
			'Each VDOT value of an activity is marked with an arrow '.
			'to show if the value is (much) higher than your current shape, '.
			'equal to it or (much) lower:'
		).' ';

		$Icon = new \Runalyze\View\Icon\VdotIcon;
		$Icon->setUp();
		$text .= $Icon->code();
		$Icon->setUpHalf();
		$text .= $Icon->code();
		$Icon->setRight();
		$text .= $Icon->code();
		$Icon->setDownHalf();
		$text .= $Icon->code();
		$Icon->setDown();
		$text .= $Icon->code();

		return $text;
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->isRunning() && $context->dataview()->usedVdot() > 0) {
			return $context->dataview()->vdotIcon();
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
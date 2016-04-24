<?php
/**
 * This file contains class::FitTrainingEffect
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: FitTrainingEffect
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class FitTrainingEffect extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::FIT_TRAINING_EFFECT;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'fit_training_effect';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Training Effect').' '.__('(by file)');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('TE');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'Training Effect is an indicator between 1.0 (easy) and 5.0 (overreaching) '.
			'to rate the impact of aerobic exercise on your body.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->fitTrainingEffect();
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
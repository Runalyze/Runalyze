<?php
/**
 * This file contains class::Type
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Type
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Type extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::TYPE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'typeid';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Activity type');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('Type');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'The activity type is shown with its abbreviation. '.
			'It is bold if the type is marked as quality session.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->hasType()) {
			if ($context->type()->isQualitySession()) {
				return '<strong>'.$context->type()->abbreviation().'</strong>';
			}

			return $context->type()->abbreviation();
		}

		return '';
	}
}
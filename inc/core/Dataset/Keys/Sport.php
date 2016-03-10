<?php
/**
 * This file contains class::Sport
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Sport
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Sport extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::SPORT;
	}

	/**
	 * Is this key always shown?
	 * @return bool
	 * @codeCoverageIgnore
	 */
	public function mustBeShown()
	{
		return true;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'sportid';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Sport type');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('The sports icon is shown.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->hasSport()) {
			return $context->sport()->icon()->code();
		}

		return '';
	}
}
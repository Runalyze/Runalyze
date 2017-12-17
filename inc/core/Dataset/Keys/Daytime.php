<?php
/**
 * This file contains class::Daytime
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Profile\View\DatasetPrivacyProfile;

/**
 * Dataset key: Daytime
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Daytime extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::DAYTIME;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'time';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Daytime');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('Time');
	}

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function defaultPrivacy() {
        return DatasetPrivacyProfile::PRIVATE_KEY;
    }

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Exact daytime of the activity. (the date itself is always shown)');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return $context->dataview()->daytime();
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'c';
	}
}

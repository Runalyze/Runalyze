<?php
/**
 * This file contains class::SharedLink
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Icon;
use Runalyze\Dataset\Context;

/**
 * Dataset key: SharedLink
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class SharedLink extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::SHARED_LINK;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'is_public';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Public link');
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
		return __(
			'You can decide for each activity whether it is visible for '.
			'everybody or not. If it is public a linked icon is shown.'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->activity()->isPublic()) {
			return '<a href="'.$context->linker()->publicUrl().'" target="_blank">'.Icon::$ATTACH.'</a>';
		}

		return '';
	}
}
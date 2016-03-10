<?php
/**
 * This file contains class::Comment
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Comment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Comment extends AbstractKey
{
	/** @var int */
	const DEFAULT_CUT = 20;

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::COMMENT;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'comment';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Comment');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return sprintf(
			__('Comments are automatically cut after %u characters.'),
			self::DEFAULT_CUT
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		return \Helper::Cut(
			$context->activity()->comment(),
			20
		);
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small l';
	}
}
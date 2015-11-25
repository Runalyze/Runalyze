<?php
/**
 * This file contains class::Splits
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Configuration;
use Runalyze\Dataset\Context;
use Runalyze\View\Icon;

/**
 * Dataset key: Splits
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Splits extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::SPLITS;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'splits';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Splits');
	}

	/**
	 * Get string to display this dataset value
	 * @param Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if (!$context->activity()->splits()->isEmpty()) {
			if (
				$context->activity()->splits()->hasActiveAndInactiveLaps() ||
				round($context->activity()->splits()->totalDistance()) != round($context->activity()->distance()) ||
				($context->hasType() && $context->type()->id() == Configuration::General()->competitionType())
			) {
				// TODO: Icon with tooltip?
				$Icon = new Icon( Icon::CLOCK );
				return $Icon->code();
			}
		}

		return '';
	}
}
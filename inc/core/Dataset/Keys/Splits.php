<?php
/**
 * This file contains class::Splits
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Activity;
use Runalyze\Configuration;
use Runalyze\Dataset\Context;
use Runalyze\Model\Activity\Splits\Entity;
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
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __(
			'Splits are shown as clock icon with all lap times as tooltip. '.
			'They are shown only if there are handmade laps, '.
			'i.e. there are active and inactive laps, or if it was a race. '
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
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
				$Icon = new Icon( Icon::CLOCK );
				$Icon->setTooltip($this->stringForActiveLaps($context->activity()->splits()));

				return $Icon->code();
			}
		}

		return '';
	}

	/**
	 * @param \Runalyze\Model\Activity\Splits\Entity $Splits
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function stringForActiveLaps(Entity $Splits)
	{
		$laps = [];
		$onlyActiveSplits = $Splits->hasActiveAndInactiveLaps();

		foreach ($Splits->asArray() as $Split) {
			if (!$onlyActiveSplits || $Split->isActive()) {
				$laps[] = Activity\Duration::format($Split->time());
			}
		}

		return str_replace('&nbsp;', ' ', implode(' / ', $laps));
	}
}
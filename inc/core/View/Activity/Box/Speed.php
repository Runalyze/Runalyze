<?php
/**
 * This file contains class::Speed
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity\PaceUnit;
use Runalyze\Configuration;

/**
 * Boxed value for speed
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Speed extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$Pace = clone $Context->dataview()->pace();

		if (Configuration::General()->distanceUnitSystem()->isImperial()) {
			$Pace->setUnit(new PaceUnit\MilesPerHour());
		} else {
			$Pace->setUnit(new PaceUnit\KmPerHour());
		}

		parent::__construct(
			$Pace->value(),
			$Pace->appendix(),
			'&oslash; '.__('Speed')
		);
	}
}
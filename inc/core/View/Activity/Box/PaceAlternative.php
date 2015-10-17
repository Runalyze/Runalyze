<?php
/**
 * This file contains class::PaceAlternative
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity\PaceUnit;
use Runalyze\Configuration;

/**
 * Boxed value for pace alternative (instead of speed, if pace is already in a decimal unit)
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class PaceAlternative extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$Pace = clone $Context->dataview()->pace();

		if (Configuration::General()->distanceUnitSystem()->isImperial()) {
			$Pace->setUnit(new PaceUnit\MinPerMile());
		} else {
			$Pace->setUnit(new PaceUnit\MinPerKilometer());
		}

		parent::__construct(
			$Pace->value(),
			$Pace->appendix(),
			'&oslash; '.__('Pace')
		);
	}
}
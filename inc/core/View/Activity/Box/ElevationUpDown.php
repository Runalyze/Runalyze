<?php
/**
 * This file contains class::ElevationUpDown
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity\Elevation as ElevationBase;
use Runalyze\Configuration;

/**
 * Boxed value for elevation up/down
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class ElevationUpDown extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		if ($Context->hasRoute()) {
			$upDown = '+'.ElevationBase::format($Context->route()->elevationUp(), false).'/-'.ElevationBase::format($Context->route()->elevationDown(), false);
		} else {
			$upDown = '+'.ElevationBase::format($Context->activity()->elevation(), false).'/-'.ElevationBase::format($Context->activity()->elevation(), false);
		}

		parent::__construct(
			$upDown,
			Configuration::General()->distanceUnitSystem()->elevationUnit(),
			__('Elevation up/down')
		);
	}
}
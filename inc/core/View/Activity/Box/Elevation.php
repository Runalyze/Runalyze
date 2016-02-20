<?php
/**
 * This file contains class::Elevation
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

/**
 * Boxed value for elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Elevation extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			$Context->dataview()->elevation()
		);
	}
}
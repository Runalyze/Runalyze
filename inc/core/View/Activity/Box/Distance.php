<?php
/**
 * This file contains class::Distance
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;
use Runalyze\View\Activity\Context;

/**
 * Boxed value for distance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Distance extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\Distance($Context->activity()->distance())
		);
	}
}
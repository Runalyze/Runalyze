<?php
/**
 * This file contains class::PoolLength
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;
use Runalyze\View\Activity\Context;

/**
 * Boxed value for pool length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class PoolLength extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\PoolLength($Context->swimdata()->poollength())
		);
	}
}
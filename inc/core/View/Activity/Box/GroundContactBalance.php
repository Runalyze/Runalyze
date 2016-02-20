<?php
/**
 * This file contains class::GroundContactBalance
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;
use Runalyze\View\Activity\Context;

/**
 * Boxed value for ground contact balance 
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class GroundContactBalance extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\GroundcontactBalance($Context->activity()->groundContactBalance())
		);
	}
}
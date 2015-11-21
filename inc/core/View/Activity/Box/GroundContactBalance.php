<?php
/**
 * This file contains class::GroundContactBalance
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Configuration;

/**
 * Boxed value for ground contact balance 
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class GroundContactBalance extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$leftRight = ($Context->activity()->groundContactBalanceLeft()/100).'L/'.($Context->activity()->groundContactBalanceRight()/100).'R';
		

		parent::__construct(
			$leftRight,
			'%',
			__('Ground Contact Balance')
		);
	}
}
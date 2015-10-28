<?php
/**
 * This file contains class::PoolLength
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;

/**
 * Boxed value for pool length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class PoolLength extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$PoolLength = new Activity\PoolLength($Context->swimdata()->poollength());

		parent::__construct(
			\Helper::Unknown($PoolLength->valueInPreferredUnit(), '-'),
			$PoolLength->unit(),
			$PoolLength->label()
		);
	}
}
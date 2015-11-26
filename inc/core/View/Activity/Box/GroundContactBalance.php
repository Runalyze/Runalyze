<?php
/**
 * This file contains class::GroundContactBalance
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

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
		parent::__construct(
			\Runalyze\Activity\GroundcontactBalance::format($Context->activity()->groundContactBalance(), false),
			'%',
			__('Ground Contact Balance')
		);
	}
}
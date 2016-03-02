<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

/**
 * Boxed value for stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class StrideLength extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			$Context->dataview()->strideLength()
		);
	}
}
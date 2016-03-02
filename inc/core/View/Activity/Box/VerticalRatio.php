<?php
/**
 * This file contains class::VerticalRatio
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;
use Runalyze\View\Activity\Context;

/**
 * Boxed value for vertical ratio
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class VerticalRatio extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\VerticalRatio($Context->activity()->verticalRatio())
		);
	}
}
<?php
/**
 * This file contains class::Energy
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;
use Runalyze\Activity;

/**
 * Boxed value for energy
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class Energy extends ValueBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\Energy($Context->activity()->energy())
		);
		
	}
}

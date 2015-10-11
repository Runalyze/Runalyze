<?php
/**
 * This file contains class::Distance
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for distance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Distance extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$Distance = new \Runalyze\Activity\Distance($Context->activity()->distance());

		parent::__construct(
			\Helper::Unknown(round($Distance->valueInPreferredUnit(), 2), '-'),
			$Distance->unit(),
			$Distance->label()
		);
	}
}
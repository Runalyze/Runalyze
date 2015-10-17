<?php
/**
 * This file contains class::Pace
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for pace
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Pace extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$Pace = $Context->dataview()->pace();

		parent::__construct(
			$Pace->value(),
			$Pace->appendix(),
			'&oslash; '.($Pace->unit()->isTimeFormat() ? __('Pace') : __('Speed'))
		);
	}
}
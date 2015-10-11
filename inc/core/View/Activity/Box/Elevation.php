<?php
/**
 * This file contains class::Elevation
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Elevation extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$Elevation = $Context->dataview()->elevation();

		parent::__construct(
			\Helper::Unknown($Elevation->valueInPreferredUnit(), '-'),
			$Elevation->unit(),
			$Elevation->label()
		);
	}
}
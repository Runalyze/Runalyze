<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class StrideLength extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$StrideLength = $Context->dataview()->strideLength();

		parent::__construct(
			\Helper::Unknown($StrideLength->valueInPreferredUnit(), '-'),
			$StrideLength->unit(),
			$StrideLength->label()
		);
	}
}
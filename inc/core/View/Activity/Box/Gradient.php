<?php
/**
 * This file contains class::Gradient
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for gradient
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Gradient extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		parent::__construct(
			\Helper::Unknown(substr($Context->dataview()->gradientInPercent(), 0, -11), '-'),
			'&#37;',
			__('&oslash; Gradient')
		);
	}
}
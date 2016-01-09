<?php
/**
 * This file contains class::WeatherWindChillFactor
* @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

/**
 * Boxed value for Pressure
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherWindChillFactor extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$WindChillFactor = $Context->activity()->windChillFactor();
		parent::__construct(
			\Helper::Unknown($WindChillFactor->valueInPreferredUnit(), '-'),
			$WindChillFactor->unit(),
			$WindChillFactor->label()
		);
	}
}
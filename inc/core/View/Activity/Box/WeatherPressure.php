<?php
/**
 * This file contains class::WeatherPressure
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
class WeatherPressure extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$pressure = $Context->activity()->weather()->pressure();
		parent::__construct(
			\Helper::Unknown($pressure->string(false), '-'),
			$pressure->unit(),
			$pressure->label()
		);
	}
}
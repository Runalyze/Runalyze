<?php
/**
 * This file contains class::WeatherWindDegree 
* @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;
use Runalyze\Data\Weather\CardinalDirection;

/**
 * Boxed value for Wind Degree
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Box
 */
class WeatherWindDegree extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$WindDirection = $Context->activity()->weather()->windDegree();
		$CardinalDirection = CardinalDirection::getDirection($WindDirection->value());
		parent::__construct(
			\Helper::Unknown($CardinalDirection.' / '.$WindDirection->string(false), '-'),
			$WindDirection->unit(),
			$WindDirection->label()
		);
	}
}
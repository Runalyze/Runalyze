<?php
/**
 * This file contains class::DistanceUnitSystem
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

use Runalyze\Activity\Distance;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\StrideLength;

/**
 * System for distance units (metric / imperial)
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Parameter\Application
 */
class DistanceUnitSystem extends \Runalyze\Parameter\Select
{
	/**
	 * Metric system (km, m, cm)
	 * @var string
	 */
	const METRIC = 'metric';

	/**
	 * Imperial system (miles, feet, yards)
	 * @var string
	 */
	const IMPERIAL = 'imperial';
        
	/**
	 * Factor: km => miles
	 * @var double 
	 */
	const MILE_MULTIPLIER = 0.621371192;

	/**
	 * Factor: km => yards
	 * @var double 
	*/
	const YARD_MULTIPLIER = 1093.6133;

	/**
	 * Factor: km => feet
	 * @var double
	 */
	const FEET_MULTIPLIER = 3280.84;

	/**
	 * Unit: km
	 * @var string
	 */
	const KM = 'km';

	/**
	 * Unit: meter
	 * @var string
	 */
	const METER = 'm';

	/**
	 * Unit: cm
	 * @var string
	 */
	const CM = 'cm';

	/**
	 * Unit: miles
	 * @var string
	 */
	const MILES = 'mi';

	/**
	 * Unit: yards
	 * @var string
	 */
	const YARDS = 'yd';

	/**
	 * Unit: feet
	 * @var string
	 */
	const FEET = 'ft';

	/**
	 * Construct
	 * @param string $default from class enum
	 */
	public function __construct($default = self::METRIC)
	{
		parent::__construct($default, [
			'options'		=> [
				self::METRIC	=> __('Metric units'),
				self::IMPERIAL	=> __('Imperial units')
			]
		]);
	}

	/**
	 * @return bool
	 */
	public function isMetric()
	{
		return ($this->value() == self::METRIC);
	}

	/**
	 * @return bool
	 */
	public function isImperial()
	{
		return ($this->value() == self::IMPERIAL);
	}

	/**
	 * @return float
	 */
	public function distanceToKmFactor()
	{
		if ($this->isImperial()) {
			return 1 / self::MILE_MULTIPLIER;
		}

		return 1;
	}

	/**
	 * @return float
	 */
	public function distanceToPreferredUnitFactor()
	{
		if ($this->isImperial()) {
			return self::MILE_MULTIPLIER;
		}

		return 1;
	}

	/**
	 * @return string
	 */
	public function distanceUnit()
	{
		return (new Distance(0, $this))->unit();
	}

	/**
	 * @return string
	 */
	public function elevationUnit()
	{
		return (new Elevation(0, $this))->unit();
	}

	/**
	 * @return string
	 */
	public function strideLengthUnit()
	{
		return (new StrideLength(0, $this))->unit();
	}
}
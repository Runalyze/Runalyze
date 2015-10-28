<?php
/**
 * This file contains class::PaceUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

use Runalyze\Activity\PaceUnit as PaceUnitObject;

/**
 * Pace unit
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class PaceUnit extends \Runalyze\Parameter\Select
{
	/**
	 * Speed unit km/h
	 * @var string
	 */
	const KM_PER_H = 'km/h';
	
	/**
	 * Speed unit mph
	 * @var string
	 */
	const MILES_PER_H = 'mph';

	/**
	 * Speed unit min/km
	 * @var string
	 */
	const MIN_PER_KM = 'min/km';
	
	/**
	 * Speed unit min/mile
	 * @var string
	 */
	const MIN_PER_MILE = 'min/mi';

	/**
	 * Speed unit m/s
	 * @var string
	 */
	const M_PER_S = 'm/s';

	/**
	 * Speed unit min/100m
	 * @var string
	 */
	const MIN_PER_100M = 'min/100m';
        
	/**
	 * Speed unit min/100y
	 * @var string
	 */
	const MIN_PER_100Y = 'min/100y';

	/**
	 * Speed unit min/500m
	 * @var string
	 */
	const MIN_PER_500M = 'min/500m';

	/**
	 * Speed unit min/500y
	 * @var string
	 */
	const MIN_PER_500Y = 'min/500y';

	/**
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $UnitObject = null;

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct(self::KM_PER_H, array(
			'options'		=> array(
				self::KM_PER_H => self::KM_PER_H,
				self::MILES_PER_H => self::MILES_PER_H,
				self::MIN_PER_KM => self::MIN_PER_KM,
				self::MIN_PER_MILE => self::MIN_PER_MILE,
				self::M_PER_S => self::M_PER_S,
				self::MIN_PER_100M => self::MIN_PER_100M,
				self::MIN_PER_100Y => self::MIN_PER_100Y,
				self::MIN_PER_500M => self::MIN_PER_500M,
				self::MIN_PER_500Y => self::MIN_PER_500Y
			)
		));
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws \InvalidArgumentException
	 */
	public function set($value)
	{
		parent::set($value);

		$this->UnitObject = $this->getNewPaceUnitObject($value);
	}

	/**
	 * @param string $value
	 * @return \Runalyze\Activity\PaceUnit\AbstractUnit
	 * @throws \InvalidArgumentException
	 */
	protected function getNewPaceUnitObject($value)
	{
		switch ($value) {
			case self::KM_PER_H:
				return new PaceUnitObject\KmPerHour();
			case self::MILES_PER_H:
				return new PaceUnitObject\MilesPerHour();
			case self::MIN_PER_KM:
				return new PaceUnitObject\MinPerKilometer();
			case self::MIN_PER_MILE:
				return new PaceUnitObject\MinPerMile();
			case self::M_PER_S:
				return new PaceUnitObject\MeterPerSecond();
			case self::MIN_PER_100M:
				return new PaceUnitObject\MinPer100m();
			case self::MIN_PER_100Y:
				return new PaceUnitObject\MinPer100y();
			case self::MIN_PER_500M:
				return new PaceUnitObject\MinPer500m();
			case self::MIN_PER_500Y:
				return new PaceUnitObject\MinPer500y();
		}

		throw new \InvalidArgumentException('Invalid value "'.$value.'" for pace unit.');
	}

	/**
	 * Unit object
	 * @return \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	public function object()
	{
		return $this->UnitObject;
	}
}
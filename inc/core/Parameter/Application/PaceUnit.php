<?php
/**
 * This file contains class::PaceUnit
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

use Runalyze\Activity\PaceUnit as PaceUnitObject;
use Runalyze\Util\AbstractEnum;
use Runalyze\Util\InterfaceChoosable;

/**
 * Pace unit
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class PaceUnit extends AbstractEnum
{
	/**
	 * Speed unit km/h
	 * @var string
	 */
	const KM_PER_H = 0;

	/**
	 * Speed unit mph
	 * @var string
	 */
	const MILES_PER_H = 1;

	/**
	 * Speed unit min/km
	 * @var string
	 */
	const MIN_PER_KM = 2;

	/**
	 * Speed unit min/mile
	 * @var string
	 */
	const MIN_PER_MILE = 3;

	/**
	 * Speed unit m/s
	 * @var string
	 */
	const M_PER_S = 4;

	/**
	 * Speed unit min/100m
	 * @var string
	 */
	const MIN_PER_100M = 5;

	/**
	 * Speed unit min/100y
	 * @var string
	 */
	const MIN_PER_100Y = 6;

	/**
	 * Speed unit min/500m
	 * @var string
	 */
	const MIN_PER_500M = 7;

	/**
	 * Speed unit min/500y
	 * @var string
	 */
	const MIN_PER_500Y = 8;

	/**
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $UnitObject = null;

    /**
     * @param int $id id from internal enum
     * @return string
     */
    static public function stringFor($id)
    {
        switch ($id) {
            case self::KM_PER_H:
                return 'km/h';
            case self::MILES_PER_H:
                return 'mph';
            case self::MIN_PER_KM:
                return 'min/km';
            case self::MIN_PER_MILE:
                return 'min/mi';
            case self::M_PER_S:
                return 'm/s';
            case self::MIN_PER_100M:
                return 'min/100m';
            case self::MIN_PER_100Y:
                return 'min/100y';
            case self::MIN_PER_500M:
                return 'min/500m';
            case self::MIN_PER_500Y:
                return 'min/500y';
            default:
                throw new \InvalidArgumentException('Invalid pace unit id "'.$id.'".');
        }
    }

    /**
     * @return array
     */
    static public function getChoices() {
        return array(
            self::stringFor(self::KM_PER_H) => self::KM_PER_H,
            self::stringFor(self::MILES_PER_H) => self::MILES_PER_H,
            self::stringFor(self::MIN_PER_KM) => self::MIN_PER_KM,
            self::stringFor(self::MIN_PER_MILE) => self::MIN_PER_MILE,
            self::stringFor(self::M_PER_S) => self::M_PER_S,
            self::stringFor(self::MIN_PER_100M) => self::MIN_PER_100M,
            self::stringFor(self::MIN_PER_100Y) => self::MIN_PER_100Y,
            self::stringFor(self::MIN_PER_500M) => self::MIN_PER_500M,
            self::stringFor(self::MIN_PER_500Y) => self::MIN_PER_500Y
        );
    }

	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws \InvalidArgumentException
	 */
	public function set($id)
	{
		$this->UnitObject = $this->getNewPaceUnitObject($id);
	}

	/**
	 * @param int $id
	 * @return \Runalyze\Activity\PaceUnit\AbstractUnit
	 * @throws \InvalidArgumentException
	 */
	protected function getNewPaceUnitObject($id)
	{
		switch ($id) {
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
			default:
				if ($this->UseFallback) {
					return new PaceUnitObject\KmPerHour();
				}
		}

		throw new \InvalidArgumentException('Invalid id "'.$id.'" for pace unit.');
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

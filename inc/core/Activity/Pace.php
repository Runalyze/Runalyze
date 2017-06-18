<?php
/**
 * This file contains class::Pace
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Metrics\LegacyUnitConverter;
use Runalyze\Parameter\Application\PaceUnit as PaceUnitOption;

/**
 * Different pace types/units
 *
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Pace {
	/**
	 * Default speed (km/h)
	 * @var int
	 */
	const STANDARD = PaceUnitOption::KM_PER_H;

	/**
	 * Time [s]
	 * @var int
	 */
	protected $Time;

	/**
	 * Distance [km]
	 * @var float
	 */
	protected $Distance;

	/**
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $UnitObject = null;

	/**
	 * Create pace
	 * @param int $time [s]
	 * @param float $distance [optional] [km]
	 * @param int $legacyPaceUnitEnum [optional]
	 */
	public function __construct($time, $distance = 1.0, $legacyPaceUnitEnum = self::STANDARD) {
		$this->Time = $time;
		$this->Distance = $distance;

		$this->setLegacyUnitEnum($legacyPaceUnitEnum);
	}

	/**
	 * Read pace from min/km
	 * @param string $string see Duration::fromString()
	 */
	public function fromMinPerKm($string) {
		$Duration = new Duration($string);

		$this->setTime($Duration->seconds())->setDistance(1);
	}

	/**
	 * Set time
	 * @param int $time [s]
	 * @return \Runalyze\Activity\Pace $this-reference
	 */
	public function setTime($time) {
		$this->Time = $time;

		return $this;
	}

	/**
	 * Set distance
	 * @param float $distance [km]
	 * @return \Runalyze\Activity\Pace $this-reference
	 */
	public function setDistance($distance) {
		$this->Distance = $distance;

		return $this;
	}

	/**
	 * Unit object
	 * @return \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	public function unit() {
		return $this->UnitObject;
	}

	/**
	 * Enum for pace unit
	 *
	 * This is for example what is saved in sports configuration.
	 *
	 * @return string
	 */
	public function unitEnum() {
		return $this->UnitObject->unit();
	}

	/**
	 * Set unit
	 * @param \Runalyze\Activity\PaceUnit\AbstractUnit $Unit
	 */
	public function setUnit(PaceUnit\AbstractUnit $Unit) {
		$this->UnitObject = $Unit;
	}

	/**
	 * Set unit
	 * @param int $legacyPaceUnitEnum
	 */
	public function setLegacyUnitEnum($legacyPaceUnitEnum) {
		$Option = new PaceUnitOption();
		$Option->set($legacyPaceUnitEnum);

		$this->UnitObject = $Option->object();
	}

    /**
     * Set unit
     * @param int $unitEnum
     */
    public function setNewUnitEnum($unitEnum) {
        $this->UnitObject = (new LegacyUnitConverter())->getLegacyPaceUnit($unitEnum);
    }

    /**
     * Set unit
     * @param int $unitEnum
     */
    public function setUnitEnum($unitEnum) {
        // TODO: remove
    }

	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->Distance <= 0 || $this->Time <= 0);
	}

	/**
	 * @return float
	 */
	public function secondsPerKm() {
		return ($this->Distance > 0) ? ($this->Time / $this->Distance) : 0;
	}

	/**
	 * Value
	 * As string, without unit
	 * @return string e.g. '12,5', '4:51'
	 */
	public function value() {
		return $this->UnitObject->format($this->secondsPerKm());
	}

	/**
	 * Value with appendix
	 * @return string
	 */
	public function valueWithAppendix() {
		return $this->value().$this->appendix();
	}

	/**
	 * Get appendix
	 * @return string
	 */
	public function appendix() {
		return $this->UnitObject->appendix();
	}

	/**
	 * @param string $UnitEnum
	 * @return string
	 */
	public function asUnitEnum($UnitEnum) {
		$Option = new PaceUnitOption();
		$Option->set($UnitEnum);

		return $this->asUnit($Option->object());
	}

	/**
	 * @param \Runalyze\Activity\PaceUnit\AbstractUnit $Unit
	 * @return string
	 */
	public function asUnit(PaceUnit\AbstractUnit $Unit) {
		return $Unit->format($this->secondsPerKm());
	}

	/**
	 * As: km/h
	 * @return string
	 */
	public function asKmPerHour() {
		return $this->asUnit(new PaceUnit\KmPerHour());
	}

	/**
	 * As: min/km
	 * @return string
	 */
	public function asMinPerKm() {
		return $this->asUnit(new PaceUnit\MinPerKilometer());
	}

	/**
	 * Compare
	 * Both pace objects must have the same unit and the unit must be comparable.
	 * @param \Runalyze\Activity\Pace $other
	 * @param boolean $raw [optional]
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function compareTo(Pace $other, $raw = false) {
		if ($this->unitEnum() != $other->unitEnum()) {
			throw new \InvalidArgumentException('Pace objects must have the same unit.');
		}

		if ($this->secondsPerKm() == 0 || $other->secondsPerKm() == 0) {
			return '';
		}

		$comparisonInSecondsPerKm = $this->UnitObject->compare($this->secondsPerKm(), $other->secondsPerKm());
		$isPositive = ($comparisonInSecondsPerKm >= 0);

		if ($comparisonInSecondsPerKm == 0 && $this->UnitObject->isTimeFormat()) {
			return $this->formatComparison('0:00', $isPositive, $raw);
		}

		return $this->formatComparison($this->UnitObject->format(abs($comparisonInSecondsPerKm)), $isPositive, $raw);
	}

	/**
	 * Format comparison
	 * @param string $string e.g. '0:27' or '1,4'
	 * @param boolean $isPositive
	 * @param boolean $raw [optional]
	 * @return string
	 */
	protected function formatComparison($string, $isPositive, $raw = false) {
		$class = ($isPositive) ? 'plus' : 'minus';
		$sign = ($isPositive) ? '+' : '-';

		if ($raw) {
			return $sign.$string;
		}

		/**
		 * @codeCoverageIgnore
		 */
		return '<span class="'.$class.'">'.$sign.$string.$this->appendix().'</span>';
	}
}

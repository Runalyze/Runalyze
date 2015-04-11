<?php
/**
 * This file contains class::Pace
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * Different pace types/units
 *
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class Pace {
	/**
	 * Default speed (km/h)
	 * @var string
	 */
	const STANDARD = "km/h";

	/**
	 * Speed unit km/h
	 * @var string
	 */
	const KM_PER_H = "km/h";

	/**
	 * Speed unit min/km
	 * @var string
	 */
	const MIN_PER_KM = "min/km";

	/**
	 * Speed unit min/100m
	 * @var string
	 */
	const MIN_PER_100M = "min/100m";

	/**
	 * Speed unit min/500m
	 * @var string
	 */
	const MIN_PER_500M = "min/500m";

	/**
	 * Speed unit m/s
	 * @var string
	 */
	const M_PER_S = "m/s";

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
	 * Unit
	 * @var enum
	 */
	protected $Unit;

	/**
	 * Options
	 * @return array
	 * @codeCoverageIgnore
	 */
	static public function options() {
		return array(
			self::KM_PER_H		=> self::KM_PER_H,
			self::MIN_PER_KM	=> self::MIN_PER_KM,
			self::MIN_PER_100M	=> self::MIN_PER_100M,
			self::MIN_PER_500M	=> self::MIN_PER_500M,
			self::M_PER_S		=> self::M_PER_S
		);
	}

	/**
	 * Create pace
	 * @param int $time [s]
	 * @param float $distance [optional] [km]
	 * @param enum $unit [optional]
	 */
	public function __construct($time, $distance = 1, $unit = self::STANDARD) {
		$this->Time = $time;
		$this->Distance = $distance;
		$this->Unit = $unit;
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
	 * Unit
	 * @return enum
	 */
	public function unit() {
		return $this->Unit;
	}

	/**
	 * Set unit
	 * @param enum $unit
	 */
	public function setUnit($unit) {
		$this->Unit = $unit;
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
		switch ($this->Unit) {
			case self::KM_PER_H:
				return $this->asKmPerHour();

			case self::MIN_PER_KM:
				return $this->asMinPerKm();

			case self::MIN_PER_100M:
				return $this->asMinPer100m();
		
			case self::MIN_PER_500M:
				return $this->asMinPer500m();

			case self::M_PER_S:
				return $this->asMeterPerSecond();
		}

		return '?';
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
		switch ($this->Unit) {
			case self::KM_PER_H:
				return '&nbsp;km/h';
			case self::MIN_PER_KM:
				return '/km';
			case self::MIN_PER_100M:
				return '/100m';
			case self::MIN_PER_500M:
				return '/500m';
			case self::M_PER_S:
				return '&nbsp;m/s';
		}

		return '';
	}

	/**
	 * As: km/h
	 * @return string
	 */
	public function asKmPerHour() {
		if ($this->isEmpty()) {
			return '0,0';
		}

		return number_format($this->Distance*3600/$this->Time, 1, ',', '.');
	}

	/**
	 * As: min/km
	 * @return string
	 */
	public function asMinPerKm() {
		if ($this->isEmpty()) {
			return '-:--';
		}

		return Duration::format(round($this->Time/$this->Distance));
	}

	/**
	 * As: min/100m
	 * @return string
	 */
	public function asMinPer100m() {
		$this->Time /= 10;
		$result = $this->asMinPerKm();
		$this->Time *= 10;

		return $result;
	}
	
	/**
	 * As: min/500m
	 * @return string
	 */
	public function asMinPer500m() {
		$this->Time /= 2;
		$result = $this->asMinPerKm();
		$this->Time *= 2;

		return $result;
	}

	/**
	 * As: m/s
	 * @return string
	 */
	public function asMeterPerSecond() {
		if ($this->isEmpty()) {
			return '0,0';
		}

		return number_format($this->Distance*1000/$this->Time, 1, ',', '.');
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
		if ($this->Unit != $other->unit()) {
			throw new \InvalidArgumentException('Pace objects must have the same unit.');
		}

		if ($this->secondsPerKm() == 0 || $other->secondsPerKm() == 0) {
			return '';
		}

		switch ($this->Unit) {
			case self::MIN_PER_KM:
			case self::MIN_PER_100M:
			case self::MIN_PER_500M:
				$first = new Duration($this->value());
				$second = new Duration($other->value());
				$string = Duration::format( abs($first->seconds() - $second->seconds()) );
				return $this->formatComparison($string, $first->seconds() <= $second->seconds(), $raw);

			case self::KM_PER_H:
			case self::M_PER_S:
				$value = abs((float)str_replace(',', '.', $this->value()) - (float)str_replace(',', '.', $other->value()));
				$string = number_format($value, 1, ',', '.');
				return $this->formatComparison($string, $other->secondsPerKm() >= $this->secondsPerKm(), $raw);
		}

		throw new \InvalidArgumentException('Pace unit '.$this->Unit.' cannot be compared.');
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

<?php
/**
 * This file contains class::SportSpeed
 * @package Runalyze\Data\Sport
 */
/**
 * Class for different speed types/units
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Sport
 */
class SportSpeed {
	/**
	 * Default speed (km/h)
	 * @var string
	 */
	static public $DEFAULT = "km/h";
	/**
	 * No speed unit
	 * @var string
	 */
	static public $NO = "";
	/**
	 * Speed unit km/h
	 * @var string
	 */
	static public $KM_PER_H = "km/h";
	/**
	 * Speed unit min/km
	 * @var string
	 */
	static public $MIN_PER_KM = "min/km";
	/**
	 * Speed unit min/100m
	 * @var string
	 */
	static public $MIN_PER_100M = "min/100m";
	/**
	 * Speed unit m/s
	 * @var string
	 */
	static public $M_PER_S = "m/s";

	/**
	 * Get select box
	 * @param string $Selected [optional]
	 * @param string $selectBoxName [optional]
	 * @codeCoverageIgnore
	 */
	static public function getSelectBox($Selected = false, $selectBoxName = 'speed') {
		if ($Selected == false)
			$Selected = (isset($_POST['speed'])) ? $_POST['speed'] : "";

		$Options = array(
			self::$NO			=> self::$NO,
			self::$KM_PER_H		=> self::$KM_PER_H,
			self::$MIN_PER_KM	=> self::$MIN_PER_KM,
			self::$MIN_PER_100M	=> self::$MIN_PER_100M,
			self::$M_PER_S		=> self::$M_PER_S
		);

		return HTML::selectBox($selectBoxName, $Options, $Selected);
	}

	/**
	 * Get speed with appendix
	 * @param float $Distance
	 * @param int $Time
	 * @param string $Unit
	 * @return string
	 */
	static public function getSpeedWithAppendix($Distance, $Time, $Unit) {
		return self::getSpeed($Distance, $Time, $Unit).self::getAppendix($Unit);
	}

	/**
	 * Get speed without appendix
	 * @param float $Distance
	 * @param int $Time
	 * @param string $Unit
	 * @return string
	 */
	static public function getSpeed($Distance, $Time, $Unit) {
		switch ($Unit) {
			case self::$KM_PER_H:
				return self::kmPerHour($Distance, $Time);
			case self::$MIN_PER_KM:
				return self::minPerKm($Distance, $Time);
			case self::$MIN_PER_100M:
				return self::minPer100m($Distance, $Time);
			case self::$M_PER_S:
				return self::mPerSecond($Distance, $Time);
			case self::$NO:
			default:
		}

		return self::noSpeed($Distance, $Time);
	}

	/**
	 * Get appendix for given unit
	 * @param string $Unit
	 * @return string
	 */
	static public function getAppendix($Unit) {
		switch ($Unit) {
			case self::$KM_PER_H:
				return "&nbsp;km/h";
			case self::$MIN_PER_KM:
				return "/km";
			case self::$MIN_PER_100M:
				return "/100m";
			case self::$M_PER_S:
				return "&nbsp;m/s";
			case self::$NO:
			default:
		}

		return "";
	}

	/**
	 * Get speed as string
	 * @param float $Distance
	 * @param int $Time
	 * @return string
	 */
	static public function noSpeed($Distance, $Time) {
		if ($Distance == 0)
			return '';

		return Running::Km($Distance).' in '.Time::toString($Time);
	}

	/**
	 * Get speed as string [km/h] without appendix
	 * @param float $Distance
	 * @param int $Time
	 * @return string
	 */
	static public function kmPerHour($Distance, $Time) {
		if ($Distance == 0 || $Time == 0)
			return '0,0';

		return number_format($Distance*3600/$Time, 1, ',', '.');
	}

	/**
	 * Get speed as string [min/km] without appendix
	 * @param float $Distance
	 * @param int $Time
	 * @return string
	 */
	static public function minPerKm($Distance, $Time) {
		if ($Distance == 0 || $Time == 0)
			return '-:--';

		if ($Time/$Distance < 60)
			return Time::toString(round($Time/$Distance), false, 2);

		return Time::toString(round($Time/$Distance));
	}

	/**
	 * Get speed as string [min/100m] without appendix
	 * @param float $Distance
	 * @param int $Time
	 * @return string
	 */
	static public function minPer100m($Distance, $Time) {
		return self::minPerKm($Distance, $Time/10);
	}

	/**
	 * Get speed as string [m/s] without appendix
	 * @param float $Distance
	 * @param int $Time
	 * @return string
	 */
	static public function mPerSecond($Distance, $Time) {
		if ($Distance == 0 || $Time == 0)
			return '0,0';

		return number_format($Distance*1000/$Time, 1, ',', '.');
	}

	/**
	 * Difference for speeds
	 * @param enum $Unit
	 * @param mixed $FirstValue
	 * @param mixed $SecondValue
	 * @return string
	 */
	static public function difference($Unit, $FirstValue, $SecondValue) {
		switch ($Unit) {
			case self::$MIN_PER_KM:
			case self::$MIN_PER_100M:
				$FirstInSeconds = Time::toSeconds($FirstValue);
				$SecondInSeconds = Time::toSeconds($SecondValue);
				$String = Time::toString( abs($FirstInSeconds - $SecondInSeconds), false, 2 );
				$Class = $SecondInSeconds < $FirstInSeconds ? 'plus' : 'minus';
				break;

			case self::$KM_PER_H:
			case self::$M_PER_S:
				$FirstValue = Helper::CommaToPoint($FirstValue);
				$SecondValue = Helper::CommaToPoint($SecondValue);
				$String = number_format(abs($FirstValue - $SecondValue), 1, ',', '.');;
				$Class = $SecondValue > $FirstValue ? 'plus' : 'minus';
				break;

			case self::$NO:
			default:
				$Class = '';
				$String = '';
		}

		$Sign = ($Class == 'plus') ? '+' : '-';

		return '<span class="'.$Class.'">'.$Sign.$String.self::getAppendix($Unit).'</span>';
	}
}
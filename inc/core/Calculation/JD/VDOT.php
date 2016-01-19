<?php
/**
 * This file contains class::VDOT
 * @package Runalyze\Calculation\JD
 */

namespace Runalyze\Calculation\JD;

// TODO:
// if (Configuration::Vdot()->method()->usesLogarithmic())

/**
 * VDOT
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\JD
 */
class VDOT {
	/**
	 * New method: logarithmic
	 * @var int enum
	 */
	const METHOD_LOGARITHMIC = 0;

	/**
	 * Old method: linear
	 * @var int enum
	 */
	const METHOD_LINEAR = 1;

	/**
	 * Precision
	 * @var int
	 */
	private static $Precision = 2;

	/**
	 * Method
	 * @var int enum
	 */
	private static $Method = 0;

	/**
	 * Value
	 * @var float
	 */
	protected $Value;

	/**
	 * Correction factor
	 * @var \Runalyze\Calculation\JD\VDOTCorrector
	 */
	protected $Corrector;

	/**
	 * Set precision
	 * @param int $decimals number of decimals to display
	 */
	public static function setPrecision($decimals) {
		self::$Precision = $decimals;
	}

	/**
	 * Set method
	 * @param int $method enum
	 */
	public static function setMethod($method) {
		self::$Method = $method;
	}

	/**
	 * Construct VDOT value
	 * @param float $value [optional]
	 * @param \Runalyze\Calculation\JD\VDOTCorrector $corrector [optional]
	 */
	public function __construct($value = 0, VDOTCorrector $corrector = null) {
		$this->setValue($value);
		$this->setCorrector($corrector);
	}

	/**
	 * Set value
	 * @param float $value
	 */
	public function setValue($value) {
		$this->Value = $value;
	}

	/**
	 * Set correction factor
	 * @param \Runalyze\Calculation\JD\VDOTCorrector $corrector [optional]
	 */
	public function setCorrector(VDOTCorrector $corrector = null) {
		if (!is_null($corrector)) {
			$this->Corrector = $corrector;
		}
	}

	/**
	 * Calculate from pace
	 * 
	 * @see self::formula
	 * 
	 * @param float $distance [km]
	 * @param int $seconds
	 */
	public function fromPace($distance, $seconds) {
		$this->Value = self::formula($distance, $seconds);
	}

	/**
	 * VDOT formula by Daniels/Gilbert
	 * 
	 * It can be read as 'oxygen cost' divided by 'drop dead'.
	 * @see http://www.simpsonassociatesinc.com/runningmath2.htm
	 * 
	 * @param float $distance [km]
	 * @param int $seconds
	 * @return float
	 */
	public static function formula($distance, $seconds) {
		$min = $seconds/60;
		$m = 1000*$distance;

		if ($m <= 0 || $min <= 0 || $m/$min < 50 || $m/$min > 1000) {
			return 0;
		} else {
			return ( -4.6+0.182258*$m / $min + 0.000104*pow($m/$min,2) )
				/ ( 0.8 + 0.1894393*exp(-0.012778*$min) + 0.2989558*exp(-0.1932605*$min) );
		}
	}

	/**
	 * Calculate VDOT by training run
	 * @param float $distance [km]
	 * @param int $seconds [s]
	 * @param float $hrInPercent in [0.0, 1.0]
	 */
	public function fromPaceAndHR($distance, $seconds, $hrInPercent) {
		if ($hrInPercent <= 0 || $seconds == 0) {
			$this->Value = 0;
		} else {
			$speedReallyAchieved = 60*1000*$distance / $seconds;
			$percentageEstimateByHR = self::percentageAt($hrInPercent);
			$speedEstimateAt100PercentVDOT = $speedReallyAchieved / $percentageEstimateByHR;

			$this->fromSpeed($speedEstimateAt100PercentVDOT);
		}
	}

	/**
	 * Value
	 * @return float
	 */
	public function value() {
		return number_format($this->exactValue(), self::$Precision);
	}

	/**
	 * Exact value
	 * @codeCoverageIgnore
	 * @return float
	 */
	public function exactValue() {
		if (!is_null($this->Corrector)) {
			return $this->Corrector->factor() * $this->Value;
		}

		return $this->Value;
	}

	/**
	 * Uncorrected value
	 * @return float
	 */
	public function uncorrectedValue() {
		return number_format($this->Value, self::$Precision);
	}

	/**
	 * Multiply value
	 * @param float $factor
	 */
	public function multiply($factor) {
		$this->Value *= $factor;
	}

	/**
	 * Calculate VDOT by speed
	 * 
	 * This formula is simply the oxygen formula of Daniels/Gilbert.
	 * The drop dead formula equals nearly 1 (exact: 1.00027...) for 11 minutes,
	 * the maximal time one can run at 100 %vVDOT.
	 * 
	 * @param float $speed [m/min] speed at 100% VDOT (i.e. for 11 minutes)
	 */
	public function fromSpeed($speed) {
		$this->Value = max(0, -4.6 + 0.182253*$speed + 0.000104*$speed*$speed);
	}

	/**
	 * Speed at 100%
	 * 
	 * This formula is derived by solving the quadratic formula of the original
	 * VDOT formula for 11 minutes, i.e. the maximal time that one can run at 100 %vVDOT.
	 * 
	 * @return float [m/min]
	 */
	public function speed() {
		if ($this->Value == 0) {
			return 0;
		}

		return -876 + pow(876*876 + (4.6 + $this->Value) / 0.000104, 0.5);
	}

	/**
	 * Pace at 100%
	 * @return int [s/km]
	 */
	public function pace() {
		if ($this->Value == 0) {
			return 0;
		}

		return round(60*1000/$this->speed());
	}

	/**
	 * Pace at %vVDOT
	 * @param float $percentage in (0.0, 1.0]
	 * @return int
	 */
	public function paceAt($percentage) {
		if ($this->Value == 0) {
			return 0;
		}

		return round(60*1000/($percentage*$this->speed()));
	}

	/**
	 * Expected heart rate at X.X % of VDOT
	 * 
	 * This formula is derived via regression for the table 2.2
	 * on page 42 of JDs running formula (german version).
	 * 
	 * @param float $percentage in [0.0, 1.0]
	 * @return float in [0.0, 1.0]
	 */
	public static function HRat($percentage) {
		if (self::$Method == self::METHOD_LOGARITHMIC)
			return 0.68725*log($percentage)+1.00466;

		return ($percentage+0.2812)/1.2812;
	}

	/**
	 * Expected % of VDOT at given heart rate
	 * 
	 * This formula is derived via regression for the table 2.2
	 * on page 42 of JDs running formula (german version).
	 * 
	 * @param float $hrInPercent in [0.0, 1.0]
	 * @return float in [0.0, 1.0]
	 */
	public static function percentageAt($hrInPercent) {
		if (self::$Method == self::METHOD_LOGARITHMIC)
			return exp( ($hrInPercent - 1.00466) / 0.68725 );

		return 1.2812*$hrInPercent-0.2812;
	}
}
<?php
/**
 * This file contains class::Intensity
 * @package Runalyze\Calculation\JD
 */

namespace Runalyze\Calculation\JD;

use Runalyze\Calculation\Distribution\TimeSeries;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;

/**
 * Training intensity by Jack Daniels
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\JD
 */
class Intensity {
	/**
	 * Value
	 * @var int
	 */
	protected $Value = 0;

	/**
	 * Maximal heart rate
	 * @var int
	 */
	static private $HRmax = 200;

	/**
	 * Set HRmax
	 * @param int $hrMax
	 */
	static public function setHRmax($hrMax) {
		self::$HRmax = $hrMax;
	}

	/**
	 * VDOT shape
	 * @var float
	 */
	static private $VDOTshape = 0;

	/**
	 * Set VDOT shape
	 * @param float $vdot
	 */
	static public function setVDOTshape($vdot) {
		self::$VDOTshape = $vdot;
	}

	/**
	 * Value
	 * @return int
	 */
	public function value() {
		return $this->Value;
	}

	/**
	 * Calculate for activity object
	 * 
	 * This method does not lookup trackdata of the activity!
	 * If you want an exact intensity value, use `calculateByTrackdata(...)`
	 * 
	 * @param \Runalyze\Model\Activity\Object $activity
	 * @return int
	 */
	public function calculateByActivity(Activity\Object $activity) {
		if ($activity->hrAvg() > 0) {
			return $this->calculateByHeartrateAverage($activity->hrAvg(), $activity->duration());
		}

		return $this->calculateByPace($activity->distance(), $activity->duration());
	}

	/**
	 * Calculate by trackdata
	 * @param \Runalyze\Model\Trackdata\Object $trackdata
	 */
	public function calculateByTrackdata(Trackdata\Object $trackdata) {
		if (!$trackdata->has(Trackdata\Object::HEARTRATE)) {
			return;
		}

		return $this->calculateByHeartrate(
			new TimeSeries(
				$trackdata->get( Trackdata\Object::HEARTRATE ),
				$trackdata->get( Trackdata\Object::TIME )
			)
		);
	}

	/**
	 * Calculate by heart rate distribution
	 * @param \Runalyze\Calculation\Distribution\TimeSeries $distribution
	 * @return int
	 */
	public function calculateByHeartrate(TimeSeries $distribution) {
		$this->Value = 0;

		foreach ($distribution->histogram() as $hr => $seconds) {
			$this->Value += $this->pointsFor($hr/self::$HRmax, $seconds);
		}

		$this->Value = round($this->Value);

		return $this->Value;
	}

	/**
	 * Calculate by average heart rate
	 * @param int $bpm
	 * @param int $seconds
	 * @return int
	 */
	public function calculateByHeartrateAverage($bpm, $seconds) {
		$this->Value = round($this->pointsFor($bpm/self::$HRmax, $seconds));

		return $this->Value;
	}

	/**
	 * Caluclate by pace
	 * @param float $distance
	 * @param int $seconds
	 * @return int
	 */
	public function calculateByPace($distance, $seconds) {
		$this->Value = round($this->pointsFor($this->guessHR($distance, $seconds), $seconds));

		return $this->Value;
	}

	/**
	 * Guess heart rate
	 * @param float $distance
	 * @param int $seconds
	 * @return float
	 */
	protected function guessHR($distance, $seconds) {
		if (self::$VDOTshape <= 0 || $seconds == 0) {
			return 0.5;
		}

		$Shape = new VDOT(self::$VDOTshape);
		$speed = 60*1000*$distance / $seconds;

		$hr = VDOT::HRat($speed / $Shape->speed());

		return $hr;
	}

	/**
	 * Calculate intensity points
	 * @param float $hrInPercent
	 * @param int $seconds
	 * @return float
	 */
	protected function pointsFor($hrInPercent, $seconds) {
		$hrInPercent = $hrInPercent < 0.5 ? 0.5 : $hrInPercent;

		return (4.742894532 * pow($hrInPercent, 2) - 5.298465448 * $hrInPercent + 1.550709462) * $seconds / 60;
	}
}
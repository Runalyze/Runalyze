<?php
/**
 * This file contains class::RunningPrognosisSteffny
 * @package Runalyze\Calculations\Prognosis
 */
/**
 * Class: RunningPrognosisSteffny
 * 
 * Competition prediction based on "Das große Laufbuch" by Herbert Steffny.
 * See page 136.
 * 
 * A linear approximation (based on the pace) is used for distances between the given distances in the book,
 * e.g. to predict a 7.5k-race from 5k in 20:00 (4:00/km), look at 10k performance (41:00, 4:06/km) and
 * predict 4:03/km and therefore 30:23.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
class RunningPrognosisSteffny extends RunningPrognosisStrategy {
	/**
	 * Reference pace for 10k
	 * @var float
	 */
	protected $ReferencePaceFor10k = 0;

	/**
	 * Running setup from database
	 */
	public function setupFromDatabase() {
		$TopResult = $this->getTopResults(1);

		if (!empty($TopResult))
			$this->setReferenceResult($TopResult['distance'], $TopResult['s']);
	}

	/**
	 * Set reference from 10k time
	 * @param int $timeInSeconds time in seconds
	 */
	public function setReferenceFrom10kTime($timeInSeconds) {
		$this->ReferencePaceFor10k = $timeInSeconds / 10;
	}

	/**
	 * Set reference result
	 * @param float $distance distance in km
	 * @param int $timeInSeconds time in s
	 */
	public function setReferenceResult($distance, $timeInSeconds) {
		$this->transformToNear10k($distance, $timeInSeconds);

		$this->ReferencePaceFor10k = $timeInSeconds / $distance;
	}

	/**
	 * Transform result nearly to 10k
	 * @param float $distance distance in km
	 * @param int $timeInSeconds time in s
	 */
	private function transformToNear10k(&$distance, &$timeInSeconds) {
		if ($distance < 10)
			$this->transformFromBelowToNear10k($distance, $timeInSeconds);
		else
			$this->transformFromAboveToNear10k($distance, $timeInSeconds);
	}

	/**
	 * Transform result nearly to 10k from below
	 * 
	 * Transform a given result from below 10k to an equivalent result near 10k.
	 * @param float $distance distance in km
	 * @param float $timeInSeconds time in seconds
	 */
	private function transformFromBelowToNear10k(&$distance, &$timeInSeconds) {
		if ($distance <= 2.25) {
			$timeInSeconds = $this->from1500mTo3000m( 1.5 * $timeInSeconds / $distance );
			$distance = 3;
		}
	
		if ($distance <= 4) {
			$timeInSeconds = $this->from3000mTo5k( 3 * $timeInSeconds / $distance );
			$distance = 5;
		}

		if ($distance <= 7.5) {
			$timeInSeconds = $this->from5kTo10k( 5 * $timeInSeconds / $distance );
			$distance = 10;
		}
	}

	/**
	 * Transform result nearly to 10k from above
	 * 
	 * Transform a given result from above 10k to an equivalent result near 10k.
	 * @param float $distance distance in km
	 * @param float $timeInSeconds time in seconds
	 */
	private function transformFromAboveToNear10k(&$distance, &$timeInSeconds) {
		if ($distance >= 31.6) {
			$timeInSeconds = $this->fromHMToM( 42.195 * $timeInSeconds / $distance, true );
			$distance = 21.0975;
		}
	
		if ($distance >= 15.55) {
			$timeInSeconds = $this->from10kToHM( 21.0975 * $timeInSeconds / $distance, true );
			$distance = 10;
		}
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance in kilometer
	 * @return float prognosis in seconds
	 */
	public function inSeconds($distance) {
		$paces          = array();
		$paces['10k']   = $this->ReferencePaceFor10k;
		$paces['5k']    = $this->from5kTo10k(10 * $paces['10k'], true) / 5;
		$paces['3000m'] = $this->from3000mTo5k(5 * $paces['5k'], true) / 3;
		$paces['1500m'] = $this->from1500mTo3000m(3 * $paces['3000m'], true) / 1.5;
		$paces['HM']    = $this->from10kToHM(10 * $paces['10k']) / 21.0975;
		$paces['M']     = $this->fromHMToM(21.0975 * $paces['HM']) / 42.195;
		$paces['100k']  = $this->fromMTo100k(42.195 * $paces['M']) / 100;

		if ($distance <= 1.5)
			return $distance * $paces['1500m'];
		if ($distance <= 3)
			return $distance * ( $paces['1500m'] + ($paces['3000m'] - $paces['1500m']) * ($distance - 1.5) / (3 - 1.5) );
		if ($distance <= 5)
			return $distance * ( $paces['3000m'] + ($paces['5k'] - $paces['3000m']) * ($distance - 3) / (5 - 3) );
		if ($distance <= 10)
			return $distance * ( $paces['5k'] + ($paces['10k'] - $paces['5k']) * ($distance - 5) / (10 - 5) );
		if ($distance <= 21.0975)
			return $distance * ( $paces['10k'] + ($paces['HM'] - $paces['10k']) * ($distance - 10) / (21.0975 - 10) );
		if ($distance <= 42.195)
			return $distance * ( $paces['HM'] + ($paces['M'] - $paces['HM']) * ($distance - 21.0975) / (42.195 - 21.0975) );
		if ($distance <= 100)
			return $distance * ( $paces['M'] + ($paces['100k'] - $paces['M']) * ($distance - 42.195) / (100 - 42.195) );

		return $distance * $paces['100k'];
	}

	/**
	 * From 1500m to 3000m
	 * @param float $s time in seconds
	 * @param bool $back [optional] calculate backwards, default false
	 * @return float
	 */
	private function from1500mTo3000m($s, $back = false) {
		if ($back)
			return ($s - 20) / 2;

		return $s * 2 + 20;
	}

	/**
	 * From 3000m to 5k
	 * @param float $s time in seconds
	 * @param bool $back [optional] calculate backwards, default false
	 * @return float
	 */
	private function from3000mTo5k($s, $back = false) {
		if ($back)
			return ($s / 1.666) - 20;

		return ($s + 20) * 1.666;
	}

	/**
	 * From 5k to 10k
	 * @param float $s time in seconds
	 * @param bool $back [optional] calculate backwards, default false
	 * @return float
	 */
	private function from5kTo10k($s, $back = false) {
		if ($back)
			return ($s - 60) / 2;

		return $s * 2 + 60;
	}

	/**
	 * From 10k to HM
	 * @param float $s time in seconds
	 * @param bool $back [optional] calculate backwards, default false
	 * @return float
	 */
	private function from10kToHM($s, $back = false) {
		if ($back)
			return $s / 2.21;

		return $s * 2.21;
	}

	/**
	 * From HM to M
	 * @param float $s time in seconds
	 * @param bool $back [optional] calculate backwards, default false
	 * @return float
	 */
	private function fromHMToM($s, $back = false) {
		if ($back)
			return $s / 2.11;

		return $s * 2.11;
	}

	/**
	 * From M to 100k
	 * @param float $s time in seconds
	 * @return float
	 */
	private function fromMTo100k($s) {
		return $s * 3 - max(0, 3*60*60 - $s);
	}
}
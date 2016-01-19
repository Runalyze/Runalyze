<?php
/**
 * This file contains class::Splits
 * @package Runalyze\Data\Splits
 */

use Runalyze\Activity\Duration;

/**
 * Class for handling splits
 * @author Hannes Christiansen
 * @package Runalyze\Data\Splits
 */
class Splits {
	/**
	 * Enum for constructor: Take data from post
	 * @var string
	 */
	public static $FROM_POST = 'TAKE_PARAMETER_FROM_POST';

	/**
	 * Splits as string
	 * @var string
	 */
	private $asString = '';

	/**
	 * Splits as array
	 * @var array
	 */
	private $asArray = array();

	/**
	 * @var bool
	 */
	protected $transformDistanceUnit = false;

	/**
	 * Construct a new object of Splits
	 * @param string $data [optional]
	 * @param array $options
	 */
	public function __construct($data = '', $options = array()) {
		if ($data == self::$FROM_POST)
			$data = isset($_POST['splits']) ? $_POST['splits'] : array();

		if (isset($options['transform-unit']) && $options['transform-unit'] === true) {
			$this->transformDistanceUnit = true;
		}

		$this->createFromData($data);
	}

	/**
	 * Create splits from POST-data
	 * @param mixed $data
	 */
	private function createFromData($data) {
		if (is_array($data)) {
			$this->createFromArray($data);
			$this->cleanArray();
			$this->arrayToString();
		} else {
			$this->asString = $data;
			$this->stringToArray();
			$this->cleanArray();
			$this->arrayToString();
		}

		$this->removeSingleSplits();
	}

	/**
	 * Create from array
	 * @param array $array
	 */
	private function createFromArray($array) {
		$this->asArray = array();
		$factor = $this->transformDistanceUnit ? Runalyze\Configuration::General()->distanceUnitSystem()->distanceToKmFactor() : 1;

		// TODO escaping
		if (isset($array['km']) && isset($array['time']))
			foreach ($array['km'] as $i => $km)
				$this->asArray[] = array(
					'km' => $km * $factor,
					'time' => $array['time'][$i],
					'active' => !isset($array['active']) || (isset($array['active']) && isset($array['active'][$i]) && $array['active'][$i])
				);
	}

	/**
	 * Are the splits empty?
	 * @return boolean
	 */
	public function areEmpty() {
		return empty($this->asArray);
	}

	/**
	 * Get splits as array
	 * @return array
	 */
	public function asArray() {
		return $this->asArray;
	}

	/**
	 * Get splits as string
	 * @return string
	 */
	public function asString() {
		return $this->asString;
	}

	/**
	 * Add a split
	 * @param double $km kilometer
	 * @param int $timeInSeconds seconds
	 * @param bool $active optional
	 */
	public function addSplit($km, $timeInSeconds, $active = true) {
		if ($km <= 0 && $timeInSeconds <= 0) {
			return;
		}

		$this->asArray[] = array(
			'km' => $this->formatKM($km),
			'time' => $this->formatTime($timeInSeconds),
			'active' => $active
		);
		$this->arrayToString();
	}

	/**
	 * Calculate and add last split
	 * Add last split to fill up remaining time and distance,
	 * calculated as difference between given distance/duration and current sum.
	 * @param float $totalDistance [km] should be larger then current sum of splits
	 * @param int $totalDuration [s] should be larger then current sum of splits
	 * @param bool $active
	 */
	public function addLastSplitToComplete($totalDistance, $totalDuration, $active = true) {
		$distance = $totalDistance - $this->totalDistance();
		$time = $totalDuration - $this->totalTime();

		if ($distance >= 0 && $time > 0) {
			$this->addSplit($distance, $time, $active);
		}
	}

	/**
	 * Remove single splits
	 */
	public function removeSingleSplits() {
		if (count($this->asArray) == 1) {
			$this->asArray = array();
			$this->asString = '';
		}
	}

	/**
	 * Get splits as readable string
	 * @param bool $restingLaps optional
	 * @return string 
	 */
	public function asReadableString($restingLaps = false) {
		$strings = array();

		foreach ($this->asArray as $split)
			if ($restingLaps || $split['active'])
				$strings[] = $split['km'].'&nbsp;km&nbsp;'.__('in').'&nbsp;'.$split['time'].(!$split['active'] ? '&nbsp;('.__('Resting').')' : '');

		return implode(', ', $strings);
	}

	/**
	 * As icon with tooltip
	 * @return string
	 */
	public function asIconWithTooltip() {
		if (!$this->areEmpty())
			return Ajax::tooltip(Icon::$CLOCK, $this->asReadableString());

		return '';
	}

	/**
	 * Transform splits from internal string to array 
	 */
	private function stringToArray() {
		$this->asArray = array();
		$splits        = explode('-', str_replace('\r\n', '-', $this->asString));

		foreach ($splits as $split) {
			if (substr($split,0,1) == 'R') {
				$active = false;
				$split  = substr($split,1);
			} else {
				$active = true;
			}

			if (strlen($split) > 3)
				$this->asArray[] = array(
					'km' => rstrstr($split, '|'),
					'time' => substr(strrchr($split, '|'), 1),
					'active' => $active
				);
		}
	}

	/**
	 * Clean internal array 
	 */
	private function cleanArray() {
		foreach ($this->asArray as $key => $split) {
			//if ($split['km'] <= 0)
			//if ($split['km'] <= 0 || empty($split['time']))
			//	unset($this->asArray[$key]);
			//else
			$this->asArray[$key]['km'] = $this->formatKM($split['km']);

			if (substr($split['time'], -1) == 's') {
				$this->asArray[$key]['time'] = $this->formatTime(substr($split['time'], 0, -1));
			}
		}
	}

	/**
	 * Format kilometer
	 * @param double $km
	 * @return string
	 */
	private function formatKM($km) {
		return number_format(Helper::CommaToPoint($km), 3, '.', '');
	}

	/**
	 * @param float $seconds
	 * @return string
	 */
	private function formatTime($seconds) {
		return Duration::format(round($seconds));
	}

	/**
	 * Transform internal array to string
	 */
	private function arrayToString() {
		$strings = array();

		foreach ($this->asArray() as $split)
			$strings[] = ($split['active'] ? '' : 'R').$split['km'].'|'.$split['time'];

		$this->asString = implode('-', $strings);
	}

	/**
	 * Get all times as array
	 * @param bool $restingLaps optional
	 * @return array 
	 */
	public function timesAsArray($restingLaps = false) {
		$times = array();

		foreach ($this->asArray as $split) {
			if ($restingLaps || $split['active']) {
				$Duration = new Duration($split['time']);
				$times[] = $Duration->seconds();
			}
		}

		return $times;
	}

	/**
	 * Get total time
	 * @return int
	 */
	public function totalTime() {
		$time = 0;

		foreach ($this->asArray as $split) {
			$Duration = new Duration($split['time']);
			$time += $Duration->seconds();
		}

		return $time;
	}

	/**
	 * Is at least one lap active?
	 * @param int $num
	 * @return boolean
	 */
	public function hasActiveLaps($num = 1) {
		$count = 0;
		foreach ($this->asArray as $split) {
			if ($split['active']) {
				$count++;
			}

			if ($count == $num) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Are there active and inactive laps?
	 * @return boolean
	 */
	public function hasActiveAndInactiveLaps() {
		$active = null;

		foreach ($this->asArray as $split) {
			if (is_null($active))
				$active = $split['active'];
			elseif ($active != $split['active'])
				return true;
		}

		return false;
	}

	/**
	 * Get all distances as array
	 * @param bool $restingLaps optional
	 * @return array 
	 */
	public function distancesAsArray($restingLaps = false) {
		$distances = array();

		foreach ($this->asArray as $split)
			if ($restingLaps || $split['active'])
				$distances[] = $split['km'];

		return $distances;
	}

	/**
	 * Get total distance
	 * @return float
	 */
	public function totalDistance() {
		$km = 0;

		foreach ($this->asArray as $split)
			$km += $split['km'];

		return $km;
	}

	/**
	 * Get all paces as array
	 * @param bool $restingLaps optional
	 * @return array 
	 */
	public function pacesAsArray($restingLaps = false) {
		$paces = array();

		foreach ($this->asArray as $split) {
			if ($restingLaps || $split['active']) {
				$Duration = new Duration($split['time']);
				$paces[] = $split['km'] > 0 ? (int)round($Duration->seconds()/$split['km']) : 0;
			}
		}

		return $paces;
	}

	/**
	 * Set empty times from array
	 * @param array $Time
	 * @param array $Distance
	 */
	public function fillTimesFromArray(array $Time, array $Distance) {
		$this->fillFromArray($Time, $Distance, 'time');
	}

	/**
	 * Set empty times from array
	 * @param array $Time
	 * @param array $Distance
	 */
	public function fillDistancesFromArray(array $Time, array $Distance) {
		$this->fillFromArray($Time, $Distance, 'km');
	}

	/**
	 * Set empty times from array
	 * @param array $Time
	 * @param array $Distance
	 * @param string $mode
	 */
	protected function fillFromArray(array $Time, array $Distance, $mode = 'time') {
		$totalDistance = 0;
		$totalTime = 0;
		$size = min(count($Time), count($Distance));
		$i = 0;

		foreach ($this->asArray as &$split) {
			if ($mode == 'km') {
				$Duration = new Duration($split['time']);

				while ($i < $size-1 && $Duration->seconds() > $Time[$i] - $totalTime)
					$i++;

				$split['km'] = $this->formatKM($Distance[$i] - $totalDistance);
			} else {
				while ($i < $size-1 && $split['km'] > $Distance[$i] - $totalDistance)
					$i++;

				$split['time'] = $this->formatTime($Time[$i] - $totalTime);
			}

			$totalTime     = $Time[$i];
			$totalDistance = $Distance[$i];
		}

		$this->arrayToString();
	}

	/**
	 * Get fieldset
	 * @return FormularFieldset
	 * @codeCoverageIgnore
	 */
	public function getFieldset() {
		$Fieldset = new FormularFieldset( __('Laps') );
		$Fieldset->addField( new TrainingInputSplits() );
                $Fieldset->addCSSclass( TrainingFormular::$ONLY_DISTANCES_CLASS );

		if ($this->areEmpty())
			$Fieldset->setCollapsed();

		return $Fieldset;
	}
}
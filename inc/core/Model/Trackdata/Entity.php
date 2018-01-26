<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Model;
use Runalyze\Calculation\Activity\PaceCalculator;
use Runalyze\Calculation\Activity\VerticalRatioCalculator;
use Runalyze\Calculation\StrideLength;

/**
 * Trackdata entity
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Entity extends Model\Entity implements Model\Loopable, Model\Common\WithNullableArraysInterface {
    use Model\Common\WithNullableArraysTrait;

	/**
	 * Key: activity id
	 * @var string
	 */
	const ACTIVITYID = 'activityid';

	/**
	 * Key: time
	 * @var string
	 */
	const TIME = 'time';

	/**
	 * Key: distance
	 * @var string
	 */
	const DISTANCE = 'distance';

	/**
	 * Key: pace - must be calculated (not in db!)
	 * @var string
	 */
	const PACE = 'pace';

	/**
	 * Key: heart rate
	 * @var string
	 */
	const HEARTRATE = 'heartrate';

	/**
	 * Key: cadence
	 * @var string
	 */
	const CADENCE = 'cadence';

	/**
	 * Key: stridelength - must be calculated (not in db!)
	 * @var string
	 */
	const STRIDE_LENGTH = 'stridelength';

	/**
	 * Key: power
	 * @var string
	 */
	const POWER = 'power';

	/**
	 * Key: temperature
	 * @var string
	 */
	const TEMPERATURE = 'temperature';

	/**
	 * Key: ground contact time
	 * @var string
	 */
	const GROUNDCONTACT = 'groundcontact';

	/**
	 * Key: vertical oscillation
	 * @var string
	 */
	const VERTICAL_OSCILLATION = 'vertical_oscillation';

	/**
	 * Key: vertical ratio - must be calculated (not in db!)
	 * @var string
	 */
	const VERTICAL_RATIO = 'vertical_ratio';

	/**
	 * Key: ground contact time balance
	 * @var string
	 */
	const GROUNDCONTACT_BALANCE = 'groundcontact_balance';

    /**
     * Key: smo2 (0)
     * @var string
     */
    const SMO2_0 = 'smo2_0';

    /**
     * Key: smo2 (1)
     * @var string
     */
    const SMO2_1 = 'smo2_1';

    /**
     * Key: thb (0)
     * @var string
     */
    const THB_0 = 'thb_0';

    /**
     * Key: thb (1)
     * @var string
     */
    const THB_1 = 'thb_1';

    /**
     * Key: impact GS left
     * @var string
     */
    const IMPACT_GS_LEFT = 'impact_gs_left';

    /**
     * Key: impact GS right
     * @var string
     */
    const IMPACT_GS_RIGHT = 'impact_gs_right';

    /**
     * Key: braking GS left
     * @var string
     */
    const BRAKING_GS_LEFT = 'braking_gs_left';

    /**
     * Key: braking GS right
     * @var string
     */
    const BRAKING_GS_RIGHT = 'braking_gs_right';

    /**
     * Key: footstrike type left
     * @var string
     */
    const FOOTSTRIKE_TYPE_LEFT = 'footstrike_type_left';

    /**
     * Key: footstrike type right
     * @var string
     */
    const FOOTSTRIKE_TYPE_RIGHT = 'footstrike_type_right';

    /**
     * Key: pronation excursion left
     * @var string
     */
    const PRONATION_EXCURSION_LEFT = 'pronation_excursion_left';

    /**
     * Key: pronation excursion right
     * @var string
     */
    const PRONATION_EXCURSION_RIGHT = 'pronation_excursion_right';


    /**
	 * Key: pauses
	 * @var string
	 */
	const PAUSES = 'pauses';

	/**
	 * Pauses
	 * @var \Runalyze\Model\Trackdata\Pauses
	 */
	protected $Pauses = null;

	/**
	 * @var boolean
	 */
	protected $TimeHasBeenRemoved = false;

	/**
	 * Flag: ensure arrays to be equally sized
	 * @var bool
	 */
	protected $checkArraySizes = true;

	/** @var bool */
	protected $hasTheoreticalPace = false;

	/**
	 * Clone object
	 */
	public function __clone() {
		$this->cloneInternalObjects();
	}

	/**
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		parent::__construct($data);

		$this->calculatePaceArray();
		$this->calculateStrideLengthArray();
		$this->calculateVerticalRatioArray();
 		$this->readPauses();
	}

	/**
	 * Check array sizes
	 * @throws \RuntimeException
	 */
	protected function checkArraySizes() {
		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				try {
					if ($key != self::TEMPERATURE && !empty($this->Data[$key]) && max($this->Data[$key]) == 0) {
						$this->Data[$key] = array();
					}

					// TODO: Move all these fixes to a new script to correct defect activities
					if ($key == self::TIME && !empty($this->Data[$key]) && min($this->Data[$key]) < 0) {
						$this->TimeHasBeenRemoved = true;
						$this->Data[$key] = array();
					}

					$count = count($this->Data[$key]);

					if ($key == self::DISTANCE && $count > 0) {
						$this->fixDistanceArray();
					}

					if (($key == self::HEARTRATE || $key == self::CADENCE) && $this->numberOfPoints > 0 && $count > 0) {
						if ($count == 1 + $this->numberOfPoints) {
							$this->Data[$key] = array_slice($this->Data[$key], 1);
						} elseif ($count == $this->numberOfPoints - 1) {
							array_unshift($this->Data[$key], $this->Data[$key][0]);
						}
					} else {
						$this->checkArraySize( $count );
					}
				} catch(\RuntimeException $E) {
					throw new \RuntimeException($E->getMessage().' (for '.$key.')');
				}
			}
		}
	}

	/**
	 * Fix distance array
	 */
	protected function fixDistanceArray() {
		foreach ($this->Data[self::DISTANCE] as $i => $dist) {
			if ($i > 0 && $dist == 0) {
				$this->Data[self::DISTANCE][$i] = $this->Data[self::DISTANCE][$i - 1];
			}
		}
	}

	/**
	 * Read pauses
	 */
	protected function readPauses() {
		$this->Pauses = new Pauses($this->Data[self::PAUSES]);
	}

	/**
	 * Synchronize
	 */
	public function synchronize() {
	    if ($this->Pauses->isEmpty()) {
	        $this->Data[self::PAUSES] = null;
        } else {
    		$this->Data[self::PAUSES] = $this->Pauses->asString();
        }
	}

	/**
	 * All databaseproperties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::ACTIVITYID,
			self::TIME,
			self::DISTANCE,
			self::HEARTRATE,
			self::CADENCE,
			self::POWER,
			self::TEMPERATURE,
			self::GROUNDCONTACT,
			self::VERTICAL_OSCILLATION,
			self::GROUNDCONTACT_BALANCE,
            self::SMO2_0,
            self::SMO2_1,
            self::THB_0,
            self::THB_1,
			self::IMPACT_GS_LEFT,
			self::IMPACT_GS_RIGHT,
			self::BRAKING_GS_LEFT,
			self::BRAKING_GS_RIGHT,
			self::FOOTSTRIKE_TYPE_LEFT,
			self::FOOTSTRIKE_TYPE_RIGHT,
			self::PRONATION_EXCURSION_LEFT,
			self::PRONATION_EXCURSION_RIGHT,
			self::PAUSES
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return array_merge(array(
				self::PACE,
				self::STRIDE_LENGTH,
				self::VERTICAL_RATIO
			),
			static::allDatabaseProperties()
		);
	}

	/**
	 * Is the property an array?
	 * @param string $key
	 * @return bool
	 */
	public function isArray($key) {
		return ($key != self::PAUSES && $key != self::ACTIVITYID);
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::TIME:
			case self::DISTANCE:
			case self::HEARTRATE:
			case self::CADENCE:
			case self::POWER:
			case self::TEMPERATURE:
			case self::GROUNDCONTACT:
			case self::VERTICAL_OSCILLATION:
			case self::GROUNDCONTACT_BALANCE:
            case self::SMO2_0:
            case self::SMO2_1:
            case self::THB_0:
            case self::THB_1:
            case self::IMPACT_GS_LEFT:
            case self::IMPACT_GS_RIGHT:
            case self::BRAKING_GS_LEFT:
            case self::BRAKING_GS_RIGHT:
            case self::FOOTSTRIKE_TYPE_LEFT:
            case self::FOOTSTRIKE_TYPE_RIGHT:
            case self::PRONATION_EXCURSION_LEFT:
            case self::PRONATION_EXCURSION_RIGHT:
			case self::PAUSES:
				return true;
		}

		return false;
	}

	/**
	 * Is not in Database?
	 * @param string $key
	 * @return boolean
	 */
	protected function notInDatabase($key) {
		switch ($key) {
			case self::PACE:
			case self::STRIDE_LENGTH:
			case self::VERTICAL_RATIO:
				return true;
		}

		return false;
	}

	/**
	 * Ignore a key while checking for emptiness
	 * @param string $key
	 * @return boolean
	 */
	protected function ignoreNonEmptyValue($key) {
		return ($key == self::ACTIVITYID);
	}

	/**
	 * Clear
	 */
	public function clear() {
		parent::clear();

		$this->Pauses->clear();
	}

	/**
	 * Can set key?
	 * @param string $key
	 * @return boolean
	 */
	protected function canSet($key) {
		if ($key == self::PAUSES) {
			return false;
		}

		return true;
	}

	/**
	 * Value at
	 *
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param string $key
	 * @return mixed
	 */
	public function at($index, $key) {
		return $this->Data[$key][$index];
	}

	/**
	 * Get activity id
	 * @return int
	 */
	public function activityID() {
		return $this->Data[self::ACTIVITYID];
	}

	/**
	 * Get time
	 * @return array unit: [s]
	 */
	public function time() {
		return $this->Data[self::TIME];
	}

	/**
	 * Total time
	 * @return int
	 */
	public function totalTime() {
		if (empty($this->Data[self::TIME])) {
			return 0;
		}

		return $this->Data[self::TIME][$this->numberOfPoints-1];
	}

	/**
	 * Get distance
	 * @return array unit: [km]
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Total distance
	 * @return int
	 */
	public function totalDistance() {
		return $this->Data[self::DISTANCE][$this->numberOfPoints-1];
	}

	/**
	 * @return float
	 */
	public function totalPace() {
		if (!empty($this->Data[self::DISTANCE])) {
			return $this->totalTime() / $this->totalDistance();
		}

		return 0.0;
	}

	/**
	 * Get pace
	 * @return array unit: [s/km]
	 */
	public function pace() {
		return $this->Data[self::PACE];
	}

    /**
     * Theoretical pace will force the loop to ignore distance data for average pace
     * @param array $data
     * @param bool $flag
     */
	public function setTheoreticalPace(array $data, $flag = true) {
	    $this->set(self::PACE, $data);

	    $this->hasTheoreticalPace = $flag;
    }

    /**
     * @return bool
     */
    public function hasTheoreticalPace() {
	    return $this->hasTheoreticalPace;
    }

	/**
	 * Get heart rate
	 * @return array unit: [bpm]
	 */
	public function heartRate() {
		return $this->Data[self::HEARTRATE];
	}

	/**
	 * Get cadence
	 * @return array unit: [rpm]
	 */
	public function cadence() {
		return $this->Data[self::CADENCE];
	}

	/**
	 * Get stride length
	 * @return array unit: [cm]
	 */
	public function strideLength() {
		return $this->Data[self::STRIDE_LENGTH];
	}

	/**
	 * Get power
	 * @return array unit: [W]
	 */
	public function power() {
		return $this->Data[self::POWER];
	}

	/**
	 * Get temperature
	 * @return array unit: [°C]
	 */
	public function temperature() {
		return $this->Data[self::TEMPERATURE];
	}

	/**
	 * Get ground contact time
	 * @return array unit: [ms]
	 */
	public function groundcontact() {
		return $this->Data[self::GROUNDCONTACT];
	}

	/**
	 * Get vertical oscillation
	 * @return array unit: [mm]
	 */
	public function verticalOscillation() {
		return $this->Data[self::VERTICAL_OSCILLATION];
	}

	/**
	 * Get ground contact time balance
	 * @return array unit: [%*100]
	 */
	public function groundContactBalance() {
		return $this->Data[self::GROUNDCONTACT_BALANCE];
	}

	/**
	 * Get vertical ratio
	 * @return array unit: [%]
	 */
	public function verticalRatio() {
		return $this->Data[self::VERTICAL_RATIO];
	}

    /**
     * Get smo2 (0)
     * @return array unit: [%]
     */
    public function smo2_0() {
        return $this->Data[self::SMO2_0];
    }

    /**
     * Get smo2 (11)
     * @return array unit: [%]
     */
    public function smo2_1() {
        return $this->Data[self::SMO2_1];
    }

    /**
     * Get thb (0)
     * @return array unit: [%]
     */
    public function thb_0() {
        return $this->Data[self::THB_0];
    }

    /**
     * Get thb (1)
     * @return array unit: [%]
     */
    public function thb_1() {
        return $this->Data[self::THB_1];
    }

    /**
     * Get impact GS left
     * @return array unit: [G]
     */
    public function impactGsLeft() {
        return $this->Data[self::IMPACT_GS_LEFT];
    }

    /**
     * Get impact GS right
     * @return array unit: [G]
     */
    public function impactGsRight() {
        return $this->Data[self::IMPACT_GS_RIGHT];
    }

    /**
     * Get braking GS left
     * @return array unit: [G]
     */
    public function brakingGsLeft() {
        return $this->Data[self::BRAKING_GS_LEFT];
    }

    /**
     * Get braking GS right
     * @return array unit: [G]
     */
    public function brakingGsRight() {
        return $this->Data[self::BRAKING_GS_RIGHT];
    }

    /**
     * Get footstrike type left
     * @return array unit: [G]
     */
    public function footstrikeTypeLeft() {
        return $this->Data[self::FOOTSTRIKE_TYPE_LEFT];
    }

    /**
     * Get footstrike type right
     * @return array unit: [G]
     */
    public function footstrikeTypeRight() {
        return $this->Data[self::FOOTSTRIKE_TYPE_RIGHT];
    }

    /**
     * Get footstrike type left
     * @return array unit: [°]
     */
    public function pronationExcursionLeft() {
        return $this->Data[self::PRONATION_EXCURSION_LEFT];
    }

    /**
     * Get footstrike type right
     * @return array unit: [°]
     */
    public function pronationExcursionRight() {
        return $this->Data[self::PRONATION_EXCURSION_RIGHT];
    }

    /**
     * @return bool
     */
    public function hasRunScribeData() {
        return !empty($this->Data[self::IMPACT_GS_LEFT]) ||
            !empty($this->Data[self::IMPACT_GS_RIGHT]) ||
            !empty($this->Data[self::BRAKING_GS_LEFT]) ||
            !empty($this->Data[self::BRAKING_GS_RIGHT]) ||
            !empty($this->Data[self::FOOTSTRIKE_TYPE_LEFT]) ||
            !empty($this->Data[self::FOOTSTRIKE_TYPE_RIGHT]) ||
            !empty($this->Data[self::PRONATION_EXCURSION_LEFT]) ||
            !empty($this->Data[self::PRONATION_EXCURSION_RIGHT]);
    }

	/**
	 * Get pauses
	 * @return \Runalyze\Model\Trackdata\Pauses
	 */
	public function pauses() {
		return $this->Pauses;
	}

	/**
	 * Are there pauses?
	 * @return bool
	 */
	public function hasPauses() {
		return !$this->Pauses->isEmpty();
	}

	/*
	 * Calculate pace array
	 */
	public function calculatePaceArray() {
		if (!$this->has(self::PACE)) {
			$PaceCalculator = new PaceCalculator($this);
			$PaceCalculator->calculate();

			$this->set(self::PACE, $PaceCalculator->result());
		}
	}

	/*
	 * Calculate stride length array
	 */
	protected function calculateStrideLengthArray() {
		if (!$this->has(self::STRIDE_LENGTH)) {
			$StridesCalculator = new StrideLength\Calculator($this);
			$StridesCalculator->calculate();

			$this->set(self::STRIDE_LENGTH, $StridesCalculator->stridesData());
		}
	}

	/*
	 * Calculate vertical ratio array
	 */
	protected function calculateVerticalRatioArray() {
		if (!$this->has(self::VERTICAL_RATIO)) {
			$RatioCalculator = new VerticalRatioCalculator($this);
			$RatioCalculator->calculate();

			$this->set(self::VERTICAL_RATIO, $RatioCalculator->verticalRatioData());
		}
	}
}

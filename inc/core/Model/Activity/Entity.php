<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Data\Weather;
use Runalyze\Model;
use Runalyze\Model\Activity\Splits;

/**
 * Activity entity
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Entity extends Model\EntityWithID {
	/**
	 * Key: timestamp
	 * @var string
	 */
	const TIMESTAMP = 'time';

	/**
	 * Key: timezone offset [minutes]
	 * @var string
	 */
	const TIMEZONE_OFFSET = 'timezone_offset';

	/**
	 * Key: timestamp created
	 * @var string
	 */
	const TIMESTAMP_CREATED = 'created';

	/**
	 * Key: timestamp last edit
	 * @var string
	 */
	const TIMESTAMP_EDITED = 'edited';

	/**
	 * Key: sportid
	 * @var string
	 */
	const SPORTID = 'sportid';

	/**
	 * Key: typeid
	 * @var string
	 */
	const TYPEID = 'typeid';

	/**
	 * Key: is public
	 * @var string
	 */
	const IS_PUBLIC = 'is_public';

	/**
	 * Key: is track
	 * @var string
	 */
	const IS_TRACK = 'is_track';

	/**
	 * Key: distance
	 * @var string
	 */
	const DISTANCE = 'distance';

	/**
	 * Key: time in seconds
	 * @var string
	 */
	const TIME_IN_SECONDS = 's';

	/**
	 * Key: elapsed time
	 * @var string
	 */
	const ELAPSED_TIME = 'elapsed_time';

	/**
	 * Key: elevation
	 * @var string
	 */
	const ELEVATION = 'elevation';

    /**
     * Key: climb score
     * @var string
     */
    const CLIMB_SCORE = 'climb_score';

    /**
     * Key: percentage hilly
     * @var string
     */
    const PERCENTAGE_HILLY = 'percentage_hilly';

	/**
	 * Key: energy
	 * @var string
	 */
	const ENERGY = 'kcal';

	/**
	 * Key: average heart rate
	 * @var string
	 */
	const HR_AVG = 'pulse_avg';

	/**
	 * Key: maximal heart rate
	 * @var string
	 */
	const HR_MAX = 'pulse_max';

	/**
	 * Key: vo2max
	 * @var string
	 */
	const VO2MAX = 'vo2max';

	/**
	 * Key: vo2max by time
	 * @var string
	 */
	const VO2MAX_BY_TIME = 'vo2max_by_time';

	/**
	 * Key: vo2max with elevation
	 * @var string
	 */
	const VO2MAX_WITH_ELEVATION = 'vo2max_with_elevation';

	/**
	 * Key: use vo2max
	 * @var string
	 */
	const USE_VO2MAX = 'use_vo2max';

	/**
	 * Key: vo2max estimate from fit file
	 * @var string
	 */
	const FIT_VO2MAX_ESTIMATE = 'fit_vo2max_estimate';

	/**
	 * Key: recovery time from fit file
	 * @var string
	 */
	const FIT_RECOVERY_TIME = 'fit_recovery_time';

	/**
	 * Key: hrv analysis score from fit file
	 * @var string
	 */
	const FIT_HRV_ANALYSIS = 'fit_hrv_analysis';

	/**
	 * Key: total training effect from fit file
	 * @var string
	 */
	const FIT_TRAINING_EFFECT = 'fit_training_effect';

	/**
	 * Key: performance condition from fit file
	 * @var string
	 */
	const FIT_PERFORMANCE_CONDITION = 'fit_performance_condition';

    /**
     * Key: performance condition from fit file
     * @var string
     */
    const FIT_PERFORMANCE_CONDITION_END = 'fit_performance_condition_end';

	/**
	 * Key: RPE
	 * @var string
	 */
	const RPE = 'rpe';

	/**
	 * Key: trimp
	 * @var string
	 */
	const TRIMP = 'trimp';

	/**
	 * Key: cadence
	 * @var string
	 */
	const CADENCE = 'cadence';

	/**
	 * Key: power
	 * @var string
	 */
	const POWER = 'power';

    /**
     * Key: power
     * @var string
     */
    const IS_POWER_CALCULATED = 'is_power_calculated';

	/**
	 * Key: stride length
	 * @var string
	 */
	const STRIDE_LENGTH = 'stride_length';

	/**
	 * Key: total_strokes
	 * @var string
	 */
	const TOTAL_STROKES = 'total_strokes';

	/**
	 * Key: SWOLF
	 * @var string
	 */
	const SWOLF = 'swolf';

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
	 * Key: ground contact time balance
	 * @var string
	 */
	const GROUNDCONTACT_BALANCE = 'groundcontact_balance';

	/**
	 * Key: vertical ratio
	 * @var string
	 */
	const VERTICAL_RATIO = 'vertical_ratio';

    /**
     * @var string
     */
    const AVG_IMPACT_GS_LEFT = 'avg_impact_gs_left';

    /**
     * @var string
     */
    const AVG_IMPACT_GS_RIGHT = 'avg_impact_gs_right';

    /**
     * @var string
     */
    const AVG_BRAKING_GS_LEFT = 'avg_braking_gs_left';

    /**
     * @var string
     */
    const AVG_BRAKING_GS_RIGHT = 'avg_braking_gs_right';

    /**
     * @var string
     */
    const AVG_FOOTSTRIKE_TYPE_LEFT = 'avg_footstrike_type_left';

    /**
     * @var string
     */
    const AVG_FOOTSTRIKE_TYPE_RIGHT = 'avg_footstrike_type_right';

    /**
     * @var string
     */
    const AVG_PRONATION_EXCURSION_LEFT = 'avg_pronation_excursion_left';

    /**
     * @var string
     */
    const AVG_PRONATION_EXCURSION_RIGHT = 'avg_pronation_excursion_right';

	/**
	 * Key: temperature
	 * @var string
	 */
	const TEMPERATURE = 'temperature';

	/**
	 * Key: wind speed
	 * @var string
	 */
	const WINDSPEED = 'wind_speed';

	/**
	 * Key: wind degree
	 * @var string
	 */
	const WINDDEG = 'wind_deg';

	/**
	 * Key: humidity
	 * @var string
	 */
	const HUMIDITY = 'humidity';

	/**
	 * Key: pressure
	 * @var string
	 */
	const PRESSURE = 'pressure';

	/**
	 * Key: weather id
	 * @var string
	 */
	const WEATHERID = 'weatherid';

	/**
	 * Key: weather source
	 * @var string
	 */
	const WEATHER_SOURCE = 'weather_source';

	/**
	 * Key: is night
	 * @var string
	 */
	const IS_NIGHT = 'is_night';

	/**
	 * Key: route id
	 * @var string
	 */
	const ROUTEID = 'routeid';

	/**
	 * Key: route
	 * @var string
	 * @deprecated
	 */
	const ROUTE = 'route';

	/**
	 * Key: splits
	 * @var string
	 */
	const SPLITS = 'splits';

	/**
	 * Key: title
	 * @var string
	 */
	const TITLE = 'title';

	/**
	 * Key: partner
	 * @var string
	 */
	const PARTNER = 'partner';

	/**
	 * Key: notes
	 * @var string
	 */
	const NOTES = 'notes';

	/**
	 * Key: creator
	 * @var string
	 */
	const CREATOR = 'creator';

	/**
	 * Key: creator details
	 * @var string
	 */
	const CREATOR_DETAILS = 'creator_details';

	/**
	 * Key: activity id
	 * @var string
	 */
	const ACTIVITY_ID = 'activity_id';

	/**
	 * Weather
	 * @var \Runalyze\Data\Weather
	 */
	protected $Weather = null;

	/**
	 * Splits
	 * @var \Runalyze\Model\Activity\Splits\Entity
	 */
	protected $Splits = null;

	/**
	 * Partner
	 * @var \Runalyze\Model\Activity\Partner
	 */
	protected $Partner = null;

	/**
	 * Clone object
	 */
	public function __clone() {
		$this->cloneInternalObjects();
	}

	/**
	 * All properties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::TIMESTAMP,
			self::TIMEZONE_OFFSET,
			self::TIMESTAMP_CREATED,
			self::TIMESTAMP_EDITED,
			self::SPORTID,
			self::TYPEID,
			self::IS_PUBLIC,
			self::IS_TRACK,
			self::DISTANCE,
			self::TIME_IN_SECONDS,
			self::ELAPSED_TIME,
			self::ELEVATION,
            self::CLIMB_SCORE,
            self::PERCENTAGE_HILLY,
			self::ENERGY,
			self::HR_AVG,
			self::HR_MAX,
			self::VO2MAX,
			self::VO2MAX_BY_TIME,
			self::VO2MAX_WITH_ELEVATION,
			self::USE_VO2MAX,
			self::FIT_VO2MAX_ESTIMATE,
			self::FIT_RECOVERY_TIME,
			self::FIT_HRV_ANALYSIS,
			self::FIT_TRAINING_EFFECT,
			self::FIT_PERFORMANCE_CONDITION,
            self::FIT_PERFORMANCE_CONDITION_END,
            self::RPE,
			self::TRIMP,
			self::CADENCE,
			self::POWER,
			self::IS_POWER_CALCULATED,
			self::STRIDE_LENGTH,
			self::SWOLF,
			self::TOTAL_STROKES,
			self::GROUNDCONTACT,
			self::VERTICAL_OSCILLATION,
			self::GROUNDCONTACT_BALANCE,
			self::VERTICAL_RATIO,
			self::AVG_IMPACT_GS_LEFT,
			self::AVG_IMPACT_GS_RIGHT,
			self::AVG_BRAKING_GS_LEFT,
			self::AVG_BRAKING_GS_RIGHT,
			self::AVG_FOOTSTRIKE_TYPE_LEFT,
			self::AVG_FOOTSTRIKE_TYPE_RIGHT,
			self::AVG_PRONATION_EXCURSION_LEFT,
			self::AVG_PRONATION_EXCURSION_RIGHT,
			self::TEMPERATURE,
			self::WINDSPEED,
			self::WINDDEG,
			self::HUMIDITY,
			self::PRESSURE,
			self::WEATHERID,
			self::WEATHER_SOURCE,
			self::IS_NIGHT,
			self::ROUTEID,
			self::ROUTE,
			self::SPLITS,
			self::TITLE,
			self::PARTNER,
			self::NOTES,
			self::CREATOR,
			self::CREATOR_DETAILS,
			self::ACTIVITY_ID
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allDatabaseProperties();
	}

	/**
	 * Can set key?
	 * @param string $key
	 * @return boolean
	 */
	protected function canSet($key) {
		switch ($key) {
			case self::TEMPERATURE:
			case self::WINDSPEED:
			case self::WINDDEG:
			case self::HUMIDITY:
			case self::PRESSURE:
			case self::WEATHERID:
			case self::WEATHER_SOURCE:
			case self::PARTNER:
			case self::SPLITS:
				return false;
		}

		return true;
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
            case self::TYPEID:
            case self::TIMESTAMP_CREATED:
            case self::TIMESTAMP_EDITED:
			case self::TIMEZONE_OFFSET:
            case self::DISTANCE:
            case self::ELAPSED_TIME:
            case self::ELEVATION:
            case self::CLIMB_SCORE:
            case self::PERCENTAGE_HILLY:
            case self::ENERGY:
            case self::HR_AVG:
            case self::HR_MAX:
            case self::VO2MAX:
            case self::VO2MAX_BY_TIME:
            case self::VO2MAX_WITH_ELEVATION:
            case self::FIT_VO2MAX_ESTIMATE:
            case self::FIT_RECOVERY_TIME:
            case self::FIT_HRV_ANALYSIS:
            case self::FIT_TRAINING_EFFECT:
            case self::FIT_PERFORMANCE_CONDITION:
            case self::FIT_PERFORMANCE_CONDITION_END:
            case self::RPE:
            case self::TRIMP:
            case self::CADENCE:
            case self::POWER:
            case self::IS_POWER_CALCULATED:
            case self::TOTAL_STROKES:
            case self::SWOLF:
            case self::STRIDE_LENGTH:
            case self::GROUNDCONTACT:
            case self::GROUNDCONTACT_BALANCE:
            case self::VERTICAL_OSCILLATION:
            case self::VERTICAL_RATIO:
            case self::AVG_IMPACT_GS_LEFT:
            case self::AVG_IMPACT_GS_RIGHT:
            case self::AVG_BRAKING_GS_LEFT:
            case self::AVG_BRAKING_GS_RIGHT:
            case self::AVG_FOOTSTRIKE_TYPE_LEFT:
            case self::AVG_FOOTSTRIKE_TYPE_RIGHT:
            case self::AVG_PRONATION_EXCURSION_LEFT:
            case self::AVG_PRONATION_EXCURSION_RIGHT:
			case self::TEMPERATURE:
			case self::WINDSPEED:
			case self::WINDDEG:
			case self::HUMIDITY:
			case self::PRESSURE:
			case self::WEATHER_SOURCE:
			case self::IS_NIGHT:
			case self::NOTES:
			case self::CREATOR_DETAILS:
            case self::ROUTEID:
			case self::ACTIVITY_ID:
				return true;
		}

		return false;
	}

	/**
	 * Synchronize
	 */
	public function synchronize() {
		parent::synchronize();

		$this->ensureNullIfEmpty(self::ACTIVITY_ID);
		$this->ensureNullIfEmpty(self::IS_NIGHT, true);
        $this->ensureNullIfEmpty(self::CLIMB_SCORE, true);
        $this->ensureNullIfEmpty(self::PERCENTAGE_HILLY, true);
        $this->ensureAllNullOrNumericValues();
		$this->ensureAllNumericValues();
		$this->synchronizeObjects();
	}

	protected function ensureAllNullOrNumericValues() {
        $this->ensureNullIfEmpty([
            self::TYPEID,
            self::TIMESTAMP_CREATED,
            self::TIMESTAMP_EDITED,
            self::TIMEZONE_OFFSET,
            self::DISTANCE,
            self::ELAPSED_TIME,
            self::ELEVATION,
            self::ENERGY,
            self::HR_AVG,
            self::HR_MAX,
            self::VO2MAX,
            self::VO2MAX_BY_TIME,
            self::VO2MAX_WITH_ELEVATION,
            self::FIT_VO2MAX_ESTIMATE,
            self::FIT_RECOVERY_TIME,
            self::FIT_HRV_ANALYSIS,
            self::FIT_TRAINING_EFFECT,
            self::FIT_PERFORMANCE_CONDITION,
            self::FIT_PERFORMANCE_CONDITION_END,
            self::RPE,
            self::TRIMP,
            self::CADENCE,
            self::POWER,
            self::IS_POWER_CALCULATED,
            self::TOTAL_STROKES,
            self::SWOLF,
            self::STRIDE_LENGTH,
            self::GROUNDCONTACT,
            self::GROUNDCONTACT_BALANCE,
            self::VERTICAL_OSCILLATION,
            self::VERTICAL_RATIO,
            self::AVG_IMPACT_GS_LEFT,
            self::AVG_IMPACT_GS_RIGHT,
            self::AVG_BRAKING_GS_LEFT,
            self::AVG_BRAKING_GS_RIGHT,
            self::AVG_FOOTSTRIKE_TYPE_LEFT,
            self::AVG_FOOTSTRIKE_TYPE_RIGHT,
            self::AVG_PRONATION_EXCURSION_LEFT,
            self::AVG_PRONATION_EXCURSION_RIGHT,
            self::ROUTEID
        ], true, true);
    }

	/**
	 * Ensure that numeric fields get numeric values
	 */
	protected function ensureAllNumericValues() {
		$this->ensureNumericValue(array(
			self::SPORTID,
			self::TIMESTAMP,
			self::IS_PUBLIC,
			self::IS_TRACK,
			self::TIME_IN_SECONDS,
			self::USE_VO2MAX
		));
	}

	protected function synchronizeObjects() {
		$this->Data[self::TEMPERATURE] = $this->weather()->temperature()->value();
		$this->Data[self::WINDSPEED] = $this->weather()->windSpeed()->value();
		$this->Data[self::WINDDEG] = $this->weather()->windDegree()->value();
		$this->Data[self::HUMIDITY] = $this->weather()->humidity()->value();
		$this->Data[self::PRESSURE] = $this->weather()->pressure()->value();
		$this->Data[self::WEATHERID] = $this->weather()->condition()->id();
		$this->Data[self::WEATHER_SOURCE] = $this->weather()->source();
		$this->Data[self::SPLITS] = $this->splits()->asString();
		$this->Data[self::PARTNER] = $this->partner()->asString();
	}

	/**
	 * Timestamp
	 * @return int
	 */
	public function timestamp() {
		return $this->Data[self::TIMESTAMP];
	}

	/**
	 * @return bool
	 */
	public function knowsTimezoneOffset() {
		return (null !== $this->Data[self::TIMEZONE_OFFSET]);
	}

	/**
	 * @return int|null offset in minutes or null if unknown
	 */
	public function timezoneOffset() {
		return $this->Data[self::TIMEZONE_OFFSET];
	}

	/**
	 * Sportid
	 * @return int
	 */
	public function sportid() {
		return $this->Data[self::SPORTID];
	}

	/**
	 * Typeid
	 * @return null|int
	 */
	public function typeid() {
		return $this->Data[self::TYPEID];
	}

	/**
	 * Is public?
	 * @return bool
	 */
	public function isPublic() {
		return ($this->Data[self::IS_PUBLIC] == 1);
	}

	/**
	 * On track?
	 * @return bool
	 */
	public function isTrack() {
		return ($this->Data[self::IS_TRACK] == 1);
	}

	/**
	 * Distance
	 * @return null|float [km]
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Time in seconds
	 * @return int [s]
	 */
	public function duration() {
		return $this->Data[self::TIME_IN_SECONDS];
	}

	/**
	 * Elapsed time
	 * @return null|int [s]
	 */
	public function elapsedTime() {
		return $this->Data[self::ELAPSED_TIME];
	}

	/**
	 * Elevation
	 * @return null|int [m]
	 */
	public function elevation() {
		return $this->Data[self::ELEVATION];
	}

    /**
     * @return null|float [0.0 .. 1.0]
     */
    public function climbScore() {
        return $this->Data[self::CLIMB_SCORE];
    }

    /**
     * @return null|float [0.00 .. 1.00]
     */
    public function percentageHilly() {
        return $this->Data[self::PERCENTAGE_HILLY];
    }

	/**
	 * Energy
	 * @return null|int [kcal]
	 */
	public function energy() {
		return $this->Data[self::ENERGY];
	}

	/**
	 * Average heart rate
	 * @return null|int [bpm]
	 */
	public function hrAvg() {
		return $this->Data[self::HR_AVG];
	}

	/**
	 * Maximal heart rate
	 * @return null|int [bpm]
	 */
	public function hrMax() {
		return $this->Data[self::HR_MAX];
	}

	/**
	 * @return null|float [ml/kg/min]
	 */
	public function vo2maxByHeartRate() {
		return $this->Data[self::VO2MAX];
	}

	/**
	 * @return null|float [ml/kg/min]
	 */
	public function vo2maxByTime() {
		return $this->Data[self::VO2MAX_BY_TIME];
	}

	/**
	 * @return null|float [ml/kg/min]
	 */
	public function vo2maxWithElevation() {
		return $this->Data[self::VO2MAX_WITH_ELEVATION];
	}

	/**
	 * @return bool
	 */
	public function usesVO2max() {
		return ($this->Data[self::USE_VO2MAX] == 1);
	}

	/**
	 * VO2max estimate from fit file
	 * @return null|float [ml/kg/min]
	 */
	public function fitVO2maxEstimate() {
		return $this->Data[self::FIT_VO2MAX_ESTIMATE];
	}

	/**
	 * Recovery time advisor from fit file
	 * @return null|int [min]
	 */
	public function fitRecoveryTime() {
		return $this->Data[self::FIT_RECOVERY_TIME];
	}

	/**
	 * HRV analysis score from fit file
	 * @return null|int
	 */
	public function fitHRVscore() {
		return $this->Data[self::FIT_HRV_ANALYSIS];
	}

	/**
	 * Total training effect
	 * @return null|float
	 */
	public function fitTrainingEffect() {
		return $this->Data[self::FIT_TRAINING_EFFECT];
	}

	/**
	 * Performance condition
	 * @return null|int
	 */
	public function fitPerformanceCondition() {
		return $this->Data[self::FIT_PERFORMANCE_CONDITION];
	}

    /**
     * Ending performance condition
     * @return null|int
     */
    public function fitPerformanceConditionEnd() {
        return $this->Data[self::FIT_PERFORMANCE_CONDITION_END];
    }

	/**
	 * @return null|int
	 */
	public function rpe() {
		return $this->Data[self::RPE];
	}

	/**
	 * TRIMP
	 * @return null|int
	 */
	public function trimp() {
		return $this->Data[self::TRIMP];
	}

	/**
	 * Cadence
	 * @return null|int [rpm]
	 */
	public function cadence() {
		return $this->Data[self::CADENCE];
	}

	/**
	 * Power
	 * @return null|int [W]
	 */
	public function power() {
		return $this->Data[self::POWER];
	}

    /**
     * @return null|bool
     */
	public function isPowerCalculated() {
	    return null === $this->Data[self::IS_POWER_CALCULATED] ? null : 1 == $this->Data[self::IS_POWER_CALCULATED];
    }

	/**
	 * Stride length
	 * @return null|int [cm]
	 */
	public function strideLength() {
		return $this->Data[self::STRIDE_LENGTH];
	}

	/**
	 * Total strokes
	 * @return null|int
	 */
	public function totalStrokes() {
		return $this->Data[self::TOTAL_STROKES];
	}

	/**
	 * Swolf
	 * @return null|int
	 */
	public function swolf() {
		return $this->Data[self::SWOLF];
	}

	/**
	 * Ground contact
	 * @return null|int [ms]
	 */
	public function groundcontact() {
		return $this->Data[self::GROUNDCONTACT];
	}

	/**
	 * Vertical oscillation
	 * @return null|int [mm]
	 */
	public function verticalOscillation() {
		return $this->Data[self::VERTICAL_OSCILLATION];
	}

	/**
	 * Ground contact time balance
	 * @return null|int [%oo]
	 */
	public function groundContactBalance() {
		return $this->Data[self::GROUNDCONTACT_BALANCE];
	}

	/**
	 * Ground contact time balance
	 * @return null|int [%]
	 */
	public function groundContactBalanceLeft() {
		return $this->Data[self::GROUNDCONTACT_BALANCE];
	}

	/**
	 * Ground contact time balance
	 * @return null|int [%]
	 */
	public function groundContactBalanceRight() {
	    if (null === $this->Data[self::GROUNDCONTACT_BALANCE]) {
	        return null;
        }

		return 10000 - $this->Data[self::GROUNDCONTACT_BALANCE];
	}

	/**
	 * Vertical ratio
	 * @return null|int [%o]
	 */
	public function verticalRatio() {
		return $this->Data[self::VERTICAL_RATIO];
	}

    /**
     * @return float|null [ms] can be negative for walking
     */
    public function flightTime() {
        if ($this->Data[self::CADENCE] > 0 && $this->Data[self::GROUNDCONTACT] > 0) {
            return 30000.0 / $this->Data[self::CADENCE] - $this->Data[self::GROUNDCONTACT];
        }

        return null;
    }

    /**
     * @return float|null [%] can be negative for walking
     */
    public function flightRatio() {
        if ($this->Data[self::CADENCE] > 0 && $this->Data[self::GROUNDCONTACT] > 0) {
            return 1.0 - $this->Data[self::CADENCE] * $this->Data[self::GROUNDCONTACT] / 30000.0;
        }

        return null;
    }

    /**
     * @return null|float [G]
     */
    public function impactGsLeft() {
        return $this->Data[self::AVG_IMPACT_GS_LEFT];
    }

    /**
     * @return null|float [G]
     */
    public function impactGsRight() {
        return $this->Data[self::AVG_IMPACT_GS_RIGHT];
    }

    /**
     * @return null|float [G]
     */
    public function brakingGsLeft() {
        return $this->Data[self::AVG_BRAKING_GS_LEFT];
    }

    /**
     * @return null|float [G]
     */
    public function brakingGsRight() {
        return $this->Data[self::AVG_BRAKING_GS_RIGHT];
    }

    /**
     * @return null|int [°]
     */
    public function footstrikeTypeLeft() {
        return $this->Data[self::AVG_FOOTSTRIKE_TYPE_LEFT];
    }

    /**
     * @return null|int [°]
     */
    public function footstrikeTypeRight() {
        return $this->Data[self::AVG_FOOTSTRIKE_TYPE_RIGHT];
    }

    /**
     * @return null|float [°]
     */
    public function pronationExcursionLeft() {
        return $this->Data[self::AVG_PRONATION_EXCURSION_LEFT];
    }

    /**
     * @return null|float [°]
     */
    public function pronationExcursionRight() {
        return $this->Data[self::AVG_PRONATION_EXCURSION_RIGHT];
    }

	/**
	 * Weather
	 * @return \Runalyze\Data\Weather
	 */
	public function weather() {
		if (is_null($this->Weather)) {
			$this->Weather = new Weather(
				new Weather\Temperature($this->Data[self::TEMPERATURE]),
				new Weather\Condition($this->Data[self::WEATHERID]),
				new Weather\WindSpeed($this->Data[self::WINDSPEED]),
				new Weather\WindDegree($this->Data[self::WINDDEG]),
				new Weather\Humidity($this->Data[self::HUMIDITY]),
				new Weather\Pressure($this->Data[self::PRESSURE])
			);
			$this->Weather->setSource($this->Data[self::WEATHER_SOURCE]);
		}

		return $this->Weather;
	}

	/**
	 * @return bool
	 */
	public function isNight() {
		return ($this->Data[self::IS_NIGHT] == 1);
	}

	/**
	 * @return bool
	 */
	public function knowsIfItIsNight() {
		return (null !== $this->Data[self::IS_NIGHT]);
	}

	/**
	 * Splits
	 * @return \Runalyze\Model\Activity\Splits\Entity
	 */
	public function splits() {
		if (is_null($this->Splits)) {
			$this->Splits = new Splits\Entity($this->Data[self::SPLITS]);
		}

		return $this->Splits;
	}

	/**
	 * Title
	 * @return string
	 */
	public function title() {
		return $this->Data[self::TITLE];
	}

	/**
	 * Partner
	 * @return \Runalyze\Model\Activity\Partner
	 */
	public function partner() {
		if (is_null($this->Partner)) {
			$this->Partner = new Partner($this->Data[self::PARTNER]);
		}

		return $this->Partner;
	}

	/**
	 * Notes
	 * @return string
	 */
	public function notes() {
		return $this->Data[self::NOTES];
	}

	/**
	 * Unset running values
	 */
	public function unsetRunningValues() {
		$this->set(Entity::VO2MAX_BY_TIME, null);
		$this->set(Entity::VO2MAX, null);
		$this->set(Entity::VO2MAX_WITH_ELEVATION, null);
	}
}

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
	 * Key: calories
	 * @var string
	 */
	const CALORIES = 'kcal';

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
	 * Key: vdot
	 * @var string
	 */
	const VDOT = 'vdot';

	/**
	 * Key: vdot by time
	 * @var string
	 */
	const VDOT_BY_TIME = 'vdot_by_time';

	/**
	 * Key: vdot with elevation
	 * @var string
	 */
	const VDOT_WITH_ELEVATION = 'vdot_with_elevation';

	/**
	 * Key: use vdot
	 * @var string
	 */
	const USE_VDOT = 'use_vdot';

	/**
	 * Key: vdot estimate from fit file
	 * @var string
	 */
	const FIT_VO2MAX_ESTIMATE = 'fit_vdot_estimate';

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
	 * Key: jd intensity
	 * @var string
	 */
	const JD_INTENSITY = 'jd_intensity';

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
	 * Key: temperature
	 * @var string
	 */
	const TEMPERATURE = 'temperature';

	/**
	 * Key: weather id
	 * @var string
	 */
	const WEATHERID = 'weatherid';

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
	 * Key: comment
	 * @var string
	 */
	const COMMENT = 'comment';

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
	 * Key: garmin activity id
	 * @var string
	 */
	const GARMIN_ACTIVITY_ID = 'activity_id';

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
			self::CALORIES,
			self::HR_AVG,
			self::HR_MAX,
			self::VDOT,
			self::VDOT_BY_TIME,
			self::VDOT_WITH_ELEVATION,
			self::USE_VDOT,
			self::FIT_VO2MAX_ESTIMATE,
			self::FIT_RECOVERY_TIME,
			self::FIT_HRV_ANALYSIS,
			self::JD_INTENSITY,
			self::TRIMP,
			self::CADENCE,
			self::POWER,
			self::STRIDE_LENGTH,
			self::SWOLF,
			self::TOTAL_STROKES,
			self::GROUNDCONTACT,
			self::VERTICAL_OSCILLATION,
			self::GROUNDCONTACT_BALANCE,
			self::VERTICAL_RATIO,
			self::TEMPERATURE,
			self::WEATHERID,
			self::ROUTEID,
			self::ROUTE,
			self::SPLITS,
			self::COMMENT,
			self::PARTNER,
			self::NOTES,
			self::CREATOR,
			self::CREATOR_DETAILS,
			self::GARMIN_ACTIVITY_ID
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
			case self::WEATHERID:
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
			case self::TEMPERATURE:
			case self::NOTES:
			case self::CREATOR_DETAILS:
				return true;
		}

		return false;
	}

	/**
	 * Get value for this key
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if ($key == self::TEMPERATURE) {
			return $this->Data[self::TEMPERATURE];
		}

		return parent::get($key);
	}

	/**
	 * Synchronize
	 */
	public function synchronize() {
		parent::synchronize();

		$this->ensureAllNumericValues();
		$this->synchronizeObjects();
	}

	/**
	 * Ensure that numeric fields get numeric values
	 */
	protected function ensureAllNumericValues() {
		$this->ensureNumericValue(array(
			self::SPORTID,
			self::TYPEID,
			self::TIMESTAMP,
			self::TIMESTAMP_CREATED,
			self::TIMESTAMP_EDITED,
			self::IS_PUBLIC,
			self::IS_TRACK,
			self::DISTANCE,
			self::TIME_IN_SECONDS,
			self::ELAPSED_TIME,
			self::ELEVATION,
			self::CALORIES,
			self::HR_AVG,
			self::HR_MAX,
			self::VDOT,
			self::VDOT_BY_TIME,
			self::VDOT_WITH_ELEVATION,
			self::USE_VDOT,
			self::FIT_VO2MAX_ESTIMATE,
			self::FIT_RECOVERY_TIME,
			self::FIT_HRV_ANALYSIS,
			self::JD_INTENSITY,
			self::TRIMP,
			self::CADENCE,
			self::POWER,
			self::STRIDE_LENGTH,
			self::TOTAL_STROKES,
			self::SWOLF,
			self::GROUNDCONTACT,
			self::VERTICAL_OSCILLATION,
			self::GROUNDCONTACT_BALANCE,
			self::VERTICAL_RATIO,
			self::ROUTEID
		));
	}

	protected function synchronizeObjects() {
		$this->Data[self::TEMPERATURE] = $this->weather()->temperature()->value();
		$this->Data[self::WEATHERID] = $this->weather()->condition()->id();
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
	 * Sportid
	 * @return int
	 */
	public function sportid() {
		return $this->Data[self::SPORTID];
	}

	/**
	 * Typeid
	 * @return int
	 */
	public function typeid() {
		return $this->Data[self::TYPEID];
	}

	/**
	 * Type
	 * @return \Type
	 */
	public function type() {
		return new \Type($this->Data[self::TYPEID]);
	}

	/**
	 * Is public?
	 * @return boolean
	 */
	public function isPublic() {
		return ($this->Data[self::IS_PUBLIC] == 1);
	}

	/**
	 * On track?
	 * @return boolean
	 */
	public function isTrack() {
		return ($this->Data[self::IS_TRACK] == 1);
	}

	/**
	 * Distance
	 * @return float [km]
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
	 * @return int [s]
	 */
	public function elapsedTime() {
		return $this->Data[self::ELAPSED_TIME];
	}

	/**
	 * Elevation
	 * @return int [m]
	 */
	public function elevation() {
		return $this->Data[self::ELEVATION];
	}

	/**
	 * Calories
	 * @return int [kcal]
	 */
	public function calories() {
		return $this->Data[self::CALORIES];
	}

	/**
	 * Average heart rate
	 * @return int [bpm]
	 */
	public function hrAvg() {
		return $this->Data[self::HR_AVG];
	}

	/**
	 * Maximal heart rate
	 * @return int [bpm]
	 */
	public function hrMax() {
		return $this->Data[self::HR_MAX];
	}

	/**
	 * VDOT by heart rate
	 * @return float
	 */
	public function vdotByHeartRate() {
		return $this->Data[self::VDOT];
	}

	/**
	 * VDOT by time
	 * @return float
	 */
	public function vdotByTime() {
		return $this->Data[self::VDOT_BY_TIME];
	}

	/**
	 * VDOT with elevation
	 * @return float
	 */
	public function vdotWithElevation() {
		return $this->Data[self::VDOT_WITH_ELEVATION];
	}

	/**
	 * Uses VDOT for shape
	 * @return boolean
	 */
	public function usesVDOT() {
		return ($this->Data[self::USE_VDOT] == 1);
	}

	/**
	 * VO2max estimate from fit file
	 * @return int
	 */
	public function fitVO2maxEstimate() {
		return $this->Data[self::FIT_VO2MAX_ESTIMATE];
	}

	/**
	 * Recovery time advisor from fit file
	 * @return int [min]
	 */
	public function fitRecoveryTime() {
		return $this->Data[self::FIT_RECOVERY_TIME];
	}

	/**
	 * HRV analysis score from fit file
	 * @return int
	 */
	public function fitHRVscore() {
		return $this->Data[self::FIT_HRV_ANALYSIS];
	}

	/**
	 * JD intensity
	 * @return int
	 */
	public function jdIntensity() {
		return $this->Data[self::JD_INTENSITY];
	}

	/**
	 * TRIMP
	 * @return int
	 */
	public function trimp() {
		return $this->Data[self::TRIMP];
	}

	/**
	 * Cadence
	 * @return int [rpm]
	 */
	public function cadence() {
		return $this->Data[self::CADENCE];
	}

	/**
	 * Power
	 * @return int [W]
	 */
	public function power() {
		return $this->Data[self::POWER];
	}

	/**
	 * Stride length
	 * @return int [cm]
	 */
	public function strideLength() {
		return $this->Data[self::STRIDE_LENGTH];
	}
        
	/**
	 * Total strokes
	 * @return int 
	 */
	public function totalStrokes() {
		return $this->Data[self::TOTAL_STROKES];
	}
        
	/**
	 * Swolf
	 * @return int 
	 */
	public function swolf() {
		return $this->Data[self::SWOLF];
	}

	/**
	 * Ground contact
	 * @return int [ms]
	 */
	public function groundcontact() {
		return $this->Data[self::GROUNDCONTACT];
	}

	/**
	 * Vertical oscillation
	 * @return int [mm]
	 */
	public function verticalOscillation() {
		return $this->Data[self::VERTICAL_OSCILLATION];
	}
	
	/**
	 * Ground contact time balance
	 * @return int [%oo]
	 */
	public function groundContactBalance() {
		return $this->Data[self::GROUNDCONTACT_BALANCE];
	}
	
	/**
	 * Ground contact time balance
	 * @return int [%]
	 */
	public function groundContactBalanceLeft() {
		return $this->Data[self::GROUNDCONTACT_BALANCE];
	}
	
	/**
	 * Ground contact time balance
	 * @return int [%]
	 */
	public function groundContactBalanceRight() {
		return 10000 - $this->Data[self::GROUNDCONTACT_BALANCE];
	}

	/**
	 * Vertical ratio
	 * @return int [%o]
	 */
	public function verticalRatio() {
		return $this->Data[self::VERTICAL_RATIO];
	}
        
	/**
	 * Weather
	 * @return \Runalyze\Data\Weather
	 */
	public function weather() {
		if (is_null($this->Weather)) {
			$this->Weather = new Weather(
				new Weather\Temperature($this->Data[self::TEMPERATURE]),
				new Weather\Condition($this->Data[self::WEATHERID])
			);
		}

		return $this->Weather;
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
	 * Comment
	 * @return string
	 */
	public function comment() {
		return $this->Data[self::COMMENT];
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
		$this->set(Entity::VDOT_BY_TIME, 0);
		$this->set(Entity::VDOT, 0);
		$this->set(Entity::VDOT_WITH_ELEVATION, 0);
		$this->set(Entity::JD_INTENSITY, 0);
	}
}

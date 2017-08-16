<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Sport
 */

namespace Runalyze\Model\Sport;

use Runalyze\Metrics\LegacyUnitConverter;
use Runalyze\Model;
use Runalyze\Profile\Sport\ProfileInterface;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\View\Icon\SportIcon;

/**
 * Sport entity
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Sport
 */
class Entity extends Model\EntityWithID implements ProfileInterface {
	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: img
	 * @var string
	 */
	const IMAGE = 'img';

	/**
	 * Key: short display
	 * @var string
	 */
	const SHORT = 'short';

	/**
	 * Key: kcal/h
	 * @var string
	 */
	const CALORIES_PER_HOUR = 'kcal';

	/**
	 * Key: average heart rate
	 * @var string
	 */
	const HR_AVG = 'HFavg';

	/**
	 * Key: pace unit
	 * @var string
	 */
	const PACE_UNIT = 'speed';

	/**
	 * Key: has distances
	 * @var string
	 */
	const HAS_DISTANCES = 'distances';

	/**
	 * Key: has power
	 * @var string
	 */
	const HAS_POWER = 'power';

	/**
	 * Key: is outside
	 * @var string
	 */
	const IS_OUTSIDE = 'outside';

	/**
	 * Key: id of main equipment (used for dataset)
	 * @var string
	 */
	const MAIN_EQUIPMENTTYPEID = 'main_equipmenttypeid';

	/**
	 * Key: id of default sport type (used for multi import/automatic upload)
	 * @var string
	 */
	const DEFAULT_TYPEID = 'default_typeid';

	/** @var string */
	const IS_MAIN = 'is_main';

	/** @var string */
	const INTERNAL_SPORT_ID = 'internal_sport_id';

	/**
	 * All properties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::NAME,
			self::IMAGE,
			self::SHORT,
			self::CALORIES_PER_HOUR,
			self::HR_AVG,
			self::HAS_DISTANCES,
			self::PACE_UNIT,
			self::HAS_POWER,
			self::IS_OUTSIDE,
			self::MAIN_EQUIPMENTTYPEID,
			self::DEFAULT_TYPEID,
            self::IS_MAIN,
            self::INTERNAL_SPORT_ID
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
	 * Name
	 * @return string
	 */
	public function name() {
		return $this->Data[self::NAME];
	}

    public function getName() {
        return $this->Data[self::NAME];
    }

    public function getInternalProfileEnum() {
        return $this->Data[self::INTERNAL_SPORT_ID] ?: SportProfile::GENERIC;
    }

	/**
	 * Icon
	 * @return \Runalyze\View\Icon\SportIcon
	 * @codeCoverageIgnore
	 */
	public function icon() {
		return new SportIcon($this->Data[self::IMAGE]);
	}

	public function getIconClass() {
        return $this->Data[self::IMAGE];
    }

    /**
	 * Uses short display?
	 * @return boolean
	 */
	public function usesShortDisplay() {
		return ($this->Data[self::SHORT] == 1);
	}

	/**
	 * Calories per hour
	 * @return int
	 */
	public function caloriesPerHour() {
		return $this->Data[self::CALORIES_PER_HOUR];
	}

    public function getCaloriesPerHour() {
        return $this->Data[self::CALORIES_PER_HOUR];
    }

	/**
	 * Average heartrate
	 * @return int
	 */
	public function avgHR() {
		return $this->Data[self::HR_AVG];
	}

    public function getAverageHeartRate() {
        return $this->Data[self::HR_AVG];
    }

	/**
	 * Pace unit
	 * @return \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	public function legacyPaceUnit() {
        return (new LegacyUnitConverter())->getLegacyPaceUnit($this->Data[self::PACE_UNIT]);
	}

	/**
	 * Pace unit
	 * @return int see \Runalyze\Activity\Pace or \Runalyze\Parameter\Application\PaceUnit
	 */
	public function getLegacyPaceUnitEnum() {
		return (new LegacyUnitConverter())->getLegacyPaceUnit($this->Data[self::PACE_UNIT], true);
	}

    public function getPaceUnitEnum() {
        return $this->Data[self::PACE_UNIT];
    }

	/**
	 * Has distances?
	 * @return boolean
	 */
	public function hasDistances() {
		return ($this->Data[self::HAS_DISTANCES] == 1);
	}

	/**
	 * Has power?
	 * @return boolean
	 */
	public function hasPower() {
		return ($this->Data[self::HAS_POWER] == 1);
	}

	/**
	 * Is this sport outside?
	 * @return boolean
	 */
	public function isOutside() {
		return ($this->Data[self::IS_OUTSIDE] == 1);
	}

	/**
	 * ID of main equipment type
	 * @return int
	 */
	public function mainEquipmentTypeID() {
		return $this->Data[self::MAIN_EQUIPMENTTYPEID];
	}

	/**
	 * ID of default type
	 * @return int
	 */
	public function defaultTypeID() {
		return $this->Data[self::DEFAULT_TYPEID];
	}

    /**
     * @return bool
     */
    public function isMainSport() {
        return ($this->Data[self::IS_MAIN] == 1);
    }
}

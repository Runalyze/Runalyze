<?php
/**
 * This file contains class::Keys
 * @package Runalyze
 */

namespace Runalyze\Dataset;

use Runalyze\Util\AbstractEnum;

/**
 * Enum for dataset keys and their internal ids
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
final class Keys extends AbstractEnum
{
	/** @var int */
	const SPORT = 1;

	/** @var int */
	const TYPE = 2;

	/** @var int */
	const DAYTIME = 3;

	/** @var int */
	const SHARED_LINK = 4;

	/** @var int */
	const DISTANCE = 5;

	/** @var int */
	const DURATION = 6;

	/** @var int */
	const PACE = 7;

	/** @var int */
	const ELAPSED_TIME = 8;

	/** @var int */
	const ELEVATION = 9;

	/** @var int */
	const CALORIES = 10;

	/** @var int */
	const HEARTRATE_AVG = 11;

	/** @var int */
	const HEARTRATE_MAX = 12;

	/** @var int */
	const VDOT_ICON = 13;

	/** @var int */
	const VDOT_VALUE = 14;

	/** @var int */
	const FIT_VO2MAX_ESTIMATE = 15;

	/** @var int */
	const FIT_RECOVERY_TIME = 16;

	/** @var int */
	const FIT_HRV_ANALYSIS = 17;

	/** @var int */
	const JD_INTENSITY = 18;

	/** @var int */
	const TRIMP = 19;

	/** @var int */
	const CADENCE = 20;

	/** @var int */
	const POWER = 21;

	/** @var int */
	const SWOLF = 22;

	/** @var int */
	const STRIDE_LENGTH = 23;

	/** @var int */
	const GROUNDCONTACT = 24;

	/** @var int */
	const VERTICAL_OSCILLATION = 25;

	/** @var int */
	const TEMPERATURE = 26;

	/** @var int */
	const WEATHER = 27;

	/** @var int */
	const ROUTE = 28;

	/** @var int */
	const SPLITS = 29;

	/** @var int */
	const COMMENT = 30;

	/** @var int */
	const TRAININGPARTNER = 31;

	/** @var int */
	const TOTAL_STROKES = 32;

	/** @var int */
	const VERTICAL_RATIO = 33;

	/** @var int */
	const GROUNDCONTACT_BALANCE = 34;

	/** @var int */
	const TAGS = 35;

	/** @var int */
	const COMPLETE_EQUIPMENT = 36;

	/** @var int */
	const MAIN_EQUIPMENT = 37;

	/** @var int */
	const WIND = 38;

	/** @var int */
	const HUMIDITY = 39;

	/** @var int */
	const AIR_PRESSURE = 40;

	/** @var int */
	const WIND_CHILL = 41;

	/**
	 * @var array|null
	 */
	private static $ClassNames = null;

	/**
	 * @var array instances of key objects
	 */
	private static $Instances = array();

	/**
	 * @var bool
	 */
	private static $KeepInstances = false;

	/**
	 * @param bool $flag
	 */
	public static function keepInstances($flag = true)
	{
		self::$KeepInstances = $flag;
	}

	/**
	 * Get key
	 * @param int $keyid int from internal enum
	 * @return \Runalyze\Dataset\Keys\AbstractKey
	 * @throws \InvalidArgumentException
	 */
	public static function get($keyid)
	{
		if (null == self::$ClassNames) {
			self::generateClassNamesArray();
		}

		if (!isset(self::$ClassNames[$keyid])) {
			throw new \InvalidArgumentException('Invalid keyid "'.$keyid.'".');
		}

		$className = 'Runalyze\\Dataset\\Keys\\'.self::$ClassNames[$keyid];

		if (self::$KeepInstances) {
			if (!isset(self::$Instances[$keyid])) {
				self::$Instances[$keyid] = new $className;
			}

			return self::$Instances[$keyid];
		}

		return new $className;
	}

	/**
	 * Generate static array with class names
	 */
	private static function generateClassNamesArray()
	{
		self::$ClassNames = array(
			self::SPORT => 'Sport',
			self::TYPE => 'Type',
			self::DAYTIME => 'Daytime',
			self::SHARED_LINK => 'SharedLink',
			self::DISTANCE => 'Distance',
			self::DURATION => 'Duration',
			self::PACE => 'Pace',
			self::ELAPSED_TIME => 'ElapsedTime',
			self::ELEVATION => 'Elevation',
			self::CALORIES => 'Calories',
			self::HEARTRATE_AVG => 'HeartrateAverage',
			self::HEARTRATE_MAX => 'HeartrateMaximum',
			self::VDOT_ICON => 'VdotIcon',
			self::VDOT_VALUE => 'VdotValue',
			self::FIT_VO2MAX_ESTIMATE => 'FitVO2maxEstimate',
			self::FIT_RECOVERY_TIME => 'FitRecoveryTime',
			self::FIT_HRV_ANALYSIS => 'FitHrvAnalysis',
			self::JD_INTENSITY => 'JdIntensity',
			self::TRIMP => 'Trimp',
			self::CADENCE => 'Cadence',
			self::POWER => 'Power',
			self::SWOLF => 'Swolf',
			self::STRIDE_LENGTH => 'StrideLength',
			self::GROUNDCONTACT => 'Groundcontact',
			self::VERTICAL_OSCILLATION => 'VerticalOscillation',
			self::TEMPERATURE => 'Temperature',
			self::WEATHER => 'Weather',
			self::ROUTE => 'Route',
			self::SPLITS => 'Splits',
			self::COMMENT => 'Comment',
			self::TRAININGPARTNER => 'TrainingPartner',
			self::TOTAL_STROKES => 'TotalStrokes',
			self::VERTICAL_RATIO => 'VerticalRatio',
			self::GROUNDCONTACT_BALANCE => 'GroundcontactBalance',
			self::TAGS => 'Tags',
			self::COMPLETE_EQUIPMENT => 'CompleteEquipment',
			self::MAIN_EQUIPMENT => 'MainEquipment',
			self::WIND => 'Wind',
			self::HUMIDITY => 'Humidity',
			self::AIR_PRESSURE => 'AirPressure',
			self::WIND_CHILL => 'WindChill'
		);
	}
}
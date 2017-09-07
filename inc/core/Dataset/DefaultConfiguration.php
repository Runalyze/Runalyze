<?php
/**
 * This file contains class::DefaultConfiguration
 * @package Runalyze
 */

namespace Runalyze\Dataset;

/**
 * Default dataset configuration that is used
 *
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
class DefaultConfiguration extends Configuration
{
	/**
	 * Construct default configuration
	 * Default construction does not need any database connection and account id.
	 */
	public function __construct()
	{
		$this->generateDataFor(array(
			Keys::SETTING => true,
			Keys::DAYTIME => false,
			Keys::SHARED_LINK => false,
			Keys::WEATHER => true,
			Keys::TEMPERATURE => true,
			Keys::HEAT_INDEX => false,
			Keys::WIND_CHILL => false,
			Keys::WIND => false,
			Keys::HUMIDITY => false,
			Keys::AIR_PRESSURE => false,
			Keys::TYPE => true,
			Keys::SPORT => true,
			Keys::DISTANCE => true,
			Keys::DURATION => true,
			Keys::ELAPSED_TIME => false,
			Keys::PACE => true,
			Keys::HEARTRATE_AVG => true,
			Keys::HEARTRATE_MAX => false,
			Keys::ELEVATION => true,
            Keys::CLIMB_SCORE => true,
            Keys::PERCENTAGE_HILLY => false,
            Keys::GRADIENT => false,
			Keys::ENERGY => true,
			Keys::SPLITS => true,
			Keys::TITLE => true,
			Keys::TRIMP => true,
			Keys::VO2MAX_ICON => true,
			Keys::VO2MAX_VALUE => false,
			Keys::TRAININGPARTNER => false,
			Keys::ROUTE => false,
            Keys::POWER => false,
			Keys::CADENCE => false,
            Keys::STRIDE_LENGTH => false,
            Keys::TOTAL_STROKES => false,
			Keys::GROUNDCONTACT => true,
            Keys::GROUNDCONTACT_BALANCE => true,
			Keys::VERTICAL_OSCILLATION => true,
            Keys::VERTICAL_RATIO => false,
            Keys::FLIGHT_TIME => false,
            Keys::FLIGHT_RATIO => false,
			Keys::FIT_VO2MAX_ESTIMATE => false,
			Keys::FIT_PERFORMANCE_CONDITION => false,
            Keys::FIT_PERFORMANCE_CONDITION_START => false,
            Keys::FIT_PERFORMANCE_CONDITION_END => false,
            Keys::FIT_RECOVERY_TIME => false,
			Keys::FIT_HRV_ANALYSIS => false,
			Keys::FIT_TRAINING_EFFECT => false,
			Keys::SWOLF => false,
			Keys::TAGS => false,
			Keys::COMPLETE_EQUIPMENT => false,
			Keys::MAIN_EQUIPMENT => false,
			Keys::RPE => false,
			Keys::RACE_RESULT => false,
            Keys::TOOLS => false,
		));
	}

	/**
	 * @return bool
	 */
	public function isDefault()
	{
		return true;
	}

	/**
	 * Generate data array from base class
	 * @param array $keysArray array(keyid => active(true|false))
	 */
	protected function generateDataFor(array $keysArray)
	{
		foreach ($keysArray as $keyid => $isActive) {
			$this->Data[$keyid] = array(
				'active' => $isActive,
				'style' => Keys::get($keyid)->defaultCssStyle()
			);
		}
	}
}

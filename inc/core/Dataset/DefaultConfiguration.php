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
			Keys::CALORIES => true,
			Keys::SPLITS => true,
			Keys::COMMENT => true,
			Keys::TRIMP => true,
			Keys::VDOT_ICON => true,
			Keys::VDOT_VALUE => false,
			Keys::TRAININGPARTNER => false,
			Keys::ROUTE => false,
			Keys::CADENCE => false,
			Keys::POWER => false,
			Keys::JD_INTENSITY => true,
			Keys::GROUNDCONTACT => true,
			Keys::VERTICAL_OSCILLATION => true,
			Keys::STRIDE_LENGTH => false,
			Keys::FIT_VO2MAX_ESTIMATE => false,
			Keys::FIT_RECOVERY_TIME => false,
			Keys::FIT_HRV_ANALYSIS => false,
			Keys::SWOLF => false,
			Keys::TOTAL_STROKES => false,
			Keys::VERTICAL_RATIO => false,
			Keys::GROUNDCONTACT_BALANCE => false,
			Keys::TAGS => false,
			Keys::COMPLETE_EQUIPMENT => false,
			Keys::MAIN_EQUIPMENT => false
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
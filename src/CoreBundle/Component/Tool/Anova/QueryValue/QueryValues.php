<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

final class QueryValues extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var string */
    const PACE = 'pace';

    /** @var string */
    const DISTANCE = 'distance';

    /** @var string */
    const DURATION = 'duration';

    /** @var string */
    const HEART_RATE_AVERAGE = 'heart_rate_average';

    /** @var string */
    const HEART_RATE_MAXIMUM = 'heart_rate_maximum';

    /** @var string */
    const TRIMP = 'trimp';

    /** @var string */
    const RPE = 'rpe';

    /** @var string */
    const POWER = 'power';

    /** @var string */
    const CADENCE = 'cadence';

    /** @var string */
    const VO2MAX = 'vo2max';

    /** @var string */
    const CLIMB_SCORE = 'climb_score';

    /** @var string */
    const PERCENTAGE_HILLY = 'percentage_hilly';

    /** @var string */
    const VO2MAX_WITH_ELEVATION = 'vo2max_with_elevation';

    /** @var string */
    const GROUND_CONTACT_TIME = 'ground_contact_time';

    /** @var string */
    const GROUND_CONTACT_BALANCE = 'ground_contact_balance';

    /** @var string */
    const VERTICAL_OSCILLATION = 'vertical_oscillation';

    /** @var string */
    const FLIGHT_TIME = 'flight_time';

    /** @var string */
    const FLIGHT_RATIO = 'flight_ratio';

    /** @var string */
    const IMPACT_GS_LEFT = 'impact_gs_left';

    /** @var string */
    const IMPACT_GS_RIGHT = 'impact_gs_right';

    /** @var string */
    const BRAKING_GS_LEFT = 'braking_gs_left';

    /** @var string */
    const BRAKING_GS_RIGHT = 'braking_gs_right';

    /** @var string */
    const FOOTSTRIKE_TYPE_LEFT = 'footstrike_type_left';

    /** @var string */
    const FOOTSTRIKE_TYPE_RIGHT = 'footstrike_type_right';

    /** @var string */
    const PRONATION_EXCURSION_LEFT = 'pronation_excursion_left';

    /** @var string */
    const PRONATION_EXCURSION_RIGHT = 'pronation_excursion_right';

    /** @var string */
    const FIT_HRV_ANALYSIS = 'fit_hrv_analysis';

    /** @var string */
    const FIT_PERFORMANCE_CONDITION_START = 'fit_performance_condition_start';

    /** @var string */
    const FIT_PERFORMANCE_CONDITION_END = 'fit_performance_condition_end';

    /** @var string */
    const FIT_RECOVERY_TIME = 'fit_recovery_time';

    /** @var string */
    const FIT_TRAINING_EFFECT = 'fit_training_effect';

    /** @var string */
    const FIT_VO2MAX_ESTIMATE = 'fit_vo2max_estimate';

    /** @var string */
    const WEATHER_TEMPERATURE = 'temperature';

    /** @var string */
    const WEATHER_HUMIDITY = 'humidity';

    /** @var string */
    const WEATHER_PRESSURE = 'pressure';

    /** @var string */
    const WEATHER_WIND_SPEED = 'wind_speed';
}

<?php

namespace Runalyze\Metrics;

use Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit;
use Runalyze\Metrics\Energy\Unit\EnergyEnum;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit;
use Runalyze\Metrics\HeartRate\Unit\HeartRateEnum;
use Runalyze\Metrics\Velocity\Unit\AbstractPaceUnit;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit;
use Runalyze\Metrics\Temperature\Unit\TemperatureEnum;
use Runalyze\Metrics\Weight\Unit\AbstractWeightUnit;
use Runalyze\Metrics\Weight\Unit\WeightEnum;
use Runalyze\Parameter\Application;

class LegacyUnitConverter
{
    /**
     * @param mixed $legacyEnergyEnum
     * @return AbstractEnergyUnit
     */
    public function getEnergyUnit($legacyEnergyEnum)
    {
        $energyUnitMap = [
            Application\EnergyUnit::KCAL => EnergyEnum::KILOCALORIES,
            Application\EnergyUnit::KJ => EnergyEnum::KILOJOULES
        ];

        if (isset($energyUnitMap[$legacyEnergyEnum])) {
            return EnergyEnum::get($energyUnitMap[$legacyEnergyEnum]);
        }

        return EnergyEnum::get(EnergyEnum::KILOCALORIES);
    }

    /**
     * @param mixed $legacyHeartRateEnum
     * @param int $maximalHeartRate
     * @param int $restingHeartRate
     * @return AbstractHeartRateUnit
     */
    public function getHeartRateUnit($legacyHeartRateEnum, $maximalHeartRate, $restingHeartRate)
    {
        $heartRateUnitMap = [
            Application\HeartRateUnit::BPM => HeartRateEnum::BEATS_PER_MINUTE,
            Application\HeartRateUnit::HRMAX => HeartRateEnum::PERCENT_MAXIMUM,
            Application\HeartRateUnit::HRRESERVE => HeartRateEnum::PERCENT_RESERVE
        ];

        if (isset($heartRateUnitMap[$legacyHeartRateEnum])) {
            return HeartRateEnum::get($heartRateUnitMap[$legacyHeartRateEnum], $maximalHeartRate, $restingHeartRate);
        }

        return HeartRateEnum::get(HeartRateEnum::BEATS_PER_MINUTE, $maximalHeartRate, $restingHeartRate);
    }

    /**
     * @param mixed $legacyPaceEnum see \Runalyze\Parameter\Application\PaceUnit
     * @return AbstractPaceUnit
     */
    public function getPaceUnit($legacyPaceEnum)
    {
        $paceUnitMap = [
            Application\PaceUnit::KM_PER_H => PaceEnum::KILOMETER_PER_HOUR,
            Application\PaceUnit::MILES_PER_H => PaceEnum::MILES_PER_HOUR,
            Application\PaceUnit::MIN_PER_KM => PaceEnum::SECONDS_PER_KILOMETER,
            Application\PaceUnit::MIN_PER_MILE => PaceEnum::SECONDS_PER_MILE,
            Application\PaceUnit::M_PER_S => PaceEnum::METER_PER_SECOND,
            Application\PaceUnit::MIN_PER_100M => PaceEnum::SECONDS_PER_100M,
            Application\PaceUnit::MIN_PER_100Y => PaceEnum::SECONDS_PER_100Y,
            Application\PaceUnit::MIN_PER_500M => PaceEnum::SECONDS_PER_500M,
            Application\PaceUnit::MIN_PER_500Y => PaceEnum::SECONDS_PER_500Y
        ];

        if (isset($paceUnitMap[$legacyPaceEnum])) {
            return PaceEnum::get($paceUnitMap[$legacyPaceEnum]);
        }

        return PaceEnum::get(PaceEnum::KILOMETER_PER_HOUR);
    }

    /**
     * @param mixed $paceEnum see \Runalyze\Metrics\Velocity\Unit\PaceEnum
     * @param bool $returnLegacyEnum
     * @return \Runalyze\Activity\PaceUnit\AbstractUnit|int
     */
    public function getLegacyPaceUnit($paceEnum, $returnLegacyEnum = false)
    {
        $parameter = new Application\PaceUnit();

        $paceUnitMap = [
            PaceEnum::KILOMETER_PER_HOUR => Application\PaceUnit::KM_PER_H,
            PaceEnum::MILES_PER_HOUR => Application\PaceUnit::MILES_PER_H,
            PaceEnum::SECONDS_PER_KILOMETER => Application\PaceUnit::MIN_PER_KM,
            PaceEnum::SECONDS_PER_MILE => Application\PaceUnit::MIN_PER_MILE,
            PaceEnum::METER_PER_SECOND => Application\PaceUnit::M_PER_S,
            PaceEnum::SECONDS_PER_100M => Application\PaceUnit::MIN_PER_100M,
            PaceEnum::SECONDS_PER_100Y => Application\PaceUnit::MIN_PER_100Y,
            PaceEnum::SECONDS_PER_500M => Application\PaceUnit::MIN_PER_500M,
            PaceEnum::SECONDS_PER_500Y => Application\PaceUnit::MIN_PER_500Y
        ];

        if ($returnLegacyEnum) {
            return isset($paceUnitMap[$paceEnum]) ? $paceUnitMap[$paceEnum] : Application\PaceUnit::KM_PER_H;
        }

        if (isset($paceUnitMap[$paceEnum])) {
            $parameter->set($paceUnitMap[$paceEnum]);
        } else {
            $parameter->set(Application\PaceUnit::KM_PER_H);
        }

        return $parameter->object();
    }

    /**
     * @param mixed $legacyTemperatureEnum
     * @return AbstractTemperatureUnit
     */
    public function getTemperatureUnit($legacyTemperatureEnum)
    {
        $temperatureUnitMap = [
            Application\TemperatureUnit::CELSIUS => TemperatureEnum::CELSIUS,
            Application\TemperatureUnit::FAHRENHEIT => TemperatureEnum::FAHRENHEIT
        ];

        if (isset($temperatureUnitMap[$legacyTemperatureEnum])) {
            return TemperatureEnum::get($temperatureUnitMap[$legacyTemperatureEnum]);
        }

        return TemperatureEnum::get(TemperatureEnum::CELSIUS);
    }

    /**
     * @param mixed $legacyWeightEnum
     * @return AbstractWeightUnit
     */
    public function getWeightUnit($legacyWeightEnum)
    {
        $weightUnitMap = [
            Application\WeightUnit::KG => WeightEnum::KILOGRAM,
            Application\WeightUnit::POUNDS => WeightEnum::POUNDS,
            Application\WeightUnit::STONES => WeightEnum::STONES
        ];

        if (isset($weightUnitMap[$legacyWeightEnum])) {
            return WeightEnum::get($weightUnitMap[$legacyWeightEnum]);
        }

        return WeightEnum::get(WeightEnum::KILOGRAM);
    }
}

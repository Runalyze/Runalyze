<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityData;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\FitDetails;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class OutOfRangeValueFilter extends AbstractFilter
{
    /** @var bool */
    protected $Strict;

    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $this->Strict = $strict;

        $this->checkActivityDataValues($container->ActivityData);
        $this->checkFitDetailsValues($container->FitDetails);
        $this->checkWeatherDataValues($container->WeatherData);
    }

    protected function checkActivityDataValues(ActivityData $activityData)
    {
        $this->checkValue($activityData->Duration, 0.00, 999999.99, 'Duration');
        $this->checkValue($activityData->Distance, 0.00, 9999.99, 'Distance');
        $this->checkValue($activityData->EnergyConsumption, 1, 65535, 'EnergyConsumption');
        $this->checkValue($activityData->RPE, 6, 20, 'RPE');
        $this->checkValue($activityData->AvgHeartRate, 30, 240, 'AvgHeartRate');
        $this->checkValue($activityData->MaxHeartRate, 30, 240, 'MaxHeartRate');
    }

    protected function checkFitDetailsValues(FitDetails $fitDetails)
    {
        $this->checkValue($fitDetails->VO2maxEstimate, 0.0, 100.0, 'VO2maxEstimate');
        $this->checkValue($fitDetails->TrainingEffect, 1.0, 5.0, 'TrainingEffect');
        $this->checkValue($fitDetails->PerformanceCondition, 80, 100, 'PerformanceCondition');
        $this->checkValue($fitDetails->PerformanceConditionEnd, 80, 100, 'PerformanceConditionEnd');
    }

    protected function checkWeatherDataValues(WeatherData $weatherData)
    {
        $this->checkValue($weatherData->Temperature, -100, 100, 'Temperature');
        $this->checkValue($weatherData->WindSpeed, 0, 255, 'WindSpeed');
        $this->checkValue($weatherData->WindDirection, 0, 359, 'WindDirection');
        $this->checkValue($weatherData->Humidity, 0, 100, 'Humidity');
        $this->checkValue($weatherData->AirPressure, 870, 1090, 'AirPressure');
    }

    /**
     * @param mixed $value
     * @param mixed|null $minimum
     * @param mixed|null $maximum
     * @param string $label
     * @param mixed|null $default
     *
     * @throws InvalidDataException
     */
    protected function checkValue(&$value, $minimum, $maximum, $label, $default = null)
    {
        if (
            $default !== $value && (
                (null !== $maximum && $value > $maximum) ||
                (null !== $minimum && $value < $minimum)
            )
        ) {
            if ($this->Strict) {
                throw new InvalidDataException(sprintf('Out of range value detected: %s not in [%u, %u] for %s.',
                    $value, $minimum, $maximum, $label
                ));
            }

            $this->logger->warning(sprintf('Out of range value fixed: %s not in [%u, %u] for %s.',
                $value, $minimum, $maximum, $label
            ));
            $value = $default;
        }
    }
}

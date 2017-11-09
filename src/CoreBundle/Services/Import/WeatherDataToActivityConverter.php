<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Profile\Weather\Mapping\EnglishTextMapping;

class WeatherDataToActivityConverter
{
    public function setActivityWeatherDataFor(Training $activity, WeatherData $weatherData)
    {
        if ($weatherData->isEmpty()) {
            return;
        }

        $activity->setTemperature($this->getRoundedValue($weatherData->Temperature));
        $activity->setWindSpeed($this->getRoundedValue($weatherData->WindSpeed));
        $activity->setWindDeg($weatherData->WindDirection);
        $activity->setHumidity($weatherData->Humidity);
        $activity->setPressure($this->getRoundedValue($weatherData->AirPressure));

        if (null !== $weatherData->InternalConditionId) {
            $activity->setWeatherid($weatherData->InternalConditionId);
        } elseif ('' != $weatherData->Condition) {
            $activity->setWeatherid((new EnglishTextMapping())->toInternal($weatherData->Condition));
        }
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    protected function getRoundedValue($value)
    {
        return null !== $value ? (int)round($value) : null;
    }
}

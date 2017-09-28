<?php

namespace Runalyze\Parser\Activity\Common\Data;

class WeatherData
{
    /** @var string */
    public $Condition = '';

    /** @var null|int */
    public $InternalConditionId = null;

    /** @var null|int|float */
    public $Temperature = null;

    /** @var null|int|float [km/h] */
    public $WindSpeed = null;

    /** @var null|int [°] */
    public $WindDirection = null;

    /** @var null|int [%] */
    public $Humidity = null;

    /** @var null|int|float [hPa] */
    public $AirPressure = null;

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (
            '' == $this->Condition &&
            null === $this->InternalConditionId &&
            null === $this->Temperature &&
            null === $this->WindSpeed &&
            null === $this->WindDirection &&
            null === $this->Humidity &&
            null === $this->AirPressure
        );
    }
}

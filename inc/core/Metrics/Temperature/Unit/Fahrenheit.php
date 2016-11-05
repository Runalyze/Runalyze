<?php

namespace Runalyze\Metrics\Temperature\Unit;

class Fahrenheit extends AbstractTemperatureUnit
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return '°F';
    }

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    public function toBaseUnit($valueInThisUnit)
    {
        return ($valueInThisUnit - 32) / 1.8;
    }

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    public function fromBaseUnit($valueInBaseUnit)
    {
        return $valueInBaseUnit * 1.8 + 32;
    }
}

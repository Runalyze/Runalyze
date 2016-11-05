<?php

namespace Runalyze\Metrics\Common;

interface UnitInterface
{
    /**
     * @return string
     */
    public function getAppendix();

    /**
     * @param mixed $valueInThisUnit
     * @return mixed
     */
    public function toBaseUnit($valueInThisUnit);

    /**
     * @param mixed $valueInBaseUnit
     * @return mixed
     */
    public function fromBaseUnit($valueInBaseUnit);
}

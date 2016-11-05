<?php

namespace Runalyze\Metrics\Temperature;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Temperature\Unit\Celsius;

class Temperature extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Celsius::class;
    }
}

<?php

namespace Runalyze\Metrics\Pace;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Pace\Unit\SecondsPerKilometer;

class Pace extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return SecondsPerKilometer::class;
    }
}

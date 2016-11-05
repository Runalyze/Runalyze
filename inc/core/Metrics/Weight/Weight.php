<?php

namespace Runalyze\Metrics\Weight;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Weight\Unit\Kilogram;

class Weight extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Kilogram::class;
    }
}

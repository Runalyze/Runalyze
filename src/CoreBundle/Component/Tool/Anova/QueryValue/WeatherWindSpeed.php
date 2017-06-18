<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Cadence\Unit\AbstractCadenceUnit;
use Runalyze\Metrics\GroundContactBalance;

class WeatherWindSpeed extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'windSpeed';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractCadenceUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new None();
    }
}

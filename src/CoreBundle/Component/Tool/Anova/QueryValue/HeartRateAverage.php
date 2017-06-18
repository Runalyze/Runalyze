<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit;

class HeartRateAverage extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'pulseAvg';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractHeartRateUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getHeartRateUnit();
    }
}

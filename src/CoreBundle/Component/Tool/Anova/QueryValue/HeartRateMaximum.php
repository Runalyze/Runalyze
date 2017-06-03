<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit;

class HeartRateMaximum extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'pulseMax';
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

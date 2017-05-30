<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Linear;

class FitPerformanceConditionStart extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'fitPerformanceCondition';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Linear
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Linear(
            function ($value) { return $value - 100; },
            function ($value) { return $value + 100; }
        );
    }
}

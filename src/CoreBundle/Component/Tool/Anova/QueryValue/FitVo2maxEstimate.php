<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\None;

class FitVo2maxEstimate extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'fitVO2maxEstimate';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return None
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new None();
    }
}

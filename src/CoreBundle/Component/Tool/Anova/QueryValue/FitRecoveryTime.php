<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Factorial;

class FitRecoveryTime extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'fitRecoveryTime';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Factorial
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Factorial('h', 1/60, 0);
    }
}

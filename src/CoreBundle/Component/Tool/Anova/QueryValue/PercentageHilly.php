<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Factorial;

class PercentageHilly extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'percentageHilly';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Factorial
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Factorial('%', 100, 0);
    }
}

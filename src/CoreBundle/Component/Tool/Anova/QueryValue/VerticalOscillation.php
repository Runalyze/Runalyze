<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Factorial;
use Runalyze\Metrics\Common\UnitInterface;

class VerticalOscillation extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'verticalOscillation';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return UnitInterface
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Factorial('cm', 0.1);
    }
}

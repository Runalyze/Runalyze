<?php

namespace Runalyze\Metrics\Weight\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class Kilogram extends AbstractWeightUnit
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'kg';
    }
}

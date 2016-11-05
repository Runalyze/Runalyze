<?php

namespace Runalyze\Metrics\Energy\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class Kilocalories extends AbstractEnergyUnit
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'kcal';
    }
}

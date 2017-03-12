<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Weight\Unit\AbstractWeightUnit;

class WeightType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 1;

    public function __construct(AbstractWeightUnit $weightUnit)
    {
        parent::__construct($weightUnit);
    }
}

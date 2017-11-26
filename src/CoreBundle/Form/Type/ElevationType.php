<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Distance\Unit\AbstractDistanceUnit;

class ElevationType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 0;

    public function __construct(AbstractDistanceUnit $distanceUnit)
    {
        parent::__construct($distanceUnit);
    }

    public function transform($value)
    {
        return parent::transform($value / 1000.00);
    }

    public function reverseTransform($value)
    {
        return parent::reverseTransform($value * 1000.00);
    }
}

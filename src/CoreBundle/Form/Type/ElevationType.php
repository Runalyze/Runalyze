<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Distance\Unit\AbstractDistanceUnit;

class ElevationType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 2;

    //TODO Use elevation unit

    public function __construct(AbstractDistanceUnit $distanceUnit)
    {
        parent::__construct($distanceUnit);
    }
}

<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

class Vo2maxWithElevation extends Vo2max
{
    protected function getColumn()
    {
        return 'vo2maxWithElevation';
    }
}

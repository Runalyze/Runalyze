<?php

namespace Runalyze\Sports\ClimbQuantification;

interface CategorizableInterface
{
    /**
     * @return array
     */
    public function getLowerLimitsForCategorization();
}

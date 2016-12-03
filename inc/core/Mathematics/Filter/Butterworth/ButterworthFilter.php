<?php

namespace Runalyze\Mathematics\Filter\Butterworth;

use Runalyze\Mathematics\Filter\InfiniteImpulseResponseFilter;

/**
 * @see https://en.wikipedia.org/wiki/Butterworth_filter
 */
class ButterworthFilter extends InfiniteImpulseResponseFilter
{
    public function __construct(AbstractButterworthCoefficients $coefficients)
    {
        parent::__construct($coefficients->getInputCoefficients(), $coefficients->getOutputCoefficients());
    }
}

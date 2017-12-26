<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Psr\Log\LoggerInterface;

class DefaultFilterCollection extends FilterCollection
{
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);

        $this->add(new InvalidRRIntervalFilter());
        $this->add(new NegativeDistanceStepFilter());
        $this->add(new NegativePauseFilter());
        $this->add(new NegativeTimeStepFilter());
        $this->add(new NonMatchingArraySizeFilter());
        $this->add(new OutOfRangeValueFilter());
    }
}

<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class AbstractFilter implements FilterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->setLogger(new NullLogger());
    }
}

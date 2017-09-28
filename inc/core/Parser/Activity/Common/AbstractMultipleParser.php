<?php

namespace Runalyze\Parser\Activity\Common;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

abstract class AbstractMultipleParser implements ParserInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ActivityDataContainer[] */
    protected $Container = [];

    public function __construct()
    {
        $this->setLogger(new NullLogger());
    }

    public function getNumberOfActivities()
    {
        return count($this->Container);
    }

    public function getActivityDataContainer($index = 0)
    {
        return $this->Container[$index];
    }
}

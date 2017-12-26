<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class FilterCollection
{
    /** @var FilterInterface[] */
    protected $Filter = [];

    /** @var LoggerInterface|null */
    protected $Logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->Logger = $logger;
    }

    public function add(FilterInterface $filter)
    {
        if (null !== $this->Logger && $filter instanceof LoggerAwareInterface) {
            $filter->setLogger($this->Logger);
        }

        $this->Filter[] = $filter;
    }

    public function filter(ActivityDataContainer $container)
    {
        foreach ($this->Filter as $filter) {
            $filter->filter($container);
        }
    }
}

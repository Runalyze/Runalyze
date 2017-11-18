<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Filter\DefaultFilterCollection;
use Runalyze\Parser\Activity\Common\Filter\FilterCollection;

class ActivityDataContainerFilter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var FilterCollection */
    protected $Filter;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();

        $this->initFilterCollection();
    }

    protected function initFilterCollection()
    {
        $this->Filter = new DefaultFilterCollection();
    }

    public function filter(ActivityDataContainer $container)
    {
        $this->Filter->filter($container);
    }
}

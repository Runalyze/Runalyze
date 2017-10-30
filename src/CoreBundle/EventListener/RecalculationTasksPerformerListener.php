<?php

namespace Runalyze\Bundle\CoreBundle\EventListener;

use ProxyManager\Proxy\LazyLoadingInterface;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationManager;

class RecalculationTasksPerformerListener
{
    /** @var RecalculationManager */
    protected $Manager;

    public function __construct(RecalculationManager $manager)
    {
        $this->Manager = $manager;
    }

    public function onTerminate()
    {
        if ($this->Manager instanceof LazyLoadingInterface && !$this->Manager->isProxyInitialized()) {
            return;
        }

        $this->Manager->runScheduledTasks();
    }
}

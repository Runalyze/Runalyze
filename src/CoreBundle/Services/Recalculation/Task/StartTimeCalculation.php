<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation\Task;

use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationTaskInterface;
use Runalyze\Util\LocalTime;

class StartTimeCalculation implements RecalculationTaskInterface
{
    use AccountAwareTaskTrait;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var ConfigurationUpdater */
    protected $ConfigurationUpdater;

    /** @var bool */
    protected $ForceRecalculation = false;

    /** @var int|null */
    protected $NewStartTime = null;

    public function __construct(TrainingRepository $repository, ConfigurationUpdater $updater)
    {
        $this->TrainingRepository = $repository;
        $this->ConfigurationUpdater = $updater;
    }

    public function forceRecalculation()
    {
        $this->ForceRecalculation = true;
    }

    /**
     * @param int $timestamp
     */
    public function setNewStartTime($timestamp)
    {
        if (null === $this->NewStartTime || $timestamp < $this->NewStartTime) {
            $this->NewStartTime = $timestamp;
        }
    }

    public function run()
    {
        if (null === $this->Account) {
            return;
        }

        if ($this->ForceRecalculation || null === $this->NewStartTime) {
            $this->NewStartTime = $this->TrainingRepository->getStartTime($this->Account);
        }

        $this->ConfigurationUpdater->updateStartTime($this->Account, $this->NewStartTime);
    }

    public function getOrder()
    {
        return 1;
    }
}

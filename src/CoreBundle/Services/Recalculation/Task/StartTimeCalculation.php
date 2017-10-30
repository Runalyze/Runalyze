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

    public function __construct(TrainingRepository $repository, ConfigurationUpdater $updater)
    {
        $this->TrainingRepository = $repository;
        $this->ConfigurationUpdater = $updater;
    }

    public function run()
    {
        if (null === $this->Account) {
            return;
        }

        $startTime = $this->TrainingRepository->getStartTime($this->Account);

        $this->ConfigurationUpdater->updateStartTime($this->Account, $startTime);
    }
}

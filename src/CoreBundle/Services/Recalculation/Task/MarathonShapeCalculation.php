<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation\Task;

use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationTaskInterface;
use Runalyze\Util\LocalTime;

class MarathonShapeCalculation implements RecalculationTaskInterface
{
    use AccountAwareTaskTrait;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var ConfigurationUpdater */
    protected $ConfigurationUpdater;

    public function __construct(
        TrainingRepository $repository,
        ConfigurationManager $manager,
        ConfigurationUpdater $updater
    )
    {
        $this->TrainingRepository = $repository;
        $this->ConfigurationManager = $manager;
        $this->ConfigurationUpdater = $updater;
    }

    public function run()
    {
        if (null === $this->Account) {
            return;
        }

        $configList = $this->ConfigurationManager->getList($this->Account);
        $timestamp = LocalTime::fromServerTime(time())->setTime(23, 59, 59)->getTimestamp();
        $shape = $this->TrainingRepository->calculateMarathonShape(
            $this->Account,
            $configList->getBasicEndurance(),
            $configList->getCurrentVO2maxShape(),
            $configList->getGeneral()->getRunningSport(),
            $timestamp
        );

        $this->ConfigurationUpdater->updateMarathonShape($this->Account, $shape);
    }
}

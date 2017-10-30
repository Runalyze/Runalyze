<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation\Task;

use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\RecalculationTaskInterface;

class VO2maxCorrectionFactorCalculation implements RecalculationTaskInterface
{
    use AccountAwareTaskTrait;

    /** @var RaceresultRepository */
    protected $RaceResultRepository;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var ConfigurationUpdater */
    protected $ConfigurationUpdater;

    public function __construct(
        RaceresultRepository $repository,
        ConfigurationManager $manager,
        ConfigurationUpdater $updater
    )
    {
        $this->RaceResultRepository = $repository;
        $this->ConfigurationManager = $manager;
        $this->ConfigurationUpdater = $updater;
    }

    public function run()
    {
        if (null === $this->Account) {
            return;
        }

        $configList = $this->ConfigurationManager->getList($this->Account);
        $factor = $this->RaceResultRepository->getEffectiveVO2maxCorrectionFactor(
            $this->Account,
            $configList->getGeneral()->getRunningSport()
        );

        $this->ConfigurationUpdater->updateVO2maxCorrectionFactor($this->Account, $factor);
    }
}

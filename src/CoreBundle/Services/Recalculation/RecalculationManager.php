<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationUpdater;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\Task\MarathonShapeCalculation;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\Task\StartTimeCalculation;
use Runalyze\Bundle\CoreBundle\Services\Recalculation\Task\VO2maxShapeCalculation;

class RecalculationManager
{
    /** @var RecalculationTaskCollection */
    protected $Tasks;

    /** @var array */
    protected $AccountRelatedTaskNames = [];

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var ConfigurationUpdater */
    protected $ConfigurationUpdater;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    public function __construct(
        ConfigurationManager $manager,
        ConfigurationUpdater $updater,
        TrainingRepository $trainingRepository
    )
    {
        $this->ConfigurationManager = $manager;
        $this->ConfigurationUpdater = $updater;
        $this->TrainingRepository = $trainingRepository;
        $this->Tasks = new RecalculationTaskCollection();
    }

    /**
     * @param Account $account
     * @return int
     */
    public function getNumberOfScheduledTasksFor(Account $account)
    {
        if (!isset($this->AccountRelatedTaskNames[$account->getId()])) {
            return 0;
        }

        return count($this->AccountRelatedTaskNames[$account->getId()]);
    }

    public function scheduleStartTimeCalculation(Account $account)
    {
        if (!$this->isTaskScheduled($account, StartTimeCalculation::class)) {
            $task = new StartTimeCalculation($this->TrainingRepository, $this->ConfigurationUpdater);
            $this->scheduleTaskForAccount($account, $task);
        }
    }

    public function scheduleEffectiveVO2maxShapeCalculation(Account $account)
    {
        if (!$this->isTaskScheduled($account, VO2maxShapeCalculation::class)) {
            if ($this->isTaskScheduled($account, MarathonShapeCalculation::class)) {
                unset($this->AccountRelatedTaskNames[$account->getId()][VO2maxShapeCalculation::class]);
            }

            $task = new VO2maxShapeCalculation($this->TrainingRepository, $this->ConfigurationManager, $this->ConfigurationUpdater);
            $this->scheduleTaskForAccount($account, $task);

            $this->scheduleMarathonShapeCalculation($account);
        }
    }

    public function scheduleMarathonShapeCalculation(Account $account)
    {
        if (!$this->isTaskScheduled($account, MarathonShapeCalculation::class)) {
            $task = new MarathonShapeCalculation($this->TrainingRepository, $this->ConfigurationManager, $this->ConfigurationUpdater);
            $this->scheduleTaskForAccount($account, $task);
        }
    }

    /**
     * @param Account $account
     * @param string $taskName
     * @return bool
     */
    protected function isTaskScheduled(Account $account, $taskName)
    {
        $accountId = $account->getId();

        if (!isset($this->AccountRelatedTaskNames[$accountId])) {
            $this->AccountRelatedTaskNames[$accountId] = [];
        }

        return isset($this->AccountRelatedTaskNames[$accountId][$taskName]);
    }

    protected function scheduleTaskForAccount(Account $account, RecalculationTaskInterface $task)
    {
        $accountId = $account->getId();
        $taskName = get_class($task);

        $task->setAccount($account);
        $this->Tasks->addTask($task);
        $this->AccountRelatedTaskNames[$accountId][$taskName] = true;
    }

    public function runScheduledTasks()
    {
        $this->Tasks->runAllTasks();
        $this->Tasks->clear();
    }
}

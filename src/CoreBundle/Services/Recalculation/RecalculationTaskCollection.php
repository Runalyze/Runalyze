<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation;

class RecalculationTaskCollection
{
    /** @var RecalculationTaskInterface[] */
    protected $Tasks = [];

    public function addTask(RecalculationTaskInterface $task)
    {
        $this->Tasks[] = $task;
    }

    public function runAllTasks()
    {
        foreach ($this->Tasks as $task) {
            $task->run();
        }
    }

    public function clear()
    {
        $this->Tasks = [];
    }
}

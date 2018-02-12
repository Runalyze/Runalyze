<?php

namespace Runalyze\Bundle\CoreBundle\Services\Recalculation;

class RecalculationTaskCollection
{
    /** @var RecalculationTaskInterface[] */
    protected $Tasks = [];

    /** @var int */
    protected $NextTaskIndex = 0;

    /**
     * @param RecalculationTaskInterface $task
     * @return int
     */
    public function addTask(RecalculationTaskInterface $task)
    {
        $this->Tasks[] = $task;

        return $this->NextTaskIndex++;
    }

    public function runAllTasks()
    {
        usort($this->Tasks, function(RecalculationTaskInterface $a, RecalculationTaskInterface $b) {
            if ($a->getOrder() == $b->getOrder()) {
                return 0;
            }

            return $a->getOrder() < $b->getOrder() ? -1 : 1;
        });

        foreach ($this->Tasks as $task) {
            $task->run();
        }
    }

    /**
     * @param int $offset
     * @return RecalculationTaskInterface
     */
    public function offsetGet($offset)
    {
        return $this->Tasks[$offset];
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->Tasks[$offset]);
    }

    public function clear()
    {
        $this->NextTaskIndex = 0;
        $this->Tasks = [];
    }
}

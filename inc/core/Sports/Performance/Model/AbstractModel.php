<?php

namespace Runalyze\Sports\Performance\Model;

/**
 * Model for human performance
 *
 * @see http://fellrnr.com/wiki/Modeling_Human_Performance
 */
abstract class AbstractModel
{
    /** @var int */
    const FITNESS = 0;

    /** @var int */
    const FATIGUE = 1;

    /** @var int */
    const PERFORMANCE = 2;

    /** @var array */
    protected $Trimp = [];

    /** @var array */
    protected $Fitness = [];

    /** @var array */
    protected $Fatigue = [];

    /** @var array */
    protected $Performance = [];

    /** @var array array('from' => int, 'to' => int) */
    protected $Range = [];

    /**
     * @param array $trimpData array('days back' => 'trimp value')
     */
    public function __construct(array $trimpData)
    {
        ksort($trimpData);

        $this->Trimp = $trimpData;
    }

    /**
     * @param int $from
     * @param int $to
     */
    final public function setRange($from, $to)
    {
        $this->Range = array(
            'from' => $from,
            'to' => $to
        );
    }

    final public function calculate()
    {
        if (empty($this->Range)) {
            if (empty($this->Trimp)) {
                $this->setRange(0, 0);
            } else {
                $Keys = array_keys($this->Trimp);
                $this->setRange($Keys[0], max(0, end($Keys)));
            }
        }

        $this->prepareArrays();
        $this->calculateArrays();
        $this->finishArrays();
    }

    protected function prepareArrays()
    {
        $this->Fitness[$this->Range['from'] - 1] = 0;
        $this->Fatigue[$this->Range['from'] - 1] = 0;
        $this->Performance[$this->Range['from'] - 1] = 0;
    }

    protected function finishArrays()
    {
        unset($this->Fitness[$this->Range['from'] - 1]);
        unset($this->Fatigue[$this->Range['from'] - 1]);
        unset($this->Performance[$this->Range['from'] - 1]);
    }

    abstract protected function calculateArrays();

    /**
     * @return array array(enum => data)
     */
    final public function getArrays()
    {
        return [
            self::FITNESS => $this->Fitness,
            self::FATIGUE => $this->Fatigue,
            self::PERFORMANCE => $this->Performance
        ];
    }

    /**
     * @return int
     */
    final public function maxFitness()
    {
        return (int)round(max($this->Fitness));
    }

    /**
     * @return int
     */
    final public function maxFatigue()
    {
        return (int)round(max($this->Fatigue));
    }

    /**
     * @return int
     */
    final public function maxPerformance()
    {
        return (int)round(max($this->Performance));
    }

    /**
     * @return int
     */
    final public function minPerformance()
    {
        return (int)round(min($this->Performance));
    }

    /**
     * @param int $index 0 for today
     * @return int
     */
    final public function fitnessAt($index)
    {
        return $this->at($index, self::FITNESS);
    }

    /**
     * @param int $index 0 for today
     * @return int
     */
    final public function fatigueAt($index)
    {
        return $this->at($index, self::FATIGUE);
    }

    /**
     * @param int $index 0 for today
     * @return int
     */
    final public function performanceAt($index)
    {
        return $this->at($index, self::PERFORMANCE);
    }

    /**
     * @param int $index e.g. -1 for yesterday
     * @param int $enum enum for value
     * @return int
     */
    private function at($index, $enum)
    {
        $Array = $this->arrayFor($enum);

        if (isset($Array[$index])) {
            return (int)round($Array[$index]);
        }

        return 0;
    }

    /**
     * @param int $enum
     * @return array
     */
    private function arrayFor($enum)
    {
        switch ($enum) {
            case self::FITNESS:
                return $this->Fitness;

            case self::FATIGUE:
                return $this->Fatigue;

            case self::PERFORMANCE:
                return $this->Performance;
        }

        return [];
    }
}

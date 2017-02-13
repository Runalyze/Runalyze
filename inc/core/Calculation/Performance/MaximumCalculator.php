<?php

namespace Runalyze\Calculation\Performance;

use Runalyze\Sports\Performance\Model\AbstractModel;

class MaximumCalculator
{
    /** @var int */
    protected $MaxFitness = 0;

    /** @var int */
    protected $MaxFatigue = 0;

    /** @var int */
    protected $MaxTrimp = 0;

    /**
     * @param \Closure $modelCreator Closure that takes an trimp array as argument and creates a performance model.
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(\Closure $modelCreator, array $data)
    {
        $model = $modelCreator($data);

        if ($model instanceof AbstractModel) {
            $model->calculate();
            $result = $model->getArrays();

            if (!empty($result)) {
                $this->MaxFitness = max($result[AbstractModel::FITNESS]);
                $this->MaxFatigue = max($result[AbstractModel::FATIGUE]);
            }

            if (!empty($data)) {
                $this->MaxTrimp = max($data);
            }
        } else {
            throw new \InvalidArgumentException('Closure has to create an instance of Model.');
        }
    }

    /**
     * @return int
     */
    public function maxFitness()
    {
        return (int)round($this->MaxFitness);
    }

    /**
     * @return int
     */
    public function maxFatigue()
    {
        return (int)round($this->MaxFatigue);
    }

    /**
     * @return int
     */
    public function maxTrimp()
    {
        return (int)round($this->MaxTrimp);
    }
}

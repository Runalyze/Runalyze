<?php
/**
 * This file contains class::AbstractMovingAverage
 * @package Runalyze\Calculation\Math\MovingAverage
 */

namespace Runalyze\Calculation\Math\MovingAverage;

/**
 * Abstract class for moving averages
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage
 */
abstract class AbstractMovingAverage implements MovingAverageInterface
{
    /**
     * @var array
     */
    protected $Data = [];

    /**
     * @var array
     */
    protected $IndexData = [];

    /**
     * @var array
     */
    protected $MovingAverage = [];

    /**
     * @var int
     */
    protected $Length = 0;

    /**
     * AbstractMovingAverage constructor
     * @param array $data data to average
     * @param array $indexData
     * @param bool $indexDataIsAscending set to false if index is not ascending
     */
    public function __construct(array $data, array $indexData = [], $indexDataIsAscending = true)
    {
        $this->Data = $data;
        $this->IndexData = $indexData;
        $this->Length = count($this->Data);

        $this->checkIndexData($indexDataIsAscending);
    }

    /**
     * Check index data
     * @param bool $indexDataIsAscending set to false if index is not ascending
     */
    protected function checkIndexData($indexDataIsAscending)
    {
        if (!empty($this->IndexData)) {
            if (count($this->IndexData) != $this->Length) {
                throw new \InvalidArgumentException('Index data must be of same length as provided data.');
            }

            if (!$indexDataIsAscending) {
                $this->makeIndexDataAscending();
            }
        }
    }

    /**
     * Make index data ascending
     */
    protected function makeIndexDataAscending()
    {
        $sum = 0;
        for ($i = 0; $i < $this->Length; ++$i) {
            $sum += $this->IndexData[$i];
            $this->IndexData[$i] = $sum;
        }
    }

    /**
     * Calculate moving average
     */
    public function calculate()
    {
        if ($this->hasIndexData()) {
            $this->calculateWithIndexData();
        } else {
            $this->calculateWithoutIndexData();
        }
    }

    /**
     * Calculate if index data is there
     */
    abstract public function calculateWithIndexData();

    /**
     * Calculate if index data is not there
     */
    abstract public function calculateWithoutIndexData();

    /**
     * @return bool
     */
    protected function hasIndexData()
    {
        return !empty($this->IndexData);
    }

    /**
     * @return array
     */
    public function movingAverage()
    {
        return $this->MovingAverage;
    }

    /**
     * @param int $index
     * @return float|int
     * @throws \InvalidArgumentException
     */
    public function at($index)
    {
        if (isset($this->MovingAverage[$index])) {
            return $this->MovingAverage[$index];
        }

        throw new \InvalidArgumentException('Unknown index "'.$index.'" of moving average.');
    }
}
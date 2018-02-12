<?php

namespace Runalyze\Mathematics\DataAnalysis;

class ConstantSegmentFinder
{
    /** @var array */
    protected $Data;

    /** @var int */
    protected $DataSize;

    /** @var array */
    protected $IndexData;

    /** @var int|float */
    protected $ConstantDelta = 0;

    /** @var int|float */
    protected $MinimumIndexDiff = 1;

    /** @var int|float|null */
    protected $MaximumIndexDiff = null;

    public function __construct(array $data, array $indexData = [])
    {
        $this->Data = $data;
        $this->DataSize = count($data);
        $this->IndexData = empty($indexData) && 0 < $this->DataSize ? range(0, $this->DataSize - 1) : $indexData;

        if (count($this->IndexData) != $this->DataSize) {
            throw new \InvalidArgumentException('Size of $indexData must match size of $data.');
        }
    }

    /**
     * @param int|float $delta
     */
    public function setConstantDelta($delta)
    {
        $this->ConstantDelta = $delta;
    }

    /**
     * @param int|float $length
     */
    public function setMinimumIndexDiff($length)
    {
        $this->MinimumIndexDiff = $length;
    }

    /**
     * @param int|float|null $length
     */
    public function setMaximumIndexDiff($length)
    {
        $this->MaximumIndexDiff = $length;
    }

    /**
     * @return array[] [index_start, index_end] for each constant segment
     */
    public function findConstantSegments()
    {
        if (0 == $this->DataSize) {
            return [];
        }

        $i = 0;
        $segments = [];
        $currentStartIndex = 0;
        $currentMin = $this->Data[0];
        $currentMax = $this->Data[0];

        while ($i < $this->DataSize) {
            $value = $this->Data[$i];

            if ($i == $currentStartIndex) {
                ++$i;
                continue;
            }

            $maxIndexDiffIsReached = null !== $this->MaximumIndexDiff && $this->IndexData[$i - 1] - $this->IndexData[$currentStartIndex] >= $this->MaximumIndexDiff;

            if ($maxIndexDiffIsReached || $value > $currentMin + $this->ConstantDelta || $value < $currentMax - $this->ConstantDelta) {
                if ($maxIndexDiffIsReached || $this->IndexData[$i - 1] - $this->IndexData[$currentStartIndex] >= $this->MinimumIndexDiff) {
                    $segments[] = [$currentStartIndex, $i - 1];
                } else {
                    $i = $currentStartIndex + 1;
                }

                $currentStartIndex = $i;
                $currentMin = $this->Data[$i];
                $currentMax = $this->Data[$i];
            } else {
                $currentMin = $value < $currentMin ? $value : $currentMin;
                $currentMax = $value > $currentMax ? $value : $currentMax;
                ++$i;
            }
        }

        if ($currentStartIndex < $this->DataSize - 1 && $this->IndexData[$this->DataSize - 1] - $this->IndexData[$currentStartIndex] >= $this->MinimumIndexDiff) {
            $segments[] = [$currentStartIndex, $this->DataSize - 1];
        }

        return $segments;
    }
}

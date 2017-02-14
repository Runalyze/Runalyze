<?php

namespace Runalyze\Mathematics\Distribution;

/**
 * Empirical distribution
 *
 * Distribution that will be determined by an array of data points.
 */
class EmpiricalDistribution extends AbstractDistribution
{
    /** @var array */
    protected $Histogram = [];

    /**
     * @param array $data array of data points
     * @param bool $dataIsAlreadyTheHistogram if enabled $data is used as histogram ['value' => 'num', ...]
     */
    public function __construct(array $data, $dataIsAlreadyTheHistogram = false)
    {
        if ($dataIsAlreadyTheHistogram) {
            $this->Histogram = $data;
        } else {
            $this->Histogram = array_count_values($data);
        }
    }

    /**
     * @return array
     */
    public function histogram()
    {
        return $this->Histogram;
    }
}

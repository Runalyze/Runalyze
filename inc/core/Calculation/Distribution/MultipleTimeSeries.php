<?php

namespace Runalyze\Calculation\Distribution;

class MultipleTimeSeries
{
	/** @var Empirical[] */
	protected $Distributions = [];

    /** @var float[] */
    protected $Quantiles = [];

	/**
	 * @param float[] $quantiles
	 */
	public function setQuantiles(array $quantiles)
    {
        $this->Quantiles = $quantiles;
	}

    /**
     * @param array[] $dataOfMultipleSeries array of multiple data series, array keys are used to get single distributions
     * @param array $time continuous time points
     * @param array $keysThatAllowZero by default '0' is removed from all histograms
     * @throws \InvalidArgumentException
     */
    public function generateDistributionsFor(array $dataOfMultipleSeries, array $time, array $keysThatAllowZero = [])
    {
        if (empty($time)) {
            throw new \InvalidArgumentException('Time array must not be empty.');
        }

        $histograms = $this->generateHistogramsFor($dataOfMultipleSeries, $time);
        $this->removeZerosFrom($histograms, array_diff(array_keys($dataOfMultipleSeries), $keysThatAllowZero));
        $this->generateDistributions($histograms);
    }

    /**
     * @param array[] $dataOfMultipleSeries array of multiple data series, array keys are used to get single distributions
     * @param array $time continuous time points
     * @return array
     */
    protected function generateHistogramsFor(array $dataOfMultipleSeries, array $time)
    {
        $lastTime = 0;
        $dataKeys = array_keys($dataOfMultipleSeries);
        $histograms = $this->getEmptyArraysForKeys($dataKeys);

        foreach ($time as $i => $currentTime) {
            $deltaTime = $currentTime - $lastTime;

            foreach ($dataKeys as $key) {
                $value = $dataOfMultipleSeries[$key][$i];

                if (!isset($histograms[$key][$value])) {
                    $histograms[$key][$value] = $deltaTime;
                } else {
                    $histograms[$key][$value] += $deltaTime;
                }
            }

            $lastTime = $currentTime;
        }

        return $histograms;
    }

    /**
     * @param array[] $histograms
     * @param string[] $keys
     */
    protected function removeZerosFrom(array &$histograms, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($histograms[$key][0])) {
                unset($histograms[$key][0]);
            }
        }
    }

    /**
     * @param array $histograms ['key a' => histogram, ...]
     */
    protected function generateDistributions(array $histograms)
    {
        foreach ($histograms as $key => $histogram) {
            $this->Distributions[$key] = new Empirical($histogram, true);
            $this->Distributions[$key]->calculateStatistic($this->Quantiles);
        }
    }

    /**
     * @param array $keys keys that will appear as indices
     * @return array ['key a' => [], ...]
     */
    protected function getEmptyArraysForKeys(array $keys)
    {
        $collection = [];

        foreach ($keys as $key) {
            $collection[$key] = [];
        }

        return $collection;
    }

    /**
     * @param mixed $key key must have been a key in array of data series
     * @return Empirical
     */
    public function getDistribution($key)
    {
        if (!isset($this->Distributions[$key])) {
            throw new \InvalidArgumentException('Unknown distribution key "'.$key.'"');
        }

        return $this->Distributions[$key];
    }
}

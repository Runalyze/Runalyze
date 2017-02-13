<?php

namespace Runalyze\Mathematics\Distribution;

/**
 * Abstract distribution
 *
 * General class that can hold various statistics for any distribution,
 * especially useful for empirical distributions and their statistical properties.
 */
abstract class AbstractDistribution
{
    /** @var string */
    const NUM = 'num';

    /** @var string */
    const MIN = 'min';

    /** @var string */
    const MAX = 'max';

    /** @var string */
    const MEAN = 'mean';

    /** @var string */
    const MEDIAN = 'median';

    /** @var string */
    const QUANTILES = 'quantiles';

    /** @var string */
    const MODE = 'mode';

    /** @var string */
    const VARIANCE = 'var';

    /** @var array */
    private $Statistic = [
        'num' => 0,
        'min' => 0,
        'max' => 0,
        'mean' => 0,
        'median' => 0,
        'quantiles' => [],
        'mode' => 0,
        'var' => 0
    ];

    /**
     * Histogram data
     *
     * @return array
     */
    abstract public function histogram();

    /**
     * @param float[] $quantiles
     */
    public function calculateStatistic(array $quantiles = [])
    {
        $this->calculateStatisticByHistogram($quantiles);
    }

    /**
     * @param float[] $quantiles
     */
    final protected function calculateStatisticByHistogram(array $quantiles)
    {
        $sortedData = $this->histogram();

        if (empty($sortedData)) {
            return;
        }

        ksort($sortedData);

        $this->calculateMinAndMax($sortedData);
        $this->calculateMeanAndMode($sortedData);
        $this->calculateQuantilesAndVariance($sortedData, $quantiles);
    }

    /**
     * @param array $sortedData
     */
    private function calculateMinAndMax(array $sortedData)
    {
        $keys = array_keys($sortedData);

        $this->setStatistic(self::MIN, $keys[0]);
        $this->setStatistic(self::MAX, end($keys));
    }

    /**
     * @param array $sortedData
     */
    private function calculateMeanAndMode(array $sortedData)
    {
        $sum = 0;
        $num = 0;
        $maxCount = 0;
        $mode = 0;

        foreach ($sortedData as $value => $count) {
            $sum += $value * $count;
            $num += $count;

            if ($count > $maxCount) {
                $maxCount = $count;
                $mode = $value;
            }
        }

        $mean = $sum / $num;
        $this->setStatistic(self::NUM, $num);
        $this->setStatistic(self::MEAN, $mean);
        $this->setStatistic(self::MODE, $mode);
    }

    /**
     * @param array $sortedData
     * @param float[] $quantiles
     */
    private function calculateQuantilesAndVariance(array $sortedData, array $quantiles)
    {
        ksort($quantiles);

        $num = $this->Statistic[self::NUM];
        $currentQuantilesIndex = 0;
        $currentQuantile = array_shift($quantiles);
        $desiredQuantileIndex = null !== $currentQuantile ? $currentQuantile * $num : $num + 1;
        $desiredMedianIndex = $num / 2;

        $mean = $this->Statistic[self::MEAN];
        $median = false;
        $var = 0;

        foreach ($sortedData as $value => $count) {
            $var += $count * ($value - $mean) * ($value - $mean);
            $currentQuantilesIndex += $count;

            if ($median === false && $currentQuantilesIndex >= $desiredMedianIndex) {
                $median = $value;
            }

            while (null !== $currentQuantile && $currentQuantilesIndex >= $desiredQuantileIndex) {
                $this->Statistic[self::QUANTILES]['p' . $currentQuantile] = $value;

                $currentQuantile = array_shift($quantiles);
                $desiredQuantileIndex = null !== $currentQuantile ? $currentQuantile * $num : $num + 1;
            }
        }

        $this->setStatistic(self::MEDIAN, $median);
        $this->setStatistic(self::VARIANCE, $var / $this->Statistic[self::NUM]);
    }

    /**
     * @param string $key
     * @param float $value
     */
    final protected function setStatistic($key, $value)
    {
        $this->Statistic[$key] = $value;
    }

    /**
     * @return float
     */
    final public function min()
    {
        return $this->Statistic[self::MIN];
    }

    /**
     * @return float
     */
    final public function max()
    {
        return $this->Statistic[self::MAX];
    }

    /**
     * @return float
     */
    final public function mean()
    {
        return $this->Statistic[self::MEAN];
    }

    /**
     * @return float
     */
    final public function median()
    {
        return $this->Statistic[self::MEDIAN];
    }

    /**
     * @param float $alpha
     * @return float
     *
     * @throws \InvalidArgumentException
     */
    final public function quantile($alpha)
    {
        if (!isset($this->Statistic[self::QUANTILES]['p' . $alpha])) {
            throw new \InvalidArgumentException('No quantile calculated for alpha = ' . $alpha);
        }

        return $this->Statistic[self::QUANTILES]['p' . $alpha];
    }

    /**
     * @return float
     */
    final public function mode()
    {
        return $this->Statistic[self::MODE];
    }

    /**
     * @return float
     */
    final public function variance()
    {
        return $this->Statistic[self::VARIANCE];
    }

    /**
     * @return float
     */
    final public function stdDev()
    {
        return sqrt($this->Statistic[self::VARIANCE]);
    }

    /**
     * @return bool|float boolean false is returned if mean is zero
     */
    final public function coefficientOfVariation()
    {
        if (0 == $this->Statistic[self::MEAN]) {
            return false;
        }

        return sqrt($this->Statistic[self::VARIANCE]) / $this->Statistic[self::MEAN];
    }
}

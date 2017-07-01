<?php

namespace Runalyze\Mathematics\Filter;

/**
 * Hampel filter
 *
 * "The Hampel filter is a member of the class of decision filters that replaces the central value in the data window
 *  with the median if it lies far enough from the median to be deemed an outlier. This filter depends on both the
 *  window width and an additional tuning parameter t, reducing to the median filter when t=0, so it may be regarded as
 *  another median filter extension."
 *
 * @see Pearson, R.K., Neuvo, Y., Astola, J. et al.: Generalized Hampel Filters,
 *      EURASIP J. Adv. Signal Process. (2016) 2016: 87. doi:10.1186/s13634-016-0383-6
 *      https://link.springer.com/article/10.1186/s13634-016-0383-6
 * @see http://de.mathworks.com/help/signal/ref/hampel.html
 * @see https://github.com/cran/pracma/blob/63e8a52ae6668e736720c89691352d6dc3bc9eb1/R/hampel.R
 */
class HampelFilter
{
    /** @var float */
    const STD_ESTIMATE_FACTOR = 1.4826;

    /** @var int */
    protected $WindowWidth;

    /** @var float */
    protected $SigmaFactor;

    /**
     * @param int $windowWidth window [x[i-w] .. x[i] .. x[i+w]]
     * @param float $sigmaFactor number of standard deviations a sample must differ from local median
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($windowWidth = 3, $sigmaFactor = 3.0)
    {
        if (!is_int($windowWidth) || $windowWidth < 1) {
            throw new \InvalidArgumentException('Window width must be a positive integer.');
        }

        $this->WindowWidth = $windowWidth;
        $this->SigmaFactor = $sigmaFactor;
    }

    /**
     * @param array $inputData
     * @param bool $returnCorrectedData by default, outlier indices are returned
     * @return array outlier indices or corrected data
     */
    public function filter(array $inputData, $returnCorrectedData = false)
    {
        $num = count($inputData);
        $outlierIndices = [];
        $correctedData = $inputData;
        $slicedData = array_slice($inputData, 0, 2 * $this->WindowWidth + 1);

        for ($i = $this->WindowWidth; $i < $num - $this->WindowWidth; ++$i) {
            $median = $this->median($slicedData);
            $sigma = self::STD_ESTIMATE_FACTOR * $this->median(array_map(function ($v) use ($median) {
                return abs($v - $median);
            }, $slicedData));

            if (abs($inputData[$i] - $median) > $this->SigmaFactor * $sigma) {
                $correctedData[$i] = $median;
                $outlierIndices[] = $i;
            }

            if ($i < $num - $this->WindowWidth - 2) {
                array_pop($slicedData);
                $slicedData[] = $inputData[$i + $this->WindowWidth + 1];
            }
        }

        if ($returnCorrectedData) {
            return $correctedData;
        }

        return $outlierIndices;
    }

    /**
     * @param array $data must be of size 2 * $this->WindowWidth + 1
     * @return mixed
     */
    protected function median(array $data)
    {
        sort($data, SORT_NUMERIC);

        return $data[$this->WindowWidth];
    }
}

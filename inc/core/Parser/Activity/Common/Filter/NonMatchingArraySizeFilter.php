<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\ContinuousData;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class NonMatchingArraySizeFilter extends AbstractFilter
{
    /** @var int */
    const MIN_MAX_TOLERANCE = 2;

    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $nonEmptyKeys = $this->getNonEmptyContinuousDataKeys($container->ContinuousData);

        if (empty($nonEmptyKeys)) {
            return;
        }

        $arraySizes = $this->getArraySizes($nonEmptyKeys, $container->ContinuousData);

        if (!$this->areAllElementsEqual($arraySizes)) {
            if ($strict) {
                $this->throwErrorForNonMatchingArraySizes($arraySizes);
            }

            $this->tryToHandleNonMatchingArraySizes($arraySizes, $container->ContinuousData);
        }
    }

    protected function throwErrorForNonMatchingArraySizes(array $arraySizes)
    {
        throw new InvalidDataException($this->getErrorOrLogMessage($arraySizes, false));
    }

    protected function logFixedArraySizes(array $arraySizes)
    {
        $this->logger->warning($this->getErrorOrLogMessage($arraySizes, true));
    }

    /**
     * @param array $arraySizes
     * @param bool $sizesHaveBeenFixed
     * @return string
     */
    protected function getErrorOrLogMessage(array $arraySizes, $sizesHaveBeenFixed = false)
    {
        return sprintf(
            '%s array sizes %s (%s).',
            $sizesHaveBeenFixed ? 'Non-matching' : 'Unrecoverable non-matching',
            $sizesHaveBeenFixed ? 'fixed' : 'detected',
            implode(', ', array_map(function ($key, $size) {
                return $key.': '.$size;
            }, array_keys($arraySizes), $arraySizes))
        );
    }

    /**
     * @param ContinuousData $continuousData
     * @return string[] keys
     */
    protected function getNonEmptyContinuousDataKeys(ContinuousData $continuousData)
    {
        $keys = $continuousData->getPropertyNamesOfArrays();

        foreach ($keys as $i => $key) {
            if (empty($continuousData->{$key})) {
                unset($keys[$i]);
            }
        }

        return $keys;
    }

    /**
     * @param string[] $keys
     * @param ContinuousData $continuousData
     * @return int[]
     */
    protected function getArraySizes(array $keys, ContinuousData $continuousData)
    {
        $sizes = [];

        foreach ($keys as $key) {
            $sizes[$key] = count($continuousData->{$key});
        }

        return $sizes;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function areAllElementsEqual(array $data)
    {
        return 1 === count(array_unique($data));
    }

    protected function tryToHandleNonMatchingArraySizes(array $arraySizes, ContinuousData $continuousData)
    {
        $minimalSize = min($arraySizes);
        $maximalSize = max($arraySizes);

        if ($minimalSize >= $maximalSize - self::MIN_MAX_TOLERANCE) {
            $this->reduceArrays($minimalSize, $arraySizes, $continuousData);
        } else {
            $this->throwErrorForNonMatchingArraySizes($arraySizes);
        }
    }

    /**
     * @param int $newSize
     * @param array $arraySizes
     * @param ContinuousData $continuousData
     */
    protected function reduceArrays($newSize, array $arraySizes, ContinuousData $continuousData)
    {
        foreach ($arraySizes as $key => $size) {
            if ($size != $newSize) {
                $continuousData->{$key} = array_slice($continuousData->{$key}, 0, $newSize);
            }
        }

        $this->logFixedArraySizes($arraySizes);
    }
}

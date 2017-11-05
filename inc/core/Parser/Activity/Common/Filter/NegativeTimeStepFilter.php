<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\ContinuousData;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class NegativeTimeStepFilter extends AbstractFilter
{
    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $negativeTimeSteps = $this->detectNegativeTimeSteps($container->ContinuousData->Time, $strict);

        if (!empty($negativeTimeSteps)) {
            $this->tryToHandleNegativeTimeSteps($negativeTimeSteps, $container->ContinuousData);
        }
    }

    /**
     * @param array $time
     * @param bool $strict
     * @return array [time array index => duration]
     *
     * @throws InvalidDataException
     */
    protected function detectNegativeTimeSteps(array $time, $strict)
    {
        $negativeTimeSteps = [];
        $numberOfPoints = count($time);

        for ($i = 1; $i < $numberOfPoints; ++$i) {
            if ($time[$i] < $time[$i - 1]) {
                if ($strict) {
                    throw new InvalidDataException(sprintf('Negative time step #%u of %ds detected.', $i, $time[$i] - $time[$i - 1]));
                }

                $negativeTimeSteps[$i] = $time[$i - 1] - $time[$i];
            }
        }

        return $negativeTimeSteps;
    }

    protected function tryToHandleNegativeTimeSteps(array $negativeTimeSteps, ContinuousData $continuousData)
    {
        $indicesToRemove = [];

        foreach ($negativeTimeSteps as $index => $duration) {
            if (isset($continuousData->Time[$index + 1]) && $continuousData->Time[$index + 1] >= $continuousData->Time[$index - 1]) {
                $indicesToRemove[] = $index;

                $this->logger->warning(sprintf('Negative time step #%u of %ds removed.', $index, -$duration));
            } else {
                throw new InvalidDataException(sprintf('Unrecoverable negative time step #%u of %ds detected.', $index, -$duration));
            }
        }

        if (!empty($indicesToRemove)) {
            $this->removeInvalidIndices($indicesToRemove, $continuousData);
        }
    }

    protected function removeInvalidIndices(array $indicesToRemove, ContinuousData $continuousData)
    {
        $arrayKeys = $continuousData->getPropertyNamesOfArrays();

        foreach ($arrayKeys as $i => $key) {
            if (empty($continuousData->{$key})) {
                unset($arrayKeys[$i]);
            }
        }

        foreach (array_reverse($indicesToRemove) as $index) {
            foreach ($arrayKeys as $key) {
                unset($continuousData->{$key}[$index]);
            }
        }

        foreach ($arrayKeys as $key) {
            $continuousData->{$key} = array_values($continuousData->{$key});
        }
    }
}

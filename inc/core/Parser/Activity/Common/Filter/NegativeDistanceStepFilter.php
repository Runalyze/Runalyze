<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class NegativeDistanceStepFilter extends AbstractFilter
{
    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $numberOfPoints = count($container->ContinuousData->Distance);

        for ($i = 1; $i < $numberOfPoints; ++$i) {
            if ($container->ContinuousData->Distance[$i] < $container->ContinuousData->Distance[$i - 1]) {
                if (!$strict && $container->ContinuousData->Distance[$i] == 0) {
                    $container->ContinuousData->Distance[$i] = $container->ContinuousData->Distance[$i - 1];

                    $this->logger->warning(sprintf('Missing distance at #%u fixed.', $i));
                } else {
                    throw new InvalidDataException(sprintf('Negative distance step #%u of %ds detected.', $i, $container->ContinuousData->Distance[$i] - $container->ContinuousData->Distance[$i - 1]));
                }
            }
        }
    }
}

<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class InvalidRRIntervalFilter extends AbstractFilter
{
    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $originalLength = count($container->RRIntervals);
        $container->RRIntervals = array_values(array_filter($container->RRIntervals));
        $filteredLength = count($container->RRIntervals);

        if ($strict && $filteredLength != $originalLength) {
            throw new InvalidDataException('Invalid r-r intervals detected.');
        } else {
            $this->logger->warning(sprintf('%u invalid r-r intervals removed.', $originalLength - $filteredLength));
        }
    }
}

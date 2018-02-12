<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class NegativeDistanceStepFilter extends AbstractFilter
{
    /** @var float [km] */
    protected $LimitForDistanceDifferenceToFix;

    /**
     * @param float $limitForDistanceDifferenceToFix [km]
     */
    public function __construct($limitForDistanceDifferenceToFix = 0.02)
    {
        parent::__construct();

        $this->LimitForDistanceDifferenceToFix = $limitForDistanceDifferenceToFix;
    }

    public function filter(ActivityDataContainer $container, $strict = false)
    {
        $numberOfPoints = count($container->ContinuousData->Distance);

        for ($i = 1; $i < $numberOfPoints; ++$i) {
            if ($container->ContinuousData->Distance[$i] < $container->ContinuousData->Distance[$i - 1]) {
                if (!$strict && (
                    $container->ContinuousData->Distance[$i] == 0 ||
                    $container->ContinuousData->Distance[$i - 1] - $container->ContinuousData->Distance[$i] <= $this->LimitForDistanceDifferenceToFix ||
                    ($i < $numberOfPoints - 1 && $container->ContinuousData->Distance[$i - 1] < $container->ContinuousData->Distance[$i + 1])
                )) {
                    $container->ContinuousData->Distance[$i] = $container->ContinuousData->Distance[$i - 1];

                    $this->logger->warning(sprintf('Missing or negative distance at #%u fixed.', $i));
                } else {
                    throw new InvalidDataException(sprintf('Negative distance step #%u of %.3f km detected.', $i, $container->ContinuousData->Distance[$i] - $container->ContinuousData->Distance[$i - 1]));
                }
            }
        }
    }
}

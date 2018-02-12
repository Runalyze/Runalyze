<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Exception\InvalidDataException;

class NegativePauseFilter extends AbstractFilter
{
    public function filter(ActivityDataContainer $container, $strict = false)
    {
        foreach ($container->Pauses->getElements() as $key => $pause) {
            if ($pause->getDuration() <= 0) {
                if ($strict) {
                    throw new InvalidDataException('Pause with negative duration detected.');
                }

                $container->Pauses->offsetUnset($key);
                $this->logger->warning(sprintf('Pause #%u with negative duration of %ds removed.', $key, $pause->getDuration()));
            }
        }

        $container->Pauses->rebase();
    }
}

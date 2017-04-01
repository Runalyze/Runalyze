<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\RouterCacheWarmer;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Runalyze\Util\InterfaceChoosable;

class SportProfile extends AbstractEnum implements InterfaceChoosable
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const GENERIC = 0;

    /** @var int */
    const RUNNING = 1;

    /** @var int */
    const CYCLING = 2;

    /** @var int */
    const SWIMMING = 3;

    /** @var int */
    const ROWING = 4;

    /** @var int */
    const HIKING = 5;

    /**
     * @return array
     */
    public static function getChoices()
    {
        return array(
            __('Generic') => self::GENERIC,
            __('Running') => self::RUNNING,
            __('Cycling') => self::CYCLING,
            __('Swimming') => self::SWIMMING,
            __('Rowing') => self::ROWING,
            __('Hiking') => self::HIKING
        );
    }

    /**
     * @param array $usedIds
     * @return array
     */
    public static function getAvailableChoices(array $usedIds)
    {
        $availableIds = array_flip(self::getChoices());

        foreach ($usedIds as $id) {
            unset($availableIds[$id]);
        }

        return array_flip($availableIds);
    }
}

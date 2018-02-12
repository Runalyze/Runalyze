<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;
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
        $choices = [];

        foreach (self::getEnum() as $enum) {
            $choices[self::get($enum)->getName()] = $enum;
        }

        return $choices;
    }
}

<?php

namespace Runalyze\Bundle\CoreBundle\Services;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class AutomaticReloadFlagSetter
{
    /** @var string */
    const FLASH_BAG_KEY = 'ajax.reload.command';

    /** @var int enum */
    const FLAG_DATA_BROWSER = 1;

    /** @var int enum */
    const FLAG_TRAINING = 2;

    /** @var int enum */
    const FLAG_TRAINING_AND_DATA_BROWSER = 3;

    /** @var int enum */
    const FLAG_PLUGINS = 4;

    /** @var int enum */
    const FLAG_ALL = 5;

    /** @var int enum */
    const FLAG_PAGE = 6;

    /** @var FlashBagInterface */
    protected $FlashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->FlashBag = $flashBag;
    }

    /**
     * @param int $flag
     * @return int
     */
    public function set($flag)
    {
        $currentFlag = $this->FlashBag->get(self::FLASH_BAG_KEY);

        if (!empty($currentFlag)) {
            $flag = $this->mergeFlags([$currentFlag[0], $flag]);
        }

        $this->FlashBag->set(self::FLASH_BAG_KEY, $flag);

        return $flag;
    }

    /**
     * @param int[] $flags
     * @return int
     */
    protected function mergeFlags(array $flags)
    {
        if (self::FLAG_DATA_BROWSER == min($flags)) {
            if (max($flags) == self::FLAG_PLUGINS) {
                return self::FLAG_ALL;
            } elseif (max($flags) == self::FLAG_TRAINING) {
                return self::FLAG_TRAINING_AND_DATA_BROWSER;
            }
        }

        return max($flags);
    }
}

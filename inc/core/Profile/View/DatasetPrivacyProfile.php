<?php

namespace Runalyze\Profile\View;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Util\InterfaceChoosable;

class DatasetPrivacyProfile extends AbstractEnum implements InterfaceChoosable
{
    /** @var int */
    const PRIVATE_KEY = 0;

    /** @var int */
    const PUBLIC_KEY = 1;

    /**
     * @param int $id id from internal enum
     * @return string
     */
    static public function stringFor($id)
    {
        switch ($id) {
            case self::PRIVATE_KEY:
                return __('private');
            case self::PUBLIC_KEY:
                return __('public');
            default:
                throw new \InvalidArgumentException('Invalid databrowser row id "'.$id.'".');
        }
    }

    /**
     * @return array
     */
    static public function getChoices() {
        return array(
            self::stringFor(self::PRIVATE_KEY) => self::PRIVATE_KEY,
            self::stringFor(self::PUBLIC_KEY) => self::PUBLIC_KEY
        );
    }

}

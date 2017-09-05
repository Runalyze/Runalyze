<?php

namespace Runalyze\Profile\View;

use Runalyze\Common\Enum\AbstractEnum;

class DataBrowserRowProfile extends AbstractEnum
{
    /** @var int */
    const COMPLETE_ROW = 0;

    /** @var int */
    const ONLY_ICON = 1;

    /** @var int */
    const INHERIT_FROM_PARENT = 2;

    /**
     * @param int $id id from internal enum
     * @return string
     */
    static public function stringFor($id)
    {
        switch ($id) {
            case self::COMPLETE_ROW:
                return __('complete row');
            case self::ONLY_ICON:
                return __('only icon');
            case self::INHERIT_FROM_PARENT:
                return __('Inherit from sport');
            default:
                throw new \InvalidArgumentException('Invalid databrowser row id "'.$id.'".');
        }
    }

    /**
     * @return array
     */
    static public function getChoices() {
        return array(
            self::stringFor(self::COMPLETE_ROW) => self::COMPLETE_ROW,
            self::stringFor(self::ONLY_ICON) => self::ONLY_ICON,
            self::stringFor(self::INHERIT_FROM_PARENT) => self::INHERIT_FROM_PARENT
        );
    }

    /**
     * @return array
     */
    static public function getChoicesWithoutParent() {
        return array(
            self::stringFor(self::COMPLETE_ROW) => self::COMPLETE_ROW,
            self::stringFor(self::ONLY_ICON) => self::ONLY_ICON
        );
    }
}

<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

final class QueryGroups extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var string */
    const MONTH = 'month';

    /** @var int */
    const YEAR = 'year';

    /** @var int */
    const SPORT = 'sport';

    /** @var int */
    const TYPE = 'type';

    /** @var int */
    const EQUIPMENT_TYPE = 'equipment';

    public static function getEnumForEquipmentType(\Runalyze\Bundle\CoreBundle\Entity\EquipmentType $equipmentType)
    {
        return self::EQUIPMENT_TYPE.'_'.$equipmentType->getId();
    }

    /**
     * @param $enum
     * @return QueryGroupInterface
     */
    public static function getGroup($enum)
    {
        if (substr($enum, 0, strlen(self::EQUIPMENT_TYPE) + 1) == self::EQUIPMENT_TYPE.'_') {
            return new EquipmentType(substr($enum, strlen(self::EQUIPMENT_TYPE) + 1));
        }

        return self::get($enum);
    }
}

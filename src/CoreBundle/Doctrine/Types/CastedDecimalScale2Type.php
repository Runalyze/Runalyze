<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DecimalType;

class CastedDecimalScale2Type extends DecimalType
{
    /** @var string */
    const CASTED_DECIMAL_2 = 'casted_decimal_2';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (float)$value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : sprintf('%.2f', $value);
    }

    public function getName()
    {
        return self::CASTED_DECIMAL_2;
    }
}

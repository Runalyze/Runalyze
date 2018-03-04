<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DecimalType;

class CastedDecimalScale1Type extends DecimalType
{
    /** @var string */
    const CASTED_DECIMAL_1 = 'casted_decimal_1';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (float)$value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : sprintf('%.1f', $value);
    }

    public function getName()
    {
        return self::CASTED_DECIMAL_1;
    }
}

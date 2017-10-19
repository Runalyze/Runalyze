<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Runalyze\Calculation\Activity\TimeArrayMinifier;

class RunalyzeTimeArray extends Type
{
    /** @var string */
    const TIME_ARRAY = 'time_array';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return TimeArrayMinifier::shorten(implode('|', $value));
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || '' == trim($value)) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        $timeArray =  array_map(function ($v) {
            return $v + 0;
        }, explode('|', $value));

        return TimeArrayMinifier::extend($timeArray);
    }

    public function getName()
    {
        return self::TIME_ARRAY;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

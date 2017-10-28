<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Runalyze\Calculation\Route\GeohashLine;

class GeohashArray extends Type
{
    /** @var string */
    const GEOHASH_ARRAY = 'geohash_array';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return implode('|', GeohashLine::shorten($value));
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || '' == trim($value)) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        return GeohashLine::extend(explode('|', $value));
    }

    public function getName()
    {
        return self::GEOHASH_ARRAY;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

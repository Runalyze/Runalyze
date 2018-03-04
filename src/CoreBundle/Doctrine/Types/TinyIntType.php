<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\SmallIntType;

class TinyIntType extends SmallIntType
{
    /** @var string */
    const TINYINT = 'tinyint';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $declaration = parent::getSQLDeclaration($fieldDeclaration, $platform);

        if ($platform instanceof MySqlPlatform) {
            $declaration = str_replace('SMALLINT', 'TINYINT', $declaration);
        }

        return $declaration;
    }

    public function getName()
    {
        return self::TINYINT;
    }
}

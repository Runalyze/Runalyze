<?php
/**
 * This file contains trait::AbstractEnumFactoryTrait
 * @package Runalyze\Util
 */

namespace Runalyze\Util;

/**
 * Trait providing a factory for an enum class
 *
 * @author Hannes Christiansen
 * @package Runalyze\Util
 */
trait AbstractEnumFactoryTrait
{
	/** @var array|null */
	private static $ClassNames = null;

    /** @var string */
    private static $Namespace = '';

    /**
     * Get object
     * @param int|string $enum from internal enum
     * @return \Runalyze\Util\AbstractEnum
     * @throws \InvalidArgumentException
     */
    public static function get($enum)
    {
        if (null == self::$ClassNames) {
            self::generateNamespace();
            self::generateClassNamesArray();
        }

        if (!isset(self::$ClassNames[$enum])) {
            throw new \InvalidArgumentException('Invalid enum "'.$enum.'".');
        }

        $className = self::$Namespace.'\\'.self::$ClassNames[$enum];

        return new $className;
    }

    private static function generateNamespace()
    {
        self::$Namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
    }

    /**
     * @throws \Exception
     */
    private static function generateClassNamesArray()
    {
        if (!method_exists(get_called_class(), 'getEnum')) {
            throw new \BadMethodCallException('Classes using this trait must have static method getEnum().');
        }

        self::$ClassNames = array_map(function($v) {
            return str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $v))));
        }, array_flip(self::getEnum()));
    }
}

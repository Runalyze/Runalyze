<?php

namespace Runalyze\Util;

interface InterfaceChoosable
{
    /**
     * @return array (name => database/form value)
     */
    public static function getChoices();
}

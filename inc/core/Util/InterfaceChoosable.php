<?php
/**
 * This file contains class::InterfaceChoosable
 * @package Runalyze\Util
 */

namespace Runalyze\Util;

/**
 * Abstract class for formular / AbstractEnums
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Util
 */
interface InterfaceChoosable
{
    /**
     * @return array ( name => database/formular value)
     */ 
    public static function getChoices();
}

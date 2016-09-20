<?php
/**
 * This file contains class::Gender
 * @package Runalyze\Profile\Athlete
 */

namespace Runalyze\Profile\Athlete;
use Runalyze\Util\AbstractEnum;
use Runalyze\Util\InterfaceChoosable;

/**
 * Gender
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Profile\Athlete
 */
class Gender extends \Runalyze\Util\AbstractEnum implements \Runalyze\Util\InterfaceChoosable{
	/**
	 * None
	 * @var string
	 */
	const NONE = 0;

	/**
	 * Male
	 * @var string
	 */
	const MALE = 1;

	/**
	 * Female
	 * @var string
	 */
	const FEMALE = 2;

	/**
	 * @param int $id id from internal enum
	 * @return string
	 */
	static public function stringFor($id)
	{
		switch ($id) {
                        case self::NONE:
				return __('not set');
			case self::MALE:
				return __('male');
			case self::FEMALE:
				return __('female');
			default:
				throw new \InvalidArgumentException('Invalid id');
		}
	}
        
        /*
         * Get choices
         */
        static public function getChoices() {
            return array(
                    self::stringFor(self::NONE) => self::NONE,
                    self::stringFor(self::MALE) => self::MALE,
                    self::stringFor(self::FEMALE) => self::FEMALE
                
            );
        }
        
}
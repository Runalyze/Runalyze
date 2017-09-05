<?php

namespace Runalyze\Profile\Athlete;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Util\InterfaceChoosable;

class Gender extends AbstractEnum implements InterfaceChoosable
{
	/** @var int */
	const NONE = 0;

	/** @var int */
	const MALE = 1;

	/** @var int */
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
				throw new \InvalidArgumentException('Invalid gender id "'.$id.'".');
		}
	}

    /**
     * @return array
     */
    static public function getChoices() {
        return array(
            self::stringFor(self::NONE) => self::NONE,
            self::stringFor(self::MALE) => self::MALE,
            self::stringFor(self::FEMALE) => self::FEMALE
        );
    }
}

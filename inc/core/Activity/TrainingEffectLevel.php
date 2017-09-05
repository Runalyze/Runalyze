<?php
/**
 * This file contains class::TrainingEffectLevel
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Common\Enum\AbstractEnum;

/**
 * Enum for training effect levels
 * 
 * @see https://www.firstbeat.com/en/consumer-products/features/#training-effect
 * @package Runalyze\Activity
 */
class TrainingEffectLevel extends AbstractEnum
{
	/** @var int */
	const EASY = 1;

	/** @var int */
	const MAINTAINING = 2;

    /** @var int */
    const IMPROVING = 3;

    /** @var int */
    const HIGHLY_IMPROVING = 4;

    /** @var int */
    const OVERREACHING = 5;

    /**
     * @param float $value value between 1.0 and 5.0
     * @return int internal enum
     * @throws \InvalidArgumentException
     */
    public static function levelFor($value)
    {
        if (!is_numeric($value) || $value < 1.0 || 5.0 < $value ) {
            throw new \InvalidArgumentException(sprintf('Provided training effect %s is invalid.', $value));
        }

        if ($value == 5.0) {
            return self::OVERREACHING;
        } elseif ($value >= 4.0) {
            return self::HIGHLY_IMPROVING;
        } elseif ($value >= 3.0) {
            return self::IMPROVING;
        } elseif ($value >= 2.0) {
            return self::MAINTAINING;
        }

        return self::EASY;
    }

    /**
     * @param int $enum internal enum
     * @return string
     * @throws \InvalidArgumentException
     * @codeCoverageIgnore
     */
    public static function label($enum)
    {
        switch ($enum) {
            case self::EASY:
                return __('Easy');
            case self::MAINTAINING:
                return __('Maintaining workout');
            case self::IMPROVING:
                return __('Improving Fitness');
            case self::HIGHLY_IMPROVING:
                return __('Highly Improving');
            case self::OVERREACHING:
                return __('Overreaching');
            default:
                throw new \InvalidArgumentException(sprintf('Provided level %u is invalid.', $enum));
        }
    }

    /**
     * @param int $enum internal enum
     * @return string
     * @throws \InvalidArgumentException
     * @codeCoverageIgnore
     */
    public static function description($enum)
    {
        switch ($enum) {
            case self::EASY:
                return __('Helps recovery (short activities). Improves endurance with longer activities (more than 40 minutes).');
            case self::MAINTAINING:
                return __('Maintains your aerobic fitness.');
            case self::IMPROVING:
                return __('Improves your aerobic fitness if repeated as part of your weekly training program.');
            case self::HIGHLY_IMPROVING:
                return __('Highly improves your aerobic fitness if repeated 1-2 times per week with adequate recovery time.');
            case self::OVERREACHING:
                return __('Causes temporary overload with high improvement. Train up to this number with extreme care. Requires additional recovery days.');
            default:
                throw new \InvalidArgumentException(sprintf('Provided level %u is invalid.', $enum));
        }
    }
}

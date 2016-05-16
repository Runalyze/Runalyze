<?php
/**
 * This file contains class::PerformanceCondition
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * FIT performance condition
 *
 * This value represents the current athlete's condition, based on his (by Garmin/Firstbeat) estimated VO2max.
 * A value of 100 is interpreted as 100% which equals the current baseline value.
 * A value of e.g. 97 is shown as "-3" on the device (usually within the first 20 minutes).
 *
 * @see http://www8.garmin.com/manuals/webhelp/fenix3/EN-US/GUID-901ACCFC-FB0D-414E-B80C-54970AF4E357.html
 * @see https://www.youtube.com/watch?v=14gYdT8W2Ts
 * @package Runalyze\Activity
 */
class PerformanceCondition implements ValueInterface
{
	/** @var int */
	const BASELINE = 100;

    /** @var int */
    const LOWER_LIMIT = 80;

    /** @var int */
    const UPPER_LIMIT = 120;

	/** @var int|null */
	protected $Value = null;

	/**
	 * Format
	 * @param int|null $value
	 * @return string
	 */
	public static function format($value)
    {
        return (new self($value))->string();
	}

	/**
	 * @param int|null $value
	 */
	public function __construct($value = null)
    {
		$this->set($value);
	}

    /**
     * @return string
     * @codeCoverageIgnore
     */
	public function label()
    {
		return __('Performance condition');
	}

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function shortLabel()
    {
        return __('PC');
    }

	/**
	 * Unit
	 * @return string
     * @codeCoverageIgnore
	 */
	public function unit()
    {
		return '';
	}

	/**
	 * Set training effect
	 * @param int|null|string $value
	 * @return \Runalyze\Activity\PerformanceCondition $this-reference
     * @throws \InvalidArgumentException
	 */
	public function set($value)
    {
        if (null === $value || (int)$value < self::LOWER_LIMIT || self::UPPER_LIMIT < (int)$value ) {
            $this->Value = null;
        } else {
    		$this->Value = (int)$value;
        }

		return $this;
	}

	/**
	 * Format training effect as string
	 * @param bool $withUnit [optional] show unit?
	 * @return string
	 */
	public function string($withUnit = true)
    {
        if (!$this->isKnown()) {
            return '';
        }

        $baselineValue = $this->Value - self::BASELINE;

        if ($baselineValue > 0) {
            $baselineValue = '+'.$baselineValue;
        } elseif ($baselineValue == 0) {
            $baselineValue = '+/- 0';
        }

		return $baselineValue;
	}

	/**
	 * @return int|null
	 */
	public function value()
    {
		return $this->Value;
	}

    /**
     * @return bool
     */
    public function isKnown()
    {
        return (null !== $this->Value);
    }
}
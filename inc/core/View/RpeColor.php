<?php

namespace Runalyze\View;

class RpeColor
{
    /** @var array */
    const LEVEL_COLORS = [
        '#225ea8',
        '#41b6c4',
        '#a1dab4',
        '#fecc5c',
        '#fd8d3c',
        '#e31a1c'
    ];

	/** @var int|null */
	protected $Value = null;

    /**
	 * @param int|null $value
	 */
	public function __construct($value = null)
    {
		$this->setValue($value);
	}

	/**
	 * @param int|null $value
	 * @return self
	 */
	public function setValue($value)
    {
        if (!is_numeric($value) || $value < 6 || $value > 20) {
            $this->Value = null;
        } else {
            $this->Value = (int)$value;
        }

		return $this;
	}

    /**
     * @return int|null
     */
    public function value()
    {
        return $this->Value;
    }

	/**
	 * @return string
	 */
	public function borderColor()
    {
        switch ($this->Value) {
            case 6:
                return self::LEVEL_COLORS[0];

            case 7:
            case 8:
            case 9:
                return self::LEVEL_COLORS[1];

            case 10:
            case 11:
            case 12:
                return self::LEVEL_COLORS[2];

            case 13:
            case 14:
            case 15:
                return self::LEVEL_COLORS[3];

            case 16:
            case 17:
            case 18:
            case 19:
                return self::LEVEL_COLORS[4];

            case 20:
                return self::LEVEL_COLORS[5];

            default:
                return 'transparent';
        }
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function string()
    {
	    if (null === $this->Value) {
	        return '';
        }

        return '<span class="rpe-icon" style="border-color:'.$this->borderColor().';">'.$this->Value.'</span>';
	}
}

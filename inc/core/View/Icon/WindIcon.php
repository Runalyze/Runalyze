<?php
/**
 * This file contains class::WindIcon
 * @package Runalyze\View\Icon
 */

namespace Runalyze\View\Icon;

use Runalyze\Data\Weather\WindDegree;
use Runalyze\Data\Weather\WindSpeed;

/**
 * Wind icon
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon
 */
class WindIcon extends \Runalyze\View\Icon
{
    /** @var string */
    const BASE_CLASS = 'windicon';

    /** @var string */
    const DIRECTION_CLASS = 'windicon-direction';

    /** @var \Runalyze\Data\Weather\WindSpeed */
    protected $WindSpeed;

    /** @var \Runalyze\Data\Weather\WindDegree */
    protected $WindDegree;

    /**
     * WindIcon constructor.
     * @param \Runalyze\Data\Weather\WindSpeed $windSpeed
     * @param \Runalyze\Data\Weather\WindDegree $windDegree
     */
    public function __construct(WindSpeed $windSpeed, WindDegree $windDegree)
    {
        $this->WindSpeed = $windSpeed;
        $this->WindDegree = $windDegree;

        $this->setDefaultTooltip();
    }

    /**
     * Set default tooltip
     */
    public function setDefaultTooltip()
    {
        $strings = [];

        if (!$this->WindSpeed->isUnknown()) {
            $strings[] = $this->WindSpeed->string();
        }

        if (!$this->WindDegree->isUnknown()) {
            $strings[] = $this->WindDegree->string();
        }

        if (!empty($strings)) {
            $this->setTooltip(__('Wind').': '.implode(', ', $strings));
        }
    }

    /**
     * Display
     * @return string
     */
    public function code()
    {
        if ($this->WindSpeed->isUnknown() && $this->WindDegree->isUnknown()) {
            return '';
        }

        $code = '<span class="'.self::BASE_CLASS.'"'.$this->tooltipAttributes().'>';

        if (!$this->WindDegree->isUnknown()) {
            $code .= '<span class="'.self::DIRECTION_CLASS.'" style="transform:rotate('.$this->WindDegree->value().'deg);"></span> ';
        }

        $code .= $this->WindSpeed->isUnknown() ? '?' : $this->WindSpeed->string(false);
        $code .= '</span>';

        return $code;
    }
}
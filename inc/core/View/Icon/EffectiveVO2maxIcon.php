<?php

namespace Runalyze\View\Icon;

use Runalyze\Configuration;

class EffectiveVO2maxIcon extends \Runalyze\View\Icon
{
    /** @var string */
    const BASE_CLASS = 'vo2max-icon small';

    /** @var string */
    const DIRECTION_UP = 'fa-arrow-up';

    /** @var string */
    const DIRECTION_UP_HALF = 'fa-arrow-up  fa-rotate-45';

    /** @var string */
    const DIRECTION_RIGHT = 'fa-arrow-right';

    /** @var string */
    const DIRECTION_DOWN_HALF = 'fa-arrow-right  fa-rotate-45';

    /** @var string */
    const DIRECTION_DOWN = 'fa-arrow-down';

    /** @var bool */
    protected $IsTransparent = false;

    /** @var string */
    protected $Direction = '';

    /**
     * @param float $value
     * @param float $currentShape
     */
    public function __construct($value = null, $currentShape = null)
    {
        parent::__construct(self::BASE_CLASS);

        if (null !== $value) {
            $this->setDirectionBasedOn($value, $currentShape);
            $this->setTooltipFor($value);
        }
    }

    /**
     * @param float $value
     */
    protected function setTooltipFor($value)
    {
        $this->setTooltip('VO<sub>2</sub>max: '.round($value, 2));
    }

    /**
     * @param float $value
     * @param float $currentShape
     */
    protected function setDirectionBasedOn($value, $currentShape)
    {
        // TODO
        if (null === $currentShape) {
            $currentShape = Configuration::Data()->vdot();
        }

        $diff = $value - $currentShape;

        if ($diff > 3.0) {
            $this->setUp();
        } elseif ($diff > 1.0) {
            $this->setUpHalf();
        } elseif ($diff > -1.0) {
            $this->setRight();
        } elseif ($diff > -3.0) {
            $this->setDownHalf();
        } else {
            $this->setDown();
        }
    }

    public function setTransparent()
    {
        $this->IsTransparent = true;
    }

    public function setUp()
    {
        $this->Direction = self::DIRECTION_UP;
    }

    public function setUpHalf()
    {
        $this->Direction = self::DIRECTION_UP_HALF;
    }

    public function setRight()
    {
        $this->Direction = self::DIRECTION_RIGHT;
    }

    public function setDownHalf()
    {
        $this->Direction = self::DIRECTION_DOWN_HALF;
    }

    public function setDown()
    {
        $this->Direction = self::DIRECTION_DOWN;
    }

    /**
     * @return string
     */
    public function code()
    {
        if ($this->IsTransparent) {
            $this->FontAwesomeName .= ' unimportant';
        }

        $this->FontAwesomeName .= ' '.$this->Direction;

        return parent::code();
    }
}

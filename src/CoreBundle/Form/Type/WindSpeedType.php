<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Velocity\Unit\AbstractPaceInDecimalFormatUnit;
use Runalyze\Metrics\Velocity\Unit\KilometerPerHour;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class WindSpeedType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 0;

    /** @var AbstractPaceInDecimalFormatUnit */
    protected $ModelUnit;

    public function __construct(AbstractPaceInDecimalFormatUnit $velocityUnit)
    {
        $this->ModelUnit = new KilometerPerHour();

        parent::__construct($velocityUnit);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr']['min'] = 0;
        $view->vars['attr']['data-base-unit-factor'] = $this->ModelUnit->getDividendFromBaseUnit() / $this->Unit->getDividendFromBaseUnit();
    }

    public function transform($value)
    {
        if (null !== $value) {
            if (0 == (int)$value) {
                return 0;
            }

            $value = $this->ModelUnit->toBaseUnit((int)$value);
        }

        return null === $value ? null : (int)round($this->Unit->fromBaseUnit($value));
    }

    public function reverseTransform($value)
    {
        if (null !== $value) {
            if (0 == (int)$value) {
                return 0;
            }

            $value = $this->Unit->toBaseUnit((int)$value);
        }

        return null === $value ? null : (int)round($this->ModelUnit->fromBaseUnit($value));
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}

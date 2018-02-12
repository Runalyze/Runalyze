<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit;
use Runalyze\Metrics\Temperature\Unit\Fahrenheit;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class TemperatureType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 0;

    public function __construct(AbstractTemperatureUnit $temperatureUnit, $modelPrecision = 0)
    {
        $this->ModelPrecision = $modelPrecision;

        parent::__construct($temperatureUnit);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['attr']['data-is-fahrenheit'] = $this->Unit instanceof Fahrenheit ? '1' : '0';
    }
}

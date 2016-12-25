<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class TemperatureType extends AbstractType implements DataTransformerInterface
{
    /** @var AbstractTemperatureUnit */
    protected $TemperatureUnit;

    public function __construct(AbstractTemperatureUnit $temperatureUnit)
    {
        $this->TemperatureUnit = $temperatureUnit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = $this->TemperatureUnit->getAppendix();
    }

    /**
     * @param  mixed $temperature [°C]
     * @return string
     */
    public function transform($temperature)
    {
        return null === $temperature ? '' : (string)round($this->TemperatureUnit->fromBaseUnit((float)$temperature));
    }

    /**
     * @param  null|string $temperature
     * @return int|null
     */
    public function reverseTransform($temperature)
    {
        return null === $temperature ? null : (int)$this->TemperatureUnit->toBaseUnit($temperature);
    }

    public function getParent()
    {
        return TextType::class;
    }
}

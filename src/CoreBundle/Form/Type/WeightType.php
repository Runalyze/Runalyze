<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Weight\Unit\AbstractWeightUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class WeightType extends AbstractType implements DataTransformerInterface
{
    /** @var AbstractWeightUnit */
    protected $WeightUnit;

    /** @var int */
    protected $ViewPrecision = 1;

    public function __construct(AbstractWeightUnit $weightUnit)
    {
        $this->WeightUnit = $weightUnit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = $this->WeightUnit->getAppendix();
    }

    /**
     * @param  mixed $weight [kg]
     * @return string
     */
    public function transform($weight)
    {
        return null === $weight ? '' : (string)round($this->WeightUnit->fromBaseUnit((float)$weight), $this->ViewPrecision);
    }

    /**
     * @param  null|string $weight
     * @return int|null
     */
    public function reverseTransform($weight)
    {
        return null === $weight ? null : (int)$this->WeightUnit->toBaseUnit($weight);
    }

    public function getParent()
    {
        return TextType::class;
    }
}

<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Common\UnitInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

abstract class AbstractUnitBasedType extends AbstractType implements DataTransformerInterface
{
    /** @var UnitInterface */
    protected $Unit;

    /** @var int */
    protected $ViewPrecision = 1;

    public function __construct(UnitInterface $unit)
    {
        $this->Unit = $unit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['input_unit'] = $this->Unit->getAppendix();
    }

    /**
     * @param  mixed $value
     * @return string
     */
    public function transform($value)
    {
        return null === $value ? '' : number_format($this->Unit->fromBaseUnit((float)$value), $this->ViewPrecision);
    }

    /**
     * @param  null|string $value
     * @return float|null
     */
    public function reverseTransform($value)
    {
        return null === $value ? null : $this->Unit->toBaseUnit((float)str_replace(',', '.', $value));
    }

    public function getParent()
    {
        return TextType::class;
    }
}
